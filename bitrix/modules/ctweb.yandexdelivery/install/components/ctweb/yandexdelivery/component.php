<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Config\Option as Option;

$MODULE_ID = 'ctweb.yandexdelivery';

\CModule::includeModule($MODULE_ID);
$bModlueCatalog = \CModule::IncludeModuleEx('catalog');

$arOptions = Option::getForModule($MODULE_ID);

#
#   arParams Preparing
#
$this->InitComponentTemplate();

if (is_file(($path = $_SERVER['DOCUMENT_ROOT'] . $this->GetTemplate()->GetFolder() . "/parameters.php"))) {
	$arParams2 = include $path;

	if (is_array($arParams2))
		$arParams = array_merge($arParams, $arParams2);
}

#
#   Regions preparing
#
$arrRegionFilter = (strlen($arParams['REGION_FILTER_NAME']) > 0 && !empty($GLOBALS[$arParams['REGION_FILTER_NAME']])) ? $GLOBALS[$arParams['REGION_FILTER_NAME']] : array();
$arRegionFilter = array();
$arRegionFilter['ACTIVE'] = 'Y';

#
#   Store preparing
#
$arrStoreFilter = (strlen($arParams['STORE_FILTER_NAME']) > 0 && !empty($GLOBALS[$arParams['STORE_FILTER_NAME']])) ? $GLOBALS[$arParams['STORE_FILTER_NAME']] : array();
$arStoreFilter = array();
$arStoreFilter['ACTIVE'] = 'Y';


#
#   Order page test
#
if (class_exists('SaleOrderAjax')) {
	$arResult['IS_ORDER_PAGE'] = 'Y';
}
if (CModule::IncludeModule('sale')) {
	$arResult['IS_PRICE_EXISTS'] = 'Y';
	// Get total price with discounts. Backdoor by Bitrix
	$basket = \Bitrix\Sale\Basket::loadItemsForFUser(\Bitrix\Sale\Fuser::getId(), \Bitrix\Main\Context::getCurrent()->getSite());
	$order  = \Bitrix\Sale\Order::create( \Bitrix\Main\Context::getCurrent()->getSite() , \Bitrix\Sale\Fuser::getId());
	$order ->setPersonTypeId( 1 );
	$order ->setBasket( $basket );
	$discounts  =  $order->getDiscount();
	$res  =  $discounts->getApplyResult();
	$price = $order->getPrice(); // Order Price
	$price = $basket->getPrice(); // Basket Price
	$currency = preg_replace('/[\d\s]/', '', CurrencyFormat(0, $order->getCurrency()));
}


$dbRegions = \Ctweb\YandexDelivery\Region::GetList(
	array(),
	array_merge($arRegionFilter, $arrRegionFilter)
);
$dbStores = \Ctweb\YandexDelivery\Store::GetList(
	array(),
	array_merge($arStoreFilter, $arrStoreFilter)
);

$arResult['REGIONS'] = array();
$arResult['STORES'] = array();
foreach ($dbRegions as $reg) {
	$arResult['REGIONS'][] = $reg->GetFieldsArray();
}
foreach ($dbStores as $store) {
	$arResult['STORES'][] = $store->GetFieldsArray();
}


#
#   API version test
#
if ($arParams['OLD_VERSION'] === 'Y')
	$arResult['OLD_VERSION'] = 'Y';


$arResult['MESSAGES'] = array(
	'NO_POINT' => $arOptions['FIELD_POINT_NO_DELIVERY'],
	'DESCRIPTION' => $arOptions['FIELD_MODAL_DESCRIPTION'],
);

#
#   Ymaps params
#
$arResult['YMAPS_PARAMS'] = array();
if ($apikey = Option::get('fileman', 'yandex_map_api_key', false))
	$arResult['YMAPS_PARAMS']['apikey'] = $apikey;

#
#   JSparams
#

$mainID = $this->GetEditAreaId('');
$arResult['JSPARAMS'] = array(
	'TEMPLATE' => array(
		'MAP' => $mainID . "map",
		'ADDRESS' => $mainID . "address",
		'PRICE' => $mainID . "price",
		'SPINNER' => $mainID . "spinner",
	),
	'OPTIONS' => array(
		'REGION_OPACITY' => &$arParams['REGION_OPACITY'],
		'DEFAULT_ZOOM' => &$arParams['MAP_ZOOM'],
		'ROUTE_OPACITY' => &$arParams['ROUTE_OPACITY'],
		'ROUTE_COLOR' => &$arParams['ROUTE_COLOR'],
	),
	'DATA' => array(
		'REGIONS' => &$arResult['REGIONS'],
		'STORES' => &$arResult['STORES'],
	),
	'MESSAGES' => array(
		'NOT_ENOUGH_PRICE' => $arOptions['FIELD_MESSAGE_NOT_ENOUGH_PRICE'],
		'POINT_NO_DELIVERY' => $arOptions['FIELD_POINT_NO_DELIVERY'],
	),
	'MODULE_OPTIONS' => $arOptions
);

if ($arResult['IS_PRICE_EXISTS'] === 'Y') {
	$arResult['JSPARAMS']['DATA']['ORDER'] = array('PRICE' => $price, 'CURRENCY' => $currency);
}

if ($arResult['IS_ORDER_PAGE'] === 'Y') {
	$allDeliverys = \Bitrix\Sale\Delivery\Services\Manager::getActiveList();
	$arResult['JSPARAMS']['DATA']['DELIVERY'] = array_values(array_filter($allDeliverys, function ($profiles) use ($MODULE_ID) {
		if (strpos($profiles['CODE'], $MODULE_ID) !== false) {
			return true;
		}
	}));
}

$this->ShowComponentTemplate();
return $mainID;
?>