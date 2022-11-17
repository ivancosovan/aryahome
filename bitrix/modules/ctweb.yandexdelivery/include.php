<?
defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

use \Bitrix\Main\Config\Option;
use Ctweb\YandexDelivery\Region;

$MODULE_DIR = substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT']));
$MODULE_ID = 'ctweb.yandexdelivery';

CModule::AddAutoloadClasses(
    $MODULE_ID,
    array(
        'Ctweb\YandexDelivery\CAdminFormCustom' => "lib/classes/CAdminFormCustom.php",
        'Ctweb\YandexDelivery\Region' => "lib/classes/region.class.php",
        'Ctweb\YandexDelivery\Store' => "lib/classes/store.class.php",
    )
);

CJSCore::RegisterExt('cwMapOptionsScript', array(
    'js' => BX_ROOT . '/js/' . $MODULE_ID . '/admin.js',
    'lang' => $MODULE_DIR . '/lang/'.LANGUAGE_ID.'/options.php',
    'rel' => array('popup', 'ajax', 'fx', 'ls', 'date', 'json'),
    'skip_core ' => true
));

global $APPLICATION;
global $arOptions;
IncludeModuleLangFile(__FILE__);
CModule::IncludeModule('sale');

require_once(__DIR__ .'/tools/delivery_ctwebyandexdelivery.php');

class CCtwebYandexDelivery {
    protected static $module_id='ctweb.yandexdelivery';

    static public function calculatePrice($regionID, $storageID, $distance, $currency = 'RUB') {
        $regionID = intval($regionID);
        $distance = floatval($distance);

        $obRegion = Region::GetByID(intval($regionID));
        if (!is_object($obRegion))
            return false;

        if (!empty($obRegion->getStores()) && !in_array($storageID, $obRegion->getStores()))
            return false;


        $arResult = array();
        $arResult['PRICE'] = $obRegion->getPriceFixed();
        $arResult['PRICE_FREE'] = $obRegion->getPriceFree();
        $arResult['PRICE'] += $distance/1000 * $obRegion->getPrice();
        $arResult['PRICE_MIN'] = $obRegion->getPriceMin();

        foreach(GetModuleEvents('ctweb.yandexdelivery', 'OnYandexDeliveryCalculatePrice', true) as $arr)
        {
            ExecuteModuleEventEx($arr, array(
                &$arResult,
                array(
                    'DISTANCE' => $distance,
                    'REGION_ID' => $regionID,
                    'STORE_ID' => $storageID,
                )
            ));
        }

        $arResult['PRICE_FORMATTED'] = CurrencyFormat($arResult['PRICE'], $currency);

        return $arResult;
    }
}

?>
