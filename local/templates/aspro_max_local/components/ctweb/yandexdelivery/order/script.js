/**
 * 1.4.8: Change extending to inheritance. Controller now is OrderController here.
 */
BX.namespace('BX.Ctweb.YandexDelivery');

(function() {
    'use strict';
    if (BX.Ctweb && BX.Ctweb.YandexDelivery && BX.Ctweb.YandexDelivery.Controller) {

        if (!('remove' in HTMLDivElement.prototype)) {
            HTMLDivElement.prototype.remove = function() {
                this.parentNode.removeChild(this);
            };
        }

        BX.Ctweb.YandexDelivery.OrderController = function(params) {
            BX.Ctweb.YandexDelivery.Controller.call(this, params);

            this.duplicateKey = Symbol;
            this.duplicateOriginalKey = Symbol;

            this.OrderButtonBehavior = params.MODULE_OPTIONS.FIELD_ORDER_BUTTON_BEHAVIOR || 'hide';
        }
        BX.Ctweb.YandexDelivery.OrderController.prototype = Object.create(BX.Ctweb.YandexDelivery.Controller.prototype);
        Object.defineProperty(BX.Ctweb.YandexDelivery.OrderController.prototype, 'constructor', {
            value: BX.Ctweb.YandexDelivery.OrderController,
            enumerable: false,
            writable: true,
        });

        BX.Ctweb.YandexDelivery.OrderController.prototype.initDelivery = function() {
            if (this.params.DATA.DELIVERY)
                this.arDelivery = this.params.DATA.DELIVERY;

            this.pointRemove();

            var bx_soa_delivery = BX('bx-soa-delivery');

            if (bx_soa_delivery) {
                BX.addCustomEvent('onAjaxSuccess', BX.proxy(this.afterFormReload, this));
                BX.Sale.OrderAjaxComponent.sendRequest();
            } else {
                var inputDelivery = document.querySelector("input[name='DELIVERY_ID']:checked");
                if (inputDelivery.length)
                    this.checkPoint(inputDelivery.value);
            }

            this.ModalInit();

            BX.bindDelegate(
                document.body,
                'click', {
                    attribute: { id: this.params.TEMPLATE.LINK }
                },
                BX.proxy(this.ModalOpen, this)
            );
        };

        BX.Ctweb.YandexDelivery.OrderController.prototype.pointRemove = function(callback) {
            BX.ajax({
                url: '/bitrix/js/ctweb.yandexdelivery/ajax.php',
                timeout: 10,
                method: 'POST',
                dataType: 'json',
                data: {
                    action: 'remove_point'
                },
                onsuccess: callback,
            });
        };

        BX.Ctweb.YandexDelivery.OrderController.prototype.ModalInit = function() {
            this.obModal = BX(this.params.TEMPLATE.MODAL);
            this.obBtnCancel = BX(this.params.TEMPLATE.MODAL_CANCEL);
            this.obBtnSave = BX(this.params.TEMPLATE.MODAL_SAVE);

            BX.bind(BX.findChild(this.obModal, { 'class': 'dark-body' }, true), 'click', BX.proxy(this.ModalClose, this));
            BX.bind(this.obBtnCancel, 'click', BX.proxy(this.Close, this));
            BX.bind(this.obBtnSave, 'click', BX.proxy(this.Choose, this));
        };

        BX.Ctweb.YandexDelivery.OrderController.prototype.ModalOpen = function() {
            if (this.obModal) {
                BX.adjust(this.obModal, {
                    style: {
                        'opacity': '1',
                        'z-index': '9999',
                        'position': 'inherit',
                    }
                });
            }
        };
        BX.Ctweb.YandexDelivery.OrderController.prototype.ModalClose = function() {
            if (this.obModal) {
                BX.adjust(this.obModal, {
                    style: {
                        'opacity': '0',
                        'z-index': '-999',
                        'position': 'absolute',
                    }
                });
            }
        };
        BX.Ctweb.YandexDelivery.OrderController.prototype.Choose = function() {
            var self = this;
            if (self.lastRoute && self.currentAddress != undefined) {
                var params = {
                    pointTo: self.currentPoint,
                    calculated: {
                        distance: self.lastRoute.getLength(),
                        storeID: self.store.ID,
                        regionID: self.region.ID,
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
                    onsuccess: function() {
                        self.checkSelectPoint();
                        BX.Sale.OrderAjaxComponent.sendRequest();
                    }
                });
            } else {
                this.pointRemove(function() {
                    self.checkSelectPoint();
                    BX.Sale.OrderAjaxComponent.sendRequest();
                });
            }
            self.ModalClose();
        };
        BX.Ctweb.YandexDelivery.OrderController.prototype.Close = function() {
            var self = this;
            if (typeof self.currentPoint === 'undefined') {
                this.pointRemove(function() {
                    self.checkSelectPoint();
                    BX.Sale.OrderAjaxComponent.sendRequest();
                });
            }
            self.ModalClose();
        };

        BX.Ctweb.YandexDelivery.OrderController.prototype.afterFormReload = function(a, b, c) {
            var self = this,
                e = a || c || {};

            if (!!e.order && !!e.order.DELIVERY) {
                e.order.DELIVERY.forEach(function(item, i, arr) {
                    if (typeof item.CHECKED !== 'undefined') {
                        self.checkPoint(item.ID);
                    }
                });
            } else {
                return false;
            }
        };
        BX.Ctweb.YandexDelivery.OrderController.prototype.checkPoint = function(delivery_id) {
            var delivery = this.arDelivery.find(function(e) {
                return e.ID == delivery_id;
            });

            if (!!delivery)
                this.checkSelectPoint(delivery.ID);
        };

        BX.Ctweb.YandexDelivery.OrderController.prototype.showError = function(error) {

            if (typeof(BX.Sale.OrderAjaxComponent.showBlockErrors) === 'function') {
                BX.Sale.OrderAjaxComponent.result.ERROR.DELIVERY = [error];
                BX.Sale.OrderAjaxComponent.showBlockErrors(BX.Sale.OrderAjaxComponent.deliveryBlockNode);
            } else if (typeof(BX.Sale.OrderAjaxComponent.showError) === 'function') {
                BX.Sale.OrderAjaxComponent.showError(BX.Sale.OrderAjaxComponent.deliveryBlockNode, error);
            }
        }

        BX.Ctweb.YandexDelivery.OrderController.prototype.checkSelectPoint = function(delivery_id) {
            var self = this;

            BX.ajax({
                url: '/bitrix/js/ctweb.yandexdelivery/ajax.php',
                method: 'POST',
                dataType: 'json',
                data: {
                    action: 'check_point'
                },
                onsuccess: function(selected) {
                    var cwyd_errors = [];

                    // show order button by default
                    self.showOrderButtons();

                    // Hide btn if no point selected
                    if (selected !== true) {
                        self.hideOrderButtons();
                        self.showError(BX.message('CW_YD_NO_POINT_ERROR'));
                    }

                    // Show calculate errors
                    var cDelivery = BX.Sale.OrderAjaxComponent.result.DELIVERY.find(function(e) {
                        return (e.CHECKED === 'Y' && e.ID == delivery_id);
                    });
                    if (cDelivery) {
                        if (cDelivery.CALCULATE_ERRORS && cDelivery.CALCULATE_ERRORS.length) {
                            self.showError(cDelivery.CALCULATE_ERRORS);
                        }
                    }
                }
            });

        };

        BX.Ctweb.YandexDelivery.OrderController.prototype.findElements = function() {
            this.obOrderSaveBtn = BX.findChild(BX('bx-soa-orderSave'), { tag: 'a' }, true) || null;
            this.obTotalOrderSaveBtn = BX.findChild(BX('bx-soa-total'), { tag: 'a', className: 'btn-order-save' }, true) || null;

            if (this.params.TEMPLATE.ORDER_SAVE_BTN)
                this.obOrderSaveBtn = BX(this.params.TEMPLATE.ORDER_SAVE_BTN) || this.obOrderSaveBtn;

            if (this.params.TEMPLATE.TOTAL_ORDER_SAVE_BTN)
                this.obTotalOrderSaveBtn = BX(this.params.TEMPLATE.TOTAL_ORDER_SAVE_BTN) || this.obTotalOrderSaveBtn;

        };
        BX.Ctweb.YandexDelivery.OrderController.prototype.hideOrderButtons = function() {
            this.findElements();

            switch (this.OrderButtonBehavior) {
                case 'hide':
                    if (this.obOrderSaveBtn)
                        this.setStyleImportant(this.obOrderSaveBtn, 'display', 'none');

                    if (this.obTotalOrderSaveBtn)
                        this.setStyleImportant(this.obTotalOrderSaveBtn, 'display', 'none');

                    break;
                case 'disable':
                    var dup;

                    if (this.obOrderSaveBtn) {
                        dup = this.getElementDuplicate(this.obOrderSaveBtn);
                        if (!dup) {
                            dup = this.createElementDuplicate(this.obOrderSaveBtn);
                        }
                        this.setStyleImportant(this.obOrderSaveBtn, 'display', 'none');
                        BX.insertAfter(dup, this.obOrderSaveBtn);
                        BX.adjust(dup, { attrs: { disabled: true } });
                        BX.bind(dup, 'click', function() {
                            BX.scrollToNode(BX('bx-soa-delivery'));
                        }.bind(this));
                    }

                    if (this.obTotalOrderSaveBtn) {
                        dup = this.getElementDuplicate(this.obTotalOrderSaveBtn);
                        if (!dup) {
                            dup = this.createElementDuplicate(this.obTotalOrderSaveBtn);
                        }
                        this.setStyleImportant(this.obTotalOrderSaveBtn, 'display', 'none');
                        BX.insertAfter(dup, this.obTotalOrderSaveBtn);
                        BX.adjust(dup, { attrs: { disabled: true } });
                        BX.bind(dup, 'click', function() {
                            BX.scrollToNode(BX('bx-soa-delivery'));
                        }.bind(this));
                    }
                    break;
            }

        }
        BX.Ctweb.YandexDelivery.OrderController.prototype.showOrderButtons = function() {
            this.findElements();

            switch (this.OrderButtonBehavior) {
                case 'hide':
                    if (this.obOrderSaveBtn)
                        BX.show(this.obOrderSaveBtn);

                    if (this.obTotalOrderSaveBtn)
                        BX.show(this.obTotalOrderSaveBtn);
                    break;
                case 'disable':
                    var dup;

                    if (this.obOrderSaveBtn) {
                        dup = this.getElementDuplicate(this.obOrderSaveBtn);
                        if (!dup)
                            dup = this.createElementDuplicate(this.obOrderSaveBtn);

                        BX.remove(dup);
                        BX.show(this.obOrderSaveBtn);
                    }

                    if (this.obTotalOrderSaveBtn) {
                        dup = this.getElementDuplicate(this.obTotalOrderSaveBtn);
                        if (!dup)
                            dup = this.createElementDuplicate(this.obTotalOrderSaveBtn);

                        BX.remove(dup, this.obTotalOrderSaveBtn);
                        BX.show(this.obTotalOrderSaveBtn);
                    }
                    break;
            }
        }

        BX.Ctweb.YandexDelivery.OrderController.prototype.getElementDuplicate = function(el) {
            if (!el instanceof HTMLElement) return undefined;

            return el[this.duplicateKey];
        }
        BX.Ctweb.YandexDelivery.OrderController.prototype.createElementDuplicate = function(el) {
            if (!el instanceof HTMLElement) return undefined;

            var dup = el.cloneNode(true);

            dup[this.duplicateOriginalKey] = el;
            el[this.duplicateKey] = dup;

            return dup;
        }
        BX.Ctweb.YandexDelivery.OrderController.prototype.setStyleImportant = function(el, style, value) {
            el.style.setProperty(style, value, 'important');
        }
    }
})();