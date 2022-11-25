<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?$this->setFrameMode(true);?>
<?use \Bitrix\Main\Localization\Loc,
	  \Bitrix\Main\Web\Json;?>
<?if( count( $arResult["ITEMS"] ) >= 1 ){?>
	<?if($arParams["AJAX_REQUEST"]=="N"){?>
		<div class="display_list <?=($arParams["SHOW_UNABLE_SKU_PROPS"] != "N" ? "show_un_props" : "unshow_un_props");?> js_append <?=$arParams["TYPE_VIEW_CATALOG_LIST"];?>  flexbox flexbox--row">
	<?}?>
		<?
		$currencyList = '';
		if (!empty($arResult['CURRENCIES'])){
			$templateLibrary[] = 'currency';
			$currencyList = CUtil::PhpToJSObject($arResult['CURRENCIES'], false, true, true);
		}
		$templateData = array(
			'TEMPLATE_LIBRARY' => $templateLibrary,
			'CURRENCIES' => $currencyList
		);
		unset($currencyList, $templateLibrary);

		$arParams["BASKET_ITEMS"] = ($arParams["BASKET_ITEMS"] ? $arParams["BASKET_ITEMS"] : array());

		$arOfferProps = implode(';', $arParams['OFFERS_CART_PROPERTIES']);

		$bNormalView = ($arParams["TYPE_VIEW_CATALOG_LIST"] == "TYPE_1");

		// params for catalog elements compact view
		$arParamsCE_CMP = $arParams;
		$arParamsCE_CMP['TYPE_SKU'] = 'N';

		?>
		<?foreach($arResult["ITEMS"] as $arItem){?>

			<?$this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "ELEMENT_EDIT"));
			$this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BCS_ELEMENT_DELETE_CONFIRM')));

			if(!empty($arItem['PRODUCT_PROPERTIES_FILL']))
			{
				foreach ($arItem['PRODUCT_PROPERTIES_FILL'] as $propID => $propInfo)
				{
					if(isset($arItem['PRODUCT_PROPERTIES'][$propID]))
						unset($arItem['PRODUCT_PROPERTIES'][$propID]);
				}
			}

			$emptyProductProperties = empty($arItem['PRODUCT_PROPERTIES']);
			$arItem["EMPTY_PROPS_JS"]=(!$emptyProductProperties ? "N" : "Y");

			$item_id = $arItem["ID"];

			$arItem["strMainID"] = $this->GetEditAreaId($arItem['ID']);
			$arItemIDs=CMax::GetItemsIDs($arItem);

			$totalCount = CMax::GetTotalCount($arItem, $arParams);
			$arQuantityData = CMax::GetQuantityArray($totalCount, array('ID' => $item_id), 'N', (($arItem["OFFERS"] || $arItem['CATALOG_TYPE'] == CCatalogProduct::TYPE_SET || $bSlide || !$arResult['STORES_COUNT']) ? false : true));

			$strMeasure = '';
			$arAddToBasketData = array();

			$arCurrentSKU = array();

			$bComplect = $arItem["PROPERTIES"]["PRODUCT_SET"]["VALUE"] === "Y";
			$addParams = array();
			if($bComplect){
				$addParams = array("DISPLAY_WISH_BUTTONS" => "N", "MESSAGE_FROM" => Loc::getMessage('FROM').' ');
				$arItem["SHOW_FROM_LANG"] = "Y";
			}

			$elementName = ((isset($arItem['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE']) && $arItem['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE']) ? $arItem['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE'] : $arItem['NAME']);

			$bUseSkuProps = ($arItem["OFFERS"] && !empty($arItem['OFFERS_PROP']));

			if(!$arItem["OFFERS"] || $arParams['TYPE_SKU'] !== 'TYPE_1'){
				if($arParams["SHOW_MEASURE"] == "Y" && $arItem["CATALOG_MEASURE"]){
					$arMeasure = CCatalogMeasure::getList(array(), array("ID" => $arItem["CATALOG_MEASURE"]), false, false, array())->GetNext();
					$strMeasure = $arMeasure["SYMBOL_RUS"];
				}
				if($bComplect){
					$arItem["SHOW_MORE_BUTTON"] = "Y";
				}
				$arAddToBasketData = CMax::GetAddToBasketArray($arItem, $totalCount, $arParams["DEFAULT_COUNT"], $arParams["BASKET_URL"], false, $arItemIDs["ALL_ITEM_IDS"], '', $arParams);
			}
			elseif($arItem["OFFERS"]){
				$strMeasure = $arItem["MIN_PRICE"]["CATALOG_MEASURE_NAME"];
				if($arParams['TYPE_SKU'] == 'TYPE_1' && $arItem['OFFERS_PROP'])
				{
					$currentSKUIBlock = $arItem["OFFERS"][$arItem["OFFERS_SELECTED"]]["IBLOCK_ID"];
					$currentSKUID = $arItem["OFFERS"][$arItem["OFFERS_SELECTED"]]["ID"];
					$arItem["OFFERS"][$arItem["OFFERS_SELECTED"]]["IS_OFFER"] = "Y";

					//$totalCountCMP = CMax::GetTotalCount($arItem, $arParamsCE_CMP);
					$totalCountCMP = $totalCount;
					$arQuantityDataCMP = CMax::GetQuantityArray($totalCountCMP, array('ID' => $item_id), 'N', false, 'ce_cmp_visible');					

					$totalCount = CMax::GetTotalCount($arItem["OFFERS"][$arItem["OFFERS_SELECTED"]], $arParams);
					$arQuantityData = CMax::GetQuantityArray($totalCount, array('ID' => $currentSKUID), 'N', (($arItem['CATALOG_TYPE'] == CCatalogProduct::TYPE_SET || $bSlide || !$arResult['STORES_COUNT']) ? false : true), 'ce_cmp_hidden');

					$arItem["DETAIL_PAGE_URL"] = $arItem["OFFERS"][$arItem["OFFERS_SELECTED"]]["DETAIL_PAGE_URL"];
					if($arItem["OFFERS"][$arItem["OFFERS_SELECTED"]]["PREVIEW_PICTURE"])
						$arItem["PREVIEW_PICTURE"] = $arItem["OFFERS"][$arItem["OFFERS_SELECTED"]]["PREVIEW_PICTURE"];
					if($arItem["OFFERS"][$arItem["OFFERS_SELECTED"]]["PREVIEW_PICTURE"])
						$arItem["DETAIL_PICTURE"] = $arItem["OFFERS"][$arItem["OFFERS_SELECTED"]]["DETAIL_PICTURE"];

					if($arItem["OFFERS"][$arItem["OFFERS_SELECTED"]]['IPROPERTY_VALUES']){
						$arItem['SELECTED_SKU_IPROPERTY_VALUES'] = $arItem["OFFERS"][$arItem["OFFERS_SELECTED"]]['IPROPERTY_VALUES'];
					}

					if($arParams["SET_SKU_TITLE"] == "Y")
						$arItem["NAME"] = $elementName = ((isset($arItem["OFFERS"][$arItem["OFFERS_SELECTED"]]['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE']) && $arItem["OFFERS"][$arItem["OFFERS_SELECTED"]]['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE']) ? $arItem["OFFERS"][$arItem["OFFERS_SELECTED"]]['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE'] : $arItem["OFFERS"][$arItem["OFFERS_SELECTED"]]['NAME']);
					$item_id = $currentSKUID;

					// ARTICLE
					if($arItem["OFFERS"][$arItem["OFFERS_SELECTED"]]["DISPLAY_PROPERTIES"]["ARTICLE"]["VALUE"])
					{
						$arItem["ARTICLE"]["NAME"] = $arItem["OFFERS"][$arItem["OFFERS_SELECTED"]]["DISPLAY_PROPERTIES"]["ARTICLE"]["NAME"];
						$arItem["ARTICLE"]["VALUE"] = (is_array($arItem["OFFERS"][$arItem["OFFERS_SELECTED"]]["DISPLAY_PROPERTIES"]["ARTICLE"]["VALUE"]) ? reset($arItem["OFFERS"][$arItem["OFFERS_SELECTED"]]["DISPLAY_PROPERTIES"]["ARTICLE"]["VALUE"]) : $arItem["OFFERS"][$arItem["OFFERS_SELECTED"]]["DISPLAY_PROPERTIES"]["ARTICLE"]["VALUE"]);
						unset($arItem["OFFERS"][$arItem["OFFERS_SELECTED"]]["DISPLAY_PROPERTIES"]["ARTICLE"]);
					}

					$arCurrentSKU = $arItem["JS_OFFERS"][$arItem["OFFERS_SELECTED"]];
					$strMeasure = $arCurrentSKU["MEASURE"];

					/* need for add basket props */
					$arItem["OFFERS"][$arItem["OFFERS_SELECTED"]]["IBLOCK_ID"] = $arItem['IBLOCK_ID'];
					/* */

					$arAddToBasketData = CMax::GetAddToBasketArray($arItem["OFFERS"][$arItem["OFFERS_SELECTED"]], $totalCount, $arParams["DEFAULT_COUNT"], $arParams["BASKET_URL"], false, $arItemIDs["ALL_ITEM_IDS"], '', $arParams);

					/* restore IBLOCK_ID */
					$arItem["OFFERS"][$arItem["OFFERS_SELECTED"]]["IBLOCK_ID"] = $currentSKUIBlock;
					/* */
				}
			}
			?>
			<div class="list_item_wrapp item_wrap item item-parent clearfix bordered box-shadow js-notice-block">
				<div class="list_item item_info catalog-adaptive flexbox flexbox--row <?=($arItem["OFFERS"] ? 'has-sku' : '')?>" id="<?=$arItemIDs["strMainID"];?>">
					<?if($arParams['SHOW_GALLERY'] == 'Y' && $arItem['OFFERS']):?>
						<div class="js-item-gallery hidden"><?\Aspro\Functions\CAsproMaxItem::showSectionGallery( array('ITEM' => $arItem, 'RESIZE' => $arResult['CUSTOM_RESIZE_OPTIONS']) );?></div>
					<?endif;?>
					<?//image block?>
					<div class="image_block">
						<div class="image_wrapper_block js-notice-block__image">
							<?\Aspro\Functions\CAsproMaxItem::showStickers($arParams, $arItem, true);?>
							<?//if($arParams['SHOW_GALLERY'] == 'Y'):?>
								<?/*if($bUseSkuProps && $arItem["OFFERS"]):?>
									<?\Aspro\Functions\CAsproMaxItem::showSectionGallery( array('ITEM' => $arItem["OFFERS"][$arItem["OFFERS_SELECTED"]], 'RESIZE' => $arResult['CUSTOM_RESIZE_OPTIONS']) );?>
								<?else:?>
									<?\Aspro\Functions\CAsproMaxItem::showSectionGallery( array('ITEM' => $arItem, 'RESIZE' => $arResult['CUSTOM_RESIZE_OPTIONS']) );?>
								<?endif;?>
							<?else:*/?>
								<?\Aspro\Functions\CAsproMaxItem::showImg($arParams, $arItem, false);?>
							<?//endif;?>
						</div>
						<?if($bNormalView && ($arParams['USE_FAST_VIEW'] != 'N')):?>
							<?if($fast_view_text_tmp = CMax::GetFrontParametrValue('EXPRESSION_FOR_FAST_VIEW'))
								$fast_view_text = $fast_view_text_tmp;
							else
								$fast_view_text = Loc::getMessage('FAST_VIEW');?>
							<?if(!$bComplect):?>
								<div class="fast_view_block rounded2 btn btn-xs font_upper_xs btn-transparent" data-event="jqm" data-param-form_id="fast_view" data-param-iblock_id="<?=$arParams["IBLOCK_ID"];?>" data-param-id="<?=$arItem["ID"];?>" data-param-item_href="<?=urlencode($arItem["DETAIL_PAGE_URL"]);?>" data-name="fast_view">
									<?=\CMax::showIconSvg("fw ncolor", SITE_TEMPLATE_PATH."/images/svg/quickview.svg", '', '', true, false);?><?=$fast_view_text;?>
								</div>
							<?endif;?>
						<?endif;?>
						<div class="adaptive">
							<?\Aspro\Functions\CAsproMaxItem::showDelayCompareBtn(array_merge($arParams, $addParams), $arItem, $arAddToBasketData, $totalCount, $bUseSkuProps, 'block', ($arParams['USE_FAST_VIEW'] != 'N'), ($arParams['SHOW_ONE_CLICK_BUY'] == 'Y'), '_small', $currentSKUID, $currentSKUIBlock);?>
						</div>
					</div>

					<?//text-block?>
					<div class="description_wrapp">
						<div class="description">
							<div class="item-title">
								<a href="<?=$arItem["DETAIL_PAGE_URL"]?>" class="dark_link js-notice-block__title"><span><?=$elementName;?></span></a>
							</div>
							<div class="wrapp_stockers md-store sa_block <?=($arParams["SHOW_RATING"] == "Y" ? 'with-rating' : '');?>" data-fields='<?=Json::encode($arParams["FIELDS"])?>' data-user-fields='<?=Json::encode($arParams["USER_FIELDS"])?>' data-stores='<?=Json::encode($arParams["STORES"])?>'>
								<?if($arParams["SHOW_RATING"] == "Y"):?>
									<div class="rating sm-stars">
										<?$frame = $this->createFrame('dv_'.$arItem["ID"])->begin('');?>
											<?
											global $arTheme;
											if( $arParams['REVIEWS_VIEW'] ):?>
												<div class="blog-info__rating--top-info">
													<div class="votes_block nstar with-text">
														<div class="ratings">
															<?$message = $arItem['PROPERTIES']['EXTENDED_REVIEWS_COUNT']['VALUE'] ? GetMessage('VOTES_RESULT', array('#VALUE#' => $arItem['PROPERTIES']['EXTENDED_REVIEWS_RAITING']['VALUE'])) : GetMessage('VOTES_RESULT_NONE')?>
															<div class="inner_rating" title="<?=$message?>">
																<?for($i=1;$i<=5;$i++):?>
																	<div class="item-rating <?=$i<=$arItem['PROPERTIES']['EXTENDED_REVIEWS_RAITING']['VALUE'] ? 'filed' : ''?>"><?=CMax::showIconSvg("star", SITE_TEMPLATE_PATH."/images/svg/catalog/star_small.svg");?></div>
																<?endfor;?>
															</div>
														</div>
													</div>
													<?if($arItem['PROPERTIES']['EXTENDED_REVIEWS_COUNT']['VALUE']):?>
														<span class="font_sxs"><?=$arItem['PROPERTIES']['EXTENDED_REVIEWS_COUNT']['VALUE']?></span>
													<?endif;?>
												</div>
											<?else:?>
												<?$APPLICATION->IncludeComponent(
												   "bitrix:iblock.vote",
												   "element_rating_front",
												   Array(
													  "IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
													  "IBLOCK_ID" => $arItem["IBLOCK_ID"],
													  "ELEMENT_ID" =>$arItem["ID"],
													  "MAX_VOTE" => 5,
													  "VOTE_NAMES" => array(),
													  "CACHE_TYPE" => $arParams["CACHE_TYPE"],
													  "CACHE_TIME" => $arParams["CACHE_TIME"],
													  "DISPLAY_AS_RATING" => 'vote_avg'
												   ),
												   $component, array("HIDE_ICONS" =>"Y")
												);?>
											<?endif;?>
										<?$frame->end();?>
									</div>
								<?endif;?>
								<?=$arQuantityData["HTML"];?>
								<?if(isset($arQuantityDataCMP) && $arQuantityDataCMP && $arItem['OFFERS'] && !empty($arItem['OFFERS_PROP'])):?>
									<?=$arQuantityDataCMP["HTML"];?>
								<?endif;?>
								<div class="article_block muted font_sxs" <?if(isset($arItem['ARTICLE']) && $arItem['ARTICLE']['VALUE']):?>data-name="<?=$arItem['ARTICLE']['NAME'];?>" data-value="<?=$arItem['ARTICLE']['VALUE'];?>"<?endif;?>>
									<?if(isset($arItem['ARTICLE']) && $arItem['ARTICLE']['VALUE']){?>
										<?=Loc::getMessage('T_ARTICLE_COMPACT');?>: <?=$arItem['ARTICLE']['VALUE'];?>
									<?}?>
								</div>
							</div>
							<?if ($arItem["PREVIEW_TEXT"] && $bNormalView):?> <div class="preview_text muted777 font_xs"><?=$arItem["PREVIEW_TEXT"]?></div> <?endif;?>
							<?$boolShowOfferProps = ($arItem['OFFERS_PROPS_DISPLAY']);
							$boolShowProductProps = (isset($arItem['DISPLAY_PROPERTIES']) && !empty($arItem['DISPLAY_PROPERTIES']));?>
							<?if($boolShowProductProps || $boolShowOfferProps):?>
								<?if($bNormalView):?>
									<div class="props_list_wrapp">
										<table class="props_list prod">
											<?if ($boolShowProductProps){
												foreach( $arItem["DISPLAY_PROPERTIES"] as $arProp ){?>
													<?if( !empty( $arProp["VALUE"] ) ){?>
														<tr>
															<td>
																<span class="char_name">
																	<?=$arProp["NAME"]?>
																	<?if($arProp["HINT"] && $arParams["SHOW_HINTS"]=="Y"):?>
																		<span class="hint"><span class="icon colored_theme_hover_bg"><i>?</i></span><span class="tooltip"><?=$arProp["HINT"]?></span></span>
																	<?endif;?>
																</span>
															</td>
															<td>
																<span>
																<?
																if(is_array($arProp["DISPLAY_VALUE"])) { foreach($arProp["DISPLAY_VALUE"] as $key => $value) { if ($arProp["DISPLAY_VALUE"][$key+1]) {echo $value.", ";} else {echo $value;} }}
																else { echo $arProp["DISPLAY_VALUE"]; }
																?>
																</span>
															</td>
														</tr>
													<?}?>
												<?}
											}?>
										</table>
										<?if($boolShowOfferProps){?>
											<table class="props_list offers js-container" id="<? echo $arItemIDs["ALL_ITEM_IDS"]['DISPLAY_PROP_DIV']; ?>">
												<?if($arItem["OFFERS"][$arItem["OFFERS_SELECTED"]]['DISPLAY_PROPERTIES']):?>
													<?foreach($arItem["OFFERS"][$arItem["OFFERS_SELECTED"]]['DISPLAY_PROPERTIES'] as $arProp):?>
														<tr>
															<td><span><?=$arProp['NAME']?></span></td>
															<td>
																<span><?
																if(is_array($arProp["DISPLAY_VALUE"])) { foreach($arProp["DISPLAY_VALUE"] as $key => $value) { if ($arProp["DISPLAY_VALUE"][$key+1]) {echo $value.", ";} else {echo $value;} }}
																else { echo $arProp["DISPLAY_VALUE"]; }
																?>
																</span>
															</td>
														</tr>
													<?endforeach;?>
												<?endif;?>
											</table>
										<?}?>
									</div>
									<div class="show_props">
										<span class="darken font_xs colored_theme_hover_text char_title"><?=CMax::showIconSvg("down", SITE_TEMPLATE_PATH.'/images/svg/arrow_catalogcloser.svg', '', '', true, false);?><span class=""><?=Loc::getMessage('PROPERTIES')?></span></span>
									</div>
								<?else:?>
									<div class="properties list">
										<div class="properties__container properties">
											<?foreach($arItem['DISPLAY_PROPERTIES'] as $arProp):?>
												<div class="properties__item properties__item--compact font_xs">
													<div class="properties__title muted properties__item--inline"><?=$arProp['NAME']?></div>
													<div class="properties__hr muted properties__item--inline">&mdash;</div>
													<div class="properties__value darken properties__item--inline">
														<?
														if(is_array($arProp["DISPLAY_VALUE"])) { foreach($arProp["DISPLAY_VALUE"] as $key => $value) { if ($arProp["DISPLAY_VALUE"][$key+1]) {echo $value.", ";} else {echo $value;} }}
														else { echo $arProp["DISPLAY_VALUE"]; }
														?>
													</div>
												</div>
											<?endforeach;?>
										</div>
										<div class="properties__container properties__container_js">
											<?if($arItem["OFFERS"][$arItem["OFFERS_SELECTED"]]['DISPLAY_PROPERTIES']):?>
												<?foreach($arItem["OFFERS"][$arItem["OFFERS_SELECTED"]]['DISPLAY_PROPERTIES'] as $arProp):?>
													<div class="properties__item properties__item--compact font_xs">
														<div class="properties__title muted properties__item--inline"><?=$arProp['NAME']?></div>
														<div class="properties__hr muted properties__item--inline">&mdash;</div>
														<div class="properties__value darken properties__item--inline">
															<?
																if(is_array($arProp["DISPLAY_VALUE"])) { foreach($arProp["DISPLAY_VALUE"] as $key => $value) { if ($arProp["DISPLAY_VALUE"][$key+1]) {echo $value.", ";} else {echo $value;} }}
																else { echo $arProp["DISPLAY_VALUE"]; }
																?>
														</div>
													</div>
												<?endforeach;?>
											<?endif;?>
										</div>
									</div>
								<?endif;?>
							<?endif;?>
							<?if($arItem["PREVIEW_TEXT"] && !$bNormalView):?>
								<div class="preview_text muted777 font_xs"><?=$arItem["PREVIEW_TEXT"]?></div>
								<div class="show_props">
									<span class="darken font_xs colored_theme_hover_text char_title"><?=CMax::showIconSvg("down", SITE_TEMPLATE_PATH.'/images/svg/arrow_catalogcloser.svg', '', '', true, false);?><span class=""><?=Loc::getMessage('PREVIEW_TEXT_MORE')?></span></span>
								</div>
							<?endif;?>
						</div>
						<?if($bNormalView):?>
							<?\Aspro\Functions\CAsproMaxItem::showDelayCompareBtn(array_merge($arParams, $addParams), $arItem, $arAddToBasketData, $totalCount, $bUseSkuProps, 'list', false, false, '_small', $currentSKUID, $currentSKUIBlock);?>
						<?else:?>
							<?\Aspro\Functions\CAsproMaxItem::showDelayCompareBtn(array_merge($arParams, $addParams), $arItem, $arAddToBasketData, $totalCount, $bUseSkuProps, 'block', ($arParams['USE_FAST_VIEW'] != 'N'), false, '_small', $currentSKUID, $currentSKUIBlock);?>
						<?endif;?>
					</div>

					<?//price block?>
					<div class="information_wrapp main_item_wrapper">
						<div class="information <?=($arItem["OFFERS"] && $arItem['OFFERS_PROP'] ? 'has_offer_prop' : '');?>  inner_content js_offers__<?=$arItem['ID'];?>_<?=$arParams["FILTER_HIT_PROP"]?>">
							<?if($arParams["SHOW_DISCOUNT_TIME"]=="Y" && $arParams['SHOW_COUNTER_LIST'] != 'N'){?>
								<?$arDiscount=[]?>
								<?$min_price_id=0;
								if($arItem["OFFERS"])
								{
									if($arCurrentSKU && isset($arCurrentSKU['PRICE_MATRIX']) && $arCurrentSKU['PRICE_MATRIX']) // USE_PRICE_COUNT
									{
										if(isset($arCurrentSKU['PRICE_MATRIX']['MATRIX']) && is_array($arCurrentSKU['PRICE_MATRIX']['MATRIX']))
										{
											$arMatrixKey = array_keys($arCurrentSKU['PRICE_MATRIX']['MATRIX']);
											$min_price_id=current($arMatrixKey);
										}
									}
								}
								else
								{
									if(isset($arItem['PRICE_MATRIX']) && $arItem['PRICE_MATRIX']) // USE_PRICE_COUNT
									{
										$arMatrixKey = array_keys($arItem['PRICE_MATRIX']['MATRIX']);
										$min_price_id=current($arMatrixKey);
									}
								}?>
								<?\Aspro\Functions\CAsproMax::showDiscountCounter($totalCount, $arDiscount, $arQuantityData, $arItem, $strMeasure, 'v2 grey', $item_id);?>
							<?}?>

							<div class="cost prices clearfix">
								<?if($arItem["OFFERS"]):?>
									<div class="ce_cmp_hidden">
										<?=\Aspro\Functions\CAsproMaxItem::showItemPricesDefault($arParams);?>
										<div class="js_price_wrapper">
											<?if($arCurrentSKU):?>
												<?$item_id = $arCurrentSKU["ID"];
												$arCurrentSKU['PRICE_MATRIX'] = $arCurrentSKU['PRICE_MATRIX_RAW'];
												$arCurrentSKU['CATALOG_MEASURE_NAME'] = $arCurrentSKU['MEASURE'];
												if(isset($arCurrentSKU['PRICE_MATRIX']) && $arCurrentSKU['PRICE_MATRIX']): // USE_PRICE_COUNT?>
													<?if ($arParams['USE_PRICE_COUNT'] != 'Y'):?>
														<?$arParams['HIDE_PRICE'] = true?>
														<?\Aspro\Functions\CAsproMaxItem::showItemPrices($arParams, $arCurrentSKU["PRICES"], $strMeasure, $min_price_id, ($arParams["SHOW_DISCOUNT_PERCENT_NUMBER"] == "Y" ? "N" : "Y"));?>
													<?endif;?>
													<?if($arCurrentSKU['ITEM_PRICE_MODE'] == 'Q' && count($arCurrentSKU['PRICE_MATRIX']['ROWS']) > 1):?>
														<?=CMax::showPriceRangeTop($arCurrentSKU, $arParams, Loc::getMessage("CATALOG_ECONOMY"));?>
													<?endif;?>
													<?if ($arParams['USE_PRICE_COUNT'] == 'Y'):?>
														<?=CMax::showPriceMatrix($arCurrentSKU, $arParams, $strMeasure, $arAddToBasketData);?>
													<?endif;?>
												<?else:?>
													<?\Aspro\Functions\CAsproMaxItem::showItemPrices($arParams, $arCurrentSKU["PRICES"], $strMeasure, $min_price_id, ($arParams["SHOW_DISCOUNT_PERCENT_NUMBER"] == "Y" ? "N" : "Y"));?>
												<?endif;?>
											<?else:?>
													<?\Aspro\Functions\CAsproMaxSku::showItemPrices($arParams, $arItem, $item_id, $min_price_id, array(), ($arParams["SHOW_DISCOUNT_PERCENT_NUMBER"] == "Y" ? "N" : "Y"));?>
											<?endif;?>
										</div>
									</div>
									<?if($arCurrentSKU):?>
										<div class="ce_cmp_visible">
											<?\Aspro\Functions\CAsproMaxSku::showItemPrices($arParamsCE_CMP, $arItem, $item_id, $min_price_id, $arItemIDs, ($arParams["SHOW_DISCOUNT_PERCENT_NUMBER"] == "Y" ? "N" : "Y"));?>
										</div>
									<?endif;?>
								<?else:?>
									<?if(isset($arItem['PRICE_MATRIX']) && $arItem['PRICE_MATRIX']): // USE_PRICE_COUNT?>
										<?if(\CMax::GetFrontParametrValue('SHOW_POPUP_PRICE') == 'Y' || $arItem['ITEM_PRICE_MODE'] == 'Q' || (\CMax::GetFrontParametrValue('SHOW_POPUP_PRICE') != 'Y' && $arItem['ITEM_PRICE_MODE'] != 'Q' && count($arItem['PRICE_MATRIX']['COLS']) <= 1)):?>
											<?=CMax::showPriceRangeTop($arItem, $arParams, Loc::getMessage("CATALOG_ECONOMY"));?>
										<?endif;?>
										<?
										$min_price = 0;
										foreach ($arItem['PRICE_MATRIX']['MATRIX'] as $key => $value) {
											if($min_price == 0){
												$min_price = $value["ZERO-INF"]["DISCOUNT_PRICE"];
											}else{
												if($min_price > $value["ZERO-INF"]["DISCOUNT_PRICE"]){
													$min_price = $value["ZERO-INF"]["DISCOUNT_PRICE"];
												}
											}
										}
										?>
										<?if(count($arItem['PRICE_MATRIX']['ROWS']) > 1 || count($arItem['PRICE_MATRIX']['COLS']) > 1):?>
											<?=showPriceMatrixCustom($arItem, $arParams, $strMeasure,$min_price, $arAddToBasketData);?>
										<?endif;?>
									<?elseif(isset($arResult["PRICES"])):?>
										<?\Aspro\Functions\CAsproMaxItem::showItemPrices(array_merge($arParams, $addParams), $arItem["PRICES"], $strMeasure, $min_price_id, ($arParams["SHOW_DISCOUNT_PERCENT_NUMBER"] == "Y" ? "N" : "Y"));?>
									<?endif;?>
								<?endif;?>
							</div>

							<?if($arItem["OFFERS"]){?>
								<?if(!empty($arItem['OFFERS_PROP'])){?>
									<div class="sku_props list ce_cmp_hidden">
										<div class="bx_catalog_item_scu wrapper_sku" id="<? echo $arItemIDs["ALL_ITEM_IDS"]['PROP_DIV']; ?>" data-site_id="<?=SITE_ID;?>" data-id="<?=$arItem["ID"];?>" data-offer_id="<?=$arItem["OFFERS"][$arItem["OFFERS_SELECTED"]]["ID"];?>" data-propertyid="<?=$arItem["OFFERS"][$arItem["OFFERS_SELECTED"]]["PROPERTIES"]["CML2_LINK"]["ID"];?>" data-offer_iblockid="<?=$arItem["OFFERS"][$arItem["OFFERS_SELECTED"]]["IBLOCK_ID"];?>" data-iblockid="<?=$arItem["IBLOCK_ID"];?>">
											<?$arSkuTemplate = array();?>
											<?$arSkuTemplate=CMax::GetSKUPropsArray($arItem['OFFERS_PROPS_JS'], $arResult["SKU_IBLOCK_ID"], $arParams["DISPLAY_TYPE"], $arParams["OFFER_HIDE_NAME_PROPS"], "N", $arItem, $arParams['OFFER_SHOW_PREVIEW_PICTURE_PROPS'], $arParams['MAX_SCU_COUNT_VIEW']);?>
											<?foreach ($arSkuTemplate as $code => $strTemplate){
												if (!isset($arItem['OFFERS_PROP'][$code]))
													continue;
												echo '<div class="item_wrapper">', str_replace('#ITEM#_prop_', $arItemIDs["ALL_ITEM_IDS"]['PROP'], $strTemplate), '</div>';
											}?>
										</div>
										<?$arItemJSParams=CMax::GetSKUJSParams($arResult, $arParams, $arItem);?>
									</div>
								<?}?>
							<?}?>

							<div class="basket_props_block" id="bx_basket_div_<?=$arItem["ID"];?>" style="display: none;">
								<?if (!empty($arItem['PRODUCT_PROPERTIES_FILL'])){
									foreach ($arItem['PRODUCT_PROPERTIES_FILL'] as $propID => $propInfo){?>
										<input type="hidden" name="<? echo $arParams['PRODUCT_PROPS_VARIABLE']; ?>[<? echo $propID; ?>]" value="<? echo htmlspecialcharsbx($propInfo['ID']); ?>">
									<?}
								}
								if (!$emptyProductProperties){?>
									<div class="wrapper">
										<table>
											<?foreach ($arItem['PRODUCT_PROPERTIES'] as $propID => $propInfo){?>
												<tr>
													<td><? echo $arItem['PROPERTIES'][$propID]['NAME']; ?></td>
													<td>
														<?if('L' == $arItem['PROPERTIES'][$propID]['PROPERTY_TYPE']	&& 'C' == $arItem['PROPERTIES'][$propID]['LIST_TYPE']){
															foreach($propInfo['VALUES'] as $valueID => $value){?>
																<label>
																	<input type="radio" name="<? echo $arParams['PRODUCT_PROPS_VARIABLE']; ?>[<? echo $propID; ?>]" value="<? echo $valueID; ?>" <? echo ($valueID == $propInfo['SELECTED'] ? '"checked"' : ''); ?>><? echo $value; ?>
																</label>
															<?}
														}else{?>
															<select name="<? echo $arParams['PRODUCT_PROPS_VARIABLE']; ?>[<? echo $propID; ?>]"><?
																foreach($propInfo['VALUES'] as $valueID => $value){?>
																	<option value="<? echo $valueID; ?>" <? echo ($valueID == $propInfo['SELECTED'] ? '"selected"' : ''); ?>><? echo $value; ?></option>
																<?}?>
															</select>
														<?}?>
													</td>
												</tr>
											<?}?>
										</table>
									</div>
									<?
								}?>
							</div>

							<?if(!$arItem["OFFERS"] || $arParams['TYPE_SKU'] !== 'TYPE_1'):?>
								<div class="counter_wrapp <?=($arItem["OFFERS"] && $arParams["TYPE_SKU"] == "TYPE_1" ? 'woffers' : '')?> list clearfix">
									<?if(($arAddToBasketData["OPTIONS"]["USE_PRODUCT_QUANTITY_LIST"] && $arAddToBasketData["ACTION"] == "ADD") && $arAddToBasketData["CAN_BUY"]):?>
										<?=\Aspro\Functions\CAsproMax::showItemCounter($arAddToBasketData, $arItem["ID"], $arItemIDs, $arParams, '', '', true);?>
									<?endif;?>
									<div id="<?=$arItemIDs["ALL_ITEM_IDS"]['BASKET_ACTIONS']; ?>" class="button_block <?=(($arAddToBasketData["ACTION"] == "ORDER"/*&& !$arItem["CAN_BUY"]*/) || !$arAddToBasketData["CAN_BUY"] || !$arAddToBasketData["OPTIONS"]["USE_PRODUCT_QUANTITY_LIST"] || $arAddToBasketData["ACTION"] == "SUBSCRIBE" ? "wide" : "");?>">
										<!--noindex-->
											<?=$arAddToBasketData["HTML"]?>
										<!--/noindex-->
									</div>
								</div>
								<?=\Aspro\Functions\CAsproMax::showItemOCB($arAddToBasketData, $arItem, $arParams);?>
								<?
								if(isset($arItem['PRICE_MATRIX']) && $arItem['PRICE_MATRIX']) // USE_PRICE_COUNT
								{?>
									<?if($arItem['ITEM_PRICE_MODE'] == 'Q' && count($arItem['PRICE_MATRIX']['ROWS']) > 1):?>
										<?$arOnlyItemJSParams = array(
											"ITEM_PRICES" => $arItem["ITEM_PRICES"],
											"ITEM_PRICE_MODE" => $arItem["ITEM_PRICE_MODE"],
											"ITEM_QUANTITY_RANGES" => $arItem["ITEM_QUANTITY_RANGES"],
											"MIN_QUANTITY_BUY" => $arAddToBasketData["MIN_QUANTITY_BUY"],
											"SHOW_DISCOUNT_PERCENT_NUMBER" => $arParams["SHOW_DISCOUNT_PERCENT_NUMBER"],
											"ID" => $arItemIDs["strMainID"],
										)?>
										<script type="text/javascript">
											var <? echo $arItemIDs["strObName"]; ?>el = new JCCatalogSectionOnlyElement(<? echo CUtil::PhpToJSObject($arOnlyItemJSParams, false, true); ?>);
										</script>
									<?endif;?>
								<?}?>
							<?elseif($arItem["OFFERS"]):?>
								<?if(empty($arItem['OFFERS_PROP'])){?>
									<div class="offer_buy_block buys_wrapp woffers">
										<div class="counter_wrapp list clearfix">
										<?
										$arItem["OFFERS_MORE"] = "Y";
										$arAddToBasketData = CMax::GetAddToBasketArray($arItem, $totalCount, $arParams["DEFAULT_COUNT"], $arParams["BASKET_URL"], false, $arItemIDs["ALL_ITEM_IDS"], 'small read_more1', $arParams);?>
										<!--noindex-->
											<?=$arAddToBasketData["HTML"]?>
										<!--/noindex-->
										</div>
									</div>
								<?}else{?>
									<div class="offer_buy_block">
										<div class="counter_wrapp list clearfix ce_cmp_hidden">
											<?if(($arAddToBasketData["OPTIONS"]["USE_PRODUCT_QUANTITY_LIST"] && $arAddToBasketData["ACTION"] == "ADD") && $arAddToBasketData["CAN_BUY"]):?>
												<?=\Aspro\Functions\CAsproMax::showItemCounter($arAddToBasketData, $arItem["OFFERS"][$arItem["OFFERS_SELECTED"]]["ID"], $arItemIDs, $arParams, '', '', true);?>
											<?endif;?>
											<div id="<?=$arItemIDs["ALL_ITEM_IDS"]['BASKET_ACTIONS']; ?>" class="button_block <?=(($arAddToBasketData["ACTION"] == "ORDER"/*&& !$arItem["CAN_BUY"]*/)  || !$arAddToBasketData["CAN_BUY"] || !$arAddToBasketData["OPTIONS"]["USE_PRODUCT_QUANTITY_LIST"] || $arAddToBasketData["ACTION"] == "SUBSCRIBE" ? "wide" : "");?>">
												<!--noindex-->
													<?=$arAddToBasketData["HTML"]?>
												<!--/noindex-->
											</div>
										</div>
										<div class="counter_wrapp list clearfix ce_cmp_visible">
											<div id="<?=$arItemIDs["ALL_ITEM_IDS"]['BASKET_ACTIONS']; ?>" class="button_block wide">
												<a class="btn btn-default basket read_more" rel="nofollow" data-item="<?=$arItem['ID']?>" href="<?=$arItem['DETAIL_PAGE_URL']?>"><?=Loc::getMessage('CATALOG_READ_MORE')?></a>
											</div>
										</div>
									</div>
									<?=\Aspro\Functions\CAsproMax::showItemOCB($arAddToBasketData, $arItem["OFFERS"][$arItem["OFFERS_SELECTED"]], $arParams);?>
									<?
									if(isset($arCurrentSKU['PRICE_MATRIX']) && $arCurrentSKU['PRICE_MATRIX']) // USE_PRICE_COUNT
									{?>
										<?if($arCurrentSKU['ITEM_PRICE_MODE'] == 'Q' && count($arCurrentSKU['PRICE_MATRIX']['ROWS']) > 1):?>
											<?$arOnlyItemJSParams = array(
												"ITEM_PRICES" => $arCurrentSKU["ITEM_PRICES"],
												"ITEM_PRICE_MODE" => $arCurrentSKU["ITEM_PRICE_MODE"],
												"ITEM_QUANTITY_RANGES" => $arCurrentSKU["ITEM_QUANTITY_RANGES"],
												"MIN_QUANTITY_BUY" => $arAddToBasketData["MIN_QUANTITY_BUY"],
												"SHOW_DISCOUNT_PERCENT_NUMBER" => $arParams["SHOW_DISCOUNT_PERCENT_NUMBER"],
												"ID" => $arItemIDs["strMainID"],
												"NOT_SHOW" => "Y",
											)?>
											<script type="text/javascript">
												var <? echo $arItemIDs["strObName"]; ?>el = new JCCatalogSectionOnlyElement(<? echo CUtil::PhpToJSObject($arOnlyItemJSParams, false, true); ?>);
											</script>
										<?endif;?>
									<?}?>
								<?}?>
							<?endif;?>
						</div>
					</div>
				</div>
			</div>
		<?}?>
	<?if($arParams["AJAX_REQUEST"]=="N"){?>
		</div>
	<?}?>
	<?if($arParams["AJAX_REQUEST"]=="Y"){?>
		<div class="wrap_nav bottom_nav_wrapper">
	<?}?>

	<?$showAllCount = false;?>
	<?if($arParams['IS_CATALOG_PAGE'] == 'Y' && $arParams['SECTION_COUNT_ELEMENTS'] == 'Y'):?>
		<?if((int)$arResult['NAV_RESULT']->NavRecordCount > 0):?>
			<?$this->SetViewTarget("more_text_title");?>
				<span class="element-count-wrapper"><span class="element-count muted font_xs rounded3"><?=$arResult['NAV_RESULT']->NavRecordCount;?></span></span>
			<?$this->EndViewTarget();?>
			<?
			$showAllCount = true;
			$allCount = $arResult['NAV_RESULT']->NavRecordCount;
			?>
		<?endif;?>
	<?endif;?>

	<div class="bottom_nav <?=$arParams["DISPLAY_TYPE"];?>" <?=($showAllCount ? 'data-all_count="'.$allCount.'"' : '')?> <?=($arParams["AJAX_REQUEST"]=="Y" ? "style='display: none; '" : "");?>>
		<?if( $arParams["DISPLAY_BOTTOM_PAGER"] == "Y" ){?><?=$arResult["NAV_STRING"]?><?}?>
	</div>
	<?if($arParams["AJAX_REQUEST"]=="Y"){?>
		</div>
	<?}?>

	

