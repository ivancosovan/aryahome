<?php

/*AddEventHandler("main", "OnBeforeProlog", "MyOnBeforePrologHandler", 50);
function MyOnBeforePrologHandler()
{
    global $USER;
    CModule::IncludeModule("main");
    if(!is_object($USER)){
        $USER = new CUser();
    }
    if ( !CSite::InGroup( array(1,12,17) ) ){
        include($_SERVER["DOCUMENT_ROOT"]."/coming-soon/underconstruction.html");
        die();
    }
}*/

use Bitrix\Main\Diag\Debug;
use Bitrix\Sale\Location\LocationTable;
use Bitrix\Main\EventManager;
use Bitrix\Sale\Order;
use Bitrix\Sale;

CModule::AddAutoloadClasses('',
    [
        'Cosmo\CosmoMax'        => '/local/php_interface/lib/cosmo/CosmoMax.php',
        'Cosmo\CosmoMaxItem'        => '/local/php_interface/lib/cosmo/CosmoMaxItem.php',
    ]
);

$eventManager = EventManager::getInstance();

$eventManager->addEventHandler('sale', 'OnSaleComponentOrderResultPrepared', 'OnSaleComponentOrderResultPrepared');
function OnSaleComponentOrderResultPrepared(&$order, &$arUserResult, $request, &$arParams, &$arResult)
{
    //AddMessage2Log($request);
}

AddEventHandler('aspro.max', 'OnAsproShowSectionGallery', array('\Aspro\Functions\CAsproMaxCustom', 'OnAsproShowSectionGalleryCustomHandler'));

AddEventHandler("sale", "OnOrderSave", "updateUserWaitingList");
function updateUserWaitingList($orderId,$fields, $orderFields, $isNew){
// При заказе товаров из листа ожидания, удаляем их в листе ожидания пользователя

    $userID = $orderFields['USER_ID'];
    $productsIdInBasket = array();
    foreach ($orderFields['BASKET_ITEMS'] as $basketItem){
        $productsIdInBasket[] = $basketItem['PRODUCT_ID'];
    }

    $arUserFilter = array("ID" => $orderFields['USER_ID']);
    $arUserParams["SELECT"] = array("UF_WAITING_GOODS");
    $rsUsers = CUser::GetList(($by="id"), ($order="desc"), $arUserFilter, $arUserParams); // выбираем пользователей

    if($arUser = $rsUsers->Fetch()){
        $idWaitingProducts = explode(',',$arUser['UF_WAITING_GOODS']);
        $idWaitingProducts = array_unique($idWaitingProducts);

        $arrIntersect = array_intersect($productsIdInBasket, $idWaitingProducts);

        if ($arrIntersect){
            global $USER, $DB, $USER_FIELD_MANAGER;

            foreach ($arrIntersect as $prodId){
                foreach ($idWaitingProducts as $idWaitingProd){
                    if ($prodId == $idWaitingProd){
                        $arDeleteFromWaitingList[] = $prodId;
                    }
                }
            }

            foreach ($arDeleteFromWaitingList as $deleteID){
                $findedID = array_search($deleteID,$idWaitingProducts);
                unset($idWaitingProducts[$findedID]);
            }
            $idWaitingProducts = implode(',',$idWaitingProducts);
            $ufFields = Array(
                "UF_WAITING_GOODS"=> $idWaitingProducts,
            );

            $USER_FIELD_MANAGER->Update("USER", $userID, $ufFields);
        }

//        \Bitrix\Main\Diag\Debug::dumpToFile($idWaitingProducts, $varName = "idWaitingProducts", $fileName = SITE_DIR.'bitrix/php_interface/test123.txt');
//        \Bitrix\Main\Diag\Debug::dumpToFile($arrIntersect, $varName = "arrIntersect", $fileName = SITE_DIR.'bitrix/php_interface/test123.txt');
//        \Bitrix\Main\Diag\Debug::dumpToFile($arDeleteFromWaitingList, $varName = "arDeleteFromWaitingList", $fileName = SITE_DIR.'bitrix/php_interface/test123.txt');
//        \Bitrix\Main\Diag\Debug::dumpToFile($fields, $varName = "fields", $fileName = SITE_DIR.'bitrix/php_interface/test123.txt');
    }

}

AddEventHandler("sale", "OnBeforeBasketDelete", "deleteBasketItemsFromWaitingList");
function deleteBasketItemsFromWaitingList($id){

    // Получаем товары из листа ожидания
    global $USER;

    $arUsetFilter = array("ID" => $USER->GetID());
    $arUserParams["SELECT"] = array("UF_WAITING_GOODS");
    $rsUsers = CUser::GetList(($by="id"), ($order="desc"), $arUsetFilter, $arUserParams);

    if($arUser = $rsUsers->Fetch()){
        $idWaitingProducts = explode(',',$arUser['UF_WAITING_GOODS']);
        $idWaitingProducts = array_unique($idWaitingProducts);
        if (count($idWaitingProducts) == '1' && $idWaitingProducts[0] == '') $idWaitingProducts = false;
    }

    if ($idWaitingProducts){ // Удаляем товары из листа ожидания
        $dbBasketItems = CSaleBasket::GetList(
            array(
                "NAME" => "ASC",
                "ID" => "ASC"
            ),
            array(
                //"FUSER_ID" => $basketID,
                'FUSER_ID'=>CSaleBasket::GetBasketUserID(True),
                "LID" => SITE_ID,
                "ORDER_ID" => "NULL",
                'CAN_BUY'=>'N',
            ),
            false,
            false,
            array("ID", "CALLBACK_FUNC", "MODULE",
                "PRODUCT_ID", "QUANTITY", "DELAY",
                "CAN_BUY", "PRICE", "WEIGHT")
        );



        while ($arItem = $dbBasketItems->Fetch())
        {
            //\Bitrix\Main\Diag\Debug::dumpToFile($arItem['ID'], $varName = "arItem[ID]", $fileName = SITE_DIR.'bitrix/php_interface/test123.txt');
            //\Bitrix\Main\Diag\Debug::dumpToFile($arItem, $varName = "arItem", $fileName = SITE_DIR.'bitrix/php_interface/test123.txt');
            //\Bitrix\Main\Diag\Debug::dumpToFile($id, $varName = "id", $fileName = SITE_DIR.'bitrix/php_interface/test123.txt');
            if ($arItem['ID'] == $id){
                if (in_array($arItem['PRODUCT_ID'], $idWaitingProducts)){ // удаляем из листа ожидания

                    $needDeleteKey = array_search($arItem['PRODUCT_ID'], $idWaitingProducts);

                    unset($idWaitingProducts[$needDeleteKey]);
                }
            }

        }

        $idWaitingProducts = implode(',',$idWaitingProducts);

        global $DB, $USER_FIELD_MANAGER;
        $fields = Array(
            "UF_WAITING_GOODS"=> $idWaitingProducts,
        );
        $USER_FIELD_MANAGER->Update("USER", $USER->GetID(), $fields);


    }

}


