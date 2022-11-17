<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die(); ?>
<?
use \Bitrix\Main\Localization\Loc as Loc;
if (method_exists($this, 'setFrameMode')) {
    $this->setFrameMode(true);
}
?>

<div id="cwYandexDeliveryModal">
    <div class="ctweb-yandexdelivery">
        <div class="header">
            <div class="title"><?=Loc::getMessage('CW_YD_MODAL_TITLE')?></div>
            <div class="description">
                <?=$arResult['MESSAGES']['DESCRIPTION']?>
            </div>
        </div>
        <div class="body">
            <div class="loader-wrap"><div class="loader"><i class="fa fa-spinner fa-spin"></i></div></div>
            <div id="ctweb-yandexdelivery__map" style="width: auto; height: 400px;"></div>
        </div>
        <div class="footer">
            <div class="ctweb-yandexdelivery__calculates"></div>
            <button class="cancel"><?=GetMessage('CW_YD_CANCEL')?></button>
            <button class="success-btn choose"><?=GetMessage('CW_YD_SAVE')?></button>
        </div>
    </div>
    <div class="dark-body"></div>
</div>
<? include "script.php"; ?>
<script>
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

