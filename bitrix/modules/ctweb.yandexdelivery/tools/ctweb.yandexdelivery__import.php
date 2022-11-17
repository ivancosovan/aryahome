<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php"); ?>
<?
use Ctweb\YandexDelivery\GeoJSON;
use \Bitrix\Main\Web\Json;

$MODULE_ID = 'ctweb.yandexdelivery';

$ERROR = false;
if (check_bitrix_sessid() && $_POST['geojson']) {
    $polygons = array();
    // Import GeoJSON

    if (!empty($_POST['geojson'])) {
        require_once $_SERVER['DOCUMENT_ROOT']."/bitrix/modules/$MODULE_ID/lib/classes/geojson.php";
        $geoJson = new GeoJSON($_POST['geojson']);
        if ($geoJson) {
            $polygons = $geoJson->getPolygons();
        }
    }

    if (empty($polygons)) {
        $ERROR = GetMessage('CW_YD_POLY_NOT_DETECTED');
    }
}
?>
<? if ($ERROR === false && !empty($polygons)) : ?>
    <div class="adm-info-message"><?=GetMessage('CW_YD_CHOOSE_POLY')?></div>
    <form method="post">
        <?= bitrix_sessid_post(); ?>
            <? foreach ($polygons as $index => $poly): ?>
                <div class="poly">
                    <? if ($_GET['multiple'] === 'Y') : ?>
                        <input type="checkbox" id="importval_<?= $index ?>" name="POINTS[]"
                               value="<?= Json::encode($poly->getMapCoords()); ?>"<?= ($index === 0) ? ' checked' : '' ?>>
                <? else: ?>
                        <input type="radio" id="importval_<?= $index ?>" name="POINTS"
                               value="<?= Json::encode($poly->getMapCoords()); ?>"<?= ($index === 0) ? ' checked' : '' ?>>
                <? endif; ?>
                <label for="importval_<?= $index ?>">
                    <?= $poly->getSVG(100, 100); ?>
                </label>
                </div>
            <? endforeach; ?>
        </ul>
    </form>
    <script>
        var btnChoose = Object.assign(BX.CDialog.prototype.btnSave);
        btnChoose.action = function () {
            var tmp = document.createElement('input');
            tmp.type = 'hidden';
            tmp.name = 'success';
            tmp.value = 'Y';
            this.parentWindow.GetForm().append(tmp);

            top.BX.WindowManager.Get().AllowClose();
            top.BX.WindowManager.Get().Close();
        };

        BX.WindowManager.Get().SetButtons([btnChoose, BX.CDialog.prototype.btnCancel]);
    </script>
<? else: ?>
    <div class="adm-info-message"><?=GetMessage('CW_YD_LOAD_FILE_DESC')?></div>
    <form method="post">
        <?= bitrix_sessid_post(); ?>
        <input hidden type="file" id="input_geojson" accept=".geojson">
        <input type="hidden" name="geojson" id="input_geojson_pseudo">
    </form>
    <script>
        var input = document.getElementById('input_geojson'),
            file = document.getElementById('input_geojson_pseudo'),
            btnUpload = {
                title: '<?= GetMessage('CW_YD_UPLOAD_FILE')?>',
                id: 'btnUpload',
                name: 'btnUpload',
                action: function () {
                    var self = this;

                    input.addEventListener('change', function () {

                        var file_data = this.files[0];

                        if (file_data) {
                            var reader = new FileReader();
                            reader.onload = function (event) {
                                file.value = event.target.result;
                                self.parentWindow.PostParameters();
                            };
                            reader.readAsText(file_data, "UTF-8");

                        }
                    });
                    input.click();
                }
            };

        BX.WindowManager.Get().SetButtons([BX.CDialog.prototype.btnCancel, btnUpload]);
    </script>
<? endif; ?>
<style>
    .poly {
        display: inline-block;
        cursor: pointer;
    }
    .poly input {
        display: none;
    }
    .poly label {
        display: block;
        outline: 1px solid #ddd;
        padding: 10px;
        cursor: pointer;
    }
    .poly input:checked + label {
        outline: 2px solid #0a7ddd;
    }
</style>
