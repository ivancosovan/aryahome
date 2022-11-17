<?
if (!function_exists('htmlspecialcharsbx')) {
	function htmlspecialcharsbx($string, $flags=ENT_COMPAT) {
		return htmlspecialchars($string, $flags, (defined('BX_UTF')? 'UTF-8' : 'ISO-8859-1'));
	}
}

// \Bitrix\Main\Loader::registerAutoLoadClasses('eutils.yago', array(
// 	'\Eutils\Yago\Events\YaGo' => '/lib/events/yago.php',
// ));
CModule::AddAutoloadClasses(
	'eutils.yago',
	array(
		'YaGo' => 'classes/general/YaGo.php',
		'YaGoRequests' => 'classes/general/YaGoRequests.php',
	)
);
?>
