<?
use Bitrix\Sale;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\EventManager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;

class YaGo {
    protected static $defaultCommonComment = "Доставка из магазина #STORE_FROM#. Узнать у кассира, как пройти в администрацию.
Назовите номер заказа #ACCOUNT_NUMBER# и заберите посылку.";

    protected static $defaultPvzComment = "Заказ доставить в магазин #STORE_TO# и отдать сотрудникам администрации";

	public function sendRequest ($options,$order,$arOrderVals,$propertyCollection) {
        if ($options['switch_on'] != "Y")
            return false;
            
        $basketWeight = floatval($order->getBasket()->getWeight());
        //если курьер по заказу уже назанчен то false
        foreach ($propertyCollection as $obProp) {
            $arProp = $obProp->getProperty();
            $arProps[$arProp["CODE"]]["VALUE"] = $obProp->getFields()->getValues();
        }
        
        if($options['delsystems'])
            if(!in_array($arOrderVals["DELIVERY_ID"],explode(",", $options['delsystems'])))
                return false;
        if($options['paysystems'])
            if(!in_array($arOrderVals["PAY_SYSTEM_ID"],explode(",", $options['paysystems'])))
                return false;
        if($options["distance_min"])
            if(floatval($arProps[$options['distance_prop']]["VALUE"]["VALUE"]) < floatval($options["distance_min"]))
                return false;
        if($options["distance_max"])
            if(floatval($arProps[$options['distance_prop']]["VALUE"]["VALUE"]) > floatval($options["distance_max"]))
                return false;
        if($options["only_partner"] == "Y")
            if($arProps[$options['is_partner_prop']]["VALUE"]["VALUE"] != "Y")
                return false;
        if($options['region_id'] && !in_array($arProps[$options['region_prop']]["VALUE"]["VALUE"],explode(",", $options['region_id'])))
            return false;
            
        //магазин
        $arStoreFrom = \Bitrix\Catalog\StoreTable::getList(array(
            'filter'=>array('=ID'=>$arProps[$options['store_prop']]["VALUE"]["VALUE"]),
            'select'=>array('*','UF_*'),
        ))->fetch();
        $comment = !empty($options["common_comment"]) ? $options["common_comment"] : self::$defaultCommonComment;
        $comment = str_replace(["#STORE_NAME#", "#STORE_FROM#", "#ACCOUNT_NUMBER#"], [$options["pickup_name"], $arStoreFrom["TITLE"], $arOrderVals["ACCOUNT_NUMBER"]], $comment);

        $arStoreFromPhone = explode(";", $arStoreFrom['PHONE'])[0];
        $arStoreFromPhone = preg_replace('/^8/', '+7', $arStoreFromPhone);
        $arStoreFromPhone = preg_replace('/^\+8/', '+7', $arStoreFromPhone);
        $arStoreFromPhone = str_replace(array(' ', '(' , ')', '-'), '', $arStoreFromPhone);
        $storeEmailFrom = explode(",", $arStoreFrom['EMAIL']);
        $storeEmailFrom = $storeEmailFrom[0];
        $skipConfirm = $options["sms_true_destination"];
        //доставка в ПВЗ коллекция
        if($options['pvz_on'] == "Y"){
            $shipmentCollection = $order->getShipmentCollection();
            foreach ($shipmentCollection as $ship) {
                $store_id = $ship->getStoreId();//ID склада
                if ($store_id) {
                    break;
                }
            }
            if($store_id == $arProps[$options['store_prop']]["VALUE"]["VALUE"])
                return false;
            if($store_id && $store_id != $arProps[$options['store_prop']]["VALUE"]["VALUE"]){
                $arStore = \Bitrix\Catalog\StoreTable::getList(array(
                    'filter'=>array('=ID'=>$store_id),
                    'select'=>array('*','UF_*'),
                ))->fetch();
            }
            if($arStore){
                $arProps[$options['address_prop']]["VALUE"]["VALUE"] = $arStore['ADDRESS'];
                $arProps[$options['long_prop']]["VALUE"]["VALUE"] = $arStore['GPS_N'];
                $arProps[$options['lat_prop']]["VALUE"]["VALUE"] = $arStore['GPS_S'];
                $arProps[$options['podezd_prop']]["VALUE"]["VALUE"] = "";
                $arProps[$options['room_prop']]["VALUE"]["VALUE"] = "";
                $arProps[$options['floor_prop']]["VALUE"]["VALUE"] = "";
                $arProps[$options['code_prop']]["VALUE"]["VALUE"] = "";
                $arOrderVals['USER_DESCRIPTION'] = "Доставка в пункт выдачи";
                $storeEmail = explode(",", $arStore['EMAIL']);
                $arProps["EMAIL"]["VALUE"]["VALUE"] = $storeEmail[0];
                $arProps["FIO"]["VALUE"]["VALUE"] = "Магазин ".$arStore['TITLE'];
                $arProps["PHONE"]["VALUE"]["VALUE"] = explode(";", $arStore['PHONE'])[0];
                $skipConfirm = $options["sms_true"];
                $pvzComment = !empty($options["pvz_comment"]) ? $options["pvz_comment"] : self::$defaultPvzComment;
                $comment .= "\n".str_replace("#STORE_TO#", $arStore['TITLE'], $pvzComment);
            }
        }
        $arProps["PHONE"]["VALUE"]["VALUE"] = preg_replace('/^8/', '+7', $arProps["PHONE"]["VALUE"]["VALUE"]);
        $arProps["PHONE"]["VALUE"]["VALUE"] = preg_replace('/^\+8/', '+7', $arProps["PHONE"]["VALUE"]["VALUE"]);
        $arProps["PHONE"]["VALUE"]["VALUE"] = str_replace(array(' ', '(' , ')', '-'), '', $arProps["PHONE"]["VALUE"]["VALUE"]);
        $emergencyPhone = preg_replace('/^8/', '+7', $options['emergency_phone']);
        $emergencyPhone = preg_replace('/^\+8/', '+7', $emergencyPhone);
        $emergencyPhone = str_replace(array(' ', '(' , ')', '-'), '', $emergencyPhone);
        if($basketWeight <= floatval($options["courier_weight"])){
                    $type = "courier";
                }else if($basketWeight <= floatval($options["express_weight"])){
                    $type = "express";
                }else if ($basketWeight <= 1400000){
                    $type = "cargo";
                }
        $client_requirements = array(
                    "taxi_class" => $type
                );
        if ($basketWeight > floatval($options["express_weight"])){
            if($basketWeight <= 300000){
                $cargoType = "van";
            }else if($basketWeight <= 700000){
                $cargoType = "lcv_m";
            }else if ($basketWeight <= 1400000){
                $cargoType = "lcv_l";
            }
        }
        if($cargoType)
            $client_requirements["cargo_type"] = $cargoType;
        $headers = array();
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        $headers[] = 'Authorization: Bearer '.$options['token'];
        $headers[] = 'Accept-Language: *';
        $headers[] = 'Connection: keep-alive';
        $basket = $order->getBasket();
        $items = [];
        foreach ($basket as $k => $basketItem) {
            $dbEl = CIBlockElement::GetList(Array(), Array("IBLOCK_ID" => 3, "ID" => $basketItem->getProductId()));
            if($obEl = $dbEl->GetNextElement())
            {
                $props = $obEl->GetProperties();
            }
            $height = isset($props['VGKH_VYSOTA_M']) ? $props['VGKH_VYSOTA_M']['VALUE'] / 100 : false;
            $length = isset($props['VGKH_DLINA_M']) ? $props['VGKH_DLINA_M']['VALUE'] / 100 : false;
            $width = isset($props['VGKH_SHIRINA_M']) ? $props['VGKH_SHIRINA_M']['VALUE'] / 100 : false;

            $items[$k]["cost_currency"] = "RUB";
            $items[$k]["cost_value"] = (string)$basketItem->getFinalPrice();
            $items[$k]["droppof_point"] = 2;
            $items[$k]["pickup_point"] = 1;
            $items[$k]["quantity"] = $basketItem->getQuantity();
            $items[$k]["title"] = $basketItem->getField('NAME');
            $items[$k]["weight"] = $basketItem->getWeight() / 1000;
            if ($height && $length && $width) {
                $items[$k]["size"] = [
                    'height' => $height,
                    'length' => $length,
                    'width' => $width,
                ];
            }
        }

        $array = array(
            "client_requirements" => $client_requirements,
            "comment" => $options["pickup_name"],
            "emergency_contact" => [
                    "name" => $options['emergency_name'],
                    "phone" => $emergencyPhone
                ],
            "items" => $items,
            "optional_return" => false,
            "route_points" => [
                    array(
                        "address" => array(
                            "comment" => $comment,
                            "coordinates" => [
                                floatval($arStoreFrom["GPS_S"]),
                                floatval($arStoreFrom["GPS_N"])
                            ],
                            "fullname" => $arStoreFrom['ADDRESS'],
                        ),
                        "contact" => array(
                            "email" => $storeEmailFrom,
                            "name" => "Магазин ".$arStoreFrom['TITLE'],
                            "phone" => $arStoreFromPhone
                        ),
                        "point_id" => 1,
                        "skip_confirmation" => $options["sms_true"],
                        "type" => "source",
                        "visit_order" => 1
                    ),
                     array(
                        "address" => array(
                            "comment" => $arOrderVals['USER_DESCRIPTION'],
                            "coordinates" => [
                                floatval($arProps[$options['lat_prop']]["VALUE"]["VALUE"]),
                                floatval($arProps[$options['long_prop']]["VALUE"]["VALUE"])
                            ],
                            "door_code" => (string)$arProps[$options['code_prop']]["VALUE"]["VALUE"],
                            "fullname" => $arProps[$options['address_prop']]["VALUE"]["VALUE"],
                            "porch" => $arProps[$options['podezd_prop']]["VALUE"]["VALUE"],
                            "sflat" => $arProps[$options['room_prop']]["VALUE"]["VALUE"],
                            "sfloor" => $arProps[$options['floor_prop']]["VALUE"]["VALUE"],
                        ),
                        "contact" => array(
                            "email" => $arProps["EMAIL"]["VALUE"]["VALUE"],
                            "name" => $arProps["FIO"]["VALUE"]["VALUE"],
                            "phone" => $arProps["PHONE"]["VALUE"]["VALUE"]
                        ),
                        "external_order_id" => $arOrderVals["ACCOUNT_NUMBER"],
//                         "payment_on_delivery" => array(
//                             "client_order_id" => $arOrderVals["ACCOUNT_NUMBER"],
//                             "cost" => (string)$order->getPrice(),
//                             "currency" => "RUB",
//                             "customer" => [
//                                 "email" => $arProps["EMAIL"]["VALUE"]["VALUE"],
//                                 "full_name" => $arProps["FIO"]["VALUE"]["VALUE"],
//                                 "phone" => $arProps["PHONE"]["VALUE"]["VALUE"]
//                             ],
//                             "tax_system_code" => 1
//                         ),
                        "point_id" => 2,
                        "skip_confirmation" => $skipConfirm,
                        "type" => "destination",
                        "visit_order" => 2
                    ),
                ],
            "referral_source" => "bitrix",
            "skip_act" => false,
            "skip_client_notify" => false,
            "skip_door_to_door" => false,
            "skip_emergency_notify" => true
        );
        //доставка ко времени (+ N минут от времени создания)
        if ($options["due"] != "" && $options["tariff"] == 'due') {
            $minutes_to_add = $options["due"];

            $time = new DateTime(date("c", time()));
            $time->add(new DateInterval('PT'.$minutes_to_add.'M'));
            $stamp = $time->format('c');

            $array["due"] = $stamp;
        } else if ($options["tariff"] == 'day') {
            //доставка В течение дня
            //запрос на доступные интервалы:
            $headersInterval = array();
            $headersInterval[] = 'Content-Type: application/json';
            $headersInterval[] = 'Authorization: Bearer '.$options['token'];
            $headersInterval[] = 'Accept-Language: *';

            $ch = curl_init('https://b2b.taxi.yandex.net/b2b/cargo/integration/v2/delivery-methods');
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(
                [
                    'fullname' => $arStoreFrom['ADDRESS'],
                    'start_point' => [
                        floatval($arStoreFrom["GPS_S"]),
                        floatval($arStoreFrom["GPS_N"])
                    ]
                ], JSON_UNESCAPED_UNICODE));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            // curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headersInterval);
            $html = curl_exec($ch);
            curl_close($ch);
            $result = json_decode($html, true);

            if (
                isset($result['same_day_delivery'])
                && isset($result['same_day_delivery']['allowed'])
                && $result['same_day_delivery']['allowed'] == 1
                && !empty($result['same_day_delivery']['available_intervals'])
            ) {
                $array["same_day_data"] = [
                    'delivery_interval' => $result['same_day_delivery']['available_intervals'][0]
                ];

                if($result["code"]) {
                    $code = $result["code"];
                    $message = $result["message"];
                    file_put_contents($_SERVER["DOCUMENT_ROOT"] . '/log/yago_' . date("d.m.Y") . '.log', date('[d-m-Y H:i] ') . print_r("Ошибка: url: https://b2b.taxi.yandex.net/b2b/cargo/integration/v2/delivery-methods order: " . $arOrderVals["ACCOUNT_NUMBER"] . " answer: ", true) . print_r($html, true) . PHP_EOL, FILE_APPEND | LOCK_EX);
                }

                unset($array['client_requirements']);
            } else {
                $code = 400;
                $message = 'Нет доступных интервалов доставки';
            }
        }

