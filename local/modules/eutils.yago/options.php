<?

use Bitrix\Sale;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\HttpApplication;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);

$request = HttpApplication::getInstance()->getContext()->getRequest();

$module_id = htmlspecialcharsbx($request["mid"] != "" ? $request["mid"] : $request["id"]);

Loader::includeModule($module_id);
$saleStatusIterator = CSaleStatus::GetList(array("SORT" => "ASC"), array(), false, false, array("ID","NAME"));
         while ($row = $saleStatusIterator->Fetch()) {
             $statuses[$row['ID']] = $row['NAME'];
         }
$db_ptype_pay = CSalePaySystem::GetList($arOrder = Array("ID"=>"ASC", "PSA_NAME"=>"ASC"), Array("ACTIVE"=>"Y"));
while ($ptype = $db_ptype_pay->Fetch())
{
   $paySystems[$ptype['ID']] = $ptype['NAME'];
}
$res = \Bitrix\Sale\Delivery\Services\Table::getList(array('filter' => array('ACTIVE' => 'Y')));
               while ($dev = $res->Fetch()) {
                   $deliverySystems[$dev['ID']] = $dev['NAME'];
               }
$aTabs = array(
    array(
        "DIV"     => "edit",
        "TAB"     => Loc::getMessage("EUTILS_YAGO_OPTIONS_TAB_NAME"),
        "TITLE"   => Loc::getMessage("EUTILS_YAGO_OPTIONS_TAB_NAME"),
        "OPTIONS" => array(
            Loc::getMessage("EUTILS_YAGO_OPTIONS_TAB_COMMON"),
            array(
                "switch_on",
                Loc::getMessage("EUTILS_YAGO_OPTIONS_TAB_SWITCH_ON"),
                "N",
                array("checkbox")
            ),
            array(
                "log_on",
                Loc::getMessage("EUTILS_YAGO_OPTIONS_TAB_LOG_ENABLED"),
                "N",
                array("checkbox")
            ),
//             array(
//                 "http",
//                 Loc::getMessage("EUTILS_YAGO_OPTIONS_TAB_HTTPS_ON"),
//                 "N",
//                 array("checkbox")
//             ),
//            array(
//                "pvz_on",
//                Loc::getMessage("EUTILS_YAGO_OPTIONS_TAB_PVZ_ON"),
//                "N",
//                array("checkbox")
//            ),
            array(
                "sms_true",
                Loc::getMessage("EUTILS_YAGO_OPTIONS_TAB_SOURCE_SMS_TRUE"),
                "N",
                array("checkbox")
            ),
            array(
                "sms_true_destination",
                Loc::getMessage("EUTILS_YAGO_OPTIONS_TAB_SOURCE_SMS_TRUE_DESTINATION"),
                "N",
                array("checkbox")
            ),
            array(
                "token",
                Loc::getMessage("EUTILS_YAGO_OPTIONS_TAB_TOKEN"),
                "",
                array("text", 35),
                "",
                "*"
            ),
            array(
                "courier_weight",
                Loc::getMessage("EUTILS_YAGO_OPTIONS_TAB_COURIER_W"),
                "10000",
                array("text", 35),
                "",
                "*"
            ),
            array(
                "express_weight",
                Loc::getMessage("EUTILS_YAGO_OPTIONS_TAB_EXPRESS_W"),
                "50000",
                array("text", 35),
                "",
                "*"
            ),
            array(
                "pickup_name",
                Loc::getMessage("EUTILS_YAGO_OPTIONS_TAB_PICKUP_NAME"),
                "",
                array("text", 35),
                "",
                "*"
            ),
            array(
                "emergency_name",
                Loc::getMessage("EUTILS_YAGO_OPTIONS_TAB_EMERGENCY_NAME"),
                "",
                array("text", 35),
                "",
                "*"
            ),
            array(
                "emergency_phone",
                Loc::getMessage("EUTILS_YAGO_OPTIONS_TAB_EMERGENCY_PHONE"),
                "",
                array("text", 35),
                "",
                "*"
            ),
            array(
                "timer",
                Loc::getMessage("EUTILS_YAGO_OPTIONS_TAB_TIMER"),
                "1800",
                array("text", 35),
                "",
                "*"
            ),
            array(
                "max_reorder",
                Loc::getMessage("EUTILS_YAGO_OPTIONS_TAB_MAX_REORDER"),
                "5",
                array("text", 35),
                "",
                "*"
            ),
            array(
                "due",
                Loc::getMessage("EUTILS_YAGO_OPTIONS_TAB_DUE"),
                "",
                array("text", 35),
                ""
            ),
            array(
                "common_comment",
                Loc::getMessage("EUTILS_YAGO_OPTIONS_TAB_COMMON_COMMENT"),
                "",
                array("textarea", 10, 40)
            ),
            array(
                "pvz_comment",
                Loc::getMessage("EUTILS_YAGO_OPTIONS_TAB_PVZ_COMMENT"),
                "",
                array("textarea", 10, 40)
            ),
            Loc::getMessage("EUTILS_YAGO_OPTIONS_TAB_COND"),
            array(
                "only_partner",
                Loc::getMessage("EUTILS_YAGO_OPTIONS_TAB_ONLY_PARTNER"),
                "N",
                array("checkbox")
            ),
            array(
                "status_id",
                Loc::getMessage("EUTILS_YAGO_OPTIONS_TAB_STATUS_ID"),
                "OA",
                array("selectbox", $statuses),
                "",
                "*"
            ),
            array(
                "finish_status",
                Loc::getMessage("EUTILS_YAGO_OPTIONS_TAB_STATUS_FINISH"),
                "D",
                array("selectbox", $statuses)
            ),
            array(
                "paysystems",
                Loc::getMessage("EUTILS_YAGO_OPTIONS_TAB_PAY_ID"),
                null,
                array("multiselectbox", $paySystems)
            ),
            array(
                "delsystems",
                Loc::getMessage("EUTILS_YAGO_OPTIONS_TAB_DEL_ID"),
                null,
                array("multiselectbox", $deliverySystems)
            ),
            array(
                "region_id",
                Loc::getMessage("EUTILS_YAGO_OPTIONS_TAB_REGION_ID"),
                "",
                array("text", 35)
            ),
            array(
                "distance_min",
                Loc::getMessage("EUTILS_YAGO_OPTIONS_TAB_DISTANCE_MIN"),
                "",
                array("text", 35)
            ),
            array(
                "distance_max",
                Loc::getMessage("EUTILS_YAGO_OPTIONS_TAB_DISTANCE_MAX"),
                "",
                array("text", 35)
            ),
            Loc::getMessage("EUTILS_YAGO_OPTIONS_TAB_PROPERTIES"),
            array(
                "distance_prop",
                Loc::getMessage("EUTILS_YAGO_OPTIONS_TAB_DISTANCE_PROP"),
                "DISTANCE",
                array("text", 35),
                "",
                "*"
            ),
            array(
                "is_partner_prop",
                Loc::getMessage("EUTILS_YAGO_OPTIONS_TAB_PARTNER_PROP"),
                "",
                array("text", 35),
                "",
                "*"
            ),
            array(
                "region_prop",
                Loc::getMessage("EUTILS_YAGO_OPTIONS_TAB_REGION_PROP"),
                "REGION_ID",
                array("text", 35),
                "",
                "*"
            ),
            array(
                "store_prop",
                Loc::getMessage("EUTILS_YAGO_OPTIONS_TAB_STORE_PROP"),
                "STORE_ID",
                array("text", 35),
                "",
                "*"
            ),
            array(
                "long_prop",
                Loc::getMessage("EUTILS_YAGO_OPTIONS_TAB_LONG_PROP"),
                "D_LONG",
                array("text", 35),
                "",
                "*"
            ),
            array(
                "lat_prop",
                Loc::getMessage("EUTILS_YAGO_OPTIONS_TAB_LAT_PROP"),
                "D_LAT",
                array("text", 35),
                "",
                "*"
            ),
            array(
                "podezd_prop",
                Loc::getMessage("EUTILS_YAGO_OPTIONS_TAB_PODEZD_PROP"),
                "PODEZD",
                array("text", 35),
                "",
                "*"
            ),
            array(
                "room_prop",
                Loc::getMessage("EUTILS_YAGO_OPTIONS_TAB_ROOM_PROP"),
                "APARTAMENT",
                array("text", 35),
                "",
                "*"
            ),
            array(
                "floor_prop",
                Loc::getMessage("EUTILS_YAGO_OPTIONS_TAB_FLOOR_PROP"),
                "FLOOR",
                array("text", 35),
                "",
                "*"
            ),
            array(
                "code_prop",
                Loc::getMessage("EUTILS_YAGO_OPTIONS_TAB_DOOR_CODE_PROP"),
                "INTERCOM",
                array("text", 35),
                "",
                "*"
            ),
            array(
                "address_prop",
                Loc::getMessage("EUTILS_YAGO_OPTIONS_TAB_ADDRESS_PROP"),
                "ADDRESS",
                array("text", 35),
                "",
                "*"
            ),
       )
   )
);
if($request->isPost() && check_bitrix_sessid()){

    foreach($aTabs as $aTab){

       foreach($aTab["OPTIONS"] as $arOption){

           if(!is_array($arOption)){

               continue;
           }

           if($arOption["note"]){

                continue;
           }

           if($request["apply"]){

                $optionValue = $request->getPost($arOption[0]);

              if($arOption[0] == "switch_on" || $arOption[0] == "http" || $arOption[0] == "log_on" || $arOption[0] == "pvz_on" || $arOption[0] == "only_partner" || $arOption[0] == "sms_true" || $arOption[0] == "sms_true_destination"){

                  if($optionValue == ""){

                       $optionValue = "N";
                   }
               }

               Option::set($module_id, $arOption[0], is_array($optionValue) ? implode(",", $optionValue) : $optionValue);
            }elseif($request["default"]){

             Option::set($module_id, $arOption[0], $arOption[2]);
            }
       }
   }

   LocalRedirect($APPLICATION->GetCurPage()."?mid=".$module_id."&lang=".LANG);
}
$tabControl = new CAdminTabControl(
  "tabControl",
 $aTabs
);

$tabControl->Begin();?>
<form action="<? echo($APPLICATION->GetCurPage()); ?>?mid=<? echo($module_id); ?>&lang=<? echo(LANG); ?>" method="post">

  <?
   foreach($aTabs as $aTab){

       if($aTab["OPTIONS"]){

         $tabControl->BeginNextTab();

         __AdmSettingsDrawList($module_id, $aTab["OPTIONS"]);
      }
   }?>
   <?$tabControl->Buttons();
  ?>

   <input type="submit" name="apply" value="<? echo(Loc::GetMessage("EUTILS_YAGO_OPTIONS_INPUT_APPLY")); ?>" class="adm-btn-save" />
    <input type="submit" name="default" value="<? echo(Loc::GetMessage("EUTILS_YAGO_OPTIONS_INPUT_DEFAULT")); ?>" />

   <?
   echo(bitrix_sessid_post());
 ?>

</form>
<?$tabControl->End();?>
