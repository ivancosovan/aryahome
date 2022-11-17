<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php"); ?>
<?
use Bitrix\Main\Config\Option;
use Ctweb\YandexDelivery\Region;
use Ctweb\YandexDelivery\Store;
use \Bitrix\Main\Web\Json;

$MODULE_ID = 'ctweb.yandexdelivery';
CModule::includeModule($MODULE_ID);

$arYmapParams = array();
if ($apikey = Option::get('fileman', 'yandex_map_api_key', false))
	$arYmapParams['apikey'] = $apikey;

$arRegions = Region::GetList();
$region_id = intval($_REQUEST['REGION_ID']);
if ($region_id > 0)
    $current = &$arRegions[$region_id];
else
    $current = $arRegions[0] = Region::GetNew();
$arRegionsJS = [];
foreach ($arRegions as $reg) {
    $arRegionsJS[$reg->getId()] = $reg->GetFieldsArray();
}

$arStores = Store::GetList();
$arStoresJS = [];
foreach ($arStores as $store) {
    $arStoresJS[$store->getId()] = $store->GetFieldsArray();
}

#
#   Saving region
#
if (check_bitrix_sessid() && !empty($_POST['REGION']) && !$_POST['DELETE']) {
    if (intval($_POST['REGION']['ID']) > 0)
        $obReg = Region::GetByID(intval($_POST['REGION']['ID']));
    else
        $obReg = Region::GetNew();

    $obReg->setActive(($_POST['REGION']['ACTIVE'] === 'Y'));
    $obReg->setName($_POST['REGION']['NAME']);
    $obReg->setDescription($_POST['REGION']['DESCRIPTION']);
    $obReg->setPrice($_POST['REGION']['PRICE']);
    $obReg->setPriceFixed($_POST['REGION']['PRICE_FIXED']);
    $obReg->setPriceFree($_POST['REGION']['PRICE_FREE']);
    $obReg->setPriceMin($_POST['REGION']['PRICE_MIN']);
    $obReg->setColor($_POST['REGION']['COLOR']);
    $coords = Json::decode($_POST['REGION']['POINTS']);
    if (!is_array($coords) || empty($coords) || empty($coords[0])) {
        $coords = array();
    }
    $obReg->createPolygon($coords);
    $obReg->setStores($_POST['REGION']['STORES']);

    $obReg->Save();
    $closeWindowAndReload = true;
}
#
#   Delete region
#
elseif (check_bitrix_sessid() && $_POST['DELETE'] && $_POST['DELETE'] === 'Y' && intval($_POST['REGION']['ID']) > 0) {
    Region::DeleteByID(intval($_POST['REGION']['ID']));
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
                <input type="hidden" name="REGION[ID]" value="<?= $current->getId(); ?>">
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
                                                                     name="REGION[ACTIVE]"
                                                                     value="Y"<?= ($current->getActive() === 'Y') ? ' checked' : ''; ?>></td>
                    </tr>
                    <tr>
                        <td class="adm-detail-content-cell-l"><?= GetMessage('CW_YD_FIELD_NAME'); ?></td>
                        <td class="adm-detail-content-cell-r"><input type="text" name="REGION[NAME]"
                                                                     value="<?= $current->getName(); ?>"></td>
                    </tr>
                    <tr>
                        <td class="adm-detail-content-cell-l"><?= GetMessage('CW_YD_FIELD_DESCRIPTION'); ?></td>
                        <td class="adm-detail-content-cell-r"><textarea name="REGION[DESCRIPTION]" cols="80"><?= $current->getDescription(); ?></textarea></td>
                    </tr>
                    <tr>
                        <td class="adm-detail-content-cell-l"><?= GetMessage('CW_YD_FIELD_PRICE_FIXED'); ?></td>
                        <td class="adm-detail-content-cell-r"><input type="text" name="REGION[PRICE_FIXED]"
                                                                     value="<?= $current->getPriceFixed(); ?>"></td>
                    </tr>
                    <tr>
                        <td class="adm-detail-content-cell-l"><?= GetMessage('CW_YD_FIELD_PRICE'); ?></td>
                        <td class="adm-detail-content-cell-r"><input type="text" name="REGION[PRICE]"
                                                                     value="<?= $current->getPrice(); ?>"></td>
                    </tr>
                    <tr>
                        <td class="adm-detail-content-cell-l"><?= GetMessage('CW_YD_FIELD_PRICE_FREE'); ?></td>
                        <td class="adm-detail-content-cell-r"><input type="text" name="REGION[PRICE_FREE]"
                                                                     value="<?= $current->getPriceFree(); ?>"></td>
                    </tr>
                    <tr>
                        <td class="adm-detail-content-cell-l"><?= GetMessage('CW_YD_FIELD_PRICE_MIN'); ?></td>
                        <td class="adm-detail-content-cell-r"><input type="text" name="REGION[PRICE_MIN]"
                                                                     value="<?= $current->getPriceMin(); ?>"></td>
                    </tr>
                    <tr>
                        <td class="adm-detail-content-cell-l"><?= GetMessage('CW_YD_FIELD_COLOR'); ?></td>
                        <td class="adm-detail-content-cell-r"><input type="text" name="REGION[COLOR]"
                                                                     value="<?= $current->getColor(); ?>"></td>
                    </tr>
                    <tr>
                        <td class="adm-detail-content-cell-l"><?= GetMessage('CW_YD_FIELD_STORAGES')?></td>
                        <td class="adm-detail-content-cell-r">
                            <select name="REGION[STORES][]" multiple>
                                <? foreach ($arStores as $id => $store) : ?>
                                    <option value="<?=$id?>"<?= (array_search($id, $current->getStores()) !== false) ? ' selected' : ''; ?> ><?= "[{$id}] {$store->getName()}" ?></option>
                                <? endforeach; ?>
                            </select>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        <div class="body">
            <div id="map" style="width: auto; height: 400px;"></div>
	        <input type="checkbox" id="show_regions" value="1" checked>
	        <label for="show_regions"><?= GetMessage('CW_YD_SHOW_HIDE_OTHERS') ?></label>
        </div>
    </div>
    <script src="/bitrix/js/ctweb.yandexdelivery/admin.js"></script>
    <script>

        var btnSave = Object.assign(BX.CDialog.btnSave),
            btnDelete = {
                title: '<?= GetMessage('CW_YD_DELETE'); ?>',
                id: 'btndelete',
                name: 'btndelete',
                action: function () {
                    if (confirm('<?= GetMessage('CW_YD_DELETE_CONFIRM'); ?>')) {
                        this.disableUntilError();
                        BX.Ctweb.YandexDelivery.Editor.prepareRegionDelete();
                        this.parentWindow.PostParameters();
                    }
                }
            },
            btnResetPolygon = {
                title: '<?= GetMessage('CW_YD_RECREATE_POLYGON'); ?>',
                action: function () {
                    BX.Ctweb.YandexDelivery.Editor.createPoly();
                }
            },
            btnImport = {
                title: '<?= GetMessage('CW_YD_IMPORT'); ?>',
                action: function () {
                    obImportDialog = new BX.CDialog({
                        resizable: true,
                        draggable: false,
                        title: '<?= GetMessage('CW_YD_IMPORT_FROM_FILE'); ?>',
                        content_url: '<?=CUtil::JSEscape("/bitrix/admin/ctweb.yandexdelivery__import.php")?>',
                    });
                    BX.addCustomEvent(obImportDialog, 'onWindowClose', function (ob) {
                        var coords = ob.GetForm().POINTS;
                        var success = ob.GetForm().success;
                        if (success && success.value === 'Y' && coords && coords.value) {
                            BX.Ctweb.YandexDelivery.Editor.importRegion(JSON.parse(coords.value));
                        }
                    });
                    obImportDialog.Show();
                }
            };
        btnSave.action = function () {
            this.disableUntilError();
            BX.Ctweb.YandexDelivery.Editor.prepareRegionSave();
            this.parentWindow.PostParameters();
        };

        BX.WindowManager.Get().SetButtons([btnSave, BX.CDialog.prototype.btnCancel, btnResetPolygon, btnImport, <?= ($current->getId() > 0) ? 'btnDelete' : ''; ?>]);

        if (typeof BX.Ctweb.YandexDelivery.Editor === 'object') {
            BX.Ctweb.YandexDelivery.loadymaps(<?= Json::encode($arYmapParams ?: []) ?>, function () {
                ymaps.ready(function () {
                    BX.Ctweb.YandexDelivery.Editor.regions = <?= Json::encode($arRegionsJS); ?>;
                    BX.Ctweb.YandexDelivery.Editor.stores = <?=Json::encode($arStoresJS);?>;
                    BX.Ctweb.YandexDelivery.Editor._supported = true;
                    BX.Ctweb.YandexDelivery.Editor.initRegionEdit(<?= $current->GetId(); ?>);
                });
            });
        } else {
            console.error('ctweb.yandexdelivery: No main script included');
        }
    </script>
<? endif; ?>
