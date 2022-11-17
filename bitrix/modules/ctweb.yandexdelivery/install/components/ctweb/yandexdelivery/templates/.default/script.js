var cwYandexDeliveryController = {
    regionOpacity: 0.2,
    defaultZoom: 10,
    routeColor: '4A84E3ff',
    routeOpacity: 0.8,
    initMap: function (centerCoord) {
        // create map
        this.map = new ymaps.Map('cwYandexDeliveryMap', {
            center: centerCoord,
            zoom: this.defaultZoom,
        });
        // turn off controls
        this.map.controls.remove('zoomControl');
        this.map.controls.remove('trafficControl');
        this.map.controls.remove('typeSelector');
        this.map.controls.remove('fullscreenControl');
        this.map.controls.remove('rulerControl');
        this.map.controls.remove('geolocationControl');
        this.map.balloon.close();

        // set cursor
        this.map.cursors.push('arrow');

        var self = this;
        this.map.events.add('dblclick', function (e) {
            e.preventDefault();

            self.currentPoint = e.get('coords');

            self.calculateRoute();
        });

        for (var region in this.regions) {
            var coords = JSON.parse(this.regions[region].COORDS);
            this.regions[region].polygon = new ymaps.Polygon(coords, {}, {
                fillColor: this.regions[region].COLOR,
                outline: false,
                opacity: this.regionOpacity,
                strokeWidth: 1
            });
            this.map.geoObjects.add(this.regions[region].polygon);

        }
        for (var store in this.stores) {
            var coords = JSON.parse(this.stores[store].COORDS);
            if (coords.length > 1) {
                this.stores[store].point = new ymaps.Placemark(coords, {
                    balloonContent: this.stores[store].NAME
                }, {preset: 'islands#circleIcon'});
                this.map.geoObjects.add(this.stores[store].point);
            }
        }

        var searchControl = this.map.controls.get('searchControl');
        searchControl.events.add('resultselect', function () {
            searchControl.hideResult();
        }, this);
    },
    init: function (options) {
        var self = this;
        for (var i in options) {
            if (!(['regionOpacity', 'defaultZoom', 'routeColor', 'routeColor', 'routeOpacity'].indexOf(i) < 0))
                this[i] = options[i];
        }
        this.modal = document.getElementById('cwYandexDeliveryModal');
        this.modal.querySelector('.dark-body').onclick = this.actionClose;
        document.onkeydown = function (evt) {
            evt = evt || window.event;
            if (evt.keyCode == 27) {
                self.actionClose();
            }
        };
        this.calcContainer = this.modal.querySelector('.calculates');
        if (document.getElementById('cwYandexDeliveryLink'))
            document.getElementById('cwYandexDeliveryLink').style.display = 'inline';

        var lastMapCoord = [55.76, 37.64];
        if (typeof BX.getCookie('cwLastMapPosition') !== 'undefined')
            lastMapCoord = JSON.parse(BX.getCookie('cwLastMapPosition'));
        this.initMap(lastMapCoord);

        ymaps.geolocation.get({
            provider: 'yandex',
            mapStateAutoApply: true
        }).then(function (res) {
            self.map.setCenter(res.geoObjects.position);
        });

        this.modal.querySelector('.footer button.choose').onclick = this.actionChoose;
        this.modal.querySelector('.footer button.cancel').onclick = this.actionClose;
    },
    actionChoose: function () {
        var self = cwYandexDeliveryController;
        if (self.lastRoute) {
            var params = {
                pointTo: self.currentPoint,
                calculated: {
                    distance: self.lastRoute.getLength(),
                    storeID: self.store,
                    regionID: self.region,
                    address: self.currentAddress

                },
                route: {
                    requestedPoints: self.lastRoute.requestPoints
                }
            };
            $.ajax({
                url: '/bitrix/js/ctweb.yandexdelivery/ajax.php',
                type: 'POST',
                dataType: 'JSON',
                data: {save_point: params},
                success: function () {
                    self.checkSelectPoint();
                    BX.Sale.OrderAjaxComponent.sendRequest();
                }
            });
        } else {
            $.ajax({
                url: '/bitrix/js/ctweb.yandexdelivery/ajax.php',
                type: 'POST',
                dataType: 'JSON',
                data: {remove_point: 1},
                success: function () {
                    self.checkSelectPoint();
                    BX.Sale.OrderAjaxComponent.sendRequest();
                }
            });
        }
        self.close();
    },
    actionClose: function () {
        var self = cwYandexDeliveryController;
        if (typeof self.currentPoint === 'undefined') {
            $.ajax({
                url: '/bitrix/js/ctweb.yandexdelivery/ajax.php',
                type: 'POST',
                dataType: 'JSON',
                data: {remove_point: 1},
                success: function () {
                    self.checkSelectPoint();
                    BX.Sale.OrderAjaxComponent.sendRequest();
                }
            });
        }
        self.close();
    },
    calculateResult: function () {
        if (this.lastRoute) {
            return parseFloat(this.regions[this.region].PRICE) * this.lastRoute.getLength() / 1000 + parseFloat(this.regions[this.region].MINPRICE);
        } else {
            return false;
        }
    },
    spinner: function (bOn) {
        if (bOn === true)
            this.modal.querySelector('.body .loader').style.display = 'block';
        else
            this.modal.querySelector('.body .loader').style.display = 'none';
    },
    calculateRoute: function () {
        this.calcContainer.innerHTML = "";
        this.map.balloon.close();

        if (this.lastRoute) {
            this.map.geoObjects.remove(this.lastRoute);
            delete this.lastRoute;
        }

        this.region = false;
        for (var i in this.regions) {
            if (this.regions[i].polygon.geometry.contains(this.currentPoint)) {
                this.region = i;
                break;
            }
        }
        if (this.region === false) {
            if (this.path) {
                this.map.geoObjects.remove(this.path);
                delete this.path;
            }
            if (this.currentPoint) {
                // this.calcContainer.innerHTML = BX.message('CW_YD_NO_POINT');
                this.map.balloon.open(this.currentPoint, BX.message('CW_YD_NO_POINT'), {
                    // Опция: не показываем кнопку закрытия.
                    closeButton: false
                });
                delete this.currentPoint;
            }

            return false;
        }

        // turn on spinner
        this.spinner(true);

        var stores = Object.keys(this.stores);
        if (stores.length === 0) {
            if (this.path) {
                this.map.geoObjects.remove(this.path);
                delete this.path;
            }
            this.spinner(false);
            console.log('no stores');
            return false;
        }

        // уберу дальние точки
        var min_distance = null,
            stores_distance = {};
        for (var i = 0; i < stores.length; i++) {
            var dis = this.distance(this.currentPoint, JSON.parse(this.stores[stores[i]].COORDS));
            stores_distance[stores[i]] = dis;
            if (!min_distance || dis < min_distance) {
                min_distance = dis;
            }
        }
        for (var i = stores.length - 1; i >= 0; i--) {
            if (stores_distance[stores[i]] > min_distance * 2)
                stores.splice(i, 1);
        }

        var self = this;
        this.loadRoutes(stores)
            .then(function (res) {
                self.store = false;
                for (var i in stores) {
                    if (self.store === false) {
                        self.store = stores[i];
                    } else {
                        if (self.stores[self.store].route.getLength() > self.stores[stores[i]].route.getLength()) {
                            self.store = stores[i];
                        }
                    }
                }

                self.map.geoObjects.add(self.stores[self.store].route);
                self.lastRoute = self.stores[self.store].route;

                self.calcContainer.innerHTML = '';
                ymaps.geocode(self.currentPoint).then(function (res) {
                    var firstGeoObject = res.geoObjects.get(0),
                        points = self.lastRoute.getWayPoints(),
                        lastPoint = points.getLength() - 1;
                    self.currentAddress = firstGeoObject.getAddressLine();
                    self.lastRoute.getWayPoints().get(lastPoint).properties.set('balloonContent', self.currentAddress);
                    self.lastRoute.getWayPoints().get(0).properties.set('balloonContent', self.stores[self.store].NAME);

                    self.calcContainer.innerHTML = self.stores[self.store].NAME + ' > ' + self.currentAddress + "<br>" + self.calcContainer.innerHTML;
                });

                self.calcContainer.innerHTML += BX.message('CW_YD_DISTANCE') + ': <b>' + self.lastRoute.getHumanLength() + '</b>; ' + BX.message('CW_YD_PRICE') + ': <b>' + self.calculateResult().toFixed(0) + ' ' + BX.message('CW_YD_RUB') + '</b>';
                self.spinner(false);
            })
            .catch(function (error) {
                self.spinner(false);
                self.calcContainer.innerHTML = BX.message('CW_YD_CALCULATION_FAIL');
            });

    },
    distance: function (point1, point2) {
        var xs = point2[0] - point1[0],
            ys = point2[1] - point1[1];

        xs *= xs;
        ys *= ys;

        return Math.sqrt(xs + ys);
    },
    getRoute: function (store_id) {
        var self = cwYandexDeliveryController;
        return new Promise(function (resolve, reject) {
            ymaps.route([self.stores[store_id].point.geometry.getCoordinates(), self.currentPoint])
                .then(function (route) {
                    route.getPaths().options.set({
                        strokeColor: self.routeColor,
                        opacity: self.routeOpacity
                    });
                    var points = route.getWayPoints(),
                        lastPoint = points.getLength() - 1;
                    points.get(0).options.set('preset', 'islands#circleIcon');
                    points.get(0).properties.unset('iconContent');
                    points.get(lastPoint).options.set('preset', 'twirl#truckIcon');
                    points.get(lastPoint).properties.unset('iconContent');
                    self.stores[store_id].route = route;
                    return resolve(store_id);
                });
        });
    },
    loadRoutes: function (storesList) {
        var promiseRoutes = storesList.map(this.getRoute);

        return promiseRoutes.reduce(function (prevPromise, curPromise) {
            return prevPromise
                .then(function () {
                    return curPromise;
                })
                .then(function (store_id) {
                    return Promise.resolve(store_id);
                });
        }, Promise.resolve());
    },
    open: function () {
        this.modal.style.opacity = '1';
        this.modal.style['z-index'] = '9999';
        this.modal.style.position = 'inherit';
    },
    close: function () {
        this.modal.style.opacity = '0';
        this.modal.style['z-index'] = '-999';
        this.modal.style.position = 'absolute';

        BX.setCookie('cwLastMapPosition', JSON.stringify(this.map.getCenter()), {expires: 86400});
    },
    afterFormReload: function (e) {
        if (e.order !== undefined) {
            e.order.DELIVERY.forEach(function (item, i, arr) {
                if (item.CHECKED !== undefined) {
                    cwYandexDeliveryController.checkPoint(item.ID)
                }
            });
        } else {
            return false;
        }
    },
    checkSelectPoint: function () {
        var self = this;

        if (!this._supported) {
            if (typeof (BX.Sale.OrderAjaxComponent.showError) === 'function') {
                BX.Sale.OrderAjaxComponent.showError(BX.Sale.OrderAjaxComponent.deliveryBlockNode, BX.message('CW_YD_BROWSER_NOT_SUPPORTED'));
            }
        } else {
            $.ajax({
                    url: '/bitrix/js/ctweb.yandexdelivery/ajax.php',
                    type: 'POST',
                    dataType: 'JSON',
                    data: {check_point: 1},
                    success: function (selected) {
                        document.getElementById('bx-soa-orderSave').querySelector('a').style.display = 'block';
                        cwyd_errors = [];
                        if (selected !== true) {
                            document.getElementById('bx-soa-orderSave').querySelector('a').style.display = 'none';
                            cwyd_errors[0] = BX.message('CW_YD_NO_POINT_ERROR');
                            if (typeof (BX.Sale.OrderAjaxComponent.showError) === 'function') {
                                BX.Sale.OrderAjaxComponent.showWarnings(BX.Sale.OrderAjaxComponent.deliveryBlockNode, cwyd_errors[0]);
                            }
                        }

                        for (var i in BX.Sale.OrderAjaxComponent.result.DELIVERY) {
                            if (BX.Sale.OrderAjaxComponent.result.DELIVERY[i].ID == self.delivery_id)
                                if (BX.Sale.OrderAjaxComponent.result.DELIVERY[i].CALCULATE_ERRORS && BX.Sale.OrderAjaxComponent.result.DELIVERY[i].CALCULATE_ERRORS.length)
                                    cwyd_errors[0] = BX.Sale.OrderAjaxComponent.result.DELIVERY[i].CALCULATE_ERRORS;
                        }
                        if (typeof (BX.Sale.OrderAjaxComponent.showBlockErrors) === 'function') {
                            BX.Sale.OrderAjaxComponent.result.ERROR.DELIVERY = cwyd_errors;
                            BX.Sale.OrderAjaxComponent.showBlockErrors(BX.Sale.OrderAjaxComponent.deliveryBlockNode);
                            if (BX.Sale.OrderAjaxComponent.deliveryBlockNode.lastChild.querySelector('.alert.alert-warning.alert-show'))
                                BX.Sale.OrderAjaxComponent.deliveryBlockNode.lastChild.querySelector('.alert.alert-warning.alert-show').remove();
                        }
                        else if (typeof (BX.Sale.OrderAjaxComponent.showError) === 'function' && cwyd_errors.length > 0) {
                            BX.Sale.OrderAjaxComponent.showError(BX.Sale.OrderAjaxComponent.deliveryBlockNode, cwyd_errors[0]);
                        }

                        var delivery_link = document.getElementById('cwYandexDeliveryLink');
                        if (self.mapLoaded === true && delivery_link) {
                            document.getElementById('cwYandexDeliveryLink').classList.remove("hidden");
                        } else if (delivery_link) {
                            document.getElementById('cwYandexDeliveryLink').classList.add("hidden");
                            ymaps.ready(function () {
                                document.getElementById('cwYandexDeliveryLink').classList.remove("hidden");
                                self.mapLoaded = true;
                            });
                        }
                    }
                }
            );
        }
    },
    checkPoint: function (delivery_id) {
        if (delivery_id == this.delivery_id) {
            this.checkSelectPoint();
        }

    }
};