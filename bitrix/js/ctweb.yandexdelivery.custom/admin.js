BX.namespace('BX.Ctweb.YandexDelivery');

(function() {
    'use strict';
    if (BX.Ctweb && BX.Ctweb.YandexDelivery) {

        BX.Ctweb.YandexDelivery.loadScript = function (url, callback_success, callback_error) {
            var script = document.createElement("script");
            script.onload = callback_success;
            script.onerror = callback_error;
            script.src = url;
            document.getElementsByTagName("head")[0].appendChild(script);
        }

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

        BX.Ctweb.YandexDelivery.Editor = {
            regions: {},
            stores: {},
            initBase: function () {
                this.form = document.getElementById('cwEditForm');

                this.lastMapCoord = [55.76, 37.64];
                if (typeof BX.getCookie('cwLastMapPosition') !== 'undefined')
                    this.lastMapCoord = JSON.parse(BX.getCookie('cwLastMapPosition'));

                this.map = new ymaps.Map('map', {
                    center: this.lastMapCoord,
                    zoom: 10
                });

                this.map.controls.remove('zoomControl');
                this.map.controls.remove('trafficControl');
                this.map.controls.remove('typeSelector');
                this.map.controls.remove('fullscreenControl');
                this.map.controls.remove('rulerControl');
                this.map.controls.remove('geolocationControl');

                for (var region in this.regions) {
                    var coords = [[[]]];
                    if (this.regions[region].POINTS.length)
                        coords = [this.regions[region].POINTS];

                    this.regions[region].polygon = new ymaps.Polygon(coords, {
                        balloonContent: this.regions[region].NAME
                    }, {
                        editorDrawingCursor: "crosshair",
                        fillColor: this.regions[region].COLOR,
                        outline: false,
                        opacity: 0.5,
                        strokeWidth: 1
                    });
                    this.map.geoObjects.add(this.regions[region].polygon);

                }

                for (var store in this.stores) {
                    var coords = [];
                    if (this.stores[store].POINT.length)
                        coords = this.stores[store].POINT;

                    if (coords.length) {
                        this.stores[store].point = new ymaps.Placemark(coords, {
                            balloonContent: this.stores[store].NAME
                        }, {preset: 'islands#circleIcon'});
                        this.map.geoObjects.add(this.stores[store].point);
                    }
                }

                var searchControl = this.map.controls.get('searchControl');
                searchControl.events.add('resultshow', function () {
                    searchControl.hideResult();
                }, this);
            },
            initRegionEdit: function (region_id) {
                this.initBase();
                this.current = region_id;

                this.map.setBounds(this.regions[this.current].polygon.geometry.getBounds(), {checkZoomRange: true});
                this.regions[this.current].polygon.editor.startEditing();

                var otherRegions = Object.assign({}, this.regions);
                delete otherRegions[this.current];
                otherRegions = Object.values(otherRegions);

                // Show/hide others
                var cbShowHideOtherRegions = BX('show_regions') || null;
                if (cbShowHideOtherRegions) {
                    BX.bind(cbShowHideOtherRegions, 'change', BX.proxy(function (e) {
                        if (e.target.checked) {
                            otherRegions.forEach(function (p) {
                                p.polygon.options.set({opacity: 0.5});
                            });
                        } else {
                            otherRegions.forEach(function (p) {
                                console.log(p);
                                p.polygon.options.set({opacity: 0});
                            });
                        }
                    }, this));
                }

                BX.setCookie('cwLastMapPosition', JSON.stringify(this.map.getCenter()), {expires: 86400});
            },
            initStoreEdit: function (store_id) {
                this.initBase();
                this.current = store_id;

                if (this.stores[this.current].point)
                    this.stores[this.current].point.options.set({draggable: true, preset: 'islands#icon'});
                else {
                    this.stores[this.current].point = new ymaps.Placemark(this.lastMapCoord, {}, {draggable: true});
                    this.stores[this.current].POINT = JSON.stringify(this.lastMapCoord);
                    this.map.geoObjects.add(this.stores[this.current].point);
                }
                this.map.setCenter(this.stores[this.current].point.geometry.getCoordinates());

                BX.setCookie('cwLastMapPosition', JSON.stringify(this.map.getCenter()), {expires: 86400});
            },
            importRegion: function (coords = []) {
                this.deletePoly();
                this.regions[this.current].polygon.geometry.setCoordinates([coords]);
                this.map.setBounds(this.regions[this.current].polygon.geometry.getBounds(), {checkZoomRange: true});
            },
            setPoint: function () {
                var self = BX.Ctweb.YandexDelivery.Editor;
                if (self.stores[self.current].point) {
                    self.stores[self.current].point.geometry.setCoordinates(self.map.getCenter());
                } else {
                    self.stores[self.current].point = new ymaps.Placemark(self.map.getCenter(), {}, {
                        draggable: true,
                        preset: 'islands#icon'
                    });
                    self.map.geoObjects.add(self.stores[self.current].point);
                }
            },
            createPoly: function () {
                var quad = this.map.getBounds();

                this.deletePoly();

                quad[0] = [quad[0][0] + (quad[1][0] - quad[0][0]) / 4, quad[0][1] + (quad[1][1] - quad[0][1]) / 4];
                quad[1] = [quad[1][0] - (quad[1][0] - quad[0][0]) / 4, quad[1][1] - (quad[1][1] - quad[0][1]) / 4];
                quad = [[
                    [quad[0][0], quad[0][1]],
                    [quad[0][0], quad[1][1]],
                    [quad[1][0], quad[1][1]],
                    [quad[1][0], quad[0][1]]
                ]];
                this.regions[this.current].polygon.geometry.setCoordinates(quad);
            },
            deletePoly: function () {
                this.regions[this.current].polygon.geometry.setCoordinates([]);
            },
            prepareRegionDelete: function() {
                var tmp = document.createElement('input');
                tmp.type = 'hidden';
                tmp.name = 'DELETE';
                tmp.value = 'Y';

                this.form.appendChild(tmp);
            },
            prepareStoreDelete: function() {
                var tmp = document.createElement('input');
                tmp.type = 'hidden';
                tmp.name = 'DELETE';
                tmp.value = 'Y';

                this.form.appendChild(tmp);
            },
            prepareRegionSave: function () {
                var tmp = document.createElement('input');
                tmp.type = 'hidden';
                tmp.name = 'REGION[POINTS]';
                tmp.value = JSON.stringify(this.regions[this.current].polygon.geometry.getCoordinates()[0]);

                this.form.appendChild(tmp);
            },
            prepareStoreSave: function() {
                var tmp = document.createElement('input');
                tmp.type = 'hidden';
                tmp.name = 'STORE[POINT]';
                tmp.value = JSON.stringify(this.stores[this.current].point.geometry.getCoordinates());

                this.form.appendChild(tmp);
            }
        };
    }
})();