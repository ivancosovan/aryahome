BX.namespace('BX.Ctweb.YandexDelivery');

(function () {
	'use strict';
	if (BX.Ctweb && BX.Ctweb.YandexDelivery) {
		BX.Ctweb.YandexDelivery.loadScript = function (url, callback_success, callback_error) {
			var script = document.createElement("script");
			script.src = url;
			document.getElementsByTagName("head")[0].appendChild(script);
			script = BX(script);
			BX.bind(script, 'load', callback_success);
			BX.bind(script, 'error', callback_error);

			BX.Ctweb.YandexDelivery.loadScript = function (url, callback_success, callback_error) {
				BX.bind(script, 'load', callback_success);
				BX.bind(script, 'error', callback_error);
			}
		};

		BX.Ctweb.YandexDelivery.loadymaps = function (params, callback) {
			if (window.ymaps) {
				// already loaded and ready to go
				ymaps.vow.Promise.resolve().then(callback);
			} else {
				params = Object.assign({
					'lang': 'ru_RU'
				}, (params instanceof Object ? params : {}));

				var str = [];
				for (var i in params) {
					if (params.hasOwnProperty(i)) {
						str.push(encodeURIComponent(i) + '=' + encodeURIComponent(params[i]));
					}
				}
				var queryString = str.join('&');

				BX.Ctweb.YandexDelivery.loadScript('https://api-maps.yandex.ru/2.1/?' + queryString, callback);
			}
		};

		BX.Ctweb.YandexDelivery.Controller = function (params) {
			this.params = params;

			this.regionOpacity = 0.3;
			this.defaultZoom = 10;
			this.routeColor = '4A84E3ff';
			this.routeOpacity = 0.8;
			this.defaultCenter = [55.76, 37.64];

			this.obMap = null;
			this.obAddress = null;
			this.obDistance = null;
			this.obPrice = null;
			this.obSpinner = null;

			this.arRegions = [];
			this.arStores = [];
			this.arOrder = null;

			this.arMessages = {};


			this.Init = function () {
				this.obMap = BX(this.params['TEMPLATE']['MAP']);
				this.obAddress = BX(this.params['TEMPLATE']['ADDRESS']);
				this.obDistance = BX(this.params['TEMPLATE']['DISTANCE']);
				this.obPrice = BX(this.params['TEMPLATE']['PRICE']);
				this.obSpinner = BX(this.params['TEMPLATE']['SPINNER']);

				this.regionOpacity = this.params['OPTIONS']['REGION_OPACITY'] || this.regionOpacity;
				this.defaultZoom = this.params['OPTIONS']['DEFAULT_ZOOM'] || this.defaultZoom;
				this.routeColor = this.params['OPTIONS']['ROUTE_COLOR'] || this.routeColor;
				this.routeOpacity = this.params['OPTIONS']['ROUTE_OPACITY'] || this.routeOpacity;
				this.defaultCenter = this.params['OPTIONS']['DEFAULT_CENTER_POINT'] || this.defaultCenter;

				this.arRegions = this.params['DATA']['REGIONS'] || this.arRegions;
				this.arStores = this.params['DATA']['STORES'] || this.arStores;
				this.arOrder = this.params['DATA']['ORDER'] || this.arOrder;

				this.arMessages = this.params['MESSAGES'] || this.arMessages;

				this.InitMap();
			}

			this.InitMap = function () {
				var _this = this;

				// create map
				this.obMap.map = new ymaps.Map(this.obMap.id, {
					center: this.defaultCenter,
					zoom: this.defaultZoom,
				});

				// options
				this.obMap.map.controls.remove('trafficControl');
				this.obMap.map.controls.remove('typeSelector');
				this.obMap.map.controls.remove('fullscreenControl');
				this.obMap.map.controls.remove('rulerControl');
				// this.obMap.map.controls.remove('geolocationControl');

				this.obMap.map.balloon.close();
				this.obMap.map.cursors.push('arrow');

				switch (this.params['MODULE_OPTIONS']['FIELD_MAP_CLICK_BEHAVIOR']) {
					case 'click': {
						this.obMap.map.events.add('click', function (e) {
							e.preventDefault();
							_this.currentPoint = e.get('coords');
							_this.calculateRoute();
						});
						break;
					}
					case 'dblclick':
					default: {
						this.obMap.map.events.add('dblclick', function (e) {
							e.preventDefault();
							_this.currentPoint = e.get('coords');
							_this.calculateRoute();
						});
						break;
					}
				}

				// Create regions
				for (var i = 0; i < this.arRegions.length; i++) {
					var coords = [[[]]];
					if (this.arRegions[i].POINTS.length)
						coords = [this.arRegions[i].POINTS];

					this.arRegions[i].polygon = new ymaps.Polygon(coords, {}, {
						fillColor: this.arRegions[i].COLOR,
						outline: false,
						opacity: this.regionOpacity,
						strokeWidth: 1
					});

					if (this.params['MODULE_OPTIONS']['FIELD_MAP_CLICK_BEHAVIOR'] === 'click') {
						this.arRegions[i].polygon.events.add('dblclick', function (e) {
							e.preventDefault();
						});
						this.arRegions[i].polygon.events.add('click', BX.debounce(function (e) {
							e.preventDefault();
							_this.currentPoint = e.get('coords');
							_this.calculateRoute();
						}, 200));
					}

					this.obMap.map.geoObjects.add(this.arRegions[i].polygon);
				}

				// Create stores
				for (var i = 0; i < this.arStores.length; i++) {
					var coords = [];
					if (this.arStores[i].POINT.length)
						coords = this.arStores[i].POINT;

					if (coords.length) {
						this.arStores[i].point = new ymaps.Placemark(coords, {
							balloonContent: this.arStores[i].NAME
						}, {preset: 'islands#circleIcon'});
						this.obMap.map.geoObjects.add(this.arStores[i].point);
					}
				}

				// Enable calculating route on user addres input
				var searchControl = this.obMap.map.controls.get('searchControl');
                searchControl.events.add('resultselect', function (e) {
					searchControl.hideResult();

                    e.preventDefault();

                    var coords = searchControl.getResultsArray()[e.get('index')].geometry.getCoordinates();

                    _this.currentPoint = coords;
                    _this.calculateRoute();
				}, this);

				BX.onCustomEvent('yandexdelivery.initialized', [this]);
			}

			this.Init();
		}

		BX.Ctweb.YandexDelivery.Controller.prototype.GetMessage = function (key, params) {
			if (typeof key === 'string' && key.length && key in this.arMessages && typeof this.arMessages[key] === 'string') {
				var res = this.arMessages[key];

				if (!!params && typeof params === 'object') {
					for (var word in params) {

						var regex = new RegExp("#" + word + "#", 'g');
						res = res.replace(regex, params[word]);
					}
				}
				return res;
			} else {
				return '';
			}
		}

		BX.Ctweb.YandexDelivery.Controller.prototype.CenterToStore = function (store_id) {
			var store = this.arStores.find(function (e) {
				return e.ID == store_id;
			}, this);

			if (!!store)
				this.obMap.map.setCenter(store.POINT);
		}

		BX.Ctweb.YandexDelivery.Controller.prototype.distance = function (point1, point2) {
			var xs = point2[0] - point1[0],
				ys = point2[1] - point1[1];

			xs *= xs;
			ys *= ys;

			return Math.sqrt(xs + ys);
		};
		BX.Ctweb.YandexDelivery.Controller.prototype.getRoute = function (store_id) {
			var _this = this,
				store = _this.arStores.find(function (e) {
					return e.ID === store_id
				});

			return new ymaps.vow.Promise(function (resolve, reject) {
				ymaps.route([store.point.geometry.getCoordinates(), _this.currentPoint])
					.then(function (route) {
						route.getPaths().options.set({
							strokeColor: _this.routeColor,
							opacity: _this.routeOpacity
						});
						var points = route.getWayPoints(),
							lastPoint = points.getLength() - 1;
						points.get(0).options.set('preset', 'islands#circleIcon');
						points.get(0).properties.unset('iconContent');
						points.get(lastPoint).options.set('preset', 'twirl#truckIcon');
						points.get(lastPoint).properties.unset('iconContent');
						store.route = route;

						return resolve(store_id);
					});
			});
		};
		BX.Ctweb.YandexDelivery.Controller.prototype.loadRoutes = function (storesList) {
			var promiseRoutes = storesList.map(this.getRoute, this);
			return promiseRoutes.reduce(function (prevPromise, curPromise) {
				return prevPromise
					.then(function () {
						return curPromise;
					})
					.then(function (store_id) {
						return ymaps.vow.Promise.resolve(store_id);
					});
			}, ymaps.vow.Promise.resolve());
		};
		BX.Ctweb.YandexDelivery.Controller.prototype.calculateRoute = function () {
			var _this = this;
			this.obMap.map.balloon.close();

			if (this.lastRoute) {
				this.obMap.map.geoObjects.remove(this.lastRoute);
				delete this.lastRoute;
			}

			this.region = false;
			for (var i = 0; i < this.arRegions.length; i++) {
				if (this.arRegions[i].polygon.geometry.contains(this.currentPoint)) {
					this.region = this.arRegions[i];
					break;
				}
			}


			if (this.region === false) {
				if (this.path) {
					this.obMap.map.geoObjects.remove(this.path);
					delete this.path;
				}
				if (this.currentPoint) {
					this.obMap.map.balloon.open(this.currentPoint,
						this.GetMessage('POINT_NO_DELIVERY'),
						{
							closeButton: false
						}
					);
					delete this.currentPoint;
				}

				return false;
			} else if (this.region && this.arOrder && this.arOrder.PRICE < this.region.PRICE_MIN) {
				if (this.path) {
					this.obMap.map.geoObjects.remove(this.path);
					delete this.path;
				}
				if (this.currentPoint) {
					this.obMap.map.balloon.open(this.currentPoint,
						this.GetMessage('NOT_ENOUGH_PRICE', {
							'PRICE': [this.arOrder.PRICE, this.arOrder.CURRENCY].join(' '),
							'PRICE_MIN': [this.region.PRICE_MIN, this.arOrder.CURRENCY].join(' '),
							'PRICE_DIFF': [(this.region.PRICE_MIN - this.arOrder.PRICE).toFixed(2), this.arOrder.CURRENCY].join(' ')
						}),
						{
							closeButton: true
						}
					);

					delete this.currentPoint;
				}
				return false;
			}


			// turn on spinner
			this.spinner(true);

			var stores = this.arStores.reduce(function (a, c) {
				return a.concat(c.ID);
			}, []);

			if (this.region.STORES.length > 0) {
				stores = stores.filter(function (value) {
					return -1 !== _this.region.STORES.indexOf(parseInt(value));
				});
			}

			if (stores.length === 0) {
				if (this.path) {
					this.obMap.map.geoObjects.remove(this.path);
					delete this.path;
				}
				this.spinner(false);
				console.log('no stores');
				return false;
			}

			// filter stores for nearest
			var min_distance = null,
				stores_distance = {};

			for (var i = 0; i < stores.length; i++) {
				var dis = this.distance(this.currentPoint, this.arStores.find(function (e) {
					return e.ID === stores[i];
				}).POINT);
				stores_distance[stores[i]] = dis;
				min_distance = Math.min(min_distance, dis) || dis;
			}

			for (var i = stores.length - 1; i >= 0; i--) {
				if (stores_distance[stores[i]] > min_distance * 2)
					stores.splice(i, 1);
			}

			this.loadRoutes(stores)
				.then(function (res) {
					_this.store = false;

					for (var i = 0; i < stores.length; i++) {
						var s = _this.arStores.find(function (e) {
							return e.ID === stores[i];
						});
						if (_this.store === false) {
							_this.store = s;
						} else {
							if (_this.store.route.getLength() > s.route.getLength()) {
								_this.store = s;
							}
						}
					}

					_this.obMap.map.geoObjects.add(_this.store.route);
					_this.lastRoute = _this.store.route;

					ymaps.geocode(_this.currentPoint).then(function (res) {
						var firstGeoObject = res.geoObjects.get(0),
							points = _this.lastRoute.getWayPoints(),
							lastPoint = points.getLength() - 1;
						_this.currentAddress = firstGeoObject.getAddressLine();

						_this.lastRoute.getWayPoints().get(lastPoint).properties.set('balloonContent', _this.currentAddress);
						_this.lastRoute.getWayPoints().get(0).properties.set('balloonContent', _this.store.NAME);

						if (_this.obAddress)
							_this.obAddress.innerHTML = _this.currentAddress;

						BX.onCustomEvent(this, 'yandexdelivery.address_response', [{ADDRESS: _this.currentAddress}]);

					});

					var calc = _this.calculateResult();
					calc.DISTANCE = _this.lastRoute.getHumanLength();

					if (_this.obPrice)
						_this.obPrice.innerHTML = calc.PRICE_FORMATTED;

					if (_this.obDistance)
						_this.obDistance.innerHTML = calc.DISTANCE;

					// event
					BX.onCustomEvent(this, 'yandexdelivery.calculate', [{result: calc}]);

					_this.spinner(false);
				})
				.catch(function (error) {
					_this.spinner(false);

					// event
					BX.onCustomEvent(this, 'yandexdelivery.calculate', [{error: error}]);
				});

		};
		BX.Ctweb.YandexDelivery.Controller.prototype.spinner = function (bOn) {
			if (this.obSpinner) {
				if (bOn === true)
					BX.adjust(this.obSpinner, {style: {display: 'block'}});
				else
					BX.adjust(this.obSpinner, {style: {display: 'none'}});
			}
		};
		BX.Ctweb.YandexDelivery.Controller.prototype.calculateResult = function () {
			var price = {};

			if (this.lastRoute && this.region && this.store) {

				var data = {
					action: 'calculate',
					order_price: this.arOrder ? this.arOrder.PRICE : 0,
					region_id: this.region.ID,
					store_id: this.store.ID,
					distance: this.lastRoute.getLength()
				};

				BX.ajax({
					url: '/bitrix/js/ctweb.yandexdelivery/ajax.php',
					method: 'POST',
					dataType: 'json',
					async: false,
					data: data,
					onsuccess: function (result) {

						price = result;
					}
				});
			}
			return price;
		};

		BX.Ctweb.YandexDelivery.Controller.prototype.getNearestStore = function (point) {
			var _this = this;
			var nearestStore = _this.arStores.reduce(function (a, c, i, s) {

				if (_this.distance(point, c.POINT) < _this.distance(point, s[a].POINT))
					return i;
				else
					return a;
			}, 0);

			return _this.arStores[nearestStore];
		};
	}
})();