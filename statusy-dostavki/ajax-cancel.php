<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
use \Bitrix\Main\Web\Json;
\Bitrix\Main\Loader::includeModule("eutils.yago");

$cancelResult = YaGo::orderCancel($_POST['id'],floatval($_POST['version']),$_POST['state']);
$response = array('SUCCESS' => $cancelResult);


header('Content-Type: application/json');
echo Json::encode($response);

?>
