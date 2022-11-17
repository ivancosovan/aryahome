<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php"); ?>
<?
use Bitrix\Main\Config\Option;
use Ctweb\YandexDelivery\Region;
use Ctweb\YandexDelivery\Store;
use \Bitrix\Main\Web\Json;

$MODULE_ID = 'ctweb.yandexdelivery';
\CModule::includeModule($MODULE_ID);

$arYmapParams = array();
if ($apikey = Option::get('fileman', 'yandex_map_api_key', false))
	$arYmapParams['apikey'] = $apikey;


$arRegions = Region::GetList();
$arRegionsJS = [];
foreach ($arRegions as $reg) {
    $arRegionsJS[$reg->getId()] = $reg->GetFieldsArray();
}

$arStores = Store::GetList();
$store_id = intval($_REQUEST['STORE_ID']);
if ($store_id > 0)
    $current = &$arStores[$store_id];
else
    $current = $arStores[0] = Store::GetNew();
$arStoresJS = [];
foreach ($arStores as $store) {
    $arStoresJS[$store->getId()] = $store->GetFieldsArray();
}

#
#   Saving region
#
if (check_bitrix_sessid() && !empty($_POST['STORE']) && !$_POST['DELETE']) {
    if (intval($_POST['STORE']['ID']) > 0)
        $obStore = Store::GetByID(intval($_POST['STORE']['ID']));
    else
        $obStore = Store::GetNew();

    $obStore->setActive(($_POST['STORE']['ACTIVE'] === 'Y'));
    $obStore->setName($_POST['STORE']['NAME']);
    $obStore->setAddress($_POST['STORE']['ADDRESS']);
    $obStore->setDescription($_POST['STORE']['DESCRIPTION']);
    $point = Json::decode($_POST['STORE']['POINT']);
    if (!is_array($point) || empty($point)) {
        $point = array();
    }
    $obStore->setPoint($point);

    $obStore->Save();
    $closeWindowAndReload = true;
}
#
#   Delete region
#
elseif (check_bitrix_sessid() && $_POST['DELETE'] && $_POST['DELETE'] === 'Y' && intval($_POST['STORE']['ID']) > 0) {

    Store::DeleteByID(intval($_POST['STORE']['ID']));
    $closeWindowAndReload = true;
}

if ($closeWindowAndReload): ?>
    <script>
        BX.showWait();
        top.BX.WindowManager.Get().AllowClose();
        top.BX.WindowManager.Get().Close();
        window.location.reload();
    </script>
<? else: ?>
    <div id="cwMapModal">
        <div class="field-list">
            <form method="post" id="cwEditForm">
                <?= bitrix_sessid_post(); ?>
                <input type="hidden" name="STORE[ID]" value="<?= $current->getId(); ?>">
                <table class="adm-detail-content-table edit-table">
                    <? if ($current->getId() > 0) : ?>
                        <tr>
                            <td class="adm-detail-content-cell-l"><?= GetMessage('CW_YD_FIELD_ID'); ?></td>
                            <td class="adm-detail-content-cell-r"><?= $current->getId(); ?></td>
                        </tr>
                    <? endif; ?>
                    <tr>
                        <td class="adm-detail-content-cell-l"><?= GetMessage('CW_YD_FIELD_ACTIVE'); ?></td>
                        <td class="adm-detail-content-cell-r"><input type="checkbox"
                                                                     name="STORE[ACTIVE]"
                                                                     value="Y"<?= ($current->getActive()) ? ' checked' : ''; ?>></td>
                    </tr>
                    <tr>
                        <td class="adm-detail-content-cell-l"><?= GetMessage('CW_YD_FIELD_NAME'); ?></td>
                        <td class="adm-detail-content-cell-r"><input type="text" name="STORE[NAME]"
                                                                     value="<?= $current->getName(); ?>"></td>
                    </tr>
                    <tr>
                        <td class="adm-detail-content-cell-l"><?= GetMessage('CW_YD_FIELD_ADDRESS'); ?></td>
                        <td class="adm-detail-content-cell-r"><input type="text" name="STORE[ADDRESS]"
                                                                     value="<?= $current->getAddress(); ?>"></td>
                    </tr>
                    <tr>
                        <td class="adm-detail-content-cell-l"><?= GetMessage('CW_YD_FIELD_DESCRIPTION'); ?></td>
                        <td class="adm-detail-content-cell-r"><textarea name="STORE[DESCRIPTION]" cols="80"><?= $current->getDescription(); ?></textarea></td>
                    </tr>
                </table>
            </form>
        </div>
        <div class="body">
            <div id="map" style="width: auto; height: 400px;"></div>
        </div>
    </div>
    <script>

        var btnSave = Object.assign(BX.CDialog.btnSave),
            btnDelete = {
                title: '<?= GetMessage('CW_YD_DELETE'); ?>',
                id: 'btndelete',
                name: 'btndelete',
                action: function () {
                    if (confirm('<?= GetMessage('CW_YD_DELETE_CONFIRM'); ?>')) {
                        this.disableUntilError();
                        BX.Ctweb.YandexDelivery.Editor.prepareStoreDelete();
                        this.parentWindow.PostParameters();
                    }
                }
            },
            btnResetPoint = {
                title: '<?= GetMessage('CW_YD_RESET_POINT'); ?>',
                action: function () {
                    BX.Ctweb.YandexDelivery.Editor.setPoint();
                }
            };
        btnSave.action = function () {
            this.disableUntilError();
            BX.Ctweb.YandexDelivery.Editor.prepareStoreSave();
            this.parentWindow.PostParameters();
        };

        BX.WindowManager.Get().SetButtons([btnSave, BX.CDialog.prototype.btnCancel, btnResetPoint, <?= ($current->getId() > 0 && $current->isCustom()) ? 'btnDelete' : ''; ?>]);

        if (typeof BX.Ctweb.YandexDelivery.Editor === 'object') {
            BX.Ctweb.YandexDelivery.loadymaps(<?= Json::encode($arYmapParams ?: []) ?>, function () {
                ymaps.ready(function () {
                    BX.Ctweb.YandexDelivery.Editor.regions = <?= Json::encode($arRegionsJS); ?>;
                    BX.Ctweb.YandexDelivery.Editor.stores = <?=Json::encode($arStoresJS);?>;
                    BX.Ctweb.YandexDelivery.Editor._supported = true;
                    BX.Ctweb.YandexDelivery.Editor.initStoreEdit(<?= $current->GetId(); ?>);
                });
            });
        } else {
            console.error('ctweb.yandexdelivery: No main script included');
        }
    </script>
<? endif; ?>