<?}else{?>
	<div class="no_goods">
		<div class="no_products">
			<div class="wrap_text_empty">
				<?if($_REQUEST["set_filter"]){?>
					<?$APPLICATION->IncludeFile(SITE_DIR."include/section_no_products_filter.php", Array(), Array("MODE" => "html",  "NAME" => GetMessage('EMPTY_CATALOG_DESCR')));?>
				<?}else{?>
					<?$APPLICATION->IncludeFile(SITE_DIR."include/section_no_products.php", Array(), Array("MODE" => "html",  "NAME" => GetMessage('EMPTY_CATALOG_DESCR')));?>
				<?}?>
			</div>
		</div>
		<?if($_REQUEST["set_filter"]){?>
			<span class="button wide btn btn-default"><?=GetMessage('RESET_FILTERS');?></span>
		<?}?>
	</div>
<?}?>
<script>
	BX.message({
		QUANTITY_AVAILIABLE: '<? echo COption::GetOptionString("aspro.max", "EXPRESSION_FOR_EXISTS", GetMessage("EXPRESSION_FOR_EXISTS_DEFAULT"), SITE_ID); ?>',
		QUANTITY_NOT_AVAILIABLE: '<? echo COption::GetOptionString("aspro.max", "EXPRESSION_FOR_NOTEXISTS", GetMessage("EXPRESSION_FOR_NOTEXISTS"), SITE_ID); ?>',
		ADD_ERROR_BASKET: '<? echo GetMessage("ADD_ERROR_BASKET"); ?>',
		ADD_ERROR_COMPARE: '<? echo GetMessage("ADD_ERROR_COMPARE"); ?>',
	})
</script>
