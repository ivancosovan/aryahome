<?
		if (CModule::IncludeModule("sale"))
			{
				//Delette props
				$db_props = CSaleOrderProps::GetList(
					array("ID" => "ASC"),
					array("CODE" => array('EUTILS_YAGO_ISORDERED')),
					false,
					false,
					array()
				);
				$PropsGroupID = array();
					while ($props = $db_props->Fetch())
					{
						$PropsGroupID[] = $props["PROPS_GROUP_ID"];
						CSaleOrderProps::Delete($props["ID"]);
					}
				//Delette groups
				foreach($PropsGroupID as $PropGroup) {
					CSaleOrderPropsGroup::Delete($PropGroup);
				}
			}
?>