if (!function_exists('showImgCustom')){
    function showImgCustom($arParams = array(), $arItem = array(), $bShowFW = true, $bWrapLink = true, $dopClassImg = ''){

        if($arItem):?>
            <?ob_start();?>

            <?if($bWrapLink):?>
                <a href="<?=$arItem["DETAIL_PAGE_URL"]?>" class="thumb shine">
            <?endif;?>
            <?
            $a_alt = (is_array($arItem["PREVIEW_PICTURE"]) && strlen($arItem["PREVIEW_PICTURE"]['DESCRIPTION']) ? $arItem["PREVIEW_PICTURE"]['DESCRIPTION'] : ($arItem['SELECTED_SKU_IPROPERTY_VALUES'] ? ($arItem["SELECTED_SKU_IPROPERTY_VALUES"]["ELEMENT_PREVIEW_PICTURE_FILE_ALT"] ? $arItem["SELECTED_SKU_IPROPERTY_VALUES"]["ELEMENT_PREVIEW_PICTURE_FILE_ALT"] : $arItem["NAME"]) : ($arItem["IPROPERTY_VALUES"]["ELEMENT_PREVIEW_PICTURE_FILE_ALT"] ? $arItem["IPROPERTY_VALUES"]["ELEMENT_PREVIEW_PICTURE_FILE_ALT"] : $arItem["NAME"])));

            $a_title = (is_array($arItem["PREVIEW_PICTURE"]) && strlen($arItem["PREVIEW_PICTURE"]['DESCRIPTION']) ? $arItem["PREVIEW_PICTURE"]['DESCRIPTION'] : ($arItem['SELECTED_SKU_IPROPERTY_VALUES'] ? ($arItem["SELECTED_SKU_IPROPERTY_VALUES"]["ELEMENT_PREVIEW_PICTURE_FILE_TITLE"] ? $arItem["SELECTED_SKU_IPROPERTY_VALUES"]["ELEMENT_PREVIEW_PICTURE_FILE_TITLE"] : $arItem["NAME"]) : ($arItem["IPROPERTY_VALUES"]["ELEMENT_PREVIEW_PICTURE_FILE_TITLE"] ? $arItem["IPROPERTY_VALUES"]["ELEMENT_PREVIEW_PICTURE_FILE_TITLE"] : $arItem["NAME"])));

            $bNeedFindSkuPicture = empty($arItem["DETAIL_PICTURE"]) && empty($arItem["PREVIEW_PICTURE"]) && (\CMax::GetFrontParametrValue("SHOW_FIRST_SKU_PICTURE") == "Y") &&  isset($arItem['OFFERS']) && !empty($arItem['OFFERS']);
            $arFirstSkuPicture = array();

            if($bNeedFindSkuPicture){

                foreach ($arItem['OFFERS'] as $keyOffer => $arOffer)
                {
                    if (!empty($arOffer['DETAIL_PICTURE'])){
                        $arFirstSkuPicture = $arOffer['DETAIL_PICTURE'];
                        if (!is_array($arFirstSkuPicture)){
                            $arFirstSkuPicture = \CFile::GetFileArray($arOffer['DETAIL_PICTURE']);
                        }
                    } elseif(!empty($arOffer['PREVIEW_PICTURE'])){
                        $arFirstSkuPicture = $arOffer['PREVIEW_PICTURE'];
                        if (!is_array($arFirstSkuPicture)){
                            $arFirstSkuPicture = \CFile::GetFileArray($arOffer['PREVIEW_PICTURE']);
                        }
                    }

                    if(isset($arFirstSkuPicture["ID"])){
                        $arFirstSkuPicture = \CFile::ResizeImageGet($arFirstSkuPicture["ID"], array( "width" => 350, "height" => 350 ), BX_RESIZE_IMAGE_PROPORTIONAL,true );
                    }

                    if(!empty( $arFirstSkuPicture )){
                        break;
                    }
                }
            }

            ?>

            <?if( !empty($arItem["DETAIL_PICTURE"])):?>
                <?if(isset($arItem["DETAIL_PICTURE"]["src"])):?>

                    <?$img["src"] = $arItem["DETAIL_PICTURE"]["src"]?>
                <?else:?>
                    <?$img = \CFile::ResizeImageGet($arItem["DETAIL_PICTURE"], array( "width" => 550, "height" => 550 ), BX_RESIZE_IMAGE_PROPORTIONAL,true );?>
                <?endif;?>

                <img class="lazy img-responsive <?=$dopClassImg;?>" src="<?=\Aspro\Functions\CAsproMax::showBlankImg($img["src"])?>" data-src="<?=$img["src"]?>" alt="<?=$a_alt;?>" title="<?=$a_title;?>" />
            <?elseif( !empty($arItem["PREVIEW_PICTURE"]) ):?>
                <img class="lazy img-responsive <?=$dopClassImg;?>" src="<?=\Aspro\Functions\CAsproMax::showBlankImg($arItem["PREVIEW_PICTURE"]["SRC"]);?>" data-src="<?=$arItem["PREVIEW_PICTURE"]["SRC"]?>" alt="<?=$a_alt;?>" title="<?=$a_title;?>" />
            <?elseif( $bNeedFindSkuPicture && !empty( $arFirstSkuPicture ) ):?>
                <img class="lazy img-responsive <?=$dopClassImg;?>" src="<?=\Aspro\Functions\CAsproMax::showBlankImg($arFirstSkuPicture["src"]);?>" data-src="<?=$arFirstSkuPicture["src"]?>" alt="<?=$a_alt;?>" title="<?=$a_title;?>" />
            <?else:?>
                <img class="lazy img-responsive <?=$dopClassImg;?>" src="<?=\Aspro\Functions\CAsproMax::showBlankImg(SITE_TEMPLATE_PATH.'/images/svg/noimage_product.svg');?>" data-src="<?=SITE_TEMPLATE_PATH?>/images/svg/noimage_product.svg" alt="<?=$a_alt;?>" title="<?=$a_title;?>" />
            <?endif;?>
            <?if($fast_view_text_tmp = \CMax::GetFrontParametrValue('EXPRESSION_FOR_FAST_VIEW'))
                $fast_view_text = $fast_view_text_tmp;
            else
                $fast_view_text = Loc::getMessage('FAST_VIEW');?>
            <?if($bWrapLink):?>
                </a>
            <?endif;?>
            <?if($bShowFW):?>
                <div class="fast_view_block wicons rounded2" data-event="jqm" data-param-form_id="fast_view" data-param-iblock_id="<?=$arParams["IBLOCK_ID"];?>" data-param-id="<?=$arItem["ID"];?>" data-param-item_href="<?=urlencode($arItem["DETAIL_PAGE_URL"]);?>" data-name="fast_view"><?=\CMax::showIconSvg("fw ncolor", SITE_TEMPLATE_PATH."/images/svg/quickview.svg");?><?=$fast_view_text;?></div>
            <?endif;?>
            <?$html = ob_get_contents();
            ob_end_clean();

            foreach(GetModuleEvents(FUNCTION_MODULE_ID, 'OnAsproShowImg', true) as $arEvent) // event for manipulation item img
                ExecuteModuleEventEx($arEvent, array($arParams, $arItem, $bShowFW, $bWrapLink, $dopClassImg, &$html));

            echo $html;?>
        <?endif;?>
    <?}
}