        //если вдруг сервер будет возвращать 50* то делаем три попытки
        if ($code || $message) {
            $result['code'] = $code;
            $result['message'] = $message;
            $html = json_encode(['code' => $code, 'message' => $message], JSON_UNESCAPED_UNICODE);
        } else {
            for ($i = 0; $i <= 2; $i++) {
                $ch = curl_init('https://b2b.taxi.yandex.net/b2b/cargo/integration/v2/claims/create?request_id=' . md5($arOrderVals["ACCOUNT_NUMBER"] . "v" . $arProps["EUTILS_YAGO_ISORDERED"]["VALUE"]["VALUE"]));
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($array, JSON_UNESCAPED_UNICODE));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                // curl_setopt($ch, CURLOPT_HEADER, 1);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                $html = curl_exec($ch);
                curl_close($ch);
                $result = json_decode($html, true);
                if ($result["code"])
                    file_put_contents($_SERVER["DOCUMENT_ROOT"] . '/log/yago_' . date("d.m.Y") . '.log', date('[d-m-Y H:i] ') . print_r("Ошибка: url: https://b2b.taxi.yandex.net/b2b/cargo/integration/v2/claims/create?request_id=" . md5($arOrderVals["ACCOUNT_NUMBER"] . "v" . $arProps["EUTILS_YAGO_ISORDERED"]["VALUE"]["VALUE"]) . " answer: ", true) . print_r($html, true) . PHP_EOL, FILE_APPEND | LOCK_EX);
                if (!$result["code"] || strpos($result["code"], '50') !== 0)
                    break;
                sleep(1);
            }
        }

        if($options['log_on'] == "Y" || $code)
            file_put_contents($_SERVER["DOCUMENT_ROOT"].'/log/yago_'.date("d.m.Y").'.log', date('[d-m-Y H:i] ') . print_r("request: ".json_encode($array, JSON_UNESCAPED_UNICODE)." url: https://b2b.taxi.yandex.net/b2b/cargo/integration/v2/claims/create?request_id=".md5($arOrderVals["ACCOUNT_NUMBER"]."v".$arProps["EUTILS_YAGO_ISORDERED"]["VALUE"]["VALUE"])." answer: ", true) . print_r($html, true) . PHP_EOL, FILE_APPEND | LOCK_EX);
        
        //создаем элемент инфоблока
        CModule::IncludeModule("iblock");
        $res = CIBlock::GetList(
            Array(), 
            Array(
                'TYPE'=>'eutils_yandex_go',
                'CODE'=>'yandex_go_deliverys'
            ), true
        );
        while($ar_res = $res->Fetch())
        {
            $iblockId = $ar_res['ID'];
        }
        if($result['status'] == "new" || ($result['code'] && $result['message'])) {
            //сначала удаляем старые элементы
            $arFilter = array("IBLOCK_ID"=>$iblockId);
            $iCount = CIBlockElement::GetList(false, $arFilter, array(), false);
            if($iCount>1000){
                    $iCount = CIBlockElement::GetList(array("ID"=>"asc"), $arFilter, false, false, array("PROPERTY_status","ID"));
                    $k = 1;
                    while($element = $iCount->Fetch()){
                        \CIBlockElement::Delete(floatval($element['ID']));
                        if($k > 50)
                            break;
                    }
            }
            //теперь создаем
            $el = new CIBlockElement;

            $PROP = array();
            $fullName = 'Доставка Яндекс.GO заказа #'.$arOrderVals["ACCOUNT_NUMBER"]." v".$arProps["EUTILS_YAGO_ISORDERED"]["VALUE"]["VALUE"];
            $PROP['ERROR_MESSAGE'] = $result['message'] ?: '';
            $PROP['ORDER_ID'] = $arOrderVals["ACCOUNT_NUMBER"];
            $PROP['CLAIM_ID'] = $result['id'] ?: '';
            $PROP['eta'] = $result['eta'] ?: '';
            $PROP['version'] = $result['version'] ?: '';
            if (isset($result['status'])) {
                $property_enums = CIBlockPropertyEnum::GetList(array("DEF" => "DESC", "SORT" => "ASC"), array("IBLOCK_ID" => $iblockId, "CODE" => "status", "XML_ID" => $result['status']));
                while ($enum_fields = $property_enums->GetNext()) {
                    $PROP['status'] = $enum_fields["ID"];
                }
            }
            if (isset($result['available_cancel_state'])) {
                $property_enums = CIBlockPropertyEnum::GetList(array("DEF" => "DESC", "SORT" => "ASC"), array("IBLOCK_ID" => $iblockId, "CODE" => "available_cancel_state", "XML_ID" => $result['available_cancel_state']));
                while ($enum_fields = $property_enums->GetNext()) {
                    $PROP['available_cancel_state'] = $enum_fields["ID"];
                }
            }
            $PROP['PICKUP'] = $result['route_points'][0]['address']['fullname'] ?: '';
            $PROP['DESTINATION'] = $result['route_points'][1]['address']['fullname'] ?: '';
            $PROP['request_id'] = md5($arOrderVals["ACCOUNT_NUMBER"]."v".$arProps["EUTILS_YAGO_ISORDERED"]["VALUE"]["VALUE"]);
            //транслит симв кода
            $params = Array(
                "max_len" => "100", // обрезает символьный код до 100 символов
                "change_case" => "L", // буквы преобразуются к нижнему регистру
                "replace_space" => "-", // меняем пробелы на нижнее подчеркивание
                "replace_other" => "-", // меняем левые символы на нижнее подчеркивание
                "delete_repeat_replace" => "true", // удаляем повторяющиеся нижние подчеркивания
                "use_google" => "false", // отключаем использование google
                );

            $arEventFields = Array(
                "IBLOCK_ID"      => $iblockId,
                "PROPERTY_VALUES"=> $PROP,
                "NAME"           => $fullName,
                "CODE" => CUtil::translit($fullName, "ru" , $params),
                "ACTIVE"         => "Y",            // активен
            //"PREVIEW_TEXT"   => $message,
            );

            if( $el->Add($arEventFields) ){
                //запись в файл
                if($options['log_on'] == "Y")
                    file_put_contents($_SERVER["DOCUMENT_ROOT"].'/log/yago_'.date("d.m.Y").'.log', date('[d-m-Y H:i] ') . "Элемент успешно создан" . PHP_EOL, FILE_APPEND | LOCK_EX);
            }
            else{
                //запись в файл
                file_put_contents($_SERVER["DOCUMENT_ROOT"].'/log/yago_'.date("d.m.Y").'.log', date('[d-m-Y H:i] ') . $arOrderVals["ACCOUNT_NUMBER"].$arProps["EUTILS_YAGO_ISORDERED"]["VALUE"]["VALUE"] . " Элемент не создан" . PHP_EOL, FILE_APPEND | LOCK_EX);
            }
            $rDate = new DateTime('+30 seconds');
            $newTime = $rDate->format('d.m.Y H:i:s');
            if (!isset($result['code'])) {
                CAgent::AddAgent('YaGoRequests::updateRequest("' . $result['id'] . '", false);', 'eutils.yago', 'N', 30, "", 'Y', $newTime);
            }
        }else if($result['id']){
            //обновляем элемент
            self::updateIblock($result['id'],$result);

            if($result['status'] == "delivered_finish"){
                //обновляем заказ
                self::updateOrder($result['id']);
            }
        }
        return true;
	}
	
    public function getOptions(): array
    { 
        return $options = array(
                'token' => Option::get("eutils.yago", "token", ""),
                'switch_on' => Option::get("eutils.yago", "switch_on", ""),
                'test_on' => Option::get("eutils.yago", "test_on", ""),
                'log_on' => Option::get("eutils.yago", "log_on", ""),
                'pvz_on' => Option::get("eutils.yago", "pvz_on", ""),
                'status_id' => Option::get("eutils.yago", "status_id", ""),
                'paysystems' => Option::get("eutils.yago", "paysystems", ""),
//                 'http' => (Option::get("eutils.yago", "http", "") == "Y") ? "https://" : "http://",
                'courier_weight' => Option::get("eutils.yago", "courier_weight", "10000"),
                'express_weight' => Option::get("eutils.yago", "express_weight", "50000"),
                'pickup_name' => Option::get("eutils.yago", "pickup_name", ""),
                'emergency_name' => Option::get("eutils.yago", "emergency_name", ""),
                'emergency_phone' => Option::get("eutils.yago", "emergency_phone", ""),
                'distance_min' => Option::get("eutils.yago", "distance_min", ""),
                'distance_max' => Option::get("eutils.yago", "distance_max", ""),
                'delsystems' => Option::get("eutils.yago", "delsystems", ""),
                'region_id' => Option::get("eutils.yago", "region_id", ""),
                'finish_status' => Option::get("eutils.yago", "finish_status", ""),
                'timer' => Option::get("eutils.yago", "timer", ""),
                'distance_prop' => Option::get("eutils.yago", "distance_prop", ""),
                'is_partner_prop' => Option::get("eutils.yago", "is_partner_prop", ""),
                'region_prop' => Option::get("eutils.yago", "region_prop", ""),
                'store_prop' => Option::get("eutils.yago", "store_prop", ""),
                'long_prop' => Option::get("eutils.yago", "long_prop", ""),
                'lat_prop' => Option::get("eutils.yago", "lat_prop", ""),
                'podezd_prop' => Option::get("eutils.yago", "podezd_prop", ""),
                'room_prop' => Option::get("eutils.yago", "room_prop", ""),
                'code_prop' => Option::get("eutils.yago", "code_prop", ""),
                'floor_prop' => Option::get("eutils.yago", "floor_prop", ""),
                'address_prop' => Option::get("eutils.yago", "address_prop", ""),
                'max_reorder' => Option::get("eutils.yago", "max_reorder", ""),
                'common_comment' => Option::get("eutils.yago", "common_comment", self::$defaultCommonComment),
                'pvz_comment' => Option::get("eutils.yago", "pvz_comment", self::$defaultPvzComment),
                'sms_true' => (Option::get("eutils.yago", "sms_true", "") == "Y") ? true : false,
                'sms_true_destination' => (Option::get("eutils.yago", "sms_true_destination", "") == "Y") ? true : false,
                'only_partner' => Option::get("eutils.yago", "only_partner", ""),
                'due' => Option::get("eutils.yago", "due"),
                'tariff' => Option::get("eutils.yago", "tariff")
            );
    }
    
    public function orderCancel($claimId,$version,$state)
    { 
        $options = self::getOptions();
        $headers = array();
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        $headers[] = 'Authorization: Bearer '.$options['token'];
        $headers[] = 'Accept-Language: *';

        $ch = curl_init('https://b2b.taxi.yandex.net/b2b/cargo/integration/v1/claims/cancel?claim_id='.$claimId);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array("version"=>$version,"cancel_state"=>$state), JSON_UNESCAPED_UNICODE));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $html = curl_exec($ch);
        $result = json_decode($html, true);
        curl_close($ch);
