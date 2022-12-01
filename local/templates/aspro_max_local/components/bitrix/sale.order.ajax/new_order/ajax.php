<?php
define('STOP_STATISTICS', true);
define('NO_KEEP_STATISTIC', 'Y');
define('NO_AGENT_STATISTIC','Y');
define('DisableEventsCheck', true);
define('BX_SECURITY_SHOW_MESSAGE', true);
define('NOT_CHECK_PERMISSIONS', true);

$siteId = isset($_REQUEST['SITE_ID']) && is_string($_REQUEST['SITE_ID']) ? $_REQUEST['SITE_ID'] : '';
$siteId = mb_substr(preg_replace('/[^a-z0-9_]/i', '', $siteId), 0, 2);
if (!empty($siteId) && is_string($siteId)) {
	define('SITE_ID', $siteId);
}

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

$request = Bitrix\Main\Application::getInstance()->getContext()->getRequest();
$request->addFilter(new \Bitrix\Main\Web\PostDecodeFilter);

if (!Bitrix\Main\Loader::includeModule('sale')) {
	return;
}

$signer = new \Bitrix\Main\Security\Sign\Signer;
try {
	$signedParamsString = $request->get('signedParamsString') ?: '';
	$params = $signer->unsign($signedParamsString, 'sale.order.ajax');
	$params = unserialize(base64_decode($params), ['allowed_classes' => false]);
} catch (\Bitrix\Main\Security\Sign\BadSignatureException $e) {
	die();
}

$arPost = $request->get('order');
$arProps = $request->get('props');

$action = $request->get($params['ACTION_VARIABLE']);
if (empty($action) && !$arProps) {
	return;
}

function getPropsByFilter($array, $field, $value){
    return array_filter($array, function($item){
        return $item[$field] === $value;
    });
}

$arUserProps = getPropsByFilter($arProps['properties'], 'USER_PROPS', 'Y');

$profileNameProp = getPropsByFilter($arUserProps, 'IS_PROFILE_NAME', 'Y')[0];
$profileName = $profileNameProp ? $arPost['ORDER_PROP_'.$profileNameProp['ID']] : '';


$arFields = array(
   "NAME" => $profileName,
   "USER_ID" => $GLOBALS['USER']->GetID(),
   "PERSON_TYPE_ID" => $arPost['PERSON_TYPE']
);

$properties = [];
foreach ($arUserProps as $key => $value) {
    $properties[$value['ID']] = $arPost['ORDER_PROP_'.$value['ID']];
}

$arErrors = [];
$profileID = CSaleOrderUserProps::DoSaveUserProfile(
    $GLOBALS['USER']->GetID(),
    $arPost['PROFILE_ID'],
    $profileName,
    $arPost['PERSON_TYPE'],
    $properties,
    $arErrors
);

header('Content-Type: application/json');

echo \Bitrix\Main\Web\Json::encode(['profileID' => $profileID, 'error' => $arErrors]);
?>
