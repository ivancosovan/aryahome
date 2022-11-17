<?php
use Bitrix\Sale;
use Bitrix\Main\Loader;

/**
 * Обработчики событий для модуля sale и catalog.
 */
class saleEvents
{
    const SBER_PAY_ID = 14;
    const ORDER_DEPOSIT_STATUS = 'F';


    //обработчики ограничения доставки
    public static function myDeliveryFunction()
    {
        return new \Bitrix\Main\EventResult(
            \Bitrix\Main\EventResult::SUCCESS,
            [
                '\MyDeliveryRestriction'       => '/local/php_interface/include/mydelrestriction.php',
            ]
        );
    }

    /**
     * Смена статуса заказа.
     *
     * @param int $orderId Идентификатора заказа.
     * @param string $orderStatus Статус заказа.
     * @return bool|void
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function OnSaleStatusOrder(int $orderId, string $orderStatus)
    {
        if ($orderStatus !== static::ORDER_DEPOSIT_STATUS) {
            //Статус заказа не нуждается в обработке
            return true;
        }

        if ('Y' !== \Bitrix\Sale\BusinessValue::get("SBERBANK_HANDLER_TWO_STAGE", "PAYSYSTEM_".static::SBER_PAY_ID)) {
            self::log('Двухстадийные оплаты не активны');
            return true;
        }

        if (!($order = Sale\Order::load($orderId))) {
            self::log('Не удалось загрузить информацияю по заказу: $orderId: ' . $orderId);
            return true;
        }
        $paymentCollection = $order->getPaymentCollection();
        $sberOrderId = null;

        foreach ($paymentCollection as $obProp) {
            $arProp = $obProp->getFields()->getValues();
            if (
                isset($arProp['PAY_SYSTEM_ID'])
                && $arProp['PAY_SYSTEM_ID'] == static::SBER_PAY_ID
                && isset($arProp['PS_INVOICE_ID'])
            ) {
                $sberOrderId = $arProp['PS_INVOICE_ID'];
            }
        }

        if (!$sberOrderId) {
            self::log('Не найдено оплат платежной системы Сбербанк либо не найден идентификатор оплаты Сбербанка');
            return true;
        }

        if ($order->getSumPaid() <= 0) {
            self::log('Заказ не был предоплачен');
            return true;
        }

        $orderSumPaid = $order->getSumPaid() * 100;
        self::log('Заказ: ' . $orderId . ' Уже оплачено: ' . $orderSumPaid, false);

        $orderPrice = $order->getPrice() * 100;
        self::log('Новая стоимость заказа: ' . $orderPrice, false);

        if ($orderPrice - $orderSumPaid > 0) {
            self::log('Списывать больше предоплаты нет возможности, дельта: ' . ($orderPrice - $orderSumPaid));
            return true;
        }

        self::log('sberOrderId: ' . $sberOrderId, false);

        $sberbankPay = new SberbankPay(static::SBER_PAY_ID);
        $sberOrder = $sberbankPay->getOrderStatusExtended($sberOrderId);

        if (1 != $sberOrder['orderStatus']) {
            self::log('Плохой статус заказа из шлюза: ' . $sberOrder['orderStatus'], false);
            self::log(json_encode($sberOrder));
            return true;
        }

        if (!Loader::includeModule('sberbank.ecom2')) {
            self::log('Модуль платежной системы не установлен');
            return true;
        }

        $RBS_Gateway = new \Sberbank\Payments\Gateway();

        /* Корзина для чека */
        $basket = $order->getBasket();
        $basketItems = $basket->getBasketItems();
        $cart = [];
        foreach ($basketItems as $key => $basketItem) {
            $lastIndex = $key + 1;
            $cart[] = [
                'positionId' => $key,
                'name'       => $basketItem->getField('NAME'),
                'quantity'   => [
                    'value'   => $basketItem->getQuantity(),
                    'measure' => $basketItem->getField('MEASURE_NAME'),
                ],
                'itemAmount' => $basketItem->getFinalPrice() * 100,
                'itemCode'   => $basketItem->getProductId(),
                'tax'        => [
                    'taxType' => $RBS_Gateway->getTaxCode($basketItem->getField('VAT_RATE') * 100),
                ],
                'itemPrice'  => $basketItem->getPrice() * 100,
            ];
        }
        if ($order->getField('PRICE_DELIVERY') > 0)
        {

            Loader::includeModule('catalog');
            $deliveryInfo = \Bitrix\Sale\Delivery\Services\Manager::getById($order->getField('DELIVERY_ID'));

            $deliveryVatItem = \CCatalogVat::GetByID($deliveryInfo['VAT_ID'])->Fetch();

            $cart[] = [
                'positionId' => $lastIndex,
                'itemCode'   => 'DELIVERY_' . $order->getField('DELIVERY_ID'),
                'name'       => "Доставка",
                'itemAmount' => $order->getField('PRICE_DELIVERY') * 100,
                'itemPrice'  => $order->getField('PRICE_DELIVERY') * 100,
                'quantity'   => [
                    'value'   => 1,
                    'measure' => "шт",
                ],
                'tax'        => [
                    'taxType' => $RBS_Gateway->getTaxCodeDelivery($deliveryVatItem['RATE']),
                ],
            ];
        }

        $depositAnswer = $sberbankPay->deposit($sberOrderId, $orderPrice, $cart);

        if (!empty($depositAnswer['errorCode'])) {
            self::log($depositAnswer['errorMessage']);
        } else {
            self::log("Оплата завершена");
        }
    }

    public static function log($param, $end = true) {
        if (\Bitrix\Sale\BusinessValue::get("SBERBANK_HANDLER_LOGGING", "PAYSYSTEM_" . static::SBER_PAY_ID) == "N") {
            return true;
        }
        $file = $_SERVER["DOCUMENT_ROOT"] . '/log/sber.log';
        $delim = $end ? "\n------------------" : "";
        file_put_contents($file, date('[d-m-Y H:i] ') . print_r($param . $delim, true) . PHP_EOL, FILE_APPEND | LOCK_EX);

        if ($end && file_exists($file)) {
            $logSize = filesize($file) / 1000;
            if ($logSize > 10000) {
                rename($file, $file . "_old_" . date('[d-m-Y H:i] '));
            }
        }
    }
}