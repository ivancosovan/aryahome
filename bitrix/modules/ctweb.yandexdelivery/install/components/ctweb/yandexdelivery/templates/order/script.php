<? if ($this->GetComponent()->__name !== 'ctweb:yandexdelivery') die(); 

use \Bitrix\Main\Web\Json;
$this->addExternalJS("/bitrix/js/ctweb.yandexdelivery/controller.js");
?>
<script type="text/javascript">
    // Locals
    BX.message({
        CW_YD_NO_POINT: '<?=strlen($arResult['MESSAGES']['NO_POINT']) ? $arResult['MESSAGES']['NO_POINT'] : GetMessage("CW_YD_NO_POINT")?>',
        CW_YD_NO_POINT_ERROR: '<?=GetMessage("CW_YD_NO_POINT_ERROR")?>',
        CW_YD_DISTANCE: '<?=GetMessage("CW_YD_DISTANCE")?>',
        CW_YD_PRICE: '<?=GetMessage("CW_YD_PRICE")?>',
        CW_YD_CALCULATION_FAIL: '<?=GetMessage("CW_YD_CALCULATION_FAIL")?>',
        CW_YD_BROWSER_NOT_SUPPORTED: '<?=GetMessage("CW_YD_BROWSER_NOT_SUPPORTED")?>'
    });

    // performance.now
    (function () {

        if ("performance" in window == false) {
            window.performance = {};
        }

        Date.now = (Date.now || function () {  // thanks IE8
            return new Date().getTime();
        });

        if ("now" in window.performance == false) {

            var nowOffset = Date.now();

            if (performance.timing && performance.timing.navigationStart) {
                nowOffset = performance.timing.navigationStart
            }

            window.performance.now = function now() {
                return Date.now() - nowOffset;
            }
        }

    })();

    if (typeof BX.Ctweb.YandexDelivery.Controller === 'function') {

        BX.addCustomEvent('yandexdelivery.initialized', function (c) { c.initDelivery(); } );

        BX.ready(function () {
            BX.Ctweb.YandexDelivery.loadymaps(<?= Json::encode($arResult['YMAPS_PARAMS'] ?: []) ?>, function () {
                ymaps.ready(function () {
                    var mapController = new BX.Ctweb.YandexDelivery.OrderController(<?= Json::encode($arResult['JSPARAMS']); ?>);
                });
            });
        });
    } else {
        console.error('ctweb.yandexdelivery: No main script included');
    }
</script>