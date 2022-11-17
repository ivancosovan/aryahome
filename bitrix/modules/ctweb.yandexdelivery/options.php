<?
use Bitrix\Main\Localization\Loc,
    Bitrix\Main\Config\Option;
use Ctweb\YandexDelivery\Region;
use Ctweb\YandexDelivery\Store;
use Ctweb\YandexDelivery\CAdminFormCustom;

Loc::loadMessages(__FILE__);
Loc::loadMessages($_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/modules/main/options.php');

$MODULE_DIR = substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT']));
$MODULE_ID = 'ctweb.yandexdelivery';


if (!$USER->IsAdmin())
    $APPLICATION->AuthForm();

$module_state = CModule::IncludeModuleEx($MODULE_ID);
CModule::IncludeModule("iblock");

if ($module_state === 3) {
    echo preg_replace("/#MODULE_ID#/", $MODULE_ID, Loc::getMessage("MODULE_EXPIRED_DESCRIPTION_LINK"));
    return;
}

Bitrix\Main\Loader::includeModule($MODULE_ID);
Bitrix\Main\Loader::includeModule('iblock');
$bModlueCatalog = Bitrix\Main\Loader::includeModule('catalog');


$APPLICATION->SetAdditionalCSS(BX_ROOT . '/css/' . $MODULE_ID . '/admin.css');
$APPLICATION->AddHeadScript(BX_ROOT . '/js/' . $MODULE_ID . '/admin.js');

/*
 * DATA PREPARING
 */
require_once 'lib/classes/region.class.php';

$moduleOptions = Option::getForModule($MODULE_ID);

$bCatalogStores = $bModlueCatalog && ($moduleOptions['FIELD_USE_CATALOG_STORES'] == 1);

$arRegions = Region::GetList();
$arStores = Store::GetList();

$arIBlocks = array();
$obIBlocks = \CIBlock::GetList(
    array("SORT" => "ASC"),
    array("ACTIVE" => "Y")
);
while ($iblock = $obIBlocks->Fetch()) {
    $arIBlocks[$iblock["ID"]] = "[" . $iblock["ID"] . "] " . $iblock["NAME"];
}


/*
 *  SAVE SETTINGS
 */

// Import GeoJSON
if (check_bitrix_sessid() && !empty($_POST['POINTS'])) {
    foreach ($_POST['POINTS'] as $poly) {
        $newRegion = Region::getNew();
        // generated
        $newRegion->setName(Loc::getMessage('CW_YD_MODAL_NEW_REGION_NAME').$id);
        $newRegion->createPolygon(json_decode($poly));

        // save
        $newRegion->Save();
    }
    LocalRedirect($APPLICATION->GetCurPageParam());

}

if ($REQUEST_METHOD == 'POST' && ($_POST['save'] || $_POST['apply']) && check_bitrix_sessid()) {

    if (isset($_POST['DELETE_OBJECT'])) {
        Option::delete($MODULE_ID, array('name' => $_POST['DELETE_OBJECT']));
    } else {
        // Checkboxes
        $varFields = array(
            "FIELD_USE_CATALOG_STORES",
            "FIELD_ADDRESS_PROP_CODE",
            "FIELD_POINT_NO_DELIVERY",
            "FIELD_MESSAGE_NOT_ENOUGH_PRICE",
            "FIELD_MODAL_DESCRIPTION",
            "FIELD_ORDER_BUTTON_BEHAVIOR",
            "FIELD_MAP_CLICK_BEHAVIOR",
        );
        foreach ($varFields as $chk) {
            Option::set($MODULE_ID, $chk, $_POST[$chk]);
        }


        // Save Regions
        foreach (json_decode($_POST['REGION_PARAMS'], true) as $id => $reg) {
            Option::set($MODULE_ID, $id, addslashes(serialize($reg)));
        }
        // Save Stores
        $stores = json_decode($_POST['STORE_PARAMS'], true);
        if ($bCatalogStores) {
            foreach ($stores as $id => $store) {
                $sid = preg_replace('/[^\d]/', '', $id);
                $coords = explode(',', trim($store['COORDS'], '[]'));
                CCatalogStore::Update($sid, array(
                    'GPS_N' => $coords[0],
                    'GPS_S' => $coords[1]
                ));
            }
        } else {
            foreach ($stores as $id => $store) {
                Option::set($MODULE_ID, $id, addslashes(serialize($store)));
            }
        }
    }
    LocalRedirect($APPLICATION->GetCurPageParam());
    exit;
}

#
#   TAB CONTROL
#
$formId = 'ctweb_yandexdelivery_settings';

$aTabs = array();
$aTabs[] = array("DIV" => "sp_settings_tab", "TAB" => Loc::getMessage("CW_YD_SETTINGS_TITLE"), "ICON" => "main_user_edit", "TITLE" => Loc::getMessage("CW_YD_SETTINGS_TITLE"));

$tabControl = new CAdminFormCustom($formId, $aTabs);

$tabControl->Begin(array(
    "FORM_ACTION" => $APPLICATION->GetCurPage() . "?lang=" . LANG . "&mid=" . $MODULE_ID . "&mid_menu=1",
    "FORM_ATTRIBUTES" => "",
));

$tabControl->BeginEpilogContent();
echo bitrix_sessid_post();
$tabControl->EndEpilogContent();

#
#   TAB 1
$tabControl->BeginNextFormTab();

if (!empty($errors)) {
    $tabControl->ShowWarnings($formId, $errors);
}

if ($bModlueCatalog) {
    $tabControl->AddSection("CW_YD_GENERAL_SETTINGS_SECTION", Loc::getMessage("CW_YD_GENERAL_SETTINGS_SECTION"));
    $tabControl->AddDropDownField("FIELD_USE_CATALOG_STORES", Loc::getMessage("CW_YD_FIELD_STORE_TYPE"), false, array(0 => GetMessage('CW_YD_TYPE_CUSTOM_STORE'), 1 => GetMessage('CW_YD_TYPE_CATALOG_STORE')), Option::get($MODULE_ID, "FIELD_USE_CATALOG_STORES", 0));
    $tabControl->AddEditField("FIELD_ADDRESS_PROP_CODE", Loc::getMessage("CW_YD_FIELD_ADDRESS_PROP_CODE"), false, array(), Option::get($MODULE_ID, "FIELD_ADDRESS_PROP_CODE", "ADDRESS"));
}
$tabControl->AddDropDownField("FIELD_ORDER_BUTTON_BEHAVIOR", Loc::getMessage('CW_YD_FIELD_ORDER_BUTTON_BEHAVIOR'), false, array('hide'=>GetMessage('CW_YD_FIELD_ORDER_BUTTON_BEHAVIOR_HIDE'),'disable'=>GetMessage('CW_YD_FIELD_ORDER_BUTTON_BEHAVIOR_DISABLE')), Option::get($MODULE_ID, "FIELD_ORDER_BUTTON_BEHAVIOR", 0));

$tabControl->AddDropDownField("FIELD_MAP_CLICK_BEHAVIOR", Loc::getMessage('CW_YD_FIELD_MAP_CLICK_BEHAVIOR'), false, array('dblclick'=>GetMessage('CW_YD_FIELD_MAP_CLICK_BEHAVIOR_DBLCLICK'),'click'=>GetMessage('CW_YD_FIELD_MAP_CLICK_BEHAVIOR_CLICK')), Option::get($MODULE_ID, "FIELD_MAP_CLICK_BEHAVIOR", 0));

$tabControl->AddSection("CW_YD_STORES", Loc::getMessage("CW_YD_STORES_SECTION"));
foreach ($arStores as $id => $obStore) {
    $tabControl->AddStoreField('STORES', $obStore->getName(), $obStore);
}
$tabControl->AddViewField('ADD_STORE_BTN', '', "<input type='button' value='" . Loc::getMessage('CW_YD_BTN_ADD') . "' onclick='ShowYandexDeliveryStoreEdit();'>");


$tabControl->AddSection("CW_YD_REGIONS", Loc::getMessage("CW_YD_REGIONS"));
$tabControl->AddButton('BUTTON_REGION_IMPORT', Loc::getMessage('CW_YD_BUTTON_REGION_IMPORT'), false, array('ONCLICK' => 'ShowYandexDeliveryImportGeoJSON();'));

foreach ($arRegions as $obReg)
    $tabControl->AddRegionField('REGIONS', $obReg->getName(), $obReg);

$tabControl->AddViewField('ADD_REG_BTN', '', "<input type='button' value='" . Loc::getMessage('CW_YD_BTN_ADD') . "' onclick='ShowYandexDeliveryRegionEdit();'>");

$tabControl->AddSection("CW_YD_MESSAGES", Loc::getMessage("CW_YD_MESSAGES"));
$tabControl->AddTextField('FIELD_POINT_NO_DELIVERY', Loc::getMessage("FIELD_POINT_NO_DELIVERY"), Option::get($MODULE_ID, "FIELD_POINT_NO_DELIVERY", Loc::getMessage("FIELD_POINT_NO_DELIVERY_DEFAULT")), array('cols' => 80));
$tabControl->AddTextField('FIELD_MESSAGE_NOT_ENOUGH_PRICE', Loc::getMessage("FIELD_MESSAGE_NOT_ENOUGH_PRICE"), Option::get($MODULE_ID, "FIELD_MESSAGE_NOT_ENOUGH_PRICE", Loc::getMessage("FIELD_MESSAGE_NOT_ENOUGH_PRICE_DEFAULT")), array('cols' => 80));
$tabControl->AddTextField('FIELD_MODAL_DESCRIPTION', Loc::getMessage("FIELD_MODAL_DESCRIPTION"), Option::get($MODULE_ID, "FIELD_MODAL_DESCRIPTION", Loc::getMessage("FIELD_MODAL_DESCRIPTION_DEFAULT")), array('cols' => 80, 'rows' => 6));


#
#   Buttons
#
$tabControl->Buttons(array(
    "disabled" => false,
    "btnSave" => true,
    "btnCancel" => false,
    "btnSaveAndAdd" => false,
));

$tabControl->Show();
?>

<script type="text/javascript">
    var form = document.getElementById('<?= $formId ?>_form');

    function ShowYandexDeliveryRegionEdit(region_id = '') {
        obRegionDialog = new BX.CDialog({
            min_width: 940,
            width: 940,
            height: '100%',
            resizable: true,
            draggable: false,
            title: '<?= GetMessage('CW_YD_REGION_EDIT_TITLE') ?>',
            content_url: '<?=CUtil::JSEscape("/bitrix/admin/ctweb.yandexdelivery__region_edit.php")?>?REGION_ID='+region_id,
        });
        obRegionDialog.Show();
    }

    function ShowYandexDeliveryStoreEdit(store_id = '') {
        obStoreDialog = new BX.CDialog({
            min_width: 940,
            width: 940,
            height: '100%',
            resizable: true,
            draggable: false,
            title: '<?= GetMessage('CW_YD_STORE_EDIT_TITLE') ?> (<?= ($bCatalogStores) ? GetMessage('CW_YD_CATALOG_STORE') : GetMessage('CW_YD_CUSTOM_STORE')?>)',
            content_url: '<?=CUtil::JSEscape("/bitrix/admin/ctweb.yandexdelivery__store_edit.php")?>?STORE_ID='+store_id,
        });
        obStoreDialog.Show();
    }

    function ShowYandexDeliveryImportGeoJSON() {
        obImportDialog = new BX.CDialog({
            resizable: true,
            draggable: false,
            title: '<?= GetMessage('CW_YD_IMPORT_FROM_FILE') ?>',
            content_url: '<?=CUtil::JSEscape("/bitrix/admin/ctweb.yandexdelivery__import.php")?>?multiple=Y'
        });
        BX.addCustomEvent(obImportDialog, 'onWindowClose', function (ob) {
            var coordNodes = ob.GetForm().querySelectorAll('[name="POINTS[]"]');
            for (var i=0; i<coordNodes.length; i++) {
                form.appendChild(coordNodes[i]);
                form.submit();
            }
        });
        obImportDialog.Show();
    }
</script>