if (!function_exists('showPriceMatrixCustom')){
    function showPriceMatrixCustom($arItem = array(), $arParams, $strMeasure = '', $arAddToBasketData = array()){

        $html = '';
        if(
            isset($arItem['PRICE_MATRIX'])
            && $arItem['PRICE_MATRIX']
            && $arParams['USE_PRICE_COUNT'] == 'Y'
        ) {
            ob_start();?>
            <?$bShowPopupPrice = (
                CMax::GetFrontParametrValue('SHOW_POPUP_PRICE') == 'Y'
                && (
                    count($arItem['PRICE_MATRIX']['ROWS']) > 1
                    || count($arItem['PRICE_MATRIX']['COLS']) > 1
                )
            );?>
            <?if($bShowPopupPrice):?>
                <div class="js-info-block rounded3">
                <div class="block_title text-upper font_xs font-bold">
                    Варианты цен
                    <?=CMax::showIconSvg("close", SITE_TEMPLATE_PATH."/images/svg/Close.svg");?>
                </div>
                <div class="block_wrap">
                <div class="block_wrap_inner prices scrollblock">
            <?endif;?>
            <div class="price_matrix_block">
                <?
                $sDiscountPrices = \Bitrix\Main\Config\Option::get(ASPRO_MAX_MODULE_ID, 'DISCOUNT_PRICE');
                $arDiscountPrices = array();

                if($sDiscountPrices)
                    $arDiscountPrices = array_flip(explode(',', $sDiscountPrices));

                \Bitrix\Main\Type\Collection::sortByColumn($arItem['PRICE_MATRIX']['COLS'], array('SORT' => SORT_ASC));

                $arTmpPrice = (isset($arItem['ITEM_PRICES']) ? current($arItem['ITEM_PRICES']) : array());

                $iCountPriceGroup = count($arItem['PRICE_MATRIX']['COLS']);
                $bPriceRows = (count($arItem['PRICE_MATRIX']['ROWS']) > 1);


                ?>
                <?foreach($arItem['PRICE_MATRIX']['COLS'] as $arPriceGroup):?>
                    <?if($iCountPriceGroup > 1):?>
                        <?
                        $class = '';
                        if($arTmpPrice)
                        {
                            if($arItem['PRICE_MATRIX']['MATRIX'][$arPriceGroup['ID']][$arTmpPrice['QUANTITY_HASH']]['ID'] == $arTmpPrice['ID'])
                                $class = 'min';
                        }?>
                        <div class="price_group <?=$class;?> <?=$arPriceGroup['XML_ID']?>"><div class="price_name <?=($arItem['ITEM_PRICE_MODE'] == 'Q' || !$bShowPopupPrice ? 'font_xs darken' : 'font_xxs muted');?>"><?=$arPriceGroup["NAME_LANG"];?></div>
                    <?endif;?>
                <?

                ?>
                    <div class="price_matrix_wrapper <?=($arDiscountPrices ? (isset($arDiscountPrices[$arPriceGroup['ID']]) ? 'strike_block 1' : '') : '');?>">
                        <?$iCountPriceInterval = count($arItem['PRICE_MATRIX']['MATRIX'][$arPriceGroup['ID']]);?>
                        <?foreach($arItem['PRICE_MATRIX']['MATRIX'][$arPriceGroup['ID']] as $key => $arPrice):?>
                            <?if($iCountPriceInterval > 1):?>
                                <div class="price_wrapper_block clearfix">
                                <div class="price_interval pull-left font_xs muted777">
                                    <?
                                    $quantity_from = ($arItem['PRICE_MATRIX']['ROWS'][$key]['QUANTITY_FROM'] ? $arItem['PRICE_MATRIX']['ROWS'][$key]['QUANTITY_FROM'] : 0);
                                    $quantity_to = $arItem['PRICE_MATRIX']['ROWS'][$key]['QUANTITY_TO'];
                                    $text = ($quantity_to ? Loc::getMessage('FROM').' '.$quantity_from.' '.Loc::getMessage('TO').' '.$quantity_to :Loc::getMessage('FROM').' '.$quantity_from );
                                    ?>
                                    <div><?=$text?><?if(($arParams["SHOW_MEASURE"]=="Y") && $strMeasure):?> <?=$strMeasure?><?endif;?></div>
                                </div>
                            <?endif;?>
                            <div class="prices-wrapper <?=($iCountPriceInterval > 1 ? ' pull-right text-right' : '');?>">

                                <?if($arPrice["PRICE"] > $arPrice["DISCOUNT_PRICE"]){?>
                                    <div class="price font-bold <?=(($iCountPriceInterval > 1) ? 'font_xs' : ($arParams['MD_PRICE'] ? 'font_mlg' : 'font_mxs'));?>" data-currency="<?=$arPrice["CURRENCY"];?>" data-value="<?=$arPrice["DISCOUNT_PRICE"];?>">
                                        <?if(strlen($arPrice["DISCOUNT_PRICE"])):?>
                                            <?if($arItem['SHOW_FROM_LANG'] == 'Y'):?><span><?=Loc::getMessage('FROM')?></span><?endif;?>
                                            <span class="values_wrapper"><?=\Aspro\Functions\CAsproMaxItem::getCurrentPrice("DISCOUNT_PRICE", $arPrice);?></span><?if(($arParams["SHOW_MEASURE"]=="Y") && $strMeasure && $arPrice["DISCOUNT_PRICE"]):?><span class="price_measure">/<?=$strMeasure?></span><?endif;?>
                                        <?endif;?>
                                    </div>
                                    <?if($arParams["SHOW_OLD_PRICE"]=="Y"):?>
                                        <div class="price discount" data-currency="<?=$arPrice["CURRENCY"];?>" data-value="<?=$arPrice["PRICE"];?>">
                                            <span class="values_wrapper <?=($arParams['MD_PRICE'] ? 'font_sm' : 'font_xs');?> muted"><?=\Aspro\Functions\CAsproMaxItem::getCurrentPrice("PRICE", $arPrice);?></span>
                                        </div>
                                    <?endif;?>
                                <?}else{?>
                                    <div class="price font-bold <?=(($iCountPriceInterval > 1) ? 'font_xs' : ($arParams['MD_PRICE'] ? 'font_mlg' : 'font_mxs dd'));?>" data-currency="<?=$arPrice["CURRENCY"];?>" data-value="<?=$arPrice["DISCOUNT_PRICE"];?>">
                                        <?if($arItem['SHOW_FROM_LANG'] == 'Y'):?><span><?=Loc::getMessage('FROM')?></span><?endif;?>
                                        <span><span class="values_wrapper"><?=\Aspro\Functions\CAsproMaxItem::getCurrentPrice("PRICE", $arPrice);?></span><?if(($arParams["SHOW_MEASURE"]=="Y") && $strMeasure && $arPrice["PRICE"]):?><span class="price_measure">/<?=$strMeasure?></span><?endif;?></span>
                                    </div>
                                <?}?>
                            </div>
                            <?if($iCountPriceInterval > 1):?>
                                </div>
                            <?else:

                                if($arParams['SHOW_DISCOUNT_PERCENT'] == 'Y' && $arPrice["PRICE"] > $arPrice["DISCOUNT_PRICE"]):?>
                                    <?$ratio = (!$bPriceRows ? $arAddToBasketData["MIN_QUANTITY_BUY"] : 1);?>
                                    <div class="sale_block">
                                        <div class="sale_wrapper font_xxs">
                                            <?$diff = ($arPrice["PRICE"] - $arPrice["DISCOUNT_PRICE"]);?>
                                            <?if($arParams['SHOW_DISCOUNT_PERCENT_NUMBER'] != 'Y'):?>
                                                <div class="inner-sale rounded1">
                                                    <span class="title">Экономия </span> <div class="text"><span class="values_wrapper" data-currency="<?=$arPrice["CURRENCY"];?>" data-value="<?=($diff*$ratio);?>"><?=\Aspro\Functions\CAsproMaxItem::getCurrentPrice($diff, $arPrice, false)?></span></div>
                                                </div>
                                            <?else:?>
                                                <div class="sale-number rounded2">
                                                    <?$percent=round(($diff/$arPrice["PRICE"])*100);?>
                                                    <?if($percent && $percent<100){?>
                                                        <div class="value">-<span><?=$percent;?></span>%</div>
                                                    <?}?>
                                                    <div class="inner-sale rounded1">
                                                        <div class="text">Экономия<span class="values_wrapper"><?=\Aspro\Functions\CAsproMaxItem::getCurrentPrice($diff, $arPrice, false);?></span></div>
                                                    </div>
                                                </div>
                                            <?endif;?>
                                        </div>
                                    </div>
                                <?endif;?>
                            <?endif;?>
                        <?endforeach;?>
                    </div>
                    <?if($iCountPriceGroup > 1):?>
                        </div>
                    <?endif;?>
                <?endforeach;?>
            </div>
            <?if($bShowPopupPrice):?>
                </div>
                <div class="more-btn text-center">
                    <a href="" class="font_upper colored_theme_hover_bg"><?=Loc::getMessage("MORE_LINK")?></a>
                </div>
                </div>
                </div>
            <?endif;?>
            <?$html = ob_get_contents();
            ob_end_clean();

            foreach(GetModuleEvents(ASPRO_MAX_MODULE_ID, 'OnAsproShowPriceMatrix', true) as $arEvent) // event for manipulation price matrix
                ExecuteModuleEventEx($arEvent, array($arItem, $arParams, $strMeasure, $arAddToBasketData, &$html));
        }
        return $html;
    }
}
if (!function_exists('showPriceMatrixCustom2')){
    function showPriceMatrixCustom2($arItem = array(), $arParams, $strMeasure = '', $arAddToBasketData = array()){


		$arColorProp = [
			'1676' => 'red_discont',
			'1677' => 'green_discont',
			'1678' => 'orange_discont',
			'1679' => 'white_discont',
			'1680' => 'yellow_discont',
		];

        /*
         * Доработано
         * Сравнивается скидка (из правил работы с корзиной) на цену (id - 8), и цену со скидкой (7) и выбирается минимальная для вывода
         * */
        $html = '';
        if(
            isset($arItem['PRICE_MATRIX'])
            && $arItem['PRICE_MATRIX']
            && $arParams['USE_PRICE_COUNT'] == 'Y'
        ) {
            ob_start();?>
            <?$bShowPopupPrice = (
                CMax::GetFrontParametrValue('SHOW_POPUP_PRICE') == 'Y'
                && (
                    count($arItem['PRICE_MATRIX']['ROWS']) > 1
                    || count($arItem['PRICE_MATRIX']['COLS']) > 1
                )
            );?>
            <?if($bShowPopupPrice):?>
                <div class="js-info-block rounded3">
                <div class="block_title text-upper font_xs font-bold">
                    Варианты цен
                    <?=CMax::showIconSvg("close", SITE_TEMPLATE_PATH."/images/svg/Close.svg");?>
                </div>
                <div class="block_wrap">
                <div class="block_wrap_inner prices scrollblock">
            <?endif;?>
            <div class="price_matrix_block">
                <?
                $sDiscountPrices = \Bitrix\Main\Config\Option::get(ASPRO_MAX_MODULE_ID, 'DISCOUNT_PRICE');
                $arDiscountPrices = array();

                if($sDiscountPrices)
                    $arDiscountPrices = array_flip(explode(',', $sDiscountPrices));

                \Bitrix\Main\Type\Collection::sortByColumn($arItem['PRICE_MATRIX']['COLS'], array('SORT' => SORT_ASC));

                $arTmpPrice = (isset($arItem['ITEM_PRICES']) ? current($arItem['ITEM_PRICES']) : array());

                $iCountPriceGroup = count($arItem['PRICE_MATRIX']['COLS']);
                $bPriceRows = (count($arItem['PRICE_MATRIX']['ROWS']) > 1);

                $idOldPrice = 7;

                $oldPriceDiscountVal = $arItem['PRICE_MATRIX']['MATRIX'][7]["ZERO-INF"]['DISCOUNT_PRICE'];// 7 - id цены "Розничная без скидки"

                if($arDiscountPrices){
                    foreach($arItem['PRICE_MATRIX']['MATRIX'] as $key => $arPrice){

                        if ($arPrice['DISCOUNT_PRICE'] < $oldPriceDiscountVal){
                            $arMinPrice[$key] = $arPrice['ZERO-INF']['DISCOUNT_PRICE'];
                        }elseif($arPrice['DISCOUNT_PRICE'] > $oldPriceDiscountVal){
                            $arMinPrice[$key] = $oldPriceDiscountVal;
                        }
                        else{
                            $arMinPrice[$key] = $arPrice['ZERO-INF']['DISCOUNT_PRICE'];
                        }
                    }

                    $keyMinPrice = array_keys($arMinPrice, min($arMinPrice))[0];
                }

                ?>
                <?foreach($arItem['PRICE_MATRIX']['COLS'] as $arPriceGroup):?>
                <?
                    $showDiscountByOldPrice = false;
                    ?>
                    <?if($iCountPriceGroup > 1):?>
                        <?
                        $class = '';
                        if($arTmpPrice)
                        {
                            if($arItem['PRICE_MATRIX']['MATRIX'][$arPriceGroup['ID']][$arTmpPrice['QUANTITY_HASH']]['ID'] == $arTmpPrice['ID']){
                                $class = 'min';
			    }else{
				$class = 'not-show';    
			    }
				    
                        }?>
                        <div class="price_group <?=$class;?> <?=$arPriceGroup['XML_ID']?>"><?/*<div class="price_name <?=($arItem['ITEM_PRICE_MODE'] == 'Q' || !$bShowPopupPrice ? 'font_xs darken' : 'font_xxs muted');?>"><?=$arPriceGroup["NAME_LANG"];?></div>*/?>
                    <?endif;?>
                    <?

                if ($arPriceGroup['ID'] == $keyMinPrice){
                    $showDiscountByOldPrice = true;
                }

                    ?>
                    <div class="price_matrix_wrapper custom <?=($arDiscountPrices ? (isset($arDiscountPrices[$arPriceGroup['ID']]) && !$showDiscountByOldPrice  ? 'strike_block 2' : '') : '');?>">
                        <?$iCountPriceInterval = count($arItem['PRICE_MATRIX']['MATRIX'][$arPriceGroup['ID']]);?>
                        <?foreach($arItem['PRICE_MATRIX']['MATRIX'][$arPriceGroup['ID']] as $key => $arPrice):?>
                            <?if($iCountPriceInterval > 1):?>
                                <div class="price_wrapper_block clearfix">
                                <div class="price_interval pull-left font_xs muted777">
                                    <?
                                    $quantity_from = ($arItem['PRICE_MATRIX']['ROWS'][$key]['QUANTITY_FROM'] ? $arItem['PRICE_MATRIX']['ROWS'][$key]['QUANTITY_FROM'] : 0);
                                    $quantity_to = $arItem['PRICE_MATRIX']['ROWS'][$key]['QUANTITY_TO'];
                                    $text = ($quantity_to ? Loc::getMessage('FROM').' '.$quantity_from.' '.Loc::getMessage('TO').' '.$quantity_to :Loc::getMessage('FROM').' '.$quantity_from );
                                    ?>
                                    <div><?=$text?><?if(($arParams["SHOW_MEASURE"]=="Y") && $strMeasure):?> <?=$strMeasure?><?endif;?></div>
                                </div>
                            <?endif;?>
                            <div class="prices-wrapper <?=($iCountPriceInterval > 1 ? ' pull-right text-right' : '');?>">

                                <?if($arPrice["PRICE"] > $arPrice["DISCOUNT_PRICE"] && $showDiscountByOldPrice){?>
                                    <div class="price font-bold <?=(($iCountPriceInterval > 1) ? 'font_xs' : ($arParams['MD_PRICE'] ? 'font_mlg' : 'font_mxs'));?>" data-currency="<?=$arPrice["CURRENCY"];?>" data-value="<?=$arPrice["DISCOUNT_PRICE"];?>">
                                        <?if(strlen($arPrice["DISCOUNT_PRICE"])):?>
                                            <?if($arItem['SHOW_FROM_LANG'] == 'Y'):?><span><?=Loc::getMessage('FROM')?></span><?endif;?>
                                            <span class="values_wrapper"><?=\Aspro\Functions\CAsproMaxItem::getCurrentPrice("DISCOUNT_PRICE", $arPrice);?></span><?if(($arParams["SHOW_MEASURE"]=="Y") && $strMeasure && $arPrice["DISCOUNT_PRICE"]):?><span class="price_measure">/<?=$strMeasure?></span><?endif;?>
                                        <?endif;?>
                                    </div>
                                    <?if($arParams["SHOW_OLD_PRICE"]=="Y"):?>
                                        <div class="price discount" data-currency="<?=$arPrice["CURRENCY"];?>" data-value="<?=$arPrice["PRICE"];?>">
                                            <span class="values_wrapper <?=($arParams['MD_PRICE'] ? 'font_sm' : 'font_xs');?> muted"><?=\Aspro\Functions\CAsproMaxItem::getCurrentPrice("PRICE", $arPrice);?></span>
                                        </div>
                                    <?endif;?>
                                <?}else{?>
                                            <?if ($showDiscountByOldPrice):?>
                                                <div class="price font-bold <?=(($iCountPriceInterval > 1) ? 'font_xs' : ($arParams['MD_PRICE'] ? 'font_mlg' : 'font_mxs dd'));?>" data-currency="<?=$arPrice["CURRENCY"];?>" data-value="<?=$arPrice["DISCOUNT_PRICE"];?>">
                                                    <?if($arItem['SHOW_FROM_LANG'] == 'Y'):?><span><?=Loc::getMessage('FROM')?></span><?endif;?>
                                                    <span><span class="values_wrapper"><?=\Aspro\Functions\CAsproMaxItem::getCurrentPrice("PRICE", $arPrice);?></span><?if(($arParams["SHOW_MEASURE"]=="Y") && $strMeasure && $arPrice["PRICE"]):?><span class="price_measure">/<?=$strMeasure?></span><?endif;?></span>
                                                </div>
                                            <?if($arParams["SHOW_OLD_PRICE"]=="Y"):?>
                                                <?$oldPriceValue = $arItem['PRICE_MATRIX']['MATRIX'][$idOldPrice]["ZERO-INF"]['PRICE'];?>
                                                <div class="price discount" data-currency="<?=$arPrice["CURRENCY"];?>" data-value="<?=$arPrice["PRICE"];?>">
                                                    <span class="values_wrapper <?=($arParams['MD_PRICE'] ? 'font_sm' : 'font_xs');?> muted">
                                                        <span class="price_value"><?=CurrencyFormat($oldPriceValue, $arPrice['CURRENCY'])?></span>
                                                    </span>
                                                </div>
                                               <? if($arParams['SHOW_DISCOUNT_PERCENT'] == 'Y' &&  $oldPriceValue > $arPrice["PRICE"]):?>
                                                <?$ratio = (!$bPriceRows ? $arAddToBasketData["MIN_QUANTITY_BUY"] : 1);?>
                                                <div class="sale_block">
                                                    <div class="sale_wrapper font_xxs">
                                                        <?$diff = ($oldPriceValue - $arPrice["PRICE"]);?>
                                                        <?if($arParams['SHOW_DISCOUNT_PERCENT_NUMBER'] != 'Y'):?>
                                                            <div class="inner-sale rounded1">
                                                                <span class="title">Экономия </span> <div class="text"><span class="values_wrapper" data-currency="<?=$arPrice["CURRENCY"];?>" data-value="<?=($diff*$ratio);?>"><?=\Aspro\Functions\CAsproMaxItem::getCurrentPrice($diff, $arPrice, false)?></span></div>
                                                            </div>
                                                        <?else:?>

														
                                                            <div class="sale-number rounded2 <?=$arItem['PROPERTIES']['TSVET_TSENNIKA']["VALUE_ENUM_ID"] ? $arColorProp[$arItem['PROPERTIES']['TSVET_TSENNIKA']["VALUE_ENUM_ID"]] : 'no_bg_color'?>">
                                                                <?$percent=round(($diff/$oldPriceValue)*100);?>
                                                                <?if($percent && $percent<100){?>
                                                                    <div class="value">-<span><?=$percent;?></span>%</div>
                                                                <?}?>
                                                                <div class="inner-sale rounded1">
                                                                    <div class="text">Экономия<span class="values_wrapper"><?=\Aspro\Functions\CAsproMaxItem::getCurrentPrice($diff, $arPrice, false);?></span></div>
                                                                </div>
                                                            </div>
                                                        <?endif;?>
                                                    </div>
                                                </div>
                                            <?endif;?>
                                        <?endif;?>
                                    <?endif;?>
                                <?}?>
                            </div>
                            <?if($iCountPriceInterval > 1):?>
                                </div>
                            <?else:



                                if($arParams['SHOW_DISCOUNT_PERCENT'] == 'Y' && $arPrice["PRICE"] > $arPrice["DISCOUNT_PRICE"] && $showDiscountByOldPrice):?>
                                    <?$ratio = (!$bPriceRows ? $arAddToBasketData["MIN_QUANTITY_BUY"] : 1);?>
                                    <div class="sale_block 1">
                                        <div class="sale_wrapper font_xxs">
                                            <?$diff = ($arPrice["PRICE"] - $arPrice["DISCOUNT_PRICE"]);?>
                                            <?if($arParams['SHOW_DISCOUNT_PERCENT_NUMBER'] != 'Y'):?>
                                                <div class="inner-sale rounded1">
                                                    <span class="title">Экономия </span> <div class="text"><span class="values_wrapper" data-currency="<?=$arPrice["CURRENCY"];?>" data-value="<?=($diff*$ratio);?>"><?=\Aspro\Functions\CAsproMaxItem::getCurrentPrice($diff, $arPrice, false)?></span></div>
                                                </div>
                                            <?else:?>
                                                <div class="sale-number rounded2">
                                                    <?$percent=round(($diff/$arPrice["PRICE"])*100);?>
                                                    <?if($percent && $percent<100){?>
                                                        <div class="value">-<span><?=$percent;?></span>%</div>
                                                    <?}?>
                                                    <div class="inner-sale rounded1">
                                                        <div class="text">Экономия<span class="values_wrapper"><?=\Aspro\Functions\CAsproMaxItem::getCurrentPrice($diff, $arPrice, false);?></span></div>
                                                    </div>
                                                </div>
                                            <?endif;?>
                                        </div>
                                    </div>
                                <?endif;?>
                            <?endif;?>
                        <?endforeach;?>
                    </div>
                    <?if($iCountPriceGroup > 1):?>
                        </div>
                    <?endif;?>
                <?endforeach;?>
            </div>
            <?if($bShowPopupPrice):?>
                </div>
                <div class="more-btn text-center">
                    <a href="" class="font_upper colored_theme_hover_bg"><?=Loc::getMessage("MORE_LINK")?></a>
                </div>
                </div>
                </div>
            <?endif;?>
            <?$html = ob_get_contents();
            ob_end_clean();

            foreach(GetModuleEvents(ASPRO_MAX_MODULE_ID, 'OnAsproShowPriceMatrix', true) as $arEvent) // event for manipulation price matrix
                ExecuteModuleEventEx($arEvent, array($arItem, $arParams, $strMeasure, $arAddToBasketData, &$html));
        }
        return $html;
    }
}