//        if($options['log_on'] == "Y")
            file_put_contents($_SERVER["DOCUMENT_ROOT"].'/log/yago_'.date("d.m.Y").'.log', date('[d-m-Y H:i] ') . print_r("Ручная отмена url: b2b.taxi.yandex.net/b2b/cargo/integration/v1/claims/cancel?claim_id=".$claimId." answer: ", true) . print_r($html, true) . PHP_EOL, FILE_APPEND | LOCK_EX);
        if($result["code"]){
//             file_put_contents($_SERVER["DOCUMENT_ROOT"].'/log/yago_'.date("d.m.Y").'.log', date('[d-m-Y H:i] ') . print_r("Ошибка: b2b.taxi.yandex.net/b2b/cargo/integration/v1/claims/cancel?claim_id=".$claimId." answer: ", true) . print_r($html, true) . PHP_EOL, FILE_APPEND | LOCK_EX);
            return false;
        }
        YaGoRequests::updateRequest($claimId, false);
        return true;
    }
    
    public function updateOrder($claimId)
    { 
        $options = self::getOptions();
        CModule::includeModule("iblock");
        $res = CIBlock::GetList(
            Array(), 
            Array(
                'TYPE'=>'eutils_yandex_go',
                'CODE'=>'yandex_go_deliverys'
            ), true
        );
        while($ar_res = $res->Fetch())
        {
            $iblockId = $ar_res['ID'];
        }
        $arFilter = array("IBLOCK_ID"=>$iblockId, "=PROPERTY_CLAIM_ID"=>$claimId);
        $res = CIBlockElement::GetList(array("ID"=>"asc"), $arFilter, false, false, array("PROPERTY_CLAIM_ID","PROPERTY_ORDER_ID","ID"));
        while($element = $res->Fetch()){
            $orderId = $element["PROPERTY_ORDER_ID_VALUE"];
        }
        if (CModule::IncludeModule("sale")){
        $select = ['ID','DATE_INSERT','PRICE','ACCOUNT_NUMBER'];
        $arFilter = Array("ACCOUNT_NUMBER" => $orderId);
        $arOrder = Bitrix\Sale\Order::loadByAccountNumber($orderId);
        if ($arOrder)
        {
        $arFields = array("STATUS_ID" => $options["finish_status"]);
        CSaleOrder::Update($arOrder->getId(), $arFields);
        }
        }
    }
    
    public function updateIblock($claimId,$result)
    { 
        CModule::includeModule("iblock");
        $res = CIBlock::GetList(
            Array(), 
            Array(
                'TYPE'=>'eutils_yandex_go',
                'CODE'=>'yandex_go_deliverys'
            ), true
        );
        while($ar_res = $res->Fetch())
        {
            $iblockId = $ar_res['ID'];
        }
        $arFilter = array("IBLOCK_ID"=>$iblockId, "=PROPERTY_CLAIM_ID"=>$claimId);
        $res = CIBlockElement::GetList(array("ID"=>"asc"), $arFilter, false, false, array("PROPERTY_CLAIM_ID","ID"));
        while($element = $res->Fetch()){
            $itemID = $element["ID"];
        }
        $PROP = array();
        $property_enums = CIBlockPropertyEnum::GetList(Array("DEF"=>"DESC", "SORT"=>"ASC"), Array("IBLOCK_ID"=>$iblockId, "CODE"=>"status", "XML_ID"=>$result['status'] ));
        while($enum_fields = $property_enums->GetNext())
        {
            $PROP['status'] = $enum_fields["ID"];
        }
        $property_enums = CIBlockPropertyEnum::GetList(Array("DEF"=>"DESC", "SORT"=>"ASC"), Array("IBLOCK_ID"=>$iblockId, "CODE"=>"available_cancel_state", "XML_ID"=>$result['available_cancel_state ']));
        while($enum_fields = $property_enums->GetNext())
        {
            $PROP['available_cancel_state'] = $enum_fields["ID"];
        }
        $PROP['eta'] = $result['eta'];
        $PROP['PICKUP'] = $result['route_points'][0]['address']['fullname'];
        $PROP['DESTINATION'] = $result['route_points'][1]['address']['fullname'];
        $PROP['DRIVER'] = $result['performer_info']['courier_name'];
        $PROP['car_number'] = $result['performer_info']['car_number'];
        $PROP['car_model'] = $result['performer_info']['car_model'];
        $PROP['version'] = $result['version'];
        $PROP['offer_id'] = $result['pricing']['offer']['offer_id'];
        $PROP['offer_price'] = $result['pricing']['final_price'];
        $PROP['pickup_code'] = $result['pickup_code'];
        CIBlockElement::SetPropertyValuesEx($itemID, $iblockId, $PROP);
    }

	function OnSaleStatusOrderChange($order)
	{
	
        $options = self::getOptions();
        if ($options['switch_on'] != "Y")
            return false;
        
        $isNew = $order->isNew();
        $arOrderVals = $order->getFields()->getValues();
        $propertyCollection = $order->getPropertyCollection();
//         $discountCollection = $order->getDiscount();

        if($arOrderVals['STATUS_ID'] != $options['status_id'])
            return false;
        
        if($isNew != '1')
            self::sendRequest($options,$order,$arOrderVals,$propertyCollection);
		
	}
    
}
