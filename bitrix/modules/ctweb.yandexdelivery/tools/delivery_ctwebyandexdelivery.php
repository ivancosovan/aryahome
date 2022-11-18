<?
use Bitrix\Main\Config\Option;

CModule::IncludeModule("sale");
IncludeModuleLangFile(__FILE__);

if (!class_exists('CDeliveryCtwebYandexDelivery')) {
    class CDeliveryCtwebYandexDelivery
    {
        protected static $module_id = 'ctweb.yandexdelivery';
        protected static $link_id = '';
        protected static $flag = true;

        public static function ShowComponent()
        {
            CModule::IncludeModule(self::$module_id);

            if(self::$flag === true) {
                self::$flag = false;
                self::$link_id = $GLOBALS['APPLICATION']->IncludeComponent("ctweb:yandexdelivery", "order", array(), false);
                self::$link_id .= "link";
            }
        }

        private static function getLink()
        {
            return "<a href='javascript:void(0);' id='".self::$link_id."'>" . GetMessage("SELECT_LINK_TEXT") . "</a>";
        }

        public static function Init()
        {
            CModule::IncludeModule(self::$module_id);

            if (class_exists('SaleOrderAjax') && strpos($GLOBALS["APPLICATION"]->GetCurPage(), 'bitrix/admin') === false || !ADMIN_SECTION) {
                self::ShowComponent();
            }

            return array(
                "SID" => self::$module_id,
                "NAME" => GetMessage('CWYD_DELIVERY_NAME'),
                "DESCRIPTION" => "",
                "DESCRIPTION_INNER" => GetMessage('DESCRIPTION_INNER'),
                "BASE_CURRENCY" => COption::GetOptionString("sale", "default_currency", "RUB"),
                "HANDLER" => __FILE__,
                "DBGETSETTINGS" => array("CDeliveryCtwebYandexDelivery", "GetSettings"),
                "DBSETSETTINGS" => array("CDeliveryCtwebYandexDelivery", "SetSettings"),
                "GETCONFIG" => array("CDeliveryCtwebYandexDelivery", "GetConfig"),

                "COMPABILITY" => array("CDeliveryCtwebYandexDelivery", "Compability"),
                "CALCULATOR" => array("CDeliveryCtwebYandexDelivery", "Calculate"),

                'PROFILES' => array(
                    'POINT' => array(
                        'TITLE' => GetMessage('YANDEXDELIVERY_POINT'),
                        'DESCRIPTION' => "",
                    )
                )
            );
        }

        public static function GetConfig()
        {
            $arConfig = array(
                "CONFIG" => array(
                    "default" => array(),
                )
            );
            return $arConfig;
        }

        public static function SetSettings($arSettings)
        {
            foreach ($arSettings as $key => $value) {
                if (strlen($value) > 0)
                    $arSettings[$key] = ($value);
                else
                    unset($arSettings[$key]);
            }

            return serialize($arSettings);
        }

        public static function GetSettings($strSettings)
        {
            $settings = unserialize($strSettings);
            if (empty($settings)) return;
            return $settings;
        }

        public static function Compability($arOrder, $arConfig)
        {
            $module_status = \CModule::IncludeModuleEx(self::$module_id);
            if ($module_status < 3 && $module_status > 0) {
                return array('POINT');
            }
            return false;
        }

        public static function Calculate($profile, $arConfig, $arOrder, $STEP, $TEMP = false)
        {
            $module_status = \CModule::IncludeModuleEx(self::$module_id);
            if ($module_status < 3 && $module_status > 0) {

                if (isset($_SESSION['yandexdelivery_point'])) {
                    CModule::IncludeModule(self::$module_id);
                    $point = $_SESSION['yandexdelivery_point'];
                    $arPrice = \CCtwebYandexDelivery::calculatePrice(
                        $point['calculated']['regionID'],
                        $point['calculated']['storeID'],
                        $point['calculated']['distance']
                    );

                    if ($arPrice === false)
                        return array(
                            "RESULT" => "ERROR",
                            "TEXT" => GetMessage('ERROR_NO_POINT_TEXT') . ' ' . self::getLink()
                        );

                    if ($arPrice['PRICE_FREE'] > 0 && $arOrder['PRICE'] >= $arPrice['PRICE_FREE'])
                        $arPrice['SET_FREE'] = 'Y';

                    foreach (GetModuleEvents(self::$module_id, 'OnAfterYandexDeliveryCalculatePrice', true) as $arr) {
                        ExecuteModuleEventEx($arr, array(
                            &$arPrice,
                            $arOrder,
                            array(
                                'DISTANCE' => $point['calculated']['distance'],
                                'REGION_ID' => $point['calculated']['regionID'],
                                'STORE_ID' => $point['calculated']['storeID'],
                            )
                        ));
                    }

                    if ($arPrice['SET_FREE'] === 'Y')
                        $arPrice['PRICE'] = 0;

                    $address = $point['calculated']['address'];

                    return array(
                        "RESULT" => "OK",
                        'VALUE' => $arPrice['PRICE'],
                        "TRANSIT" => $address
                    );
                } else {
                    return array(
                        "RESULT" => "ERROR",
                        "TEXT" => GetMessage('ERROR_NO_POINT_TEXT') . ' ' . self::getLink()
                    );
                }
            } else {
                $message = GetMessage('ERROR_DEMO_EXPIRED_TEXT');
                return array(
                    "RESULT" => "ERROR",
                    "TEXT" => $message
                );
            }
        }

        public static function OnOrderAdd($ID, $arFields, $orderFields, $isNew)
        {
            if ($isNew) {
                if (\CModule::IncludeModule('sale')) {

                    $allDeliverys = \Bitrix\Sale\Delivery\Services\Manager::getActiveList();
                    $arDelivery = array_keys(array_filter($allDeliverys, function ($profiles) {
                        if (strpos($profiles['CODE'], self::$module_id) !== false) {
                            return true;
                        }
                    }));

                    if (in_array($arFields['DELIVERY_ID'], $arDelivery)) {
                        $point = $_SESSION['yandexdelivery_point'];

                        if ($point) {
                            $obStore = \Ctweb\YandexDelivery\Store::getByID(intval($point['calculated']['storeID']));

                            $address_prop_code = Option::get(self::$module_id, 'FIELD_ADDRESS_PROP_CODE', 'ADDRESS');

                            $val = $_SESSION['yandexdelivery_point']['calculated']['address'];
//                            $val = "(" . GetMessage("CW_YD_DELIVERY_FROM") . ": [{$obStore->getName()}] {$obStore->getAddress()} " . GetMessage("CW_YD_DELIVERY_TO") . " {$_SESSION['yandexdelivery_point']['calculated']['address']}. GEO: [{$point['pointTo'][0]}, {$point['pointTo'][1]}])";

                            if ($arProp = \CSaleOrderProps::GetList(array(), array('PERSON_TYPE_ID' => $arFields['PERSON_TYPE_ID'], 'CODE' => $address_prop_code))->Fetch()) {
//                                if (!empty($orderFields['ORDER_PROP'][intval($arProp['ID'])]))
//                                    $val = $orderFields['ORDER_PROP'][intval($arProp['ID'])] . " --- $val";
                                $res = self::AddOrderProperty($ID, $arProp['ID'], $val);
                            }

                            $orderLong = Option::get("eutils.yago", "long_prop", "");
                            $orderLat  = Option::get("eutils.yago", "lat_prop", "");
                            $orderStore  = Option::get("eutils.yago", "store_prop", "");
                            $distance  = Option::get("eutils.yago", "distance_prop", "");
                            $region  = Option::get("eutils.yago", "region_prop", "");
                            if ($orderLong != "" && $arProp = \CSaleOrderProps::GetList(array(), array('PERSON_TYPE_ID' => $arFields['PERSON_TYPE_ID'], 'CODE' => $orderLong))->Fetch()) {
                                $val = $point['pointTo'][0];
                                $res = self::AddOrderProperty($ID, $arProp['ID'], $val);
                            }
                            if ($orderLat != "" && $arProp = \CSaleOrderProps::GetList(array(), array('PERSON_TYPE_ID' => $arFields['PERSON_TYPE_ID'], 'CODE' => $orderLat))->Fetch()) {
                                $val = $point['pointTo'][1];
                                $res = self::AddOrderProperty($ID, $arProp['ID'], $val);
                            }
                            if ($orderStore != "" && $arProp = \CSaleOrderProps::GetList(array(), array('PERSON_TYPE_ID' => $arFields['PERSON_TYPE_ID'], 'CODE' => $orderStore))->Fetch()) {
                                $val = intval($point['calculated']['storeID']);
                                $res = self::AddOrderProperty($ID, $arProp['ID'], $val);
                            }
                            if ($distance != "" && $arProp = \CSaleOrderProps::GetList(array(), array('PERSON_TYPE_ID' => $arFields['PERSON_TYPE_ID'], 'CODE' => $distance))->Fetch()) {
                                $val = intval($point['calculated']['distance']);
                                $res = self::AddOrderProperty($ID, $arProp['ID'], $val);
                            }
                            if ($region != "" && $arProp = \CSaleOrderProps::GetList(array(), array('PERSON_TYPE_ID' => $arFields['PERSON_TYPE_ID'], 'CODE' => $region))->Fetch()) {
                                $val = $point['calculated']['regionID'];
                                $res = self::AddOrderProperty($ID, $arProp['ID'], $val);
                            }

                            unset($_SESSION['yandexdelivery_point']);
                        }
                    }
                }
            }
        }

        function AddOrderProperty($order, $prop_id, $value)
        {
            if (!strlen($prop_id)) {
                return false;
            }
            if (\CModule::IncludeModule('sale')) {
                if ($arOrderProps = \CSaleOrderProps::GetByID($prop_id)) {
                    $db_vals = \CSaleOrderPropsValue::GetList(array(), array('ORDER_ID' => $order, 'ORDER_PROPS_ID' => $arOrderProps['ID']));
                    if ($arVals = $db_vals->Fetch()) {
                        return \CSaleOrderPropsValue::Update($arVals['ID'], array(
                            'NAME' => $arVals['NAME'],
                            'CODE' => $arVals['CODE'],
                            'ORDER_PROPS_ID' => $arVals['ORDER_PROPS_ID'],
                            'ORDER_ID' => $arVals['ORDER_ID'],
                            'VALUE' => $value,
                        ));
                    } else {
                        return \CSaleOrderPropsValue::Add(array(
                            'NAME' => $arOrderProps['NAME'],
                            'CODE' => $arOrderProps['CODE'],
                            'ORDER_PROPS_ID' => $arOrderProps['ID'],
                            'ORDER_ID' => $order,
                            'VALUE' => $value,
                        ));
                    }
                }
            }
        }
    }

    AddEventHandler("sale", "onSaleDeliveryHandlersBuildList", array('CDeliveryCtwebYandexDelivery', 'Init'));
    AddEventHandler("sale", "OnSaleComponentOrderJsData", array('CDeliveryCtwebYandexDelivery', 'ShowComponent'));
    AddEventHandler("sale", "OnOrderSave", array('CDeliveryCtwebYandexDelivery', 'OnOrderAdd'));
}
