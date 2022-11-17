BX.namespace('BX.Ctweb.YandexDelivery');

(function() {
    'use strict';
    if (BX.Ctweb && BX.Ctweb.YandexDelivery && BX.Ctweb.YandexDelivery.Controller) {

        if (!('remove' in HTMLDivElement.prototype)) {
            HTMLDivElement.prototype.remove = function() {
                this.parentNode.removeChild(this);
            };
        }

        // Modal initialization
        BX.Ctweb.YandexDelivery.Controller.init = function (options) {
            var self = this;
            for (var i in options) {
                if (!(['regionOpacity', 'defaultZoom', 'routeColor', 'routeColor', 'routeOpacity'].indexOf(i) < 0))
                    this[i] = options[i] || this[i];
            }

            this.container = document.querySelector('.ctweb-yandexdelivery');


            this.modal = document.getElementById('cwYandexDeliveryModal');
            this.modal.querySelector('.dark-body').onclick = this.actionClose;
            document.onkeydown = function (evt) {
                evt = evt || window.event;
                if (evt.keyCode == 27) {
                    self.actionClose();
                }
            };

            this.calculateText = "";
            this.calcContainer = this.modal.querySelector('.calculates');
            if (document.getElementById('cwYandexDeliveryLink'))
                document.getElementById('cwYandexDeliveryLink').style.display = 'inline';

            var lastMapCoord = [55.76, 37.64];
            if (typeof BX.getCookie('CW_YD_LAST_COORD') !== 'undefined')
                lastMapCoord = JSON.parse(BX.getCookie('CW_YD_LAST_COORD'));

            this.initMap(lastMapCoord);

            ymaps.geolocation.get({
                provider: 'yandex',
                mapStateAutoApply: true
            }).then(function (res) {
                self.map.setCenter(res.geoObjects.position);
            });

            this.modal.querySelector('.footer button.choose').onclick = this.actionChoose;
            this.modal.querySelector('.footer button.cancel').onclick = this.actionClose;
        };

        BX.Ctweb.YandexDelivery.Controller.actionChoose = function () {
            var self = BX.Ctweb.YandexDelivery.Controller;
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
                BX.ajax({
                    url: '/bitrix/js/ctweb.yandexdelivery/ajax.php',
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'save_point',
                        point: params
                    },
                    onsuccess: function () {
                        self.checkSelectPoint();
                        BX.Sale.OrderAjaxComponent.sendRequest();
                    }
                });
            } else {
                BX.ajax({
                    url: '/bitrix/js/ctweb.yandexdelivery/ajax.php',
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'remove_point'
                    },
                    onsuccess: function () {
                        self.checkSelectPoint();
                        BX.Sale.OrderAjaxComponent.sendRequest();
                    }
                });
            }
            self.close();
        };

        BX.Ctweb.YandexDelivery.Controller.actionClose = function () {
            var self = BX.Ctweb.YandexDelivery.Controller;
            if (typeof self.currentPoint === 'undefined') {
                BX.ajax({
                    url: '/bitrix/js/ctweb.yandexdelivery/ajax.php',
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'remove_point'
                    },
                    success: function () {
                        self.checkSelectPoint();
                        BX.Sale.OrderAjaxComponent.sendRequest();
                    }
                });
            }
            self.close();
        };

        BX.Ctweb.YandexDelivery.Controller.open = function () {
            this.modal.style.opacity = '1';
            this.modal.style['z-index'] = '9999';
            this.modal.style.position = 'inherit';
        };

        BX.Ctweb.YandexDelivery.Controller.close = function () {
            this.modal.style.opacity = '0';
            this.modal.style['z-index'] = '-999';
            this.modal.style.position = 'absolute';

            BX.setCookie('cwLastMapPosition', JSON.stringify(this.map.getCenter()), {expires: 86400});
        };

        BX.Ctweb.YandexDelivery.Controller.afterFormReload = function (a,b,c) {
            var e = a || c || {};
            if (e.order !== undefined) {
                e.order.DELIVERY.forEach(function (item, i, arr) {
                    if (item.CHECKED !== undefined) {
                        BX.Ctweb.YandexDelivery.Controller.checkPoint(item.ID)
                    }
                });
            } else {
                return false;
            }
        };

        BX.Ctweb.YandexDelivery.Controller.checkSelectPoint = function (delivery_id) {
            var self = this;

            BX.ajax({
                    url: '/bitrix/js/ctweb.yandexdelivery/ajax.php',
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'check_point'
                    },
                    onsuccess: function (selected) {
                        document.getElementById('bx-soa-orderSave').querySelector('a').style.display = 'block';
                        var cwyd_errors = [];
                        if (selected !== true) {
                            document.getElementById('bx-soa-orderSave').querySelector('a').style.display = 'none';
                            cwyd_errors[0] = BX.message('CW_YD_NO_POINT_ERROR');
                            if (typeof (BX.Sale.OrderAjaxComponent.showError) === 'function') {
                                BX.Sale.OrderAjaxComponent.showWarnings(BX.Sale.OrderAjaxComponent.deliveryBlockNode, cwyd_errors[0]);
                            }
                        }

                        for (var i in BX.Sale.OrderAjaxComponent.result.DELIVERY) {
                            if (BX.Sale.OrderAjaxComponent.result.DELIVERY[i].ID == delivery_id)
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

        };

        BX.Ctweb.YandexDelivery.Controller.checkPoint = function (delivery_id) {
            if (this.delivery_id.indexOf(parseInt(delivery_id)) >= 0)
                this.checkSelectPoint(delivery_id);
        };
    }
})();