<?
IncludeModuleLangFile(__FILE__);
if (CModule::IncludeModule("sale"))
{
	//Add Order Props and Groups
	$dbPersonTypes = CSalePersonType::GetList(Array("SORT" => "ASC"), Array());
	while ($ptype = $dbPersonTypes->Fetch())
	{
		$PropGroupID = CSaleOrderPropsGroup::Add(array("PERSON_TYPE_ID" => $ptype["ID"], "NAME" => GetMessage("ORDER_GROUP_NAME"), "SORT" => 100));
		$arFields = array(
		   "PERSON_TYPE_ID" => $ptype["ID"],
		   "NAME" => GetMessage("ORDER_PROP_YAGO_ISORDERED_NAME"),
		   "TYPE" => "NUMBER",
		   "REQUIED" => "N",
		   "DEFAULT_VALUE" => "1",
		   "SORT" => 100,
		   "CODE" => "EUTILS_YAGO_ISORDERED",
		   "USER_PROPS" => "N",
		   "IS_LOCATION" => "N",
		   "IS_LOCATION4TAX" => "N",
		   "PROPS_GROUP_ID" => $PropGroupID,
		   "SIZE1" => 0,
		   "SIZE2" => 0,
		   "DESCRIPTION" => "",
		   "IS_EMAIL" => "N",
		   "IS_PROFILE_NAME" => "N",
		   "IS_PAYER" => "N",
		);
		if($info = CModule::CreateModuleObject('sale'))
		{
			$testVersion = '18.6.350';
			$moduleVersion = $info->MODULE_VERSION;
			if(CheckVersion($moduleVersion, $testVersion) == true)
				$arFields["UTIL"] = "Y";
		}
		CSaleOrderProps::Add($arFields);
	}
}
?>