if (!function_exists('showPriceMatrixCustomForDetail')){
    function showPriceMatrixCustomForDetail($arItem = array(), $arParams, $strMeasure = '', $arAddToBasketData = array()){


        $arColorProp = [
            '1676' => 'red_discont',
            '1677' => 'green_discont',
            '1678' => 'orange_discont',
            '1679' => 'white_discont',
            '1680' => 'yellow_discont',
        ];

        /*
         * Доработано
         * Сравнивается скидка (из правил работы с корзиной) на цену (id - 8), и цену со скидкой (7) и выбирается минимальная для вывода
         * */
        $html = '';
        if(
            isset($arItem['PRICE_MATRIX'])
            && $arItem['PRICE_MATRIX']
            && $arParams['USE_PRICE_COUNT'] == 'Y'
        ) {
            ob_start();?>
            <?$bShowPopupPrice = (
                CMax::GetFrontParametrValue('SHOW_POPUP_PRICE') == 'Y'
                && (
                    count($arItem['PRICE_MATRIX']['ROWS']) > 1
                    || count($arItem['PRICE_MATRIX']['COLS']) > 1
                )
            );?>
            <?if($bShowPopupPrice):?>
                <div class="js-info-block rounded3">
                <div class="block_title text-upper font_xs font-bold">
                    Варианты цен
                    <?=CMax::showIconSvg("close", SITE_TEMPLATE_PATH."/images/svg/Close.svg");?>
                </div>
                <div class="block_wrap">
                <div class="block_wrap_inner prices scrollblock">
            <?endif;?>
            <div class="price_matrix_block">
                <?
                $sDiscountPrices = \Bitrix\Main\Config\Option::get(ASPRO_MAX_MODULE_ID, 'DISCOUNT_PRICE');
                $arDiscountPrices = array();

                if($sDiscountPrices)
                    $arDiscountPrices = array_flip(explode(',', $sDiscountPrices));

                \Bitrix\Main\Type\Collection::sortByColumn($arItem['PRICE_MATRIX']['COLS'], array('SORT' => SORT_ASC));

                $arTmpPrice = (isset($arItem['ITEM_PRICES']) ? current($arItem['ITEM_PRICES']) : array());

                $iCountPriceGroup = count($arItem['PRICE_MATRIX']['COLS']);
                $bPriceRows = (count($arItem['PRICE_MATRIX']['ROWS']) > 1);

                $idOldPrice = 7;

                $oldPriceDiscountVal = $arItem['PRICE_MATRIX']['MATRIX'][7]["ZERO-INF"]['DISCOUNT_PRICE'];// 7 - id цены "Розничная без скидки"

                if($arDiscountPrices){
                    foreach($arItem['PRICE_MATRIX']['MATRIX'] as $key => $arPrice){

                        if ($arPrice['DISCOUNT_PRICE'] < $oldPriceDiscountVal){
                            $arMinPrice[$key] = $arPrice['ZERO-INF']['DISCOUNT_PRICE'];
                        }elseif($arPrice['DISCOUNT_PRICE'] > $oldPriceDiscountVal){
                            $arMinPrice[$key] = $oldPriceDiscountVal;
                        }
                        else{
                            $arMinPrice[$key] = $arPrice['ZERO-INF']['DISCOUNT_PRICE'];
                        }
                    }

                    $keyMinPrice = array_keys($arMinPrice, min($arMinPrice))[0];
                }

                ?>
                <?foreach($arItem['PRICE_MATRIX']['COLS'] as $arPriceGroup):?>
                    <?
                    $showDiscountByOldPrice = false;
                    ?>
                    <?if($iCountPriceGroup > 1):?>
                        <?
                        $class = '';
                        if($arTmpPrice)
                        {
                            if($arItem['PRICE_MATRIX']['MATRIX'][$arPriceGroup['ID']][$arTmpPrice['QUANTITY_HASH']]['ID'] == $arTmpPrice['ID']){
                                $class = 'min';
                            }else{
                                $class = 'not-show';
                            }

                        }?>
                        <div class="price_group <?=$class;?> <?=$arPriceGroup['XML_ID']?>">
                    <?endif;?>
                    <?

                    if ($arPriceGroup['ID'] == $keyMinPrice){
                        $showDiscountByOldPrice = true;
                    }

                    ?>
                    <div class="price_matrix_wrapper custom <?=($arDiscountPrices ? (isset($arDiscountPrices[$arPriceGroup['ID']]) && !$showDiscountByOldPrice  ? 'strike_block 2' : '') : '');?>">
                        <?$iCountPriceInterval = count($arItem['PRICE_MATRIX']['MATRIX'][$arPriceGroup['ID']]);?>
                        <?foreach($arItem['PRICE_MATRIX']['MATRIX'][$arPriceGroup['ID']] as $key => $arPrice):?>
                            <?if($iCountPriceInterval > 1):?>
                                <div class="price_wrapper_block clearfix">
                                <div class="price_interval pull-left font_xs muted777">
                                    <?
                                    $quantity_from = ($arItem['PRICE_MATRIX']['ROWS'][$key]['QUANTITY_FROM'] ? $arItem['PRICE_MATRIX']['ROWS'][$key]['QUANTITY_FROM'] : 0);
                                    $quantity_to = $arItem['PRICE_MATRIX']['ROWS'][$key]['QUANTITY_TO'];
                                    $text = ($quantity_to ? Loc::getMessage('FROM').' '.$quantity_from.' '.Loc::getMessage('TO').' '.$quantity_to :Loc::getMessage('FROM').' '.$quantity_from );
                                    ?>
                                    <div><?=$text?><?if(($arParams["SHOW_MEASURE"]=="Y") && $strMeasure):?> <?=$strMeasure?><?endif;?></div>
                                </div>
                            <?endif;?>
                            <div class="prices-wrapper price__wrapper <?=($iCountPriceInterval > 1 ? ' pull-right text-right' : '');?>">

                                <? if ($arPrice["PRICE"] > $arPrice["DISCOUNT_PRICE"] && $showDiscountByOldPrice) {?>
                                    <div class="price font-bold <?=(($iCountPriceInterval > 1) ? 'font_xs' : ($arParams['MD_PRICE'] ? 'font_mlg' : 'font_mxs dd'));?>" data-currency="<?=$arPrice["CURRENCY"];?>" data-value="<?=$arPrice["DISCOUNT_PRICE"];?>">
                                        <? if(strlen($arPrice["DISCOUNT_PRICE"])): ?>
                                            <? if ($arItem['SHOW_FROM_LANG'] == 'Y'): ?><span><?=Loc::getMessage('FROM')?></span><?endif;?>
                                            <span class="values_wrapper">
                                                <?=\Aspro\Functions\CAsproMaxItem::getCurrentPrice("DISCOUNT_PRICE", $arPrice);?>
                                            </span>
                                            <?if(($arParams["SHOW_MEASURE"]=="Y") && $strMeasure && $arPrice["DISCOUNT_PRICE"]):?><span class="price_measure">/<?=$strMeasure?></span><?endif;?>
                                        <? endif; ?>
                                    </div>
                                    <? if ($arParams["SHOW_OLD_PRICE"]=="Y"): ?>
                                        <div class="price discount" data-currency="<?=$arPrice["CURRENCY"];?>" data-value="<?=$arPrice["PRICE"];?>">
                                            <span class="values_wrapper <?=($arParams['MD_PRICE'] ? 'font_sm' : 'font_xs');?> muted"><?=\Aspro\Functions\CAsproMaxItem::getCurrentPrice("PRICE", $arPrice);?></span>
                                        </div>
                                    <? endif; ?>
                                <? } else { ?>
                                    <? if ($showDiscountByOldPrice): ?>
                                        <div class="price" data-currency="<?=$arPrice["CURRENCY"];?>" data-value="<?=$arPrice["DISCOUNT_PRICE"];?>">
                                            <? if ($arItem['SHOW_FROM_LANG'] == 'Y'): ?><span><?=Loc::getMessage('FROM')?></span><? endif; ?>
                                            <span class="price__inner">
                                                <span class="price__value"><?=\Aspro\Functions\CAsproMaxItem::getCurrentPrice("PRICE", $arPrice);?></span>
                                                <?if(($arParams["SHOW_MEASURE"]=="Y") && $strMeasure && $arPrice["PRICE"]):?><span class="price_measure">/<?=$strMeasure?></span><?endif;?>
                                            </span>
                                        </div>
                                        <? if($arParams["SHOW_OLD_PRICE"]=="Y"): ?>
                                            <? $oldPriceValue = $arItem['PRICE_MATRIX']['MATRIX'][$idOldPrice]["ZERO-INF"]['PRICE']; ?>

                                            <? if($arParams['SHOW_DISCOUNT_PERCENT'] == 'Y' &&  $oldPriceValue > $arPrice["PRICE"]): ?>
                                                <? $ratio = (!$bPriceRows ? $arAddToBasketData["MIN_QUANTITY_BUY"] : 1); ?>
                                                <? $diff = ($oldPriceValue - $arPrice["PRICE"]); ?>
                                                <? if($arParams['SHOW_DISCOUNT_PERCENT_NUMBER'] != 'Y'): ?>
                                                    <div class="sale_block">
                                                        <div class="inner-sale rounded1">
                                                            <span class="title">Экономия </span>
                                                            <div class="text"><span class="values_wrapper" data-currency="<?=$arPrice["CURRENCY"];?>" data-value="<?=($diff*$ratio);?>">
                                                                <?=\Aspro\Functions\CAsproMaxItem::getCurrentPrice($diff, $arPrice, false)?>
                                                            </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <? else: ?>
                                                    <span class="price__discount <?=$arItem['PROPERTIES']['TSVET_TSENNIKA']["VALUE_ENUM_ID"] ? $arColorProp[$arItem['PROPERTIES']['TSVET_TSENNIKA']["VALUE_ENUM_ID"]] : 'no_bg_color'?>">
                                                    <? $percent=round(($diff/$oldPriceValue)*100); ?>
                                                        <? if ($percent && $percent<100) { ?>
                                                            <span class="value">-<span><?=$percent;?></span>%</span>
                                                        <? } ?>
                                                </span>
                                                <? endif; ?>
                                            <? endif; ?>

                                            <div class="price discount" data-currency="<?=$arPrice["CURRENCY"];?>" data-value="<?=$arPrice["PRICE"];?>">
                                                    <del class="price__old">
                                                        <span class="price_value"><?=CurrencyFormat($oldPriceValue, $arPrice['CURRENCY'])?></span>
                                                    </del>
                                            </div>
                                        <? endif; ?>
                                    <? endif; ?>
                                <? } ?>
                            </div>
                            <? if($iCountPriceInterval > 1): ?>
                                </div>
                            <? else: ?>
                                <? if ($arParams['SHOW_DISCOUNT_PERCENT'] == 'Y' && $arPrice["PRICE"] > $arPrice["DISCOUNT_PRICE"] && $showDiscountByOldPrice): ?>
                                    <?$ratio = (!$bPriceRows ? $arAddToBasketData["MIN_QUANTITY_BUY"] : 1);?>
                                    <div class="sale_block 1">
                                        <div class="sale_wrapper font_xxs">
                                            <? $diff = ($arPrice["PRICE"] - $arPrice["DISCOUNT_PRICE"]); ?>
                                            <? if($arParams['SHOW_DISCOUNT_PERCENT_NUMBER'] != 'Y'): ?>
                                                <div class="inner-sale rounded1">
                                                    <span class="title">Экономия </span> <div class="text"><span class="values_wrapper" data-currency="<?=$arPrice["CURRENCY"];?>" data-value="<?=($diff*$ratio);?>"><?=\Aspro\Functions\CAsproMaxItem::getCurrentPrice($diff, $arPrice, false)?></span></div>
                                                </div>
                                            <? else: ?>
                                                <div class="sale-number rounded2">
                                                    <? $percent=round(($diff/$arPrice["PRICE"])*100); ?>
                                                    <? if ($percent && $percent<100) { ?>
                                                        <div class="value">-<span><?=$percent;?></span>%</div>
                                                    <? } ?>
                                                    <div class="inner-sale rounded1">
                                                        <div class="text">Экономия<span class="values_wrapper"><?=\Aspro\Functions\CAsproMaxItem::getCurrentPrice($diff, $arPrice, false);?></span></div>
                                                    </div>
                                                </div>
                                            <? endif; ?>
                                        </div>
                                    </div>
                                <? endif; ?>
                            <? endif; ?>
                        <? endforeach; ?>
                    </div>
                    <? if ($iCountPriceGroup > 1) : ?>
                        </div>
                    <? endif; ?>
                <? endforeach; ?>
            </div>
            <?if($bShowPopupPrice):?>
                </div>
                <div class="more-btn text-center">
                    <a href="" class="font_upper colored_theme_hover_bg"><?=Loc::getMessage("MORE_LINK")?></a>
                </div>
                </div>
                </div>
            <?endif;?>
            <?$html = ob_get_contents();
            ob_end_clean();

            foreach(GetModuleEvents(ASPRO_MAX_MODULE_ID, 'OnAsproShowPriceMatrix', true) as $arEvent) // event for manipulation price matrix
                ExecuteModuleEventEx($arEvent, array($arItem, $arParams, $strMeasure, $arAddToBasketData, &$html));
        }
        return $html;
    }
}

//AddEventHandler("sale", "OnOrderSave", "changeLocationFromAddres");
//function changeLocationFromAddres($orderId,$fields, $orderFields, $isNew){
//    \Bitrix\Main\Diag\Debug::dumpToFile($fields, $varName = "Logs", $fileName = SITE_DIR.'local/php_interface/testOrder.txt');
//    \Bitrix\Main\Diag\Debug::dumpToFile($orderFields, $varName = "Logs", $fileName = SITE_DIR.'local/php_interface/testOrder.txt');
//
//}




?>
