<?php

namespace Cosmo;

use \Aspro\Functions\CAsproMaxItem;
use CMax;

/**
 *
 */

class CosmoMaxItem extends CAsproMaxItem
{

    public static function showDelayCompareBtn($arParams = array(), $arItem = array(), $arAddToBasketData = array(), $totalCount, $bUseSkuProps = false, $class = '', $bShowFW = false, $bShowOCB = false, $typeSvg = '', $currentSKUID = '', $currentSKUIBlock = ''){
        if($arItem):?>
            <?ob_start();?>
            <?
            $i = 0;
            if($arParams["DISPLAY_WISH_BUTTONS"] == "Y")
            {
                if(!$arItem["OFFERS"])
                {
                    if(\CMax::checkShowDelay($arParams, $totalCount, $arItem))
                        $i++;
                }
                elseif($bUseSkuProps)
                {
                    // if($arAddToBasketData["CAN_BUY"])
                    $i++;
                }
            }

            if($arParams["DISPLAY_COMPARE"] == "Y")
                $i++;

            if($arItem["OFFERS_MORE"] != "Y" && $bShowOCB)
            {
                if($arAddToBasketData["CAN_BUY"])
                    $i++;
            }

            $bWithText = (strpos($class, 'list') !== false);
            $bWithIcons = (strpos($class, 'icons') !== false);

            if(!$currentSKUID)
                $currentSKUID = $arItem["ID"];
            ?>
            <div class="like_icons <?=$class;?>" data-size="<?=$i;?>">
                <?if($arParams["DISPLAY_WISH_BUTTONS"] != "N" || $arParams["DISPLAY_COMPARE"] == "Y"):?>
                    <?if($arParams["DISPLAY_WISH_BUTTONS"] == "Y"):?>
                        <?if(!$arItem["OFFERS"]):?>
                            <div class="wish_item_button" <?=(\CMax::checkShowDelay($arParams, $totalCount, $arItem) ? '' : 'style="display:none"');?>>
                                <button class="product__fav wish_item to" title="<?=GetMessage('CATALOG_WISH')?>" data-quantity="<?=$arAddToBasketData["MIN_QUANTITY_BUY"]?>" data-item="<?=$arItem["ID"]?>" data-iblock="<?=$arItem["IBLOCK_ID"]?>">
                                    <svg width="20" height="18" viewBox="0 0 20 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M15 0.5C12.5 0.5 10 2.5 10 4.5C10 2.5 7.5 0.5 5 0.5C2.5 0.5 0.5 2.5 0.5 5.5C0.5 8.5 2.5 14.5 10 17.5C17.5 14.5 19.5 8.5 19.5 5.5C19.5 2.5 17.5 0.5 15 0.5Z" stroke="#333333" stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>
                                    <span class="product__fav-text"><?=GetMessage('CATALOG_WISH')?></span>
                                </button>
                                <button style="display: none;" class="product__fav product__fav--active wish_item in added" title="<?=GetMessage('CATALOG_WISH_OUT')?>" data-quantity="<?=$arAddToBasketData["MIN_QUANTITY_BUY"]?>" data-item="<?=$arItem["ID"]?>" data-iblock="<?=$arItem["IBLOCK_ID"]?>">
                                    <svg width="20" height="18" viewBox="0 0 20 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M15 0.5C12.5 0.5 10 2.5 10 4.5C10 2.5 7.5 0.5 5 0.5C2.5 0.5 0.5 2.5 0.5 5.5C0.5 8.5 2.5 14.5 10 17.5C17.5 14.5 19.5 8.5 19.5 5.5C19.5 2.5 17.5 0.5 15 0.5Z" stroke="#333333" stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>
                                    <span class="product__fav-text"><?=GetMessage('CATALOG_WISH_OUT')?></span>
                                </button>
                                <?/*
                                <span title="<?=GetMessage('CATALOG_WISH')?>" data-quantity="<?=$arAddToBasketData["MIN_QUANTITY_BUY"]?>"
        class="wish_item to rounded3 <?=($bWithText ? 'btn btn-xs font_upper_xs btn-transparent' : 'colored_theme_hover_bg');?>" data-item="<?=$arItem["ID"]?>" data-iblock="<?=$arItem["IBLOCK_ID"]?>"><?=\CMax::showIconSvg("wish ncolor colored", SITE_TEMPLATE_PATH."/images/svg/chosen".$typeSvg.".svg");?><?if($bWithText && !$bWithIcons):?><span class="like-text"><?=GetMessage('CATALOG_WISH')?></span><?endif;?></span>
                                <span title="<?=GetMessage('CATALOG_WISH_OUT')?>" data-quantity="<?=$arAddToBasketData["MIN_QUANTITY_BUY"]?>" class="wish_item in added rounded3 <?=($bWithText ? 'btn btn-xs font_upper_xs btn-transparent' : 'colored_theme_bg');?>" style="display: none;" data-item="<?=$arItem["ID"]?>" data-iblock="<?=$arItem["IBLOCK_ID"]?>"><?=\CMax::showIconSvg("wish ncolor colored", SITE_TEMPLATE_PATH."/images/svg/chosen".$typeSvg.".svg");?><?if($bWithText && !$bWithIcons):?><span class="like-text"><?=GetMessage('CATALOG_WISH_OUT')?></span><?endif;?></span>
                            */?>
                                </div>
                        <?elseif($bUseSkuProps):?>
                            <div class="wish_item_button" <?=(!$arAddToBasketData["CAN_BUY"] ? 'style="display:none;"' : '');?>>
                                <span title="<?=GetMessage('CATALOG_WISH')?>" data-quantity="<?=$arAddToBasketData["MIN_QUANTITY_BUY"]?>" class="wish_item to <?=$arParams["TYPE_SKU"];?> rounded3 <?=($bWithText ? 'btn btn-xs font_upper_xs btn-transparent' : 'colored_theme_hover_bg');?>" data-item="<?=$currentSKUID;?>" data-iblock="<?=$currentSKUIBlock?>" data-offers="Y" data-props="<?=$arOfferProps?>"><?=\CMax::showIconSvg("wish ncolor colored", SITE_TEMPLATE_PATH."/images/svg/chosen".$typeSvg.".svg");?><?if($bWithText && !$bWithIcons):?><span class="like-text"><?=GetMessage('CATALOG_WISH')?></span><?endif;?></span>
                                <span title="<?=GetMessage('CATALOG_WISH_OUT')?>" data-quantity="<?=$arAddToBasketData["MIN_QUANTITY_BUY"]?>" class="wish_item in added <?=$arParams["TYPE_SKU"];?> rounded3 <?=($bWithText ? 'btn btn-xs font_upper_xs btn-transparent' : 'colored_theme_bg');?>" style="display: none;" data-item="<?=$currentSKUID;?>" data-iblock="<?=$currentSKUIBlock?>"><?=\CMax::showIconSvg("wish ncolor colored", SITE_TEMPLATE_PATH."/images/svg/chosen".$typeSvg.".svg");?><?if($bWithText && !$bWithIcons):?><span class="like-text"><?=GetMessage('CATALOG_WISH_OUT')?></span><?endif;?></span>
                            </div>
                        <?endif;?>
                    <?endif;?>
                    <?if($arParams["DISPLAY_COMPARE"] == "Y"):?>
                        <?if(!$bUseSkuProps):?>
                            <div class="compare_item_button">
                                <span title="<?=GetMessage('CATALOG_COMPARE')?>" class="compare_item to rounded3 <?=($bWithText ? 'btn btn-xs font_upper_xs btn-transparent' : 'colored_theme_hover_bg');?>" data-iblock="<?=$arParams["IBLOCK_ID"]?>" data-item="<?=$arItem["ID"]?>" ><?=\CMax::showIconSvg("compare ncolor colored", SITE_TEMPLATE_PATH."/images/svg/compare".$typeSvg.".svg");?><?if($bWithText && !$bWithIcons):?><span class="like-text"><?=GetMessage('CATALOG_COMPARE')?></span><?endif;?></span>
                                <span title="<?=GetMessage('CATALOG_COMPARE_OUT')?>" class="compare_item in added rounded3 <?=($bWithText ? 'btn btn-xs font_upper_xs btn-transparent' : 'colored_theme_bg');?>" style="display: none;" data-iblock="<?=$arParams["IBLOCK_ID"]?>" data-item="<?=$arItem["ID"]?>"><?=\CMax::showIconSvg("compare ncolor colored", SITE_TEMPLATE_PATH."/images/svg/compare".$typeSvg.".svg");?><?if($bWithText && !$bWithIcons):?><span class="like-text"><?=GetMessage('CATALOG_COMPARE_OUT')?></span><?endif;?></span>
                            </div>
                        <?elseif($arItem["OFFERS"]):?>
                            <div class="compare_item_button">
                                <span title="<?=GetMessage('CATALOG_COMPARE')?>" class="compare_item to <?=$arParams["TYPE_SKU"];?> rounded3 <?=($bWithText ? 'btn btn-xs font_upper_xs btn-transparent' : 'colored_theme_hover_bg');?>" data-item="<?=$currentSKUID;?>" data-iblock="<?=$arItem["IBLOCK_ID"]?>" ><?=\CMax::showIconSvg("compare ncolor colored", SITE_TEMPLATE_PATH."/images/svg/compare".$typeSvg.".svg");?><?if($bWithText && !$bWithIcons):?><span class="like-text"><?=GetMessage('CATALOG_COMPARE')?></span><?endif;?></span>
                                <span title="<?=GetMessage('CATALOG_COMPARE_OUT')?>" class="compare_item in added <?=$arParams["TYPE_SKU"];?> rounded3 <?=($bWithText ? 'btn btn-xs font_upper_xs btn-transparent' : 'colored_theme_bg');?>" style="display: none;" data-item="<?=$currentSKUID;?>" data-iblock="<?=$arItem["IBLOCK_ID"]?>"><?=\CMax::showIconSvg("compare ncolor colored", SITE_TEMPLATE_PATH."/images/svg/compare".$typeSvg.".svg");?><?if($bWithText && !$bWithIcons):?><span class="like-text"><?=GetMessage('CATALOG_COMPARE_OUT')?></span><?endif;?></span>
                            </div>
                        <?endif;?>
                    <?endif;?>
                <?endif;?>
                <?if($bShowOCB):?>
                    <div class="wrapp_one_click">
                        <?if(/*$arAddToBasketData["ACTION"] == "ADD" &&*/$arItem["OFFERS_MORE"] != "Y" && $arAddToBasketData["CAN_BUY"]):?>
                        <?if($currentSKUID && $currentSKUIBlock):?>
                        <span class="rounded3 colored_theme_hover_bg one_click" data-item="<?=$currentSKUID?>" data-iblockID="<?=$currentSKUIBlock?>" data-quantity="<?=$arAddToBasketData["MIN_QUANTITY_BUY"];?>" onclick="oneClickBuy('<?=$currentSKUID?>', '<?= $arItem['IBLOCK_ID']; ?>', this)" title="<?=Loc::getMessage('ONE_CLICK_BUY')?>">
										<?else:?>
											<span class="rounded3 colored_theme_hover_bg one_click" data-item="<?=$arItem["ID"]?>" data-iblockID="<?=$arItem["IBLOCK_ID"]?>" data-quantity="<?=$arAddToBasketData["MIN_QUANTITY_BUY"];?>" onclick="oneClickBuy('<?=$arItem["ID"]?>', '<?=$arItem["IBLOCK_ID"]?>', this)" title="<?=Loc::getMessage('ONE_CLICK_BUY')?>">
										<?endif;?>
                                        <?=\CMax::showIconSvg("fw ncolor colored", SITE_TEMPLATE_PATH."/images/svg/quickbuy".$typeSvg.".svg");?>
										</span>
								<?endif;?>
                    </div>
                <?endif;?>
                <?if($bShowFW):?>
                    <?if($fast_view_text_tmp = \CMax::GetFrontParametrValue('EXPRESSION_FOR_FAST_VIEW'))
                        $fast_view_text = $fast_view_text_tmp;
                    else
                        $fast_view_text = Loc::getMessage('FAST_VIEW');?>
                    <div class="fast_view_button">
                        <span title="<?=$fast_view_text?>" class="rounded3 colored_theme_hover_bg" data-event="jqm" data-param-form_id="fast_view" data-param-iblock_id="<?=$arParams["IBLOCK_ID"];?>" data-param-id="<?=$arItem["ID"];?>" data-param-item_href="<?=urlencode($arItem["DETAIL_PAGE_URL"]);?>" data-name="fast_view"><?=\CMax::showIconSvg("fw ncolor colored", SITE_TEMPLATE_PATH."/images/svg/quickview".$typeSvg.".svg");?></span>
                    </div>
                <?endif;?>
            </div>
            <?$html = ob_get_contents();
            ob_end_clean();

            foreach(GetModuleEvents(FUNCTION_MODULE_ID, 'OnAsproShowDelayCompareBtn', true) as $arEvent) // event for manipulation item delay and compare buttons
                ExecuteModuleEventEx($arEvent, array($arParams, $arItem, $arAddToBasketData, $totalCount, $bUseSkuProps, &$html));

            echo $html;?>
        <?endif;?>
    <?php }

}