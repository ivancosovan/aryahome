<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die(); ?>
<?
use \Bitrix\Main\Localization\Loc as Loc;
if (method_exists($this, 'setFrameMode')) {
    $this->setFrameMode(true);
}

$templateIDs = &$arResult['JSPARAMS']['TEMPLATE'];
?>

<div class="ctweb-yandexdelivery">
    <div class="header">
        <div class="description">
            <?=$arResult['MESSAGES']['DESCRIPTION']?>
        </div>
    </div>
    <div class="body">
        <div id="<?= $templateIDs['SPINNER']; ?>" class="loader-wrap"><div class="loader"><i class="fa fa-spinner fa-spin"></i></div></div>
        <div id="<?= $templateIDs['MAP']; ?>" style="width: <?= $arParams['MAP_WIDTH']; ?>; height: <?= $arParams['MAP_HEIGHT']; ?>;"></div>
    </div>
    <div class="ctweb-footer">
        <div class="ctweb-yandexdelivery__calculates">
            <span id="<?= $templateIDs['ADDRESS']; ?>" ></span>
            <span id="<?= $templateIDs['DISTANCE']; ?>" ></span>
            <span id="<?= $templateIDs['PRICE']; ?>" ></span>
        </div>
    </div>
</div>

<? include "script.php"; ?>

<script>
    //
    //  Example handlers
    //

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

    // display formatted calculate results example
    BX.addCustomEvent('yandexdelivery.calculate', function(e) {
        var calcInput = document.querySelector('.ctweb-yandexdelivery__calculates');

        if (!e.error)
            calcInput.innerHTML = BX.message('CW_YD_DISTANCE') + ': <b>' + e.result.DISTANCE + '</b>; ' + BX.message('CW_YD_PRICE') + ': <b>' + e.result.PRICE_FORMATTED + '</b>';
    });
</script>
