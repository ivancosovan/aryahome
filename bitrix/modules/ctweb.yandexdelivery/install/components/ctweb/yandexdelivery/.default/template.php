<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die(); ?>
<?
use \Bitrix\Main\Localization\Loc as Loc;
use \Bitrix\Main\Web\Json;

if (method_exists($this, 'setFrameMode')) {
    $this->setFrameMode(true);
}
CJSCore::Init(array("jquery"));
?>
<div id="cwYandexDeliveryModal">
    <div class="cw-modal-window">
        <div class="header">
            <div class="title"><?=Loc::getMessage('CW_YD_MODAL_TITLE')?></div>
            <div class="description">
                <?=$arResult['MESSAGES']['DESCRIPTION']?>
            </div>
        </div>
        <div class="body">
            <div class="loader"><i class="fa fa-spinner fa-spin"></i></div>
            <div id="cwYandexDeliveryMap" style="width: auto; height: 400px;"></div>
        </div>
        <div class="footer">
            <div class="calculates"></div>
            <button class="cancel"><?=GetMessage('CW_YD_CANCEL')?></button>
            <button class="success-btn choose"><?=GetMessage('CW_YD_SAVE')?></button>
        </div>
    </div>
    <div class="dark-body"></div>
</div>
<script type="text/javascript">
    BX.message({
        CW_YD_NO_POINT: '<?=strlen($arResult['MESSAGES']['NO_POINT']) ? $arResult['MESSAGES']['NO_POINT'] : Loc::getMessage("CW_YD_NO_POINT")?>',
        CW_YD_NO_POINT_ERROR: '<?=Loc::getMessage("CW_YD_NO_POINT_ERROR")?>',
        CW_YD_DISTANCE: '<?=Loc::getMessage("CW_YD_DISTANCE")?>',
        CW_YD_PRICE: '<?=Loc::getMessage("CW_YD_PRICE")?>',
        CW_YD_RUB: '<?=Loc::getMessage("CW_YD_RUB")?>',
        CW_YD_CALCULATION_FAIL: '<?=Loc::getMessage("CW_YD_CALCULATION_FAIL")?>',
        CW_YD_BROWSER_NOT_SUPPORTED: '<?=Loc::getMessage("CW_YD_BROWSER_NOT_SUPPORTED")?>'
    });

    $(document).ready(function () {
        cwYandexDeliveryController.delivery_id = <?=$arResult['DELIVERY_ID']?>;

        if (typeof performance.now === 'function') {
            loadymaps()
                .then(function () {
                        ymaps.ready(function () {
                            cwYandexDeliveryController.regions = <?=Json::encode($arResult['REGIONS']);?>;
                            cwYandexDeliveryController.stores = <?=Json::encode($arResult['STORES']);?>;
                            cwYandexDeliveryController._supported = true;

                            cwYandexDeliveryController.init({});
                        });
                    },
                    function (e) {
                        console.error('ymaps return error: ' + e);
                    });
        } else {
            cwYandexDeliveryController._supported = false;
        }
        bx_soa_delivery = document.querySelector('#bx-soa-delivery');
        $.ajax({
            url: '/bitrix/js/ctweb.yandexdelivery/ajax.php',
            type: 'POST',
            dataType: 'JSON',
            data: {remove_point: 1}
        });

        if (bx_soa_delivery) {
            BX.addCustomEvent('onAjaxSuccess', cwYandexDeliveryController.afterFormReload);
            BX.Sale.OrderAjaxComponent.sendRequest();
        } else {
            $("input[name='DELIVERY_ID']").each(function (i, el) {
                if ($(el).prop('checked')) {
                    cwYandexDeliveryController.checkPoint($(el).val());
                }
            })
        }

    });

    function loadScript(url) {
        return new Promise(function(resolve, reject) {
            var script = document.createElement("script");
            script.onload = resolve;
            script.onerror = reject;
            script.src = url;
            document.getElementsByTagName("head")[0].appendChild(script);
        });
    }

    function loadymaps() {
        if (window.ymaps) {
            // already loaded and ready to go
            return Promise.resolve();
        } else {
            return loadScript('https://api-maps.yandex.ru/2.1/?lang=ru_RU');
        }
    }

</script>