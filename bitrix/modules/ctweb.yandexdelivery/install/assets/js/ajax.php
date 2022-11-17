<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule("ctweb.yandexdelivery");

use \Bitrix\Main\Web\Json;

$response = array('ERROR' => "Error");
if ($_POST['action']) {
    switch ($_POST['action']) {
        case 'calculate':

            $region_id = intval($_POST['region_id']);
            $store_id = $_POST['store_id'];
            $distance = floatval($_POST['distance']);

            $price = \CCtwebYandexDelivery::calculatePrice($region_id, $store_id, $distance);

            $response = $price;
            break;
        case 'save_point':
            session_start();
            $point = $_POST['point'];
            $_SESSION['yandexdelivery_point'] = $point;
            $response = true;
            break;
        case 'check_point':
            session_start();
            $response = isset($_SESSION['yandexdelivery_point']);
            break;
        case 'remove_point':
            session_start();
            unset($_SESSION['yandexdelivery_point']);
            $response = true;
            break;
    }
}

header('Content-Type: application/json');
echo Json::encode($response);

?>