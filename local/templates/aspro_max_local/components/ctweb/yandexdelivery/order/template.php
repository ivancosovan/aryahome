<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die(); ?>
<?
use \Bitrix\Main\Localization\Loc as Loc;
if (method_exists($this, 'setFrameMode')) {
    $this->setFrameMode(true);
}

$mainID = $this->GetEditAreaId('');
$arResult['JSPARAMS']['TEMPLATE']['MODAL'] = $mainID . "modal";
$arResult['JSPARAMS']['TEMPLATE']['MODAL_CANCEL'] = $mainID . "cancel";
$arResult['JSPARAMS']['TEMPLATE']['MODAL_SAVE'] = $mainID . "save";
$arResult['JSPARAMS']['TEMPLATE']['LINK'] = $mainID . "link"; // for delivery

$templateIDs = &$arResult['JSPARAMS']['TEMPLATE'];
?>

<div id="<?= $templateIDs['MODAL']; ?>" class="cwYandexDeliveryModal">
    <div class="ctweb-yandexdelivery">
        <div class="header">
            <div class="title"><?=Loc::getMessage('CW_YD_MODAL_TITLE')?></div>
            <div class="description">
                <?=$arResult['MESSAGES']['DESCRIPTION']?>
            </div>

            <input type="text" id="suggest" class="yamaps-suggest-input" placeholder="Адрес:" autocomplete="off" style="border: 1px solid;">
        </div>
        <div class="body">
            <div id="<?= $templateIDs['SPINNER']; ?>" class="loader-wrap"><div class="loader"><i class="fa fa-spinner fa-spin"></i></div></div>
            <div id="<?= $templateIDs['MAP']; ?>" style="width: auto; height: 400px;"></div>
        </div>
        <div class="footer">
            <div class="ctweb-yandexdelivery__calculates">
                <span id="<?= $templateIDs['ADDRESS']; ?>" ></span>
                <span id="<?= $templateIDs['DISTANCE']; ?>" ></span>
                <span id="<?= $templateIDs['PRICE']; ?>" ></span>
            </div>
            <button id="<?= $templateIDs['MODAL_CANCEL']; ?>" class="cancel"><?=GetMessage('CW_YD_CANCEL')?></button>
            <button id="<?= $templateIDs['MODAL_SAVE']; ?>" class="success-btn choose"><?=GetMessage('CW_YD_SAVE')?></button>
        </div>
    </div>
    <div class="dark-body"></div>
</div>
<? include "script.php"; ?>
<script>
    //
    //  Example handlers
    //
    function ctwebShow() {
        $('.cwYandexDeliveryModal').css('z-index', 9999);
        $('.cwYandexDeliveryModal').css('opacity', 1);
    }

    BX.addCustomEvent('yandexdelivery.initialized', function (controller) {
        // Set focus to nearest store
        if (controller.arStores.length) {
            controller.CenterToStore(controller.arStores[0].ID);
            ymaps.geolocation.get({
                provider: 'yandex',
                mapStateAutoApply: true
            }).then(function (res) {
                controller.CenterToStore(controller.getNearestStore(res.geoObjects.position).ID);
            }, function () {
                controller.CenterToStore(controller.getNearestStore([0, 0]).ID);
            });
        }
    });


    // display calculate results example
    BX.addCustomEvent('yandexdelivery.calculate', function(e) {
        var calcInput = document.querySelector('.ctweb-yandexdelivery__calculates');

        if (!e.error && calcInput)
            calcInput.innerHTML = BX.message('CW_YD_DISTANCE') + ': <b>' + e.result.DISTANCE + '</b>; ' + BX.message('CW_YD_PRICE') + ': <b>' + e.result.PRICE_FORMATTED + '</b>';
    });

    // display address result
    BX.addCustomEvent('yandexdelivery.address_response', function (e) {
        var calcInput = document.querySelector('.ctweb-yandexdelivery__calculates');

        if (!e.error && calcInput)
            calcInput.innerHTML = e.ADDRESS + "<br>" + calcInput.innerHTML;
    });
</script>