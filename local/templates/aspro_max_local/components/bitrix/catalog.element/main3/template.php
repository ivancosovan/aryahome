<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
use \Bitrix\Main\Localization\Loc;
$this->setFrameMode(true);
$this->addExternalJS("/local/templates/aspro_max_local/components/bitrix/catalog.element/main3/card.js");

$haveOffers = !empty($arResult['OFFERS']);
if ($haveOffers) {
	$actualItem = isset($arResult['OFFERS'][$arResult['OFFERS_SELECTED']])
		? $arResult['OFFERS'][$arResult['OFFERS_SELECTED']]
		: reset($arResult['OFFERS']);
	// $showSliderControls = false;

	// foreach ($arResult['OFFERS'] as $offer) {
	// 	if ($offer['MORE_PHOTO_COUNT'] > 1) {
	// 		$showSliderControls = true;
	// 		break;
	// 	}
	// }
} else {
	$actualItem = $arResult;
	//$showSliderControls = $arResult['MORE_PHOTO_COUNT'] > 1;
}
$price = $actualItem['ITEM_PRICES'][$actualItem['ITEM_PRICE_SELECTED']];

$name = !empty($arResult['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE'])
	? $arResult['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE']
	: $arResult['NAME'];

$namecode = !empty($arResult['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE'])
	? 'NAIMENOVANIE_DLYA_SAYTA'
	: 'NAME';

//Размер
// $array = array(
// 	'1,5-спальное',
// 	'Евро',
// 	'Евро (2-спальное)',
// 	'Семейное',
// 	'Семейное (2-пододеяльника)'
// );


$RAZMER = $arResult["PROPERTIES"]['OBSHCHIY_RAZMER_DLYA_SAYTA']['VALUE'];
	$RAZMERCODE = "OBSHCHIY_RAZMER_DLYA_SAYTA";
if(!$RAZMER){
	$RAZMER = $arResult["PROPERTIES"]['RAZMER']['VALUE'];
	$RAZMERCODE = "RAZMER";
}
?>
<main style="display: none"></main>
<div class="basket_props_block" id="bx_basket_div_<?=$arResult["ID"];?>" style="display: none;">
	<?if (!empty($arResult['PRODUCT_PROPERTIES_FILL'])){
		foreach ($arResult['PRODUCT_PROPERTIES_FILL'] as $propID => $propInfo){?>
			<input type="hidden" name="<? echo $arParams['PRODUCT_PROPS_VARIABLE']; ?>[<? echo $propID; ?>]" value="<? echo htmlspecialcharsbx($propInfo['ID']); ?>">
			<?if (isset($arResult['PRODUCT_PROPERTIES'][$propID]))
				unset($arResult['PRODUCT_PROPERTIES'][$propID]);
		}
	}
	$arResult["EMPTY_PROPS_JS"]="Y";
	$emptyProductProperties = empty($arResult['PRODUCT_PROPERTIES']);
	if (!$emptyProductProperties){
		$arResult["EMPTY_PROPS_JS"]="N";?>
		<div class="wrapper">
			<table>
				<?foreach ($arResult['PRODUCT_PROPERTIES'] as $propID => $propInfo){?>
					<tr>
						<td><? echo $arResult['PROPERTIES'][$propID]['NAME']; ?></td>
						<td>
							<?if('L' == $arResult['PROPERTIES'][$propID]['PROPERTY_TYPE'] && 'C' == $arResult['PROPERTIES'][$propID]['LIST_TYPE']){
								foreach($propInfo['VALUES'] as $valueID => $value){?>
									<label>
										<input type="radio" name="<? echo $arParams['PRODUCT_PROPS_VARIABLE']; ?>[<? echo $propID; ?>]" value="<? echo $valueID; ?>" <? echo ($valueID == $propInfo['SELECTED'] ? '"checked"' : ''); ?>><? echo $value; ?>
									</label>
								<?}
							}else{?>
								<select name="<? echo $arParams['PRODUCT_PROPS_VARIABLE']; ?>[<? echo $propID; ?>]">
									<?foreach($propInfo['VALUES'] as $valueID => $value){?>
										<option value="<? echo $valueID; ?>" <? echo ($valueID == $propInfo['SELECTED'] ? '"selected"' : ''); ?>><? echo $value; ?></option>
									<?}?>
								</select>
							<?}?>
						</td>
					</tr>
				<?}?>
			</table>
		</div>
	<?}?>
</div>

<?if ($arResult['SKU_CONFIG']):?><div class="js-sku-config" data-params='<?=str_replace('\'', '"', CUtil::PhpToJSObject($arResult['SKU_CONFIG'], false))?>'></div><?endif;?>

<?
$currencyList = '';
if (!empty($arResult['CURRENCIES']))
{
	$templateLibrary[] = 'currency';
	$currencyList = CUtil::PhpToJSObject($arResult['CURRENCIES'], false, true, true);
}

$bComplect = $arResult["PROPERTIES"]["PRODUCT_SET"]["VALUE"] === "Y";
$addParams = array();
if($bComplect){
	$addParams = array("DISPLAY_WISH_BUTTONS" => "N");
}

$templateData = array(
	'TEMPLATE_LIBRARY' => $templateLibrary,
	'CURRENCIES' => $currencyList,
	'STORES' => array(
		"USE_STORE_PHONE" => $arParams["USE_STORE_PHONE"],
		"SCHEDULE" => $arParams["SCHEDULE"],
		"USE_MIN_AMOUNT" => $arParams["USE_MIN_AMOUNT"],
		"MIN_AMOUNT" => $arParams["MIN_AMOUNT"],
		"ELEMENT_ID" => $arResult["ID"],
		"STORE_PATH"  =>  $arParams["STORE_PATH"],
		"MAIN_TITLE"  =>  $arParams["MAIN_TITLE"],
		"MAX_AMOUNT"=>$arParams["MAX_AMOUNT"],
		"USE_ONLY_MAX_AMOUNT" => $arParams["USE_ONLY_MAX_AMOUNT"],
		"SHOW_EMPTY_STORE" => $arParams['SHOW_EMPTY_STORE'],
		"SHOW_GENERAL_STORE_INFORMATION" => $arParams['SHOW_GENERAL_STORE_INFORMATION'],
		"USE_ONLY_MAX_AMOUNT" => $arParams["USE_ONLY_MAX_AMOUNT"],
		"USER_FIELDS" => $arParams['USER_FIELDS'],
		"FIELDS" => $arParams['FIELDS'],
		"STORES_FILTER_ORDER" => $arParams['STORES_FILTER_ORDER'],
		"STORES_FILTER" => $arParams['STORES_FILTER'],
		"STORES" => $arParams['STORES'] = array_diff($arParams['STORES'], array('')),
		"SET_ITEMS" => $arResult["SET_ITEMS"],
		'OFFERS_ID' => is_array($arResult['OFFERS']) ? array_column($arResult['OFFERS'], 'ID') : [],
	),
	'OFFERS_INFO' => array(
		'OFFERS' => is_array($arResult['OFFERS']) ? array_column($arResult['OFFERS'], 'OFFER_GROUP', 'ID') : [],
		'OFFER_GROUP' => $arResult['OFFER_GROUP'],
		'OFFERS_IBLOCK' => $arResult['OFFERS_IBLOCK'],
	),
	'LINK_SALES' => $arResult['STOCK'],
	'LINK_SERVICES' => $arResult['SERVICES'],
	'LINK_NEWS' => $arResult['NEWS'],
	'LINK_TIZERS' => $arParams['SECTION_TIZERS'],
	'LINK_REVIEWS' => $arResult['LINK_REVIEWS'],
	'LINK_BLOG' => $arResult['BLOG'],
	'LINK_STAFF' => $arResult['LINK_STAFF'],
	'LINK_VACANCY' => $arResult['LINK_VACANCY'],
	'CATALOG_SETS' => array(
		'SET_ITEMS_QUANTITY' => $arResult["SET_ITEMS_QUANTITY"],
		'SET_ITEMS' => $arResult["SET_ITEMS"]
	),
	'VIDEO' => $arResult['VIDEO'],
	'ASSOCIATED' => $arResult['ASSOCIATED'],
	'EXPANDABLES' => $arResult['EXPANDABLES'],
	'REVIEWS_COUNT' => $arResult['PROPERTIES']['BLOG_COMMENTS_CNT']['VALUE'],
	'PRODUCT_SET_OPTIONS' => array(
		'PRODUCT_SET' => $bComplect,
		'PRODUCT_SET_FILTER' => $arResult["PROPERTIES"]["PRODUCT_SET_FILTER"]["~VALUE"],
		'PRODUCT_SET_GROUP' => $arResult["PROPERTIES"]["PRODUCT_SET_GROUP"]["VALUE"] === "Y",
	),
);
unset($currencyList, $templateLibrary);

if($arResult["PROPERTIES"]["YM_ELEMENT_ID"] && $arResult["PROPERTIES"]["YM_ELEMENT_ID"]["VALUE"])
	$templateData["YM_ELEMENT_ID"] = $arResult["PROPERTIES"]["YM_ELEMENT_ID"]["VALUE"];

$arSkuTemplate = array();
if(!empty($arResult['SKU_PROPS']))
	$arSkuTemplate=CMax::GetSKUPropsArray($arResult['SKU_PROPS'], $arResult["SKU_IBLOCK_ID"], "list", $arParams["OFFER_HIDE_NAME_PROPS"], "N", $arResult, $arParams['OFFER_SHOW_PREVIEW_PICTURE_PROPS']);
	//$arSkuTemplate=CMax::GetSKUPropsArray($arResult['SKU_PROPS'], $arResult["SKU_IBLOCK_ID"], "list", $arParams["OFFER_HIDE_NAME_PROPS"]);

$strMainID = $this->GetEditAreaId($arResult['ID']);
$strObName = 'ob'.preg_replace("/[^a-zA-Z0-9_]/", "x", $strMainID);

$arResult["strMainID"] = $this->GetEditAreaId($arResult['ID']);
$arItemIDs=CMax::GetItemsIDs($arResult, "Y");

$showCustomOffer=(($arResult['OFFERS'] && $arParams["TYPE_SKU"] !="N") ? true : false);

if( $showCustomOffer && isset($arResult['OFFERS'][$arResult['OFFERS_SELECTED']]) ){
	$arCurrentSKU = $arResult['OFFERS'][$arResult['OFFERS_SELECTED']];
	$templateData['TOTAL_COUNT'] = $totalCount = CMax::GetTotalCount($arCurrentSKU, $arParams);
	$templateData['QUANTITY_DATA'] = $arQuantityData = CMax::GetQuantityArray($totalCount, array('ID' => $arCurrentSKU["ID"]), ($arParams["USE_STORE"] == "Y" && $arResult["STORES_COUNT"] && $arResult['CATALOG_TYPE'] != CCatalogProduct::TYPE_SET ? "Y" : "N"));
} else {
	$templateData['TOTAL_COUNT'] = $totalCount = CMax::GetTotalCount($arResult, $arParams);
	$templateData['QUANTITY_DATA'] = $arQuantityData = CMax::GetQuantityArray($totalCount, $arItemIDs["ALL_ITEM_IDS"], ($arParams["USE_STORE"] == "Y" && $arResult["STORES_COUNT"] && $arResult['CATALOG_TYPE'] != CCatalogProduct::TYPE_SET && (!$arResult["OFFERS"] || ($arResult["OFFERS"] && $arParams["TYPE_SKU"]!="N")) ? "Y" : "N"));
}
$templateData['ID_OFFER_GROUP'] = $arItemIDs['ALL_ITEM_IDS']['OFFER_GROUP'];

$arParams["BASKET_ITEMS"]=($arParams["BASKET_ITEMS"] ? $arParams["BASKET_ITEMS"] : array());
$useStores = $arParams["USE_STORE"] == "Y" && $arResult["STORES_COUNT"] && $arQuantityData["RIGHTS"]["SHOW_QUANTITY"] && $arResult['CATALOG_TYPE'] != CCatalogProduct::TYPE_SET;
$templateData['STORES']['USE_STORES'] = $useStores;

if($showCustomOffer)
	$templateData['JS_OBJ'] = $strObName;

$strMeasure='';
$arAddToBasketData = array();

$templateData['STR_ID'] = $strObName;
$item_id = $arResult["ID"];

$currentSKUID = $currentSKUIBlock = '';

$bUseSkuProps = ($arResult["OFFERS"] && !empty($arResult['OFFERS_PROP']));
$popupVideo = $arResult['PROPERTIES']['POPUP_VIDEO']['VALUE'];
$bOfferDetailText = false;
if( $showCustomOffer && isset($arResult['OFFERS'][$arResult['OFFERS_SELECTED']]) ){
	//$arCurrentSKU = $arResult['OFFERS'][$arResult['OFFERS_SELECTED']];
	$item_id = $arCurrentSKU["ID"];
	$bOfferDetailText = $arParams['SHOW_SKU_DESCRIPTION'] === 'Y' && $arCurrentSKU["DETAIL_TEXT"];
	if(strlen($arParams["SKU_DETAIL_ID"]))
		$arResult['DETAIL_PAGE_URL'].= '?'.$arParams["SKU_DETAIL_ID"].'='.$arCurrentSKU['ID'];
	$templateData["OFFERS_INFO"]["CURRENT_OFFER"] = $arCurrentSKU["ID"];
	$templateData["OFFERS_INFO"]["CURRENT_OFFER_TITLE"] = $arCurrentSKU['IPROPERTY_VALUES']["ELEMENT_PAGE_TITLE"] ?? $arCurrentSKU["NAME"];
	$templateData["OFFERS_INFO"]["CURRENT_OFFER_WINDOW_TITLE"] = $arCurrentSKU['IPROPERTY_VALUES']["ELEMENT_META_TITLE"] ?? $templateData["OFFERS_INFO"]["CURRENT_OFFER_TITLE"];
	if ($arCurrentSKU["DISPLAY_PROPERTIES"]["ARTICLE"]["VALUE"]) {
		$arCurrentSKU['DISPLAY_PROPERTIES']['ARTICLE']["VALUE"] = (is_array($arCurrentSKU['DISPLAY_PROPERTIES']['ARTICLE']["VALUE"]) ? reset($arCurrentSKU['DISPLAY_PROPERTIES']['ARTICLE']["VALUE"]) : $arCurrentSKU['DISPLAY_PROPERTIES']['ARTICLE']["VALUE"]);
        $article = $arCurrentSKU['DISPLAY_PROPERTIES']['ARTICLE'];
		unset($arCurrentSKU['DISPLAY_PROPERTIES']['ARTICLE']);
    } elseif($arParams['SHOW_ARTICLE_SKU'] === 'Y') {
		$article = $arResult["CML2_ARTICLE"];
	}
	if($arCurrentSKU['PROPERTIES']['POPUP_VIDEO']['VALUE']){
		$popupVideo = $arCurrentSKU['PROPERTIES']['POPUP_VIDEO']['VALUE'];
	}
	$arResult['OFFER_PROP'] = $arCurrentSKU['DISPLAY_PROPERTIES'];
	CIBlockPriceTools::clearProperties($arResult['OFFER_PROP'], $arParams['OFFER_TREE_PROPS']);
	$arResult['OFFER_PROP'] = CMax::PrepareItemProps($arResult['OFFER_PROP']);
} else {
	$article = $arResult["CML2_ARTICLE"];
}

if($arResult["OFFERS"])
{
	$strMeasure=$arResult["MIN_PRICE"]["CATALOG_MEASURE_NAME"];
	$templateData["STORES"]["OFFERS"]="Y";

	if($showCustomOffer){
		$currentSKUIBlock = $arResult["OFFERS"][$arResult["OFFERS_SELECTED"]]["IBLOCK_ID"];
		$currentSKUID = $arResult["OFFERS"][$arResult["OFFERS_SELECTED"]]["ID"];
		$arResult["OFFERS"][$arResult["OFFERS_SELECTED"]]["IS_OFFER"] = "Y";

		/* need for add basket props */
		$arResult["OFFERS"][$arResult["OFFERS_SELECTED"]]["IBLOCK_ID"] = $arResult['IBLOCK_ID'];
		/* */
		// for current offer buy block
		$arAddToBasketData = \Cosmo\CosmoMax::GetAddToBasketArray($arResult["OFFERS"][$arResult["OFFERS_SELECTED"]], $totalCount, $arParams["DEFAULT_COUNT"], $arParams["BASKET_URL"], false, $arItemIDs["ALL_ITEM_IDS"], 'btn-lg', $arParams);
		/* restore IBLOCK_ID */
		$arResult["OFFERS"][$arResult["OFFERS_SELECTED"]]["IBLOCK_ID"] = $currentSKUIBlock;
		/* */
	}
}
else
{
	if(($arParams["SHOW_MEASURE"]=="Y")&&($arResult["CATALOG_MEASURE"]))
	{
		$arMeasure = CCatalogMeasure::getList(array(), array("ID"=>$arResult["CATALOG_MEASURE"]), false, false, array())->GetNext();
		$strMeasure=$arMeasure["SYMBOL_RUS"];
	}
	$arAddToBasketData = \Cosmo\CosmoMax::GetAddToBasketArray($arResult, $totalCount, $arParams["DEFAULT_COUNT"], $arParams["BASKET_URL"], true, $arItemIDs["ALL_ITEM_IDS"], 'btn-lg', $arParams);
}

$arOfferProps = implode(';', $arParams['OFFERS_CART_PROPERTIES']);

// save item viewed
$arFirstPhoto = reset($arResult['MORE_PHOTO']);
$viwedItem = $arCurrentSKU ?? $arResult;
$arItemPrices = $viwedItem['MIN_PRICE'];
if(isset($viwedItem['PRICE_MATRIX']) && $viwedItem['PRICE_MATRIX'])
{
	$rangSelected = $viwedItem['ITEM_QUANTITY_RANGE_SELECTED'];
	$priceSelected = $viwedItem['ITEM_PRICE_SELECTED'];
	if(isset($viwedItem['FIX_PRICE_MATRIX']) && $viwedItem['FIX_PRICE_MATRIX'])
	{
		$rangSelected = $viwedItem['FIX_PRICE_MATRIX']['RANGE_SELECT'];
		$priceSelected = $viwedItem['FIX_PRICE_MATRIX']['PRICE_SELECT'];
	}
	$arItemPrices = $viwedItem['ITEM_PRICES'][$priceSelected];
	$arItemPrices['VALUE'] = $arItemPrices['BASE_PRICE'];
	$arItemPrices['PRINT_VALUE'] = \Aspro\Functions\CAsproMaxItem::getCurrentPrice('BASE_PRICE', $arItemPrices);
	$arItemPrices['DISCOUNT_VALUE'] = $arItemPrices['PRICE'];
	$arItemPrices['PRINT_DISCOUNT_VALUE'] = \Aspro\Functions\CAsproMaxItem::getCurrentPrice('PRICE', $arItemPrices);
}
$arViewedData = array(
	'PRODUCT_ID' => $arResult['ID'],
	'IBLOCK_ID' => $viwedItem['IBLOCK_ID'],
	'NAME' => $viwedItem['NAME'],
	'DETAIL_PAGE_URL' => $viwedItem['DETAIL_PAGE_URL'],
	'PICTURE_ID' => $viwedItem['PREVIEW_PICTURE'] ? $viwedItem['PREVIEW_PICTURE']['ID'] : ($arFirstPhoto ? $arFirstPhoto['ID'] : false),
	'CATALOG_MEASURE_NAME' => $viwedItem['CATALOG_MEASURE_NAME'],
	'MIN_PRICE' => $arItemPrices,
	'CAN_BUY' => $viwedItem['CAN_BUY'] ? 'Y' : 'N',
	'IS_OFFER' => $arCurrentSKU ? 'Y' : 'N',
	'WITH_OFFERS' => $arResult['OFFERS'] && !isset($arCurrentSKU) ? 'Y' : 'N',
);

$actualItem = $arResult["OFFERS"] ? (isset($arResult['OFFERS'][$arResult['OFFERS_SELECTED']]) ? $arResult['OFFERS'][$arResult['OFFERS_SELECTED']] : reset($arResult['OFFERS'])) : $arResult;

if($arResult["OFFERS"] && $arParams["TYPE_SKU"]=="N")
	unset($templateData['STORES']);
$offerPropCount = $arParams["VISIBLE_PROP_WITH_OFFER"] ==="Y" && is_array($arResult['OFFER_PROP']) ? count($arResult['OFFER_PROP']) : 0;
$iCountProps = count($arResult['DISPLAY_PROPERTIES']) + $offerPropCount;
?>

<?if($arResult["OFFERS"] && $arParams["TYPE_SKU"]=="N"):?>
	<?$templateData['OFFERS_INFO']['OFFERS_MORE'] = true;?>
	<?
	$showSkUName = ((in_array('NAME', $arParams['OFFERS_FIELD_CODE'])));
	$showSkUImages = false;
	if(((in_array('PREVIEW_PICTURE', $arParams['OFFERS_FIELD_CODE']) || in_array('DETAIL_PICTURE', $arParams['OFFERS_FIELD_CODE']))))
	{
		foreach ($arResult["OFFERS"] as $key => $arSKU)
		{
			if($arSKU['PREVIEW_PICTURE'] || $arSKU['DETAIL_PICTURE'])
			{
				$showSkUImages = true;
				break;
			}
		}
	}?>

	<?//list offers TYPE_2?>
	<?if($arResult["OFFERS"] && $arParams["TYPE_SKU"] !== "TYPE_1"):?>
		<?$this->SetViewTarget('PRODUCT_OFFERS_INFO');?>
			<div class="list-offers ajax_load">
				<div class="bx_sku_props" style="display:none;">
					<?$arSkuKeysProp='';
					$propSKU=$arParams["OFFERS_CART_PROPERTIES"];
					if($propSKU){
						$arSkuKeysProp=base64_encode(serialize(array_keys($propSKU)));
					}?>
					<input type="hidden" value="<?=$arSkuKeysProp;?>" />
				</div>
				<div class="table-view flexbox flexbox--row">
					<?foreach($arResult["OFFERS"] as $key => $arSKU):?>
						<?
						if($arResult["PROPERTIES"]["CML2_BASE_UNIT"]["VALUE"])
							$sMeasure = $arResult["PROPERTIES"]["CML2_BASE_UNIT"]["VALUE"];
						else
							$sMeasure = $arSKU['CATALOG_MEASURE_NAME'] ?? GetMessage("MEASURE_DEFAULT");

						$skutotalCount = $arSKU['TOTAL_COUNT'];
						$arskuQuantityData = CMax::GetQuantityArray($skutotalCount);

						$arSKU["IBLOCK_ID"]=$arResult["IBLOCK_ID"];
						$arSKU["IS_OFFER"]="Y";
						$arskuAddToBasketData =$arSKU['ADD_TO_BASKET_DATA'];
						$arskuAddToBasketData["HTML"] = str_replace('data-item', 'data-props="'.$arOfferProps.'" data-item', $arskuAddToBasketData["HTML"]);
						?>

						<div class="table-view__item item bordered box-shadow main_item_wrapper <?=($useStores ? "table-view__item--has-stores" : "");?>">
							<div class="table-view__item-wrapper item_info catalog-adaptive flexbox flexbox--row">
								<?if($showSkUImages):?>
									<?//image-block?>
									<div class="item-foto">
										<div class="item-foto__picture">
											<?\Aspro\Functions\CAsproMaxItem::showImg($arParams, $arSKU, false, false);?>
										</div>
										<div class="adaptive">
											<?\Aspro\Functions\CAsproMaxItem::showDelayCompareBtn(array_merge($arParams, $addParams), $arSKU, $arskuAddToBasketData, $skutotalCount, '', 'block', true, false, '_small');?>
										</div>
									</div>
								<?endif;?>

								<?//text-block?>
								<div class="item-info">
									<div class="item-title font_sm"><?=$arSKU['NAME']?></div>
									<div class="quantity_block_wrapper">
										<?if($arQuantityData["RIGHTS"]["SHOW_QUANTITY"]):?>
											<?=$arskuQuantityData["HTML"];?>
										<?endif;?>
										<?if($arSKU['PROPERTIES']['ARTICLE']['VALUE']):?>
											<div class="font_sxs muted article">
												<span class="name"><?=Loc::getMessage('ARTICLE_COMPACT');?></span><span class="value"><?=$arSKU['PROPERTIES']['ARTICLE']['VALUE'];?></span>
											</div>
										<?endif;?>
									</div>
									<?if($arResult["SKU_PROPERTIES"]):?>
										<div class="properties list">
											<div class="properties__container properties props_list">
												<?foreach ($arResult["SKU_PROPERTIES"] as $key => $arProp){?>
													<?if(!$arProp["IS_EMPTY"] && $key != 'ARTICLE'):?>
														<?$bHasValue = (
															$arResult["TMP_OFFERS_PROP"][$arProp["CODE"]]
															|| $arSKU["PROPERTIES"][$arProp["CODE"]]["VALUE"]
														);?>
														<?if (!$bHasValue) continue;?>
														<div class="properties__item properties__item--compact ">
															<div class="properties__title muted properties__item--inline char_name font_sxs">
																<?if($arProp["HINT"] && $arParams["SHOW_HINTS"]=="Y"):?><div class="hint"><span class="icon"><i>?</i></span><div class="tooltip"><?=$arProp["HINT"]?></div></div><?endif;?>
																<span class="props_item"><?=$arProp["NAME"]?>:</span>
															</div>
															<div class="properties__value darken properties__item--inline char_value font_xs">
																<?if($arResult["TMP_OFFERS_PROP"][$arProp["CODE"]]){
																	echo $arResult["TMP_OFFERS_PROP"][$arProp["CODE"]]["VALUES"][$arSKU["TREE"]["PROP_".$arProp["ID"]]]["NAME"];?>
																<?}else{
																	if (is_array($arSKU["PROPERTIES"][$arProp["CODE"]]["VALUE"])){
																		echo implode("/", $arSKU["PROPERTIES"][$arProp["CODE"]]["VALUE"]);
																	}else{
																		if($arSKU["PROPERTIES"][$arProp["CODE"]]["USER_TYPE"]=="directory" && isset($arSKU["PROPERTIES"][$arProp["CODE"]]["USER_TYPE_SETTINGS"]["TABLE_NAME"])){
																			$rsData = \Bitrix\Highloadblock\HighloadBlockTable::getList(array('filter'=>array('=TABLE_NAME'=>$arSKU["PROPERTIES"][$arProp["CODE"]]["USER_TYPE_SETTINGS"]["TABLE_NAME"])));
																	        if ($arData = $rsData->fetch()){
																	            $entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($arData);
																	            $entityDataClass = $entity->getDataClass();
																	            $arFilter = array(
																	                'limit' => 1,
																	                'filter' => array(
																	                    '=UF_XML_ID' => $arSKU["PROPERTIES"][$arProp["CODE"]]["VALUE"]
																	                )
																	            );
																	            $arValue = $entityDataClass::getList($arFilter)->fetch();
																	            if(isset($arValue["UF_NAME"]) && $arValue["UF_NAME"]){
																	            	echo $arValue["UF_NAME"];
																	            }else{
																	            	echo $arSKU["PROPERTIES"][$arProp["CODE"]]["VALUE"];
																	            }
																	        }
																		}else{
																			echo $arSKU["PROPERTIES"][$arProp["CODE"]]["VALUE"];
																		}
																	}
																}?>
															</div>
														</div>
													<?endif;?>
												<?}?>
											</div>
										</div>
									<?endif;?>
								</div>

								<div class="item-actions flexbox flexbox--row">
									<?//prices-block?>
									<div class="item-price">
										<div class="cost prices clearfix">
											<?if(isset($arSKU['PRICE_MATRIX']) && $arSKU['PRICE_MATRIX']): // USE_PRICE_COUNT?>
												<?if(\CMax::GetFrontParametrValue('SHOW_POPUP_PRICE') == 'Y' || $arSKU['ITEM_PRICE_MODE'] == 'Q' || (\CMax::GetFrontParametrValue('SHOW_POPUP_PRICE') != 'Y' && $arSKU['ITEM_PRICE_MODE'] != 'Q' && count($arSKU['PRICE_MATRIX']['COLS']) <= 1)):?>
													<?=CMax::showPriceRangeTop($arSKU, $arParams, Loc::getMessage("CATALOG_ECONOMY"));?>
												<?endif;?>
												<?if(count($arSKU['PRICE_MATRIX']['ROWS']) > 1 || count($arSKU['PRICE_MATRIX']['COLS']) > 1):?>
													<?=CMax::showPriceMatrix($arSKU, $arParams, $sMeasure, $arskuAddToBasketData);?>
												<?endif;?>
											<?elseif($arSKU["PRICES"]):?>
												<?\Aspro\Functions\CAsproMaxItem::showItemPrices($arParams, $arSKU["PRICES"], $sMeasure, $min_price_id, ($arParams["SHOW_DISCOUNT_PERCENT_NUMBER"] == "Y" ? "N" : "Y"));?>
											<?endif;?>
										</div>

										<div class="basket_props_block" id="bx_basket_div_<?=$arSKU["ID"];?>" style="display: none;">
											<?if (!empty($arSKU['PRODUCT_PROPERTIES_FILL'])){
												foreach ($arSKU['PRODUCT_PROPERTIES_FILL'] as $propID => $propInfo){?>
													<input type="hidden" name="<? echo $arParams['PRODUCT_PROPS_VARIABLE']; ?>[<? echo $propID; ?>]" value="<? echo htmlspecialcharsbx($propInfo['ID']); ?>">
													<?if (isset($arSKU['PRODUCT_PROPERTIES'][$propID]))
														unset($arSKU['PRODUCT_PROPERTIES'][$propID]);
												}
											}
											$arSKU["EMPTY_PROPS_JS"]="Y";
											$emptyProductProperties = empty($arSKU['PRODUCT_PROPERTIES']);
											if (!$emptyProductProperties){
												$arSKU["EMPTY_PROPS_JS"]="N";?>
												<div class="wrapper">
													<table>
														<?foreach ($arSKU['PRODUCT_PROPERTIES'] as $propID => $propInfo){?>
															<tr>
																<td><? echo $arSKU['PROPERTIES'][$propID]['NAME']; ?></td>
																<td>
																	<?if('L' == $arSKU['PROPERTIES'][$propID]['PROPERTY_TYPE']	&& 'C' == $arSKU['PROPERTIES'][$propID]['LIST_TYPE']){
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
									</div>

									<?//buttons-block?>
									<div class="item-buttons item_<?=$arSKU["ID"]?>">
										<div class="counter_wrapp list clearfix n-mb small-block">
											<?if($arskuAddToBasketData["OPTIONS"]["USE_PRODUCT_QUANTITY_DETAIL"] && !count($arSKU["OFFERS"]) && $arskuAddToBasketData["ACTION"] == "ADD" && $arskuAddToBasketData["CAN_BUY"]):?>
												<?=\Aspro\Functions\CAsproMax::showItemCounter($arskuAddToBasketData, $arSKU["ID"], $arSKUIDs, $arParams, '', '', true, true);?>
											<?endif;?>
											<div class="button_block <?=(in_array($arSKU["ID"], $arParams["BASKET_ITEMS"]) || $arskuAddToBasketData["ACTION"] == "ORDER" || ($arskuAddToBasketData["ACTION"] == 'MORE' || !$arskuAddToBasketData["CAN_BUY"]) || !$arskuAddToBasketData["OPTIONS"]["USE_PRODUCT_QUANTITY_DETAIL"] ? "wide" : "");?>">
												<!--noindex-->
													<?=$arskuAddToBasketData["HTML"]?>
												<!--/noindex-->
											</div>
										</div>
										<?=\Aspro\Functions\CAsproMax::showItemOCB($arskuAddToBasketData, $arSKU, $arParams);?>

										<?//delivery calculate?>
										<?if(
											$arskuAddToBasketData["ACTION"] == "ADD" &&
											$arskuAddToBasketData["CAN_BUY"]
										):?>
											<?=\Aspro\Functions\CAsproMax::showCalculateDeliveryBlock($arSKU['ID'], $arParams, $arParams['TYPE_SKU'] !== 'TYPE_1');?>
										<?endif;?>

										<?
										if(isset($arSKU['PRICE_MATRIX']) && $arSKU['PRICE_MATRIX']) // USE_PRICE_COUNT
										{?>
											<?if($arSKU['ITEM_PRICE_MODE'] == 'Q' && count($arSKU['PRICE_MATRIX']['ROWS']) > 1):?>
												<?$arOnlyItemJSParams = array(
													"ITEM_PRICES" => $arSKU["ITEM_PRICES"],
													"ITEM_PRICE_MODE" => $arSKU["ITEM_PRICE_MODE"],
													"ITEM_QUANTITY_RANGES" => $arSKU["ITEM_QUANTITY_RANGES"],
													"MIN_QUANTITY_BUY" => $arskuAddToBasketData["MIN_QUANTITY_BUY"],
													"SHOW_DISCOUNT_PERCENT_NUMBER" => $arParams["SHOW_DISCOUNT_PERCENT_NUMBER"],
													"ID" => $this->GetEditAreaId($arSKU["ID"]),
												)?>
												<script type="text/javascript">
													var ob<? echo $this->GetEditAreaId($arSKU["ID"]); ?>el = new JCCatalogSectionOnlyElement(<? echo CUtil::PhpToJSObject($arOnlyItemJSParams, false, true); ?>);
												</script>
											<?endif;?>
										<?}?>
										<!--noindex-->
										<?/*if(isset($arSKU['PRICE_MATRIX']) && $arSKU['PRICE_MATRIX'] && count($arSKU['PRICE_MATRIX']['ROWS']) > 1) // USE_PRICE_COUNT
										{?>
											<?$arOnlyItemJSParams = array(
												"ITEM_PRICES" => $arSKU["ITEM_PRICES"],
												"ITEM_PRICE_MODE" => $arSKU["ITEM_PRICE_MODE"],
												"ITEM_QUANTITY_RANGES" => $arSKU["ITEM_QUANTITY_RANGES"],
												"MIN_QUANTITY_BUY" => $arskuAddToBasketData["MIN_QUANTITY_BUY"],
												"SHOW_DISCOUNT_PERCENT_NUMBER" => $arParams["SHOW_DISCOUNT_PERCENT_NUMBER"],
												"ID" => $this->GetEditAreaId($arSKU["ID"]),
											)?>
											<script type="text/javascript">
												var ob<? echo $this->GetEditAreaId($arSKU["ID"]); ?>el = new JCCatalogOnlyElement(<? echo CUtil::PhpToJSObject($arOnlyItemJSParams, false, true); ?>);
											</script>
										<?}*/?>
									<!--/noindex-->
									</div>
								</div>

								<?//icons-block?>
								<?if($arResult['ICONS_SIZE']):?>
									<div class="item-icons s_<?=$arResult['ICONS_SIZE']?>">
										<?\Aspro\Functions\CAsproMaxItem::showDelayCompareBtn(array_merge($arParams, $addParams), $arSKU, $arskuAddToBasketData, $skutotalCount, '', 'list static icons', false, false, '_small', $currentSKUID, $currentSKUIBlock);?>
									</div>
								<?endif;?>

								<?//stores icon?>
								<?if($useStores):?>
									<div class="stores-icons">
										<div class="like_icons list static icons">
											<div>
												<span class="btn btn_xs btn-transparent"><?=CMax::showIconSvg("cat_icons", SITE_TEMPLATE_PATH."/images/svg/catalog/arrow_sku.svg", "", "", true, false);?></span>
											</div>
										</div>
									</div>
								<?endif;?>
							</div>
							<div class="offer-stores" style="display: none;">
								<?$APPLICATION->IncludeComponent("bitrix:catalog.store.amount", "main", array(
										"PER_PAGE" => "10",
										"USE_STORE_PHONE" => $arParams["USE_STORE_PHONE"],
										"SCHEDULE" => $arParams["SCHEDULE"],
										"USE_MIN_AMOUNT" => $arParams["USE_MIN_AMOUNT"],
										"MIN_AMOUNT" => $arParams["MIN_AMOUNT"],
										"ELEMENT_ID" => $arSKU["ID"],
										"STORE_PATH"  =>  $arParams["STORE_PATH"],
										"MAIN_TITLE"  =>  $arParams["MAIN_TITLE"],
										"MAX_AMOUNT"=>$arParams["MAX_AMOUNT"],
										"SHOW_EMPTY_STORE" => $arParams['SHOW_EMPTY_STORE'],
										"SHOW_GENERAL_STORE_INFORMATION" => $arParams['SHOW_GENERAL_STORE_INFORMATION'],
										"USE_ONLY_MAX_AMOUNT" => $arParams["USE_ONLY_MAX_AMOUNT"],
										"USER_FIELDS" => $arParams['USER_FIELDS'],
										"FIELDS" => $arParams['FIELDS'],
										"STORES" => $arParams['STORES'],
										"CACHE_TYPE" => "A",
										"SET_ITEMS" => $arResult["SET_ITEMS"],
									),
									$component
								);?>
							</div>
						</div>
					<?endforeach;?>
				</div>
			</div>
		<?$this->EndViewTarget();?>
	<?endif;?>
<?endif;?>

<?//top info?>
<section class="product">
    <div class="product-info-wrapper" data-id="<?= $arResult["ID"] ?>" data-price="<?= $price['PRICE'] ?>" data-name="<?= $name ?>" data-name-code="<?= $namecode ?>" data-color="<?= $arResult["PROPERTIES"]['TSVET']['VALUE'] ?>" data-preview="<?= $arResult['PREVIEW_PICTURE']['SRC'] ?>" data-size="<?= $RAZMER ?>" data-size-code="<?= $RAZMERCODE ?>">
        <div class="product-info <?=(!$showCustomOffer ? "noffer" : "");?> product__container" id="<?=$arItemIDs["strMainID"];?>"> <?// old class product-info--type2-old ?>
            <script type="text/javascript">setViewedProduct(<?=$arResult['ID']?>, <?=CUtil::PhpToJSObject($arViewedData, false)?>);</script>

            <?//meta?>
            <meta itemprop="name" content="<?=$name = strip_tags(!empty($arResult['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE']) ? $arResult['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE'] : $arResult['NAME'])?>" />
            <link itemprop="url" href="<?=$arResult['DETAIL_PAGE_URL']?>" />
            <meta itemprop="category" content="<?=$arResult['CATEGORY_PATH']?>" />
            <meta itemprop="description" content="<?=(strlen(strip_tags($arResult['PREVIEW_TEXT'])) ? strip_tags($arResult['PREVIEW_TEXT']) : (strlen(strip_tags($arResult['DETAIL_TEXT'])) ? strip_tags($arResult['DETAIL_TEXT']) : $name))?>" />
            <meta itemprop="sku" content="<?=$arResult['ID'];?>" />

            <div class="product__wrapper"> <?// old class flexbox flexbox--row?>

                <?//main gallery?>
                <?$bVertical = ($arParams['GALLERY_THUMB_POSITION'] == 'vertical');?>
                <div class="product__left">
                    <div class="product__gallery gallery"> <? // product-detail-gallery swipeignore js-notice-block__image?>

                        <?reset($arResult['MORE_PHOTO']);
                        $countPhoto = count($arResult["MORE_PHOTO"]);
                        $arFirstPhoto = ($arParams['ADD_DETAIL_TO_SLIDER'] != 'Y' && !$arCurrentSKU ? $arResult['PREVIEW_PICTURE'] : current($arResult['MORE_PHOTO']));
                        $viewImgType=$arParams["DETAIL_PICTURE_MODE"];
                        $bMagnifier = ($viewImgType=="MAGNIFIER");?>
                        <?$bIsOneImage = $bMagnifier; ?>

                        <?\Aspro\Functions\CAsproMaxItem::showStickers($arParams, $arResult, true, "product-info-headnote__stickers1");?>

                        <?if($arResult["OFFERS"] && $arResult["FIRST_SKU_PICTURE"]):?>
                        <link class="first_sku_picture" href="<?=$arResult["FIRST_SKU_PICTURE"]["src"];?>"/>
                        <?endif;?>

                        <link href="<?=($arFirstPhoto["BIG"]["src"] ? $arFirstPhoto["BIG"]["src"] : $arFirstPhoto["SRC"]);?>" itemprop="image"/>
                        <?if($popupVideo123): // $popupVideo?>
                            <div class="video-block video-block_popup_custom sm video-block_detail">
                                <a id="ato_video_slide" data-slide="<?=$countPhoto;?>" class="various image" href="#photo-0" title="<?=Loc::getMessage("VIDEO")?>">
                                <span class="play text-upper font_xs">
                                    <span><?=Loc::getMessage("VIDEO")?></span>
                                </span>
                                </a>
                            </div>
                        <?endif;?>
                        <div class="gallery__navigation product-navigation">
                            <?php if ($arResult["MORE_PHOTO"]) { ?>

                            <?//Увеличиваем ключи на 1 если есть видео чтобы не сломать слайдер
                            if($arResult["MORE_PHOTO"] && $popupVideo122 ) { //$popupVideo
                                $arResult["MORE_PHOTO"] = array_combine(range(1, count($arResult["MORE_PHOTO"])), $arResult["MORE_PHOTO"]);
                            }
                            ?>
                            <div class="swiper">
                                <div class="swiper-wrapper">
                                    <?php if ($popupVideo):?>
                                        <div class="swiper-slide swiper-slide-active">
                                            <div class="product-images__slide product-images__slide--active">
                                                <div class="product-images__video">
                                                    <img src="<?= $arResult["MORE_PHOTO"][0]["SMALL"]["src"];?>" alt="<?= $arResult["MORE_PHOTO"][0]["ALT"]?>">
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif;?>
                                <?php foreach($arResult["MORE_PHOTO"] as $i => $arImage)
                                {
                                    if ($i && $bMagnifier):
                                        continue;
                                    endif;
                                    $isEmpty = ($arImage["SMALL"]["src"] ? false : true );
                                    $alt = $arImage["ALT"];
                                    $title = $arImage["TITLE"];
                                    ?>
                                    <div class="swiper-slide" id="photo-<?=$i?>">
                                        <div class="product-images__slide">
                                            <div class="product-images__img">
                                                <?php if (!$isEmpty) :?>
                                                <img class="lazy" src="<?=\Aspro\Functions\CAsproMax::showBlankImg($arImage["SMALL"]["src"])?>" data-src="<?=$arImage["SMALL"]["src"]?>" alt="<?=$alt;?>" title="<?=$title;?>">
                                                <?php else:?>
                                                    <img class="lazy" src="<?=\Aspro\Functions\CAsproMax::showBlankImg($arImage["SRC"])?>" data-src="<?=$arImage["SRC"]?>" alt="<?=$alt;?>" title="<?=$title;?>" />
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php
                                }
                                ?>
                                </div>
                            </div>

                            <?if($popupVideo123) : //$popupVideo?>
                                <div id="photo-0" class="product-detail-gallery__item product-detail-gallery__item--middle text-center">
                                    <iframe class="frame_custom_detail product-detail-gallery__item product-detail-gallery__item--middle" allowfullscreen="allowfullscreen" src="<?=$popupVideo;?>?&modestbranding=1&showinfo=0&controls=1&rel=0&mute=1&rel=0&autohide=1" allow="autoplay" frameborder="0" scrolling="auto">
                                    </iframe>
                                </div>
                            <? endif; ?>

                            <?php } ?>
                        </div>

                        <?if($bMagnifier123): //$bMagnifier?>
                            <div class="product-detail-gallery__slider owl-carousel owl-theme big visible-xs <?=$arParams['PICTURE_RATIO'];?>" data-plugin-options='{"items": "1", "dots": false, "nav": true, "loop": false}'>
                                <?if($arResult["MORE_PHOTO"]){?>
                                    <?foreach($arResult["MORE_PHOTO"] as $i => $arImage){?>
                                        <?$isEmpty=($arImage["SMALL"]["src"] ? false : true );?>
                                        <?
                                        $alt=$arImage["ALT"];
                                        $title=$arImage["TITLE"];
                                        ?>
                                        <div id="photo-<?=$i?>" class="product-detail-gallery__item product-detail-gallery__item--big text-center">
                                            <?if(!$isEmpty){?>
                                                <a href="<?=$arImage["BIG"]["src"];?>" data-fancybox="gallery" class="product-detail-gallery__link popup_link fancy" title="<?=$title;?>">
                                                    <img class="lazy product-detail-gallery__picture" data-src="<?=$arImage["SMALL"]["src"]?>" src="<?=\Aspro\Functions\CAsproMax::showBlankImg($arImage["SMALL"]["src"])?>" alt="<?=$alt;?>" title="<?=$title;?>"<?//=(!$i ? ' itemprop="image"' : '')?>/>
                                                </a>
                                            <?}else{?>
                                                <img class="lazy product-detail-gallery__picture" data-src="<?=\Aspro\Functions\CAsproMax::showBlankImg($arImage["SRC"])?>" src="<?=$arImage["SRC"]?>" alt="<?=$alt;?>" title="<?=$title;?>" />
                                            <?}?>
                                        </div>
                                    <?}?>
                                <?}?>
                            </div>
                        <?endif;?>

                        <div class="gallery__images product-images">
                            <div class="swiper">
                                <div class="swiper-wrapper">
                                    <?php if ($popupVideo):?>
                                    <div class="swiper-slide">
                                        <div class="product-images__slide">
                                            <div class="product-images__video">
                                                <iframe src="<?=$popupVideo;?>?controls=0?enablejsapi=1&modestbranding=1&rel=0" srcdoc="
                                                        <style>
                                                          * {
                                                            padding: 0;
                                                            margin: 0;
                                                            overflow: hidden;
                                                          }
                                                          html, body {
                                                            height: 100%;
                                                          }
                                                          img, span {
                                                            position: absolute;
                                                            width: 100%;
                                                            top: 0;
                                                            bottom: 0;
                                                            margin: auto;
                                                            height: 100%;
                                                            object-fit: cover;
                                                          }
                                                          .product-images__button {
                                                            text-align: center;
                                                            position: relative;
                                                            top: 50%;
                                                            transform: translateY(-50%);
                                                          }
                                                        </style>
                                                        <a class='product-images__preview' href='<?=$popupVideo;?>?autoplay=1'>
                                                        <div class='product-images__prev' style='background-image:url(<?= $arResult["MORE_PHOTO"][0]["SMALL"]["src"];?>);background-size: cover;height: 100%;'></div>
                                                        <div class='product-images__button' style='z-index:1;position: absolute;left: 50%;transform: translate(-50%,-50%);'>
                                                          <svg width='96' height='96' viewBox='0 0 96 96' fill='none' xmlns='http://www.w3.org/2000/svg'>
                                                            <path d='M88 48C88 70.0914 70.0914 88 48 88C25.9086 88 8 70.0914 8 48C8 25.9086 25.9086 8 48 8C70.0914 8 88 25.9086 88 48Z' fill='#333333' fill-opacity='0.5'/>
                                                            <path d='M66 48L38 66V30L66 48Z' stroke='white' stroke-linejoin='round'/>
                                                          </svg>
                                                        </div></a>"
                                                        frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen>
                                                </iframe>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif;?>
                                    <?foreach($arResult["MORE_PHOTO"] as $i => $arImage):?>
                                        <div class="swiper-slide" id="p-photo-<?=$i?>">
                                            <div class="product-images__slide">
                                                <div class="product-images__img">
                                                    <?php if(!$isEmpty) :?>
                                                    <img class="lazy" data-src="<?=$arImage["SMALL"]["src"]?>" src="<?=\Aspro\Functions\CAsproMax::showBlankImg($arImage["SMALL"]["src"])?>" alt="<?=$alt;?>" title="<?=$title;?>">
                                                    <?php else:?>
                                                        <img class="lazy" data-src="<?=\Aspro\Functions\CAsproMax::showBlankImg($arImage["SRC"])?>" src="<?=$arImage["SRC"]?>" alt="<?=$alt;?>" title="<?=$title;?>" />
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?endforeach;?>
                                </div>
                            </div>
                            <div class="product-images__prev">
                                <svg width="6" height="12" viewBox="0 0 6 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M5.5 0.5L0.5 6L5.5 11.5" stroke="#333333" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </div>
                            <div class="product-images__next">
                                <svg width="6" height="12" viewBox="0 0 6 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M0.5 0.5L5.5 6L0.5 11.5" stroke="#333333" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </div>

                        </div> <?// end gallery__images product-images?>
                    </div>
                    <div class="product__info">
                        <div class="product__description description">
                            <div class="description__title title"><?=($arParams["T_DESC"] ? $arParams["T_DESC"] : Loc::getMessage("T_DESC"));?></div>
                            <?if($arResult['DETAIL_TEXT'] || $bOfferDetailText ):?>
                                <?$templateData['DETAIL_TEXT'] = true;?>
                                <div class="description__paragraph" itemprop="description">
                                    <?if($bOfferDetailText):?>
                                        <?=$arCurrentSKU["DETAIL_TEXT"];?>
                                    <?else:?>
                                        <?=$arResult['DETAIL_TEXT'];?>
                                    <?endif;?>
                                </div>
                            <?endif;?>
                        </div>
                        <?foreach($arResult["DISPLAY_PROPERTIES"] as $arProp):
                            if ($arProp["CODE"] != "UKHOD")
                                continue;
                            ?>
                            <div class="product__description description" itemprop="additionalProperty" itemscope itemtype="http://schema.org/PropertyValue">
                                <div class="description__title title" itemprop="name"><?=$arProp["NAME"]?></div>
                                <p class="description__paragraph" itemprop="value">
                                    <?=$arProp["DISPLAY_VALUE"];?>
                                </p>
                            </div>
                        <?endforeach;?>
                    </div>
                </div>
                <div class="product__right">
                    <div class="product-main">
                        <div class="product__header">
                            <?//article,rating,brand?>
                            <?php $isArticle=(strlen($article["VALUE"]) || ($arResult['SHOW_OFFERS_PROPS'] && $showCustomOffer));?>
                            <?php if ($isArticle) :?>
                            <div class="product__code" itemprop="additionalProperty" itemscope itemtype="http://schema.org/PropertyValue" <?php if (!strlen($article["VALUE"])){ ?>id="<?= $arItemIDs["ALL_ITEM_IDS"]['DISPLAY_PROP_ARTICLE_DIV'] ?>" style="display: none;"<? } ?>>
                                <span itemprop="name"><?=$article["NAME"];?>:</span> <span itemprop="value"><?=$article["VALUE"]?></span>
                            </div>
                            <?php endif;?>
                            <?php if ($arParams["SHOW_RATING"] == "Y"): ?>
                                <div class="product-info-headnote__rating">
                                    <?$frame = $this->createFrame('dv_'.$arResult["ID"])->begin();?>
                                    <div class="rating">
                                        <?
                                        global $arTheme;
                                        if($arParams['REVIEWS_VIEW'] == 'EXTENDED'):?>
                                            <div class="blog-info__rating--top-info pointer">
                                                <div class="votes_block nstar with-text" itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">
                                                    <meta itemprop="ratingValue" content="<?=($arResult['PROPERTIES']['EXTENDED_REVIEWS_RAITING']['VALUE'] ? $arResult['PROPERTIES']['EXTENDED_REVIEWS_RAITING']['VALUE'] : 5)?>" />
                                                    <meta itemprop="reviewCount" content="<?=(intval($arResult['PROPERTIES']['EXTENDED_REVIEWS_COUNT']['VALUE']) ? intval($arResult['PROPERTIES']['EXTENDED_REVIEWS_COUNT']['VALUE']) : 1)?>" />
                                                    <meta itemprop="bestRating" content="5" />
                                                    <meta itemprop="worstRating" content="0" />
                                                    <div class="ratings">
                                                        <?$message = $arResult['PROPERTIES']['EXTENDED_REVIEWS_COUNT']['VALUE'] ? GetMessage('VOTES_RESULT', array('#VALUE#' => $arResult['PROPERTIES']['EXTENDED_REVIEWS_RAITING']['VALUE'])) : GetMessage('VOTES_RESULT_NONE')?>
                                                        <div class="inner_rating" title="<?=$message?>">
                                                            <?for($i=1;$i<=5;$i++):?>
                                                                <div class="item-rating <?=$i<=$arResult['PROPERTIES']['EXTENDED_REVIEWS_RAITING']['VALUE'] ? 'filed' : ''?>"><?=CMax::showIconSvg("star", SITE_TEMPLATE_PATH."/images/svg/catalog/star_small.svg");?></div>
                                                            <?endfor;?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?if($arResult['PROPERTIES']['EXTENDED_REVIEWS_COUNT']['VALUE']):?>
                                                    <span class="font_sxs"><?=$arResult['PROPERTIES']['EXTENDED_REVIEWS_COUNT']['VALUE']?></span>
                                                <?endif;?>
                                            </div>
                                        <?else:?>
                                            <?$APPLICATION->IncludeComponent(
                                                "bitrix:iblock.vote",
                                                "element_rating",
                                                Array(
                                                    "IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
                                                    "IBLOCK_ID" => $arResult["IBLOCK_ID"],
                                                    "ELEMENT_ID" => $arResult["ID"],
                                                    "MAX_VOTE" => 5,
                                                    "VOTE_NAMES" => array(),
                                                    "CACHE_TYPE" => $arParams["CACHE_TYPE"],
                                                    "CACHE_TIME" => $arParams["CACHE_TIME"],
                                                    "DISPLAY_AS_RATING" => 'vote_avg'
                                                ),
                                                $component, array("HIDE_ICONS" =>"Y")
                                            );?>
                                        <?endif;?>
                                    </div>
                                    <?$frame->end();?>
                                </div>
                            <?php endif;?>
                            <?php if ($arResult["BRAND_ITEM"]): ?>
                                <div class="product-info-headnote__brand">
                                    <div class="brand">
                                        <meta itemprop="brand" content="<?=$arResult["BRAND_ITEM"]["NAME"]?>" />
                                        <?php if (!$arResult["BRAND_ITEM"]["IMAGE"]) : ?>
                                            <a href="<?=$arResult["BRAND_ITEM"]["DETAIL_PAGE_URL"]?>" class="brand__link dark_link"><?=$arResult["BRAND_ITEM"]["NAME"]?></a>
                                        <?php else: ?>
                                            <a class="brand__picture" href="<?=$arResult["BRAND_ITEM"]["DETAIL_PAGE_URL"]?>">
                                                <img  src="<?=$arResult["BRAND_ITEM"]["IMAGE"]["src"]?>" alt="<?=$arResult["BRAND_ITEM"]["IMAGE"]["ALT"]?>" title="<?=$arResult["BRAND_ITEM"]["IMAGE"]["TITLE"]?>" />
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif;?>
                            <?//name?>
                            <div class="product__title"><?= $name ?></div>
                            <?//delay|compare?>
                            <div class="product__fav-wrapper">
                            <? // \Aspro\Functions\CAsproMaxItem::showDelayCompareBtn(array_merge($arParams, $addParams), $arResult, $arAddToBasketData, $totalCount, $bUseSkuProps, 'list static sm', false, false, '_small', $currentSKUID, $currentSKUIBlock);?>
                            <?php \Cosmo\CosmoMaxItem::showDelayCompareBtn(array_merge($arParams, $addParams), $arResult, $arAddToBasketData, $totalCount, $bUseSkuProps, 'list static sm', false, false, '_small', $currentSKUID, $currentSKUIBlock);?>
                            <button class="product__fav product__fav--active" style="display: none">
                                <svg width="20" height="18" viewBox="0 0 20 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M15 0.5C12.5 0.5 10 2.5 10 4.5C10 2.5 7.5 0.5 5 0.5C2.5 0.5 0.5 2.5 0.5 5.5C0.5 8.5 2.5 14.5 10 17.5C17.5 14.5 19.5 8.5 19.5 5.5C19.5 2.5 17.5 0.5 15 0.5Z" stroke="#333333" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                                <span>Отложить</span>
                            </button>
                            </div>
                        </div>

                        <?//buttons,props,sales?>
                        <?$bShowPropsBlock = ($arResult["OFFERS"] && $showCustomOffer) || ($arResult["SIZE_PATH"]) || ( ($arResult['DISPLAY_PROPERTIES'] || $arResult['OFFER_PROP']) && $arParams['VISIBLE_PROP_COUNT'] > 0 );?>

                        <?if($bShowPropsBlock):?>
                            <div class="product-chars flex-50">

                                <?//sales?>
                                <div class="js-sales"></div>

                                <?//offers tree props?>
                                <?if($arResult["OFFERS"] && $showCustomOffer):?>
                                    <?$frame = $this->createFrame()->begin('');?>
                                        <div class="buy_block offer-props-wrapper">
                                            <div class="sku_props inner_content js_offers__<?=$arResult['ID'];?>_detail">
                                                <?if (!empty($arResult['OFFERS_PROP'])){?>
                                                    <div class="bx_catalog_item_scu wrapper_sku sku_in_detail" id="<? echo $arItemIDs["ALL_ITEM_IDS"]['PROP_DIV']; ?>" data-site_id="<?=SITE_ID;?>" data-id="<?=$arResult["ID"];?>" data-offer_id="<?=$arResult["OFFERS"][$arResult["OFFERS_SELECTED"]]["ID"];?>" data-propertyid="<?=$arResult["OFFERS"][$arResult["OFFERS_SELECTED"]]["PROPERTIES"]["CML2_LINK"]["ID"];?>" data-offer_iblockid="<?=$arResult["OFFERS"][$arResult["OFFERS_SELECTED"]]["IBLOCK_ID"];?>" data-iblockid="<?=$arResult["IBLOCK_ID"];?>">
                                                        <?foreach ($arSkuTemplate as $code => $strTemplate){
                                                            if (!isset($arResult['OFFERS_PROP'][$code]))
                                                                continue;
                                                            echo '<div class="item_wrapper">', str_replace('#ITEM#_prop_', $arItemIDs["ALL_ITEM_IDS"]['PROP'], $strTemplate), '</div>';
                                                        }?>
                                                    </div>
                                                <?}?>
                                            </div>
                                        </div>
                                    <?$frame->end();?>
                                <?endif;?>
                                <?if($arResult["SIZE_PATH"]):?>
                                    <div class="table_sizes muted777 font_xs">
                                        <span>
                                            <?=CMax::showIconSvg("cheaper", SITE_TEMPLATE_PATH.'/images/svg/catalog/sizestable.svg', '', '', true, false);?>
                                            <span class="animate-load dotted" data-event="jqm" data-param-form_id="TABLES_SIZE" data-param-url="<?=$arResult["SIZE_PATH"];?>" data-name="TABLES_SIZE"><?=GetMessage("TABLES_SIZE");?></span>
                                        </span>
                                    </div>
                                <?endif;?>

                                <?//props?>
                                <?$bShowMoreLink = ($iCountProps > $arParams['VISIBLE_PROP_COUNT']);?>
                                <?if( ($arResult['DISPLAY_PROPERTIES'] || $arResult['OFFER_PROP']) && $arParams['VISIBLE_PROP_COUNT'] > 0 ):?>
                                    <div class="char-side">
                                        <div class="char-side__title font_sm darken"><?=($arParams["T_CHARACTERISTICS"] ? $arParams["T_CHARACTERISTICS"] : Loc::getMessage("T_CHARACTERISTICS"));?></div>
                                        <div class="properties list">
                                            <div class="properties__container properties <?=!$bShowMoreLink ? 'js-offers-prop' : ''?>">
                                                <?$j=0;?>
                                                <?foreach($arResult['DISPLAY_PROPERTIES'] as $arProp):?>
                                                    <?if($j<$arParams['VISIBLE_PROP_COUNT']):?>
                                                        <div class="properties__item properties__item--compact font_xs js-prop-replace">
                                                            <div class="properties__title muted properties__item--inline js-prop-title">
                                                                <?=$arProp['NAME']?>
                                                                <?if($arProp["HINT"] && $arParams["SHOW_HINTS"]=="Y"):?>
                                                                    <div class="hint">
                                                                        <span class="icon colored_theme_hover_bg"><i>?</i></span>
                                                                        <div class="tooltip"><?=$arProp["HINT"]?></div>
                                                                    </div>
                                                                <?endif;?>
                                                            </div>
                                                            <div class="properties__hr muted properties__item--inline">&mdash;</div>
                                                            <div class="properties__value darken properties__item--inline js-prop-value">
                                                                <?if(is_array($arProp["DISPLAY_VALUE"]) && count($arProp["DISPLAY_VALUE"]) > 1):?>
                                                                    <?=implode(', ', $arProp["DISPLAY_VALUE"]);?>
                                                                <?else:?>
                                                                    <?=$arProp["DISPLAY_VALUE"];?>
                                                                <?endif;?>
                                                            </div>
                                                        </div>
                                                    <?endif;?>
                                                    <?$j++;?>
                                                <?endforeach;?>
                                                <?foreach($arResult['OFFER_PROP'] as $arProp):?>
                                                    <?if($j<$arParams['VISIBLE_PROP_COUNT'] || (!$bShowMoreLink && $arParams["VISIBLE_PROP_WITH_OFFER"] !=="Y")):?>
                                                        <div class="properties__item properties__item--compact font_xs js-prop">
                                                            <div class="properties__title muted properties__item--inline js-prop-title">
                                                                <?=$arProp['NAME']?>
                                                                <?if($arProp["HINT"] && $arParams["SHOW_HINTS"]=="Y"):?>
                                                                    <div class="hint">
                                                                        <span class="icon colored_theme_hover_bg"><i>?</i></span>
                                                                        <div class="tooltip"><?=$arProp["HINT"]?></div>
                                                                    </div>
                                                                <?endif;?>
                                                            </div>
                                                            <div class="properties__hr muted properties__item--inline">&mdash;</div>
                                                            <div class="properties__value darken properties__item--inline js-prop-value">
                                                                <?if(is_array($arProp["DISPLAY_VALUE"]) && count($arProp["DISPLAY_VALUE"]) > 1):?>
                                                                    <?=implode(', ', $arProp["DISPLAY_VALUE"]);?>
                                                                <?else:?>
                                                                    <?=$arProp["DISPLAY_VALUE"];?>
                                                                <?endif;?>
                                                            </div>
                                                        </div>
                                                    <?endif;?>
                                                    <?$j++;?>
                                                <?endforeach;?>
                                            </div>

                                        </div>
                                        <?if($bShowMoreLink):?>
                                            <div class="more-char-link"><span class="choise colored_theme_text_with_hover font_sxs dotted" data-block=".js-scrolled"><?=Loc::getMessage('ALL_CHARS');?></span></div>
                                        <?endif;?>
                                    </div>
                                <?endif;?>
                            </div>
                        <?endif;?>

                        <?//discount,buy|order|subscribe?>
                        <div class="product-action">
                            <div class="product__sizes size">
                                <div class="size__title title">Размер</div>
                                <?php if (!empty($RAZMER)) : ?>
                                    <? if ($_POST['ajax'] == 'Y') { ?>
                                        <a href="" onclick="UpdateParamAjaxCatalogElement(this); return false" class="active position-relative mb-1" data-id="<?= $arResult["ID"] ?>"><span class="d-block bg-graylight text-gold px-2 py-2"><?= $RAZMER ?></span></a>
                                    <? } else { /*?>
                                    <a href="<?= $APPLICATION->GetCurPage(false); ?>" class="active position-relative mb-1" data-id="<?= $arResult["ID"] ?>"><span class="d-block bg-graylight text-gold px-2 py-2"><?= $RAZMER ?></span></a>
                                <? */} ?>
                                <?php endif ?>
                            </div>
                        </div>

                        <div class="product-action product__price detail"> <? //price?>
                            <?//discount timer?>
                            <?php if($arParams["SHOW_DISCOUNT_TIME"]=="Y"){ ?>
                                <?php $arUserGroups = $USER->GetUserGroupArray();?>
                                <?php $arDiscount = []?>
                                <?php if ($arParams['SHOW_DISCOUNT_TIME_EACH_SKU'] != 'Y' || ($arParams['SHOW_DISCOUNT_TIME_EACH_SKU'] == 'Y' && (!$arResult['OFFERS'] || ($arResult['OFFERS'] && $arParams['TYPE_SKU'] != 'TYPE_1')))):?>
                                    <?\Aspro\Functions\CAsproMax::showDiscountCounter($totalCount, $arDiscount, $arQuantityData, $arResult, $strMeasure, 'compact red', $arResult["ID"]);?>
                                <?php else: ?>
                                    <?\Aspro\Functions\CAsproMax::showDiscountCounter($totalCount, $arDiscount, $arQuantityData, $arResult, $strMeasure, 'compact red', $item_id);?>
                                <?php endif; ?>
                            <? } ?>
                            <div class="price-inner">
                                <?php if ($arResult["OFFERS"]): ?>
                                    <?=\Aspro\Functions\CAsproMaxItem::showItemPricesDefault($arParams);?>
                                    <div class="js_price_wrapper">
                                        <?if($arCurrentSKU):?>
                                            <?$arParams['HIDE_PRICE'] = false?>
                                            <?
                                            $arCurrentSKU['CATALOG_MEASURE_NAME'] = $arCurrentSKU['MEASURE'];
                                            if(isset($arCurrentSKU['PRICE_MATRIX']) && $arCurrentSKU['PRICE_MATRIX'] && $arCurrentSKU['ITEM_PRICE_MODE'] == 'Q'): // USE_PRICE_COUNT?>
                                                <?if (!$arParams['USE_PRICE_COUNT']):?>
                                                    <?$arParams['HIDE_PRICE'] = true?>
                                                    <?\Aspro\Functions\CAsproMaxItem::showItemPrices($arParams, $arCurrentSKU["PRICES"], $strMeasure, $min_price_id, ($arParams["SHOW_DISCOUNT_PERCENT_NUMBER"] == "Y" ? "N" : "Y"));?>
                                                <?endif;?>
                                                <?if($arCurrentSKU['ITEM_PRICE_MODE'] == 'Q' && count($arCurrentSKU['PRICE_MATRIX']['ROWS']) > 1):?>
                                                    <?=CMax::showPriceRangeTop($arCurrentSKU, $arParams, Loc::getMessage("CATALOG_ECONOMY"));?>
                                                <?endif;?>
                                                <?if ($arParams['USE_PRICE_COUNT']):?>
                                                    <?=CMax::showPriceMatrix($arCurrentSKU, $arParams, $strMeasure, $arAddToBasketData);?>
                                                <?endif;?>
                                            <?else:?>
                                                <?\Aspro\Functions\CAsproMaxItem::showItemPrices($arParams, $arCurrentSKU["PRICES"], $strMeasure, $min_price_id, ($arParams["SHOW_DISCOUNT_PERCENT_NUMBER"] == "Y" ? "N" : "Y"));?>
                                            <?endif;?>
                                        <?else:?>
                                            <?\Aspro\Functions\CAsproMaxSku::showItemPrices($arParams, $arResult, $item_id, $min_price_id, $arItemIDs, ($arParams["SHOW_DISCOUNT_PERCENT_NUMBER"] == "Y" ? "N" : "Y"));?>
                                        <?endif;?>
                                    </div>
                                <?php else: ?>
                                    <?php if (isset($arResult['PRICE_MATRIX']) && $arResult['PRICE_MATRIX']): // USE_PRICE_COUNT?>
                                        <?if(\CMax::GetFrontParametrValue('SHOW_POPUP_PRICE') == 'Y' || $arResult['ITEM_PRICE_MODE'] == 'Q' || (\CMax::GetFrontParametrValue('SHOW_POPUP_PRICE') != 'Y' && $arResult['ITEM_PRICE_MODE'] != 'Q' && count($arResult['PRICE_MATRIX']['COLS']) <= 1)):?>
                                            <?=CMax::showPriceRangeTop($arResult, $arParams, Loc::getMessage("CATALOG_ECONOMY"));?>
                                        <?endif;?>
                                        <?if(count($arResult['PRICE_MATRIX']['ROWS']) > 1 || count($arResult['PRICE_MATRIX']['COLS']) > 1):?>
                                            <?if (count($arResult['PRICE_MATRIX']['CAN_BUY']) == 2 && in_array('7',$arResult['PRICE_MATRIX']['CAN_BUY']) && in_array('8',$arResult['PRICE_MATRIX']['CAN_BUY'])):?>
                                                <?=showPriceMatrixCustomForDetail($arResult, $arParams, $strMeasure, $arAddToBasketData);?>
                                            <?else:?>
                                                <?=CMax::showPriceMatrix($arResult, $arParams, $strMeasure, $arAddToBasketData);?>
                                            <?endif;?>
                                        <?endif;?>
                                        <div class="" itemprop="offers" itemscope itemtype="http://schema.org/Offer">
                                            <meta itemprop="price" content="<?=($arResult['MIN_PRICE']['DISCOUNT_VALUE'] ? $arResult['MIN_PRICE']['DISCOUNT_VALUE'] : $arResult['MIN_PRICE']['VALUE'])?>" />
                                            <meta itemprop="priceCurrency" content="<?=$arResult['MIN_PRICE']['CURRENCY']?>" />
                                            <link itemprop="availability" href="http://schema.org/<?=($arResult['PRICE_MATRIX']['AVAILABLE'] == 'Y' ? 'InStock' : 'OutOfStock')?>" />
                                            <?
                                            if($arDiscount["ACTIVE_TO"]){?>
                                                <meta itemprop="priceValidUntil" content="<?=date("Y-m-d", MakeTimeStamp($arDiscount["ACTIVE_TO"]))?>" />
                                            <?}?>
                                            <link itemprop="url" href="<?=$arResult["DETAIL_PAGE_URL"]?>" />
                                        </div>
                                    <?php elseif (isset($arResult["PRICES"])): ?>

                                        <?\Aspro\Functions\CAsproMaxItem::showItemPrices($arParams, $arResult["PRICES"], $strMeasure, $min_price_id, ($arParams["SHOW_DISCOUNT_PERCENT_NUMBER"] == "Y" ? "N" : "Y"));?>

                                    <?php endif;?>
                                <?php endif; ?>

                                <?// quantity ?>
                                <span class="price__status"><?=$arQuantityData["HTML"];?></span>

                                <?//for product wo POPUP_PRICE in fixed header?>
                                <?if($arParams['SHOW_POPUP_PRICE'] !== "Y" && !$arResult["OFFERS"]):?>
                                    <script>
                                        <?if(isset($arResult['PRICE_MATRIX']) && $arResult['PRICE_MATRIX']): // USE_PRICE_COUNT?>
                                        <?$priceHtml = CMax::showPriceMatrix($arResult, $arParams, $strMeasure, $arAddToBasketData);?>
                                        <?$countPricesMatrix = count($arResult['PRICE_MATRIX']['MATRIX'])?>
                                        <?$countPricesRows = count($arResult['PRICE_MATRIX']['ROWS'])?>
                                        <?$countPrices = ($countPricesMatrix > $countPricesRows ? $countPricesMatrix : $countPricesRows)?>
                                        BX.message({
                                            ASPRO_ITEM_PRICE_MATRIX: <?=CUtil::PhpToJSObject($priceHtml, false, true);?>
                                        })
                                        <?elseif($arResult["PRICES"]):?>
                                        <?$priceHtml = \Aspro\Functions\CAsproMaxItem::showItemPrices($arParams, $arResult["PRICES"], $strMeasure, $min_price_id, ($arParams["SHOW_DISCOUNT_PERCENT_NUMBER"] == "Y" ? "N" : "Y"), false, true);?>
                                        <?$countPrices = count($arResult['PRICES'])?>
                                        BX.message({
                                            ASPRO_ITEM_PRICE: <?=CUtil::PhpToJSObject($priceHtml, false, true);?>
                                        })
                                        <?endif;?>
                                        BX.message({
                                            ASPRO_ITEM_POPUP_PRICE: 'Y',
                                            ASPRO_ITEM_PRICES: <?=$countPrices;?>
                                        })
                                    </script>
                                <?endif;?>

                                <?//for offer product wo POPUP_PRICE in fixed header?>
                                <?if($arParams['SHOW_POPUP_PRICE'] !== "Y" && $arCurrentSKU):?>
                                    <script>
                                        <?if(isset($arCurrentSKU['PRICE_MATRIX']) && $arCurrentSKU['PRICE_MATRIX']): // USE_PRICE_COUNT?>
                                        <?$priceHtml = CMax::showPriceMatrix($arCurrentSKU, $arParams, $strMeasure, $arAddToBasketData);?>
                                        <?$countPricesMatrix = count($arCurrentSKU['PRICE_MATRIX']['MATRIX'])?>
                                        <?$countPricesRows = count($arCurrentSKU['PRICE_MATRIX']['ROWS'])?>
                                        <?$countPrices = ($countPricesMatrix > $countPricesRows ? $countPricesMatrix : $countPricesRows)?>
                                        BX.message({
                                            ASPRO_ITEM_PRICE_MATRIX: <?=CUtil::PhpToJSObject($priceHtml, false, true);?>
                                        })
                                        <?elseif($arCurrentSKU["PRICES"]):?>
                                        <?$priceHtml = \Aspro\Functions\CAsproMaxItem::showItemPrices($arParams, $arCurrentSKU["PRICES"], $strMeasure, $min_price_id, ($arParams["SHOW_DISCOUNT_PERCENT_NUMBER"] == "Y" ? "N" : "Y"), false, true);?>
                                        <?$countPrices = count($arCurrentSKU['PRICES'])?>
                                        BX.message({
                                            ASPRO_ITEM_PRICE: <?=CUtil::PhpToJSObject($priceHtml, false, true);?>
                                        })
                                        <?endif;?>
                                        BX.message({
                                            ASPRO_ITEM_POPUP_PRICE: 'Y',
                                            ASPRO_ITEM_PRICES: <?=$countPrices;?>
                                        })
                                    </script>
                                <?endif;?>
                            </div> <? // end price-inner ?>
                        </div>

                        <? // price && buttons ?>
                        <?php if ($bComplect): ?>
                            <div class="complect_prices_block">
                            <div class="cost prices detail prices_block">
                                <div class="prices-wrapper">
                                    <div class="price font-bold font_mxs">
                                        <div class="price_value_block values_wrapper">
                                            <span class="price_value complect_price_value">0</span>
                                            <span class="price_currency">
                                                                <?//$arResult['MIN_PRICE']['CURRENCY']?>
                                                                <?=str_replace("999", "", \CCurrencyLang::CurrencyFormat("999", $arResult["CURRENCIES"][0]["CURRENCY"]))?>
                                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="buy_complect_wrap hidden">
                                <span data-currency="RUB" class="button_buy_complect opt_action btn btn-default btn-sm no-action" data-action="buy" data-iblock_id="<?=$arParams["IBLOCK_ID"]?>"><span><?=\Bitrix\Main\Config\Option::get("aspro.max", "EXPRESSION_ADDTOBASKET_BUTTON_DEFAULT", GetMessage("EXPRESSION_ADDTOBASKET_BUTTON_DEFAULT"));?></span></span>
                            </div>
                            <span class="btn btn-default btn-lg type_block has-ripple choise btn-wide" data-block=".js-scroll-complect"><span><?=Loc::getMessage("COMPLECT_BUTTON")?></span></span>
                        </div>
                        <?php else: ?>

                            <div class="product__purchase purchase">
                            <?php $frame = $this->createFrame()->begin('');?>
                                <?//buttons?>
                                <div class="buy_block product__purchase-inner">
                                <?php if (!$arResult["OFFERS"]): ?>
                                    <script>$(document).ready(function(){$('.catalog_detail input[data-sid="PRODUCT_NAME"]').attr('value', $('h1').text());});</script>
                                    <div class="counter_wrapper">
                                        <?php if (($arAddToBasketData["OPTIONS"]["USE_PRODUCT_QUANTITY_DETAIL"] && $arAddToBasketData["ACTION"] == "ADD") && $arAddToBasketData["CAN_BUY"]) :?>
                                        <? // \Aspro\Functions\CAsproMax::showItemCounter($arAddToBasketData, $arResult["ID"], $arItemIDs, $arParams, 'md', '', true, true);?>
                                        <?php endif;?>

                                        <div onmousedown="try { rrApi.addToBasket('<?=$arResult["ID"]?>') } catch(e) {}" id="<? echo $arItemIDs["ALL_ITEM_IDS"]['BASKET_ACTIONS']; ?>" class="button_block <?=(($arAddToBasketData["ACTION"] == "ORDER" /*&& !$arResult["CAN_BUY"]*/) || !$arAddToBasketData["CAN_BUY"] || !$arAddToBasketData["OPTIONS"]["USE_PRODUCT_QUANTITY_DETAIL"] || ($arAddToBasketData["ACTION"] == "SUBSCRIBE" && $arResult["CATALOG_SUBSCRIBE"] == "Y")  ? "wide" : "");?>">
                                            <!--noindex-->

                                            <?=$arAddToBasketData["HTML"]?>
                                            <!--/noindex-->
                                        </div>
                                    </div>
                                    <?php if (isset($arResult['PRICE_MATRIX']) && $arResult['PRICE_MATRIX']) // USE_PRICE_COUNT
                                    { ?>
                                        <?if($arResult['ITEM_PRICE_MODE'] == 'Q' && count($arResult['PRICE_MATRIX']['ROWS']) > 1):?>
                                            <?$arOnlyItemJSParams = array(
                                                "ITEM_PRICES" => $arResult["ITEM_PRICES"],
                                                "ITEM_PRICE_MODE" => $arResult["ITEM_PRICE_MODE"],
                                                "ITEM_QUANTITY_RANGES" => $arResult["ITEM_QUANTITY_RANGES"],
                                                "MIN_QUANTITY_BUY" => $arAddToBasketData["MIN_QUANTITY_BUY"],
                                                "SHOW_DISCOUNT_PERCENT_NUMBER" => $arParams["SHOW_DISCOUNT_PERCENT_NUMBER"],
                                                "ID" => $arItemIDs["strMainID"],
                                            )?>
                                            <script type="text/javascript">
                                                var <? echo $arItemIDs["strObName"]; ?>el = new JCCatalogOnlyElement(<? echo CUtil::PhpToJSObject($arOnlyItemJSParams, false, true); ?>);
                                            </script>
                                        <?endif;?>
                                    <? } ?>
                                    <?php if($arAddToBasketData["ACTION"] !== "NOTHING"): ?>
                                        <?=\Aspro\Functions\CAsproMax::showItemOCB($arAddToBasketData, $arResult, $arParams, false, ' round-ignore ');?>
                                    <?php endif;?>
                                <?php elseif ($arResult["OFFERS"] && $arParams['TYPE_SKU'] == 'TYPE_1'): ?>
                                    <div class="offer_buy_block buys_wrapp">
                                        <div class="counter_wrapp big clearfix">
                                            <?=\Aspro\Functions\CAsproMax::showItemCounter($arAddToBasketData, $arResult["OFFERS"][$arResult["OFFERS_SELECTED"]]["ID"], $arItemIDs, $arParams, 'md', '', true, true);?>
                                            <div id="<?=$arItemIDs["ALL_ITEM_IDS"]['BASKET_ACTIONS']; ?>" class="button_block <?=($arAddToBasketData["ACTION"] == "ORDER" || !$arAddToBasketData["CAN_BUY"] || !$arAddToBasketData["OPTIONS"]["USE_PRODUCT_QUANTITY_DETAIL"] || $arAddToBasketData["ACTION"] == "SUBSCRIBE" ? "wide" : "");?>">
                                                <!--noindex-->
                                                <?=$arAddToBasketData["HTML"]?>
                                                <!--/noindex-->
                                            </div>
                                        </div>
                                        <?php if (isset($arCurrentSKU['PRICE_MATRIX']) && $arCurrentSKU['PRICE_MATRIX']) // USE_PRICE_COUNT
                                        { ?>
                                            <?php if ($arCurrentSKU['ITEM_PRICE_MODE'] == 'Q' && count($arCurrentSKU['PRICE_MATRIX']['ROWS']) > 1) : ?>
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
                                                var <? echo $arItemIDs["strObName"]; ?>el = new JCCatalogOnlyElement(<? echo CUtil::PhpToJSObject($arOnlyItemJSParams, false, true); ?>);
                                            </script>
                                        <?php endif;?>
                                        <? } ?>
                                        <?php if ($arAddToBasketData["ACTION"] !== "NOTHING") : ?>
                                            <?=\Aspro\Functions\CAsproMax::showItemOCB($arAddToBasketData, $arResult["OFFERS"][$arResult["OFFERS_SELECTED"]], $arParams, false, ' round-ignore ');?>
                                        <?php endif;?>
                                    </div>
                                <?php elseif($arResult["OFFERS"] && $arParams['TYPE_SKU'] != 'TYPE_1'):?>
                                    <span class="btn btn-default btn-lg slide_offer type_block"><i></i><span><?=\Bitrix\Main\Config\Option::get("aspro.max", "EXPRESSION_READ_MORE_OFFERS_DEFAULT", GetMessage("MORE_TEXT_BOTTOM"));?></span></span>
                                <?php endif;?>
                            </div>
                            <?php $frame->end();?>
                        <?php endif;?>

                        <?//services?>
                        <div class="js-services"></div>

                    </div>

                    <div class="product__disclaimer product__text-additional">
                        <?$path = SITE_DIR."include/element_detail_text.php"?>
                        <div class="product__disclaimer-text <?=((CMax::checkContentFile($path) ? ' filed' : ''));?>">
                            <?$APPLICATION->IncludeFile($path, Array(), Array("MODE" => "html",  "NAME" => GetMessage('CT_BCE_CATALOG_DOP_DESCR')));?>
                        </div>
                        <?if($arParams['SHOW_SEND_GIFT'] != 'N'):?>
                            <?$sCurrentPage = (CMain::IsHTTPS()) ? "https://" : "http://";
                            $sCurrentPage .= $_SERVER["HTTP_HOST"];
                            $sCurrentPage .= $APPLICATION->GetCurPage();?>
                            <div class="text-form muted ncolor">
                                <div class="price_txt muted777 font_sxs ">
                                    <?=CMax::showIconSvg("info_big pull-left", SITE_TEMPLATE_PATH.'/images/svg/catalog/iwantgift.svg', '', '', true, false);?>
                                    <div class="text-form-info">
                                        <span><span class="animate-load dotted" data-event="jqm" data-param-form_id="SEND_GIFT" data-name="send_gift" data-autoload-product_name="<?=CMax::formatJsName($arResult["NAME"]);?>" data-autoload-product_link="<?=$sCurrentPage;?>" data-autoload-product_id="<?=$arResult["ID"];?>"><?=($arParams["SEND_GIFT_FORM_NAME"] ? $arParams["SEND_GIFT_FORM_NAME"] : GetMessage("SEND_GIFT_FORM"));?></span></span>
                                    </div>
                                </div>
                            </div>
                        <?endif;?>
                        <?if($arParams["SHOW_CHEAPER_FORM"] == "Y"):?>
                        <div class="quantity_block_wrapper">
                            <div class="cheaper_form muted777 font_sxs">
                                <?=CMax::showIconSvg("cheaper", SITE_TEMPLATE_PATH.'/images/svg/catalog/cheaper.svg', '', '', true, false);?>
                                <span class="animate-load dotted" data-event="jqm" data-param-form_id="CHEAPER" data-name="cheaper" data-autoload-product_name="<?=CMax::formatJsName($arCurrentSKU ? $arCurrentSKU['NAME'] : $arResult['NAME']);?>" data-autoload-product_id="<?=$arCurrentSKU ? $arCurrentSKU['ID'] : $arResult['ID'];?>"><?=($arParams["CHEAPER_FORM_NAME"] ? $arParams["CHEAPER_FORM_NAME"] : Loc::getMessage("CHEAPER"));?></span>
                            </div>
                        </div>
                        <?endif;?>
                    </div>
                    <div class="product__options options">
                        <div class="color">
                            <?php if (!empty($arResult["PROPERTIES"]['TSVET']['VALUE'])) : ?>
                                <div class="options__title title">Цвет</div>
                                <ul class="options__wrapper">
                                    <? if ($_POST['ajax'] == 'Y') { ?>
                                        <a href="#" onclick="UpdateParamAjaxCatalogElement(this); return false" class="active d-inline-block position-relative" data-id="<?= $arResult["ID"] ?>"><img width="56px" height="56px" src="<?= $arResult['PREVIEW_PICTURE']['SRC'] ?>" loading="lazy" title="<?= $arResult["PROPERTIES"]['TSVET']['VALUE'] ?>"></a>
                                    <? } else { /*?>
                                        <a href="<?= $APPLICATION->GetCurPage(false); ?>" class="active d-inline-block position-relative" data-name="<?= $name ?>" data-color="<?= $arResult["PROPERTIES"]['TSVET']['VALUE'] ?>">
                                            <img width="56px" height="56px" src="<?= $arResult['PREVIEW_PICTURE']['SRC'] ?>" loading="lazy" title="<?= $arResult["PROPERTIES"]['TSVET']['VALUE'] ?>">
                                        </a>
                                    <? */} ?>
                                </ul>
                            <?php endif ?>
                        </div>
                    </div>

                    <div class="product__delivery delivery">
                        <div class="delivery__title title">Доставка</div>
                        <?//delivery calculate?>
                        <?if(
                            (
                                !$arResult["OFFERS"] &&
                                $arAddToBasketData["ACTION"] == "ADD" &&
                                $arAddToBasketData["CAN_BUY"] &&
                                !$bComplect
                            ) ||
                            (
                                $arResult["OFFERS"] &&
                                $arParams['TYPE_SKU'] === 'TYPE_1'
                            )
                        ):?>
                            <?$productIdForDelivery = $arCurrentSKU ? $arCurrentSKU['ID'] : $arResult['ID'];?>
                            <?=\Aspro\Functions\CAsproMax::showCalculateDeliveryBlock($productIdForDelivery, $arParams);?>
                        <?endif;?>
                        <?//help text?>
                        <?if($arResult['HELP_TEXT']):?>
                            <div class="delivery__paragraph-wrapper">
                                <? //CMax::showIconSvg("info_big pull-left", SITE_TEMPLATE_PATH.'/images/svg/catalog/info_big.svg', '', '', true, false);?>
                                <div class="delivery__paragraph">
                                    <?php
                                    if(!$arResult['HELP_TEXT_FILE']):?>
                                        <?=$arResult['HELP_TEXT'];?>
                                    <?php else:?>
                                        <?$APPLICATION->IncludeComponent(
                                            "bitrix:main.include",
                                            "",
                                            Array(
                                                "AREA_FILE_SHOW" => "page",
                                                "AREA_FILE_SUFFIX" => "help_text",
                                                "EDIT_TEMPLATE" => ""
                                            )
                                        );?>
                                    <?php endif;?>
                                </div>
                            </div>
                        <?endif;?>
                    </div>

                    <?//brand?>
                    <?$this->SetViewTarget('PRODUCT_SIDE_INFO', 900);?>
                        <?if($arResult['BRAND_ITEM']):?>
                            <div class="brand-detail">
                                <div class="brand-detail-info bordered rounded3">
                                    <?if($arResult['BRAND_ITEM']["IMAGE"]):?>
                                        <div class="brand-detail-info__image"><a href="<?=$arResult['BRAND_ITEM']["DETAIL_PAGE_URL"];?>"><img src="<?=$arResult['BRAND_ITEM']["IMAGE"]["src"];?>" alt="<?=$arResult['BRAND_ITEM']["NAME"];?>" title="<?=$arResult['BRAND_ITEM']["NAME"];?>" itemprop="image"></a></div>
                                    <?endif;?>
                                    <div class="brand-detail-info__preview">
                                        <?if($arResult['BRAND_ITEM']["PREVIEW_TEXT"]):?>
                                            <div class="text muted777 font_xs"><?=$arResult['BRAND_ITEM']["PREVIEW_TEXT"];?></div>
                                        <?endif;?>
                                        <?if($arResult['SECTION']):?>
                                            <div class="link font_xs"><a href="<?= $arResult['BRAND_ITEM']['CATALOG_PAGE_URL'] ?>" target="_blank"><?=GetMessage("ITEMS_BY_SECTION")?></a></div>
                                        <?endif;?>
                                        <div class="link font_xs"><a href="<?=$arResult['BRAND_ITEM']["DETAIL_PAGE_URL"];?>" target="_blank"><?=GetMessage("ITEMS_BY_BRAND", array("#BRAND#" => $arResult['BRAND_ITEM']["NAME"]))?></a></div>
                                    </div>
                                </div>
                            </div>
                        <?endif;?>
                    <?$this->EndViewTarget();?>

                        <? // mob description ?>
                        <div class="product__info product__info--mobile">
                            <div class="product__description description">
                                <div class="description__title title"><?=($arParams["T_DESC"] ? $arParams["T_DESC"] : Loc::getMessage("T_DESC"));?></div>
                                <?if($arResult['DETAIL_TEXT'] || $bOfferDetailText ):?>
                                    <?$templateData['DETAIL_TEXT'] = true;?>
                                    <div class="description__paragraph" itemprop="description">
                                        <?if($bOfferDetailText):?>
                                            <?=$arCurrentSKU["DETAIL_TEXT"];?>
                                        <?else:?>
                                            <?=$arResult['DETAIL_TEXT'];?>
                                        <?endif;?>
                                    </div>
                                <?endif;?>
                            </div>
                            <?foreach($arResult["DISPLAY_PROPERTIES"] as $arProp):
                                if ($arProp["CODE"] != "UKHOD")
                                    continue;
                                ?>
                                <div class="product__description description" itemprop="additionalProperty" itemscope itemtype="http://schema.org/PropertyValue">
                                    <div class="description__title title" itemprop="name"><?=$arProp["NAME"]?></div>
                                    <p class="description__paragraph" itemprop="value">
                                        <?=$arProp["DISPLAY_VALUE"];?>
                                    </p>
                                </div>
                            <?endforeach;?>
                        </div>

                        <div class="product__specs specs">

                            <div class="specs__column">
                                <div class="specs__title title">Характеристики</div>
                                <ul class="specs__list">
                                    <?php
                                    $arPropCode = array("UKHOD", "VGKH_SHIRINA_M", "VGKH_VYSOTA_M", "VGKH_DLINA_M", "VGKH_VES_KG");
                                    foreach($arResult["DISPLAY_PROPERTIES"] as $propCode => $arProp):
                                        if( in_array($arProp["CODE"], $arPropCode) )
                                            continue;
                                        ?>
                                        <li class="specs__item js-prop" itemprop="additionalProperty" itemscope itemtype="http://schema.org/PropertyValue">
                                            <div class="specs__caption">
                                                <span class="js-prop-title" itemprop="name"><?=$arProp["NAME"]?></span>
                                            </div>
                                            <span class="specs__value js-prop-value" itemprop="value">
                                                <?if(is_array($arProp["DISPLAY_VALUE"]) && count($arProp["DISPLAY_VALUE"]) > 1):?>
                                                    <?=implode(', ', $arProp["DISPLAY_VALUE"]);?>
                                                <?else:?>
                                                    <?=$arProp["DISPLAY_VALUE"];?>
                                                <?endif;?>
                                            </span>
                                        </li>
                                    <?endforeach;?>
                                    <?foreach($arResult['OFFER_PROP'] as $propCode => $arProp):?>
                                        <li class="specs__item js-prop" itemprop="additionalProperty" itemscope itemtype="http://schema.org/PropertyValue">
                                            <div class="specs__caption">
                                                <span class="js-prop-title" itemprop="name"><?=$arProp["NAME"]?></span>
                                            </div>
                                            <span class="specs__value js-prop-value" itemprop="value">
                                                <?if(is_array($arProp["DISPLAY_VALUE"]) && count($arProp["DISPLAY_VALUE"]) > 1):?>
                                                    <?=implode(', ', $arProp["DISPLAY_VALUE"]);?>
                                                <?else:?>
                                                    <?=$arProp["DISPLAY_VALUE"];?>
                                                <?endif;?>
                                            </span>
                                        </li>
                                    <?endforeach;?>
                                </ul>
                            </div>
                            <div class="specs__column">
                                <div class="specs__title title">Упаковка</div>
                                <ul class="specs__list">
                                    <?php
                                    $arPropCode = array("VGKH_SHIRINA_M", "VGKH_VYSOTA_M", "VGKH_DLINA_M", "VGKH_VES_KG");
                                    foreach($arResult["DISPLAY_PROPERTIES"] as $propCode => $arProp):
                                        if(!in_array($arProp["CODE"], $arPropCode))
                                            continue;
                                        ?>
                                        <li class="specs__item js-prop" itemprop="additionalProperty" itemscope itemtype="http://schema.org/PropertyValue">
                                            <div class="specs__caption">
                                                <span class="js-prop-title" itemprop="name"><?=$arProp["NAME"]?></span>
                                            </div>
                                            <span class="specs__value js-prop-value" itemprop="value">
                                                <?if(is_array($arProp["DISPLAY_VALUE"]) && count($arProp["DISPLAY_VALUE"]) > 1):?>
                                                    <?=implode(', ', $arProp["DISPLAY_VALUE"]);?>
                                                <?else:?>
                                                    <?=$arProp["DISPLAY_VALUE"];?> <?= ($arProp["CODE"] == "VGKH_VES_KG" ? 'кг' : 'см') ;?>
                                                <?endif;?>
                                            </span>
                                        </li>
                                    <?endforeach;?>
                                </ul>
                            </div>
                        </div>


                </div>
            </div>
        </div>
    </div>

            <?$bPriceCount = ($arParams['USE_PRICE_COUNT'] == 'Y');?>
            <?if($arResult['OFFERS']):?>
                <span itemprop="offers" itemscope itemtype="http://schema.org/AggregateOffer" style="display:none;">
                    <meta itemprop="offerCount" content="<?=count($arResult['OFFERS'])?>" />
                    <meta itemprop="lowPrice" content="<?=($arResult['MIN_PRICE']['DISCOUNT_VALUE'] ? $arResult['MIN_PRICE']['DISCOUNT_VALUE'] : $arResult['MIN_PRICE']['VALUE'] )?>" />
                    <meta itemprop="highPrice" content="<?=($arResult['MAX_PRICE']['DISCOUNT_VALUE'] ? $arResult['MAX_PRICE']['DISCOUNT_VALUE'] : $arResult['MAX_PRICE']['VALUE'] )?>" />
                    <meta itemprop="priceCurrency" content="<?=$arResult['MIN_PRICE']['CURRENCY']?>" />
                    <?foreach($arResult['OFFERS'] as $arOffer):?>
                        <?$currentOffersList = array();?>
                        <?foreach($arOffer['TREE'] as $propName => $skuId):?>
                            <?$propId = (int)substr($propName, 5);?>
                            <?foreach($arResult['SKU_PROPS'] as $prop):?>
                                <?if($prop['ID'] == $propId):?>
                                    <?foreach($prop['VALUES'] as $propId => $propValue):?>
                                        <?if($propId == $skuId):?>
                                            <?$currentOffersList[] = $propValue['NAME'];?>
                                            <?break;?>
                                        <?endif;?>
                                    <?endforeach;?>
                                <?endif;?>
                            <?endforeach;?>
                        <?endforeach;?>
                        <span itemprop="offers" itemscope itemtype="http://schema.org/Offer">
                            <meta itemprop="sku" content="<?=implode('/', $currentOffersList)?>" />
                            <a href="<?=$arOffer['DETAIL_PAGE_URL']?>" itemprop="url"></a>
                            <meta itemprop="price" content="<?=($arOffer['MIN_PRICE']['DISCOUNT_VALUE']) ? $arOffer['MIN_PRICE']['DISCOUNT_VALUE'] : $arOffer['MIN_PRICE']['VALUE']?>" />
                            <meta itemprop="priceCurrency" content="<?=$arOffer['MIN_PRICE']['CURRENCY']?>" />
                            <link itemprop="availability" href="http://schema.org/<?=($arOffer['CAN_BUY'] ? 'InStock' : 'OutOfStock')?>" />
                            <?
                            if($arDiscount["ACTIVE_TO"]){?>
                                <meta itemprop="priceValidUntil" content="<?=date("Y-m-d", MakeTimeStamp($arDiscount["ACTIVE_TO"]))?>" />
                            <?}?>
                        </span>
                    <?endforeach;?>
                </span>
                <?unset($arOffer, $currentOffersList);?>
            <?else:?>
                <?if(!$bPriceCount):?>
                    <span itemprop="offers" itemscope itemtype="http://schema.org/Offer">
                        <meta itemprop="price" content="<?=($arResult['MIN_PRICE']['DISCOUNT_VALUE'] ? $arResult['MIN_PRICE']['DISCOUNT_VALUE'] : $arResult['MIN_PRICE']['VALUE'])?>" />
                        <meta itemprop="priceCurrency" content="<?=$arResult['MIN_PRICE']['CURRENCY']?>" />
                        <link itemprop="availability" href="http://schema.org/<?=($arResult['MIN_PRICE']['CAN_BUY'] ? 'InStock' : 'OutOfStock')?>" />
                        <?
                        if($arDiscount["ACTIVE_TO"]){?>
                            <meta itemprop="priceValidUntil" content="<?=date("Y-m-d", MakeTimeStamp($arDiscount["ACTIVE_TO"]))?>" />
                        <?}?>
                        <link itemprop="url" href="<?=$arResult["DETAIL_PAGE_URL"]?>" />
                    </span>
                <?endif;?>
            <?endif;?>

            <script type="text/javascript">
                BX.message({
                    QUANTITY_AVAILIABLE: '<? echo COption::GetOptionString("aspro.max", "EXPRESSION_FOR_EXISTS", GetMessage("EXPRESSION_FOR_EXISTS_DEFAULT"), SITE_ID); ?>',
                    QUANTITY_NOT_AVAILIABLE: '<? echo COption::GetOptionString("aspro.max", "EXPRESSION_FOR_NOTEXISTS", GetMessage("EXPRESSION_FOR_NOTEXISTS"), SITE_ID); ?>',
                    ADD_ERROR_BASKET: '<? echo GetMessage("ADD_ERROR_BASKET"); ?>',
                    ADD_ERROR_COMPARE: '<? echo GetMessage("ADD_ERROR_COMPARE"); ?>',
                    ONE_CLICK_BUY: '<? echo GetMessage("ONE_CLICK_BUY"); ?>',
                    MORE_TEXT_BOTTOM: '<?=\Bitrix\Main\Config\Option::get("aspro.max", "EXPRESSION_READ_MORE_OFFERS_DEFAULT", GetMessage("MORE_TEXT_BOTTOM"));?>',
                    TYPE_SKU: '<? echo $arParams['TYPE_SKU']; ?>',
                    HAS_SKU_PROPS: '<? echo ($arResult['OFFERS_PROP'] ? 'Y' : 'N'); ?>',
                    SITE_ID: '<? echo SITE_ID; ?>'
                })
            </script>
        </div>
    </div>
</section>


<?//additional gallery?>
<?if($arResult['ADDITIONAL_GALLERY']):?>
	<?$bShowSmallGallery = $arParams['ADDITIONAL_GALLERY_TYPE'] === 'SMALL';?>
	<?$templateData['ADDITIONAL_GALLERY'] = true;
	$additionalGallery = $arCurrentSKU ? $arResult['ADDITIONAL_GALLERY'][$arCurrentSKU['ID']] : $arResult['ADDITIONAL_GALLERY'];
	?>
	<?$this->SetViewTarget('PRODUCT_ADDITIONAL_GALLERY_INFO');?>
		<div class="ordered-block">
			<div class="additional-gallery <?=($arResult['OFFERS'] && 'TYPE_1' === $arParams['TYPE_SKU'] && !$arResult['ADDITIONAL_GALLERY'][$arCurrentSKU['ID']] ? ' hidden' : '')?>">
				<div class="ordered-block__title option-font-bold font_lg">
					<?=$arParams['BLOCK_ADDITIONAL_GALLERY_NAME'];?>
				</div>
				<?//switch gallery?>
				<div class="switch-item-block">
					<div class="flexbox flexbox--row">
						<div class="switch-item-block__count muted777 font_xs">
							<div class="switch-item-block__count-wrapper switch-item-block__count-wrapper--small" <?=($bShowSmallGallery ? "" : "style='display:none;'");?>>
								<span class="switch-item-block__count-value"><?=count($additionalGallery);?></span>
								<?=Loc::getMessage('PHOTO');?>
								<span class="switch-item-block__count-separate">&mdash;</span>
							</div>
							<div class="switch-item-block__count-wrapper switch-item-block__count-wrapper--big" <?=($bShowSmallGallery ? "style='display:none;'" : "");?>>
								<span class="switch-item-block__count-value">1/<?=count($additionalGallery);?></span>
								<span class="switch-item-block__count-separate">&mdash;</span>
							</div>
						</div>
						<div class="switch-item-block__icons-wrapper">
							<span class="switch-item-block__icons<?=(!$bShowSmallGallery ? ' active' : '');?> switch-item-block__icons--big" title="<?=Loc::getMessage("BIG_GALLERY");?>"><?=CMax::showIconSvg("gallery", SITE_TEMPLATE_PATH."/images/svg/gallery_alone.svg", "", "colored_theme_hover_bg-el-svg", true, false);?></span>
							<span class="switch-item-block__icons<?=($bShowSmallGallery ? ' active' : '');?> switch-item-block__icons--small" title="<?=Loc::getMessage("SMALL_GALLERY");?>"><?=CMax::showIconSvg("gallery", SITE_TEMPLATE_PATH."/images/svg/gallery_list.svg", "", "colored_theme_hover_bg-el-svg", true, false);?></span>
						</div>
					</div>
				</div>

				<?//big gallery?>
				<div class="big-gallery-block"<?=($bShowSmallGallery ? ' style="display:none;"' : '');?> >
					<div class="owl-carousel owl-theme owl-bg-nav short-nav" data-plugin-options='{"items": "1", "autoplay" : false, "autoplayTimeout" : "3000", "smartSpeed":1000, "dots": false, "nav": true, "loop": false, "index": true, "margin": 5}'>
						<?foreach($additionalGallery as $i => $arPhoto):?>
							<div class="item">
								<a href="<?=$arPhoto['DETAIL']['SRC']?>" class="fancy" data-fancybox="big-gallery" target="_blank" title="<?=$arPhoto['TITLE']?>">
									<img data-src="<?=$arPhoto['PREVIEW']['src']?>" src="<?=\Aspro\Functions\CAsproMax::showBlankImg($arPhoto['PREVIEW']['src']);?>" class="img-responsive inline lazy" title="<?=$arPhoto['TITLE']?>" alt="<?=$arPhoto['ALT']?>" />
								</a>
							</div>
						<?endforeach;?>
					</div>
				</div>

				<?//small gallery?>
				<div class="small-gallery-block"<?=($bShowSmallGallery ? '' : ' style="display:none;"');?>>
					<div class="row flexbox flexbox--row">
						<?foreach($additionalGallery as $i => $arPhoto):?>
							<div class="col-md-3 col-sm-4 col-xs-6">
								<div class="item">
									<div class="wrap"><a href="<?=$arPhoto['DETAIL']['SRC']?>" class="fancy" data-fancybox="small-gallery" target="_blank" title="<?=$arPhoto['TITLE']?>">
										<img data-src="<?=$arPhoto['PREVIEW']['src']?>" src="<?=\Aspro\Functions\CAsproMax::showBlankImg($arPhoto['PREVIEW']['src']);?>" class="lazy img-responsive inline" title="<?=$arPhoto['TITLE']?>" alt="<?=$arPhoto['ALT']?>" /></a>
									</div>
								</div>
							</div>
						<?endforeach;?>
					</div>
				</div>
			</div>
		</div>
	<?$this->EndViewTarget();?>
<?endif;?>

<?//custom tab?>
<?if($arParams['SHOW_ADDITIONAL_TAB'] == 'Y'):?>
	<?$this->SetViewTarget('PRODUCT_CUSTOM_TAB_INFO');?>
		<?$APPLICATION->IncludeFile(SITE_DIR."include/additional_products_description.php", array(), array("MODE" => "html", "NAME" => GetMessage('CT_BCE_CATALOG_ADDITIONAL_DESCRIPTION')));?>
	<?$this->EndViewTarget();?>
<?endif;?>
<?//props content?>
<?if(($arResult['DISPLAY_PROPERTIES'] || $arResult['OFFER_PROP']) && ($iCountProps > $arParams['VISIBLE_PROP_COUNT'])):?>
	<?$templateData['CHARACTERISTICS'] = true;?>
	<?$this->SetViewTarget('PRODUCT_PROPS_INFO');?>
		<?$strGrupperType = $arParams["GRUPPER_PROPS"];?>
		<?if($strGrupperType == "GRUPPER"):?>
			<div class="char_block bordered rounded3 js-scrolled">
				<?$APPLICATION->IncludeComponent(
					"redsign:grupper.list",
					"",
					Array(
						"CACHE_TIME" => "3600000",
						"CACHE_TYPE" => "A",
						"COMPOSITE_FRAME_MODE" => "A",
						"COMPOSITE_FRAME_TYPE" => "AUTO",
						"DISPLAY_PROPERTIES" => $arResult["GROUPS_PROPS"]
					),
					$component, array('HIDE_ICONS'=>'Y')
				);?>
				<table class="props_list" id="<? echo $arItemIDs["ALL_ITEM_IDS"]['DISPLAY_PROP_DIV']; ?>"></table>
			</div>
		<?elseif($strGrupperType == "WEBDEBUG"):?>
			<div class="char_block bordered rounded3 js-scrolled">
				<?$APPLICATION->IncludeComponent(
					"webdebug:propsorter",
					"linear",
					array(
						"IBLOCK_TYPE" => $arResult['IBLOCK_TYPE'],
						"IBLOCK_ID" => $arResult['IBLOCK_ID'],
						"PROPERTIES" => $arResult['GROUPS_PROPS'],
						"EXCLUDE_PROPERTIES" => array(),
						"WARNING_IF_EMPTY" => "N",
						"WARNING_IF_EMPTY_TEXT" => "",
						"NOGROUP_SHOW" => "Y",
						"NOGROUP_NAME" => "",
						"MULTIPLE_SEPARATOR" => ", "
					),
					$component, array('HIDE_ICONS'=>'Y')
				);?>
				<table class="props_list" id="<? echo $arItemIDs["ALL_ITEM_IDS"]['DISPLAY_PROP_DIV']; ?>"></table>
			</div>
		<?elseif($strGrupperType == "YENISITE_GRUPPER"):?>
			<div class="char_block bordered rounded3 js-scrolled">
				<?$APPLICATION->IncludeComponent(
					'yenisite:ipep.props_groups',
					'',
					array(
						'DISPLAY_PROPERTIES' => $arResult['GROUPS_PROPS'],
						'IBLOCK_ID' => $arParams['IBLOCK_ID']
					),
					$component, array('HIDE_ICONS'=>'Y')
				)?>
				<table class="props_list colored_char" id="<? echo $arItemIDs["ALL_ITEM_IDS"]['DISPLAY_PROP_DIV']; ?>"></table>
			</div>
		<?else:?>
			<?if($arParams["PROPERTIES_DISPLAY_TYPE"] != "TABLE"):?>
				<div class="props_block js-scrolled clearfix flexbox row js-offers-prop" id="<? echo $arItemIDs["ALL_ITEM_IDS"]['DISPLAY_PROP_DIV']; ?>">
					<?foreach($arResult["DISPLAY_PROPERTIES"] as $propCode => $arProp):?>
						<div class="char js-prop-replace col-lg-3 col-md-4 col-xs-6 bordered" itemprop="additionalProperty" itemscope itemtype="http://schema.org/PropertyValue">
							<div class="char_name">
								<div class="props_item muted <?if($arProp["HINT"] && $arParams["SHOW_HINTS"] == "Y"){?>whint<?}?>">
									<span itemprop="name" class="js-prop-title"><?=$arProp["NAME"]?></span>
								</div>
								<?if($arProp["HINT"] && $arParams["SHOW_HINTS"]=="Y"):?><div class="hint"><span class="icon"><i>?</i></span><div class="tooltip"><?=$arProp["HINT"]?></div></div><?endif;?>
							</div>
							<div class="char_value darken js-prop-value" itemprop="value">
								<?if(is_array($arProp["DISPLAY_VALUE"]) && count($arProp["DISPLAY_VALUE"]) > 1):?>
									<?=implode(', ', $arProp["DISPLAY_VALUE"]);?>
								<?else:?>
									<?=$arProp["DISPLAY_VALUE"];?>
								<?endif;?>
							</div>
						</div>
					<?endforeach;?>
					<?//offers props?>
					<?foreach($arResult['OFFER_PROP'] as $propCode => $arProp):?>
						<div class="char js-prop col-lg-3 col-md-4 col-xs-6 bordered" itemprop="additionalProperty" itemscope itemtype="http://schema.org/PropertyValue">
							<div class="char_name">
								<div class="props_item muted <?if($arProp["HINT"] && $arParams["SHOW_HINTS"] == "Y"){?>whint<?}?>">
									<span itemprop="name" class="js-prop-title"><?=$arProp["NAME"]?></span>
								</div>
								<?if($arProp["HINT"] && $arParams["SHOW_HINTS"]=="Y"):?><div class="hint"><span class="icon"><i>?</i></span><div class="tooltip"><?=$arProp["HINT"]?></div></div><?endif;?>
							</div>
							<div class="char_value darken js-prop-value" itemprop="value">
								<?if(is_array($arProp["DISPLAY_VALUE"]) && count($arProp["DISPLAY_VALUE"]) > 1):?>
									<?=implode(', ', $arProp["DISPLAY_VALUE"]);?>
								<?else:?>
									<?=$arProp["DISPLAY_VALUE"];?>
								<?endif;?>
							</div>
						</div>
					<?endforeach;?>
				</div>
			<?else:?>
				<div class="char_block bordered rounded3 js-scrolled">
					<table class="props_list nbg">
						<tbody class="js-offers-prop">
							<?foreach($arResult["DISPLAY_PROPERTIES"] as $arProp):?>
								<tr class="js-prop-replace" itemprop="additionalProperty" itemscope itemtype="http://schema.org/PropertyValue">
									<td class="char_name">
										<div class="props_item <?if($arProp["HINT"] && $arParams["SHOW_HINTS"] == "Y"){?>whint<?}?>">
											<span itemprop="name" class="js-prop-title"><?=$arProp["NAME"]?></span>
											<?if($arProp["HINT"] && $arParams["SHOW_HINTS"]=="Y"):?><div class="hint"><span class="icon"><i>?</i></span><div class="tooltip"><?=$arProp["HINT"]?></div></div><?endif;?>
										</div>
									</td>
									<td class="char_value">
										<span class="js-prop-value" itemprop="value">
											<?if(is_array($arProp["DISPLAY_VALUE"]) && count($arProp["DISPLAY_VALUE"]) > 1):?>
												<?=implode(', ', $arProp["DISPLAY_VALUE"]);?>
											<?else:?>
												<?=$arProp["DISPLAY_VALUE"];?>
											<?endif;?>
										</span>
									</td>
								</tr>
							<?endforeach;?>

							<?foreach($arResult["OFFER_PROP"] as $arProp):?>
								<tr class="js-prop" itemprop="additionalProperty" itemscope itemtype="http://schema.org/PropertyValue">
									<td class="char_name">
										<div class="props_item <?if($arProp["HINT"] && $arParams["SHOW_HINTS"] == "Y"){?>whint<?}?>">
											<span itemprop="name" class="js-prop-title"><?=$arProp["NAME"]?></span>
											<?if($arProp["HINT"] && $arParams["SHOW_HINTS"]=="Y"):?><div class="hint"><span class="icon"><i>?</i></span><div class="tooltip"><?=$arProp["HINT"]?></div></div><?endif;?>
										</div>
									</td>
									<td class="char_value">
										<span class="js-prop-value" itemprop="value">
											<?if(is_array($arProp["DISPLAY_VALUE"]) && count($arProp["DISPLAY_VALUE"]) > 1):?>
												<?=implode(', ', $arProp["DISPLAY_VALUE"]);?>
											<?else:?>
												<?=$arProp["DISPLAY_VALUE"];?>
											<?endif;?>
										</span>
									</td>
								</tr>
							<?endforeach;?>
						</tbody>
					</table>
					<table class="props_list nbg" id="<? echo $arItemIDs["ALL_ITEM_IDS"]['DISPLAY_PROP_DIV']; ?>"></table>
				</div>
			<?endif;?>
		<?endif;?>
	<?$this->EndViewTarget();?>
<?endif;?>

<?if($arParams["SHOW_HOW_BUY"] == "Y"):?>
	<?$this->SetViewTarget('PRODUCT_HOW_BUY_INFO');?>
		<?$APPLICATION->IncludeFile(SITE_DIR."include/tab_catalog_detail_howbuy.php", array(), array("MODE" => "html", "NAME" => GetMessage('TITLE_HOW_BUY')));?>
	<?$this->EndViewTarget();?>
<?endif;?>

<?if($arParams["SHOW_PAYMENT"] == "Y"):?>
	<?$this->SetViewTarget('PRODUCT_PAYMENT_INFO');?>
		<?$APPLICATION->IncludeFile(SITE_DIR."include/tab_catalog_detail_payment.php", array(), array("MODE" => "html", "NAME" => GetMessage('TITLE_PAYMENT')));?>
	<?$this->EndViewTarget();?>
<?endif;?>

<?if($arParams["SHOW_DELIVERY"] == "Y"):?>
	<?$this->SetViewTarget('PRODUCT_DELIVERY_INFO');?>
		<?$APPLICATION->IncludeFile(SITE_DIR."include/tab_catalog_detail_delivery.php", array(), array("MODE" => "html", "NAME" => GetMessage('TITLE_DELIVERY')));?>
	<?$this->EndViewTarget();?>
<?endif;?>

<?if($arResult['VIDEO']):?>
	<?$this->SetViewTarget('PRODUCT_VIDEO_INFO');?>
		<div class="hidden_print">
			<div class="video_block row">
				<?if(count($arResult['VIDEO']) > 1):?>
					<?foreach($arResult['VIDEO'] as $v => $value):?>
						<div class="col-sm-6">
							<?=str_replace('src=', 'width="660" height="457" src=', str_replace(array('width', 'height'), array('data-width', 'data-height'), $value));?>
						</div>
					<?endforeach;?>
				<?else:?>
					<div class="col-md-12"><?=$arResult['VIDEO'][0]?></div>
				<?endif;?>
			</div>
		</div>
	<?$this->EndViewTarget();?>
<?endif;?>

<?//files?>
<?$instr_prop = ($arParams["DETAIL_DOCS_PROP"] ? $arParams["DETAIL_DOCS_PROP"] : "INSTRUCTIONS");?>
<?
$bItemFiles = isset($arResult["PROPERTIES"][$instr_prop]) && (is_array($arResult["PROPERTIES"][$instr_prop]["VALUE"]) && count($arResult["PROPERTIES"][$instr_prop]["VALUE"]));
$bSectionFiles = isset($arResult["SECTION_FULL"]["UF_FILES"]) && is_array($arResult["SECTION_FULL"]["UF_FILES"]) && count($arResult["SECTION_FULL"]["UF_FILES"]);
?>
<?if($bItemFiles || $bSectionFiles):?>
	<?
	$arFiles = array();
	if($arResult["PROPERTIES"][$instr_prop]["VALUE"])
	{
		$arFiles = $arResult["PROPERTIES"][$instr_prop]["VALUE"];
	}
	else
	{
		$arFiles = $arResult["SECTION_FULL"]["UF_FILES"];
	}
	if(is_array($arFiles))
	{
		foreach($arFiles as $key => $value)
		{
			if(!intval($value))
			{
				unset($arFiles[$key]);
			}
		}
	}
	$templateData['FILES'] = $arFiles;
	if($arFiles):?>
		<?$this->SetViewTarget('PRODUCT_FILES_INFO');?>
			<div class="char_block rounded3 bordered files_block">
				<div class="row flexbox">
					<?foreach($arFiles as $arItem):?>
						<div class="col-md-4 col-sm-6 col-xs-12">
							<?$arFile=CMax::GetFileInfo($arItem);?>
							<div class="file_type clearfix <?=$arFile["TYPE"];?>">
								<i class="icon"></i>
								<div class="description">
									<a target="_blank" href="<?=$arFile["SRC"];?>" class="dark_link font_sm"><?=$arFile["DESCRIPTION"];?></a>
									<span class="size font_xs muted">
										<?=$arFile["FILE_SIZE_FORMAT"];?>
									</span>
								</div>
							</div>
						</div>
					<?endforeach;?>
				</div>
			</div>
		<?$this->EndViewTarget();?>
	<?endif;?>
<?endif;?>

<?
if($arResult['CATALOG'] && $actualItem['CAN_BUY'] && $arParams['USE_PREDICTION'] === 'Y' && \Bitrix\Main\ModuleManager::isModuleInstalled('sale')){
	$APPLICATION->IncludeComponent(
		'bitrix:sale.prediction.product.detail',
		'main',
		array(
			'BUTTON_ID' => false,
			'CUSTOM_SITE_ID' => isset($arParams['CUSTOM_SITE_ID']) ? $arParams['CUSTOM_SITE_ID'] : null,
			'POTENTIAL_PRODUCT_TO_BUY' => array(
				'ID' => $arResult['ID'],
				'MODULE' => isset($arResult['MODULE']) ? $arResult['MODULE'] : 'catalog',
				'PRODUCT_PROVIDER_CLASS' => isset($arResult['PRODUCT_PROVIDER_CLASS']) ? $arResult['PRODUCT_PROVIDER_CLASS'] : 'CCatalogProductProvider',
				'QUANTITY' => isset($arResult['QUANTITY']) ? $arResult['QUANTITY'] : null,
				'IBLOCK_ID' => $arResult['IBLOCK_ID'],
				'PRIMARY_OFFER_ID' => isset($arResult['OFFERS'][0]['ID']) ? $arResult['OFFERS'][0]['ID'] : null,
				'SECTION' => array(
					'ID' => isset($arResult['SECTION']['ID']) ? $arResult['SECTION']['ID'] : null,
					'IBLOCK_ID' => isset($arResult['SECTION']['IBLOCK_ID']) ? $arResult['SECTION']['IBLOCK_ID'] : null,
					'LEFT_MARGIN' => isset($arResult['SECTION']['LEFT_MARGIN']) ? $arResult['SECTION']['LEFT_MARGIN'] : null,
					'RIGHT_MARGIN' => isset($arResult['SECTION']['RIGHT_MARGIN']) ? $arResult['SECTION']['RIGHT_MARGIN'] : null,
				),
			)
		),
		$component,
		array('HIDE_ICONS' => 'Y')
	);
}
?>
<?$this->SetViewTarget('PRODUCT_GIFT_INFO');?>
<div class="gifts">
<?if ($arResult['CATALOG'] && $arParams['USE_GIFTS_DETAIL'] == 'Y' && \Bitrix\Main\ModuleManager::isModuleInstalled("sale"))
{
	$APPLICATION->IncludeComponent("bitrix:sale.gift.product", "main", array(
			"USE_REGION" => $arParams['USE_REGION'] !== 'N' ? 'Y' : 'N',
			"STORES" => $arParams['STORES'],
			"SHOW_UNABLE_SKU_PROPS"=>$arParams["SHOW_UNABLE_SKU_PROPS"],
			'PRODUCT_ID_VARIABLE' => $arParams['PRODUCT_ID_VARIABLE'],
			'ACTION_VARIABLE' => $arParams['ACTION_VARIABLE'],
			'BUY_URL_TEMPLATE' => $arResult['~BUY_URL_TEMPLATE'],
			'ADD_URL_TEMPLATE' => $arResult['~ADD_URL_TEMPLATE'],
			'SUBSCRIBE_URL_TEMPLATE' => $arResult['~SUBSCRIBE_URL_TEMPLATE'],
			'COMPARE_URL_TEMPLATE' => $arResult['~COMPARE_URL_TEMPLATE'],
			"OFFER_HIDE_NAME_PROPS" => $arParams["OFFER_HIDE_NAME_PROPS"],

			"SHOW_DISCOUNT_PERCENT" => "N",
			"SHOW_OLD_PRICE" => $arParams['GIFTS_SHOW_OLD_PRICE'],
			"PAGE_ELEMENT_COUNT" => $arParams['GIFTS_DETAIL_PAGE_ELEMENT_COUNT'],
			"LINE_ELEMENT_COUNT" => $arParams['GIFTS_DETAIL_PAGE_ELEMENT_COUNT'],
			"HIDE_BLOCK_TITLE" => $arParams['GIFTS_DETAIL_HIDE_BLOCK_TITLE'],
			"BLOCK_TITLE" => $arParams['GIFTS_DETAIL_BLOCK_TITLE'],
			"TEXT_LABEL_GIFT" => $arParams['GIFTS_DETAIL_TEXT_LABEL_GIFT'],
			"SHOW_NAME" => $arParams['GIFTS_SHOW_NAME'],
			"SHOW_IMAGE" => $arParams['GIFTS_SHOW_IMAGE'],
			"MESS_BTN_BUY" => $arParams['GIFTS_MESS_BTN_BUY'],

			"SHOW_PRODUCTS_{$arParams['IBLOCK_ID']}" => "Y",
			"HIDE_NOT_AVAILABLE" => $arParams["HIDE_NOT_AVAILABLE"],
			"PRODUCT_SUBSCRIPTION" => $arParams["PRODUCT_SUBSCRIPTION"],
			"MESS_BTN_DETAIL" => $arParams["MESS_BTN_DETAIL"],
			"MESS_BTN_SUBSCRIBE" => $arParams["MESS_BTN_SUBSCRIBE"],
			"TEMPLATE_THEME" => $arParams["TEMPLATE_THEME"],
			"PRICE_CODE" => $arParams["PRICE_CODE"],
			"SHOW_PRICE_COUNT" => $arParams["SHOW_PRICE_COUNT"],
			"PRICE_VAT_INCLUDE" => $arParams["PRICE_VAT_INCLUDE"],
			"CONVERT_CURRENCY" => $arParams["CONVERT_CURRENCY"],
			"CURRENCY_ID" => $arParams["CURRENCY_ID"],
			"BASKET_URL" => $arParams["BASKET_URL"],
			"ADD_PROPERTIES_TO_BASKET" => $arParams["ADD_PROPERTIES_TO_BASKET"],
			"PRODUCT_PROPS_VARIABLE" => $arParams["PRODUCT_PROPS_VARIABLE"],
			"PARTIAL_PRODUCT_PROPERTIES" => $arParams["PARTIAL_PRODUCT_PROPERTIES"],
			"USE_PRODUCT_QUANTITY" => 'N',
			"OFFER_TREE_PROPS_{$arResult['OFFERS_IBLOCK']}" => $arParams['OFFER_TREE_PROPS'],
			"CART_PROPERTIES_{$arResult['OFFERS_IBLOCK']}" => $arParams['OFFERS_CART_PROPERTIES'],
			"PRODUCT_QUANTITY_VARIABLE" => $arParams["PRODUCT_QUANTITY_VARIABLE"],
			"CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
			"CACHE_TYPE" => $arParams["CACHE_TYPE"],
			"CACHE_TIME" => $arParams["CACHE_TIME"],
			"SHOW_DISCOUNT_TIME" => "N",
			"SHOW_ONE_CLICK_BUY" => "N",
			"SHOW_DISCOUNT_PERCENT_NUMBER" => "N",
			"SALE_STIKER" => $arParams["SALE_STIKER"],
			"STIKERS_PROP" => $arParams["STIKERS_PROP"],
			"SHOW_OLD_PRICE" => $arParams["SHOW_OLD_PRICE"],
			"SHOW_MEASURE" => $arParams["SHOW_MEASURE"],
			"DISPLAY_TYPE" => "block",
			"SHOW_RATING" => $arParams["SHOW_RATING"],
			"DISPLAY_COMPARE" => $arParams["DISPLAY_COMPARE"],
			"DISPLAY_WISH_BUTTONS" => $arParams["DISPLAY_WISH_BUTTONS"],
			"DEFAULT_COUNT" => $arParams["DEFAULT_COUNT"],
			"TYPE_SKU" => "Y",

			"POTENTIAL_PRODUCT_TO_BUY" => array(
				'ID' => isset($arResult['ID']) ? $arResult['ID'] : null,
				'MODULE' => isset($arResult['MODULE']) ? $arResult['MODULE'] : 'catalog',
				'PRODUCT_PROVIDER_CLASS' => isset($arResult['PRODUCT_PROVIDER_CLASS']) ? $arResult['PRODUCT_PROVIDER_CLASS'] : 'CCatalogProductProvider',
				'QUANTITY' => isset($arResult['QUANTITY']) ? $arResult['QUANTITY'] : null,
				'IBLOCK_ID' => isset($arResult['IBLOCK_ID']) ? $arResult['IBLOCK_ID'] : null,

				'PRIMARY_OFFER_ID' => isset($arResult['OFFERS'][0]['ID']) ? $arResult['OFFERS'][0]['ID'] : null,
				'SECTION' => array(
					'ID' => isset($arResult['SECTION']['ID']) ? $arResult['SECTION']['ID'] : null,
					'IBLOCK_ID' => isset($arResult['SECTION']['IBLOCK_ID']) ? $arResult['SECTION']['IBLOCK_ID'] : null,
					'LEFT_MARGIN' => isset($arResult['SECTION']['LEFT_MARGIN']) ? $arResult['SECTION']['LEFT_MARGIN'] : null,
					'RIGHT_MARGIN' => isset($arResult['SECTION']['RIGHT_MARGIN']) ? $arResult['SECTION']['RIGHT_MARGIN'] : null,
				),
			)
		), $component, array("HIDE_ICONS" => "Y"));
}
if ($arResult['CATALOG'] && $arParams['USE_GIFTS_MAIN_PR_SECTION_LIST'] == 'Y' && \Bitrix\Main\ModuleManager::isModuleInstalled("sale"))
{
	$APPLICATION->IncludeComponent(
			"bitrix:sale.gift.main.products",
			"main",
			array(
				"USE_REGION" => $arParams['USE_REGION'] !== 'N' ? 'Y' : 'N',
				"STORES" => $arParams['STORES'],
				"SHOW_UNABLE_SKU_PROPS"=>$arParams["SHOW_UNABLE_SKU_PROPS"],
				"PAGE_ELEMENT_COUNT" => $arParams['GIFTS_MAIN_PRODUCT_DETAIL_PAGE_ELEMENT_COUNT'],
				"BLOCK_TITLE" => $arParams['GIFTS_MAIN_PRODUCT_DETAIL_BLOCK_TITLE'],

				"OFFERS_FIELD_CODE" => $arParams["OFFERS_FIELD_CODE"],
				"OFFERS_PROPERTY_CODE" => $arParams["OFFERS_PROPERTY_CODE"],

				"AJAX_MODE" => $arParams["AJAX_MODE"],
				"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
				"IBLOCK_ID" => $arParams["IBLOCK_ID"],

				"ELEMENT_SORT_FIELD" => 'ID',
				"ELEMENT_SORT_ORDER" => 'DESC',
				//"ELEMENT_SORT_FIELD2" => $arParams["ELEMENT_SORT_FIELD2"],
				//"ELEMENT_SORT_ORDER2" => $arParams["ELEMENT_SORT_ORDER2"],
				"FILTER_NAME" => 'searchFilter',
				"SECTION_URL" => $arParams["SECTION_URL"],
				"DETAIL_URL" => $arParams["DETAIL_URL"],
				"BASKET_URL" => $arParams["BASKET_URL"],
				"ACTION_VARIABLE" => $arParams["ACTION_VARIABLE"],
				"PRODUCT_ID_VARIABLE" => $arParams["PRODUCT_ID_VARIABLE"],
				"SECTION_ID_VARIABLE" => $arParams["SECTION_ID_VARIABLE"],

				"CACHE_TYPE" => $arParams["CACHE_TYPE"],
				"SHOW_ONE_CLICK_BUY" => "N",
				"CACHE_TIME" => $arParams["CACHE_TIME"],

				"CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
				"SET_TITLE" => $arParams["SET_TITLE"],
				"PROPERTY_CODE" => "",
				"PRICE_CODE" => $arParams["PRICE_CODE"],
				"USE_PRICE_COUNT" => $arParams["USE_PRICE_COUNT"],
				"SHOW_PRICE_COUNT" => $arParams["SHOW_PRICE_COUNT"],

				"PRICE_VAT_INCLUDE" => $arParams["PRICE_VAT_INCLUDE"],
				"CONVERT_CURRENCY" => $arParams["CONVERT_CURRENCY"],
				"CURRENCY_ID" => $arParams["CURRENCY_ID"],
				"HIDE_NOT_AVAILABLE" => $arParams["HIDE_NOT_AVAILABLE"],
				"TEMPLATE_THEME" => (isset($arParams["TEMPLATE_THEME"]) ? $arParams["TEMPLATE_THEME"] : ""),

				"ADD_PICT_PROP" => (isset($arParams["ADD_PICT_PROP"]) ? $arParams["ADD_PICT_PROP"] : ""),

				"LABEL_PROP" => (isset($arParams["LABEL_PROP"]) ? $arParams["LABEL_PROP"] : ""),
				"OFFER_ADD_PICT_PROP" => (isset($arParams["OFFER_ADD_PICT_PROP"]) ? $arParams["OFFER_ADD_PICT_PROP"] : ""),
				"OFFER_TREE_PROPS" => (isset($arParams["OFFER_TREE_PROPS"]) ? $arParams["OFFER_TREE_PROPS"] : ""),
				"SHOW_DISCOUNT_PERCENT" => "N",
				"SHOW_OLD_PRICE" => (isset($arParams["SHOW_OLD_PRICE"]) ? $arParams["SHOW_OLD_PRICE"] : ""),
				"MESS_BTN_BUY" => (isset($arParams["MESS_BTN_BUY"]) ? $arParams["MESS_BTN_BUY"] : ""),
				"MESS_BTN_ADD_TO_BASKET" => (isset($arParams["MESS_BTN_ADD_TO_BASKET"]) ? $arParams["MESS_BTN_ADD_TO_BASKET"] : ""),
				"MESS_BTN_DETAIL" => (isset($arParams["MESS_BTN_DETAIL"]) ? $arParams["MESS_BTN_DETAIL"] : ""),
				"MESS_NOT_AVAILABLE" => (isset($arParams["MESS_NOT_AVAILABLE"]) ? $arParams["MESS_NOT_AVAILABLE"] : ""),
				'ADD_TO_BASKET_ACTION' => (isset($arParams["ADD_TO_BASKET_ACTION"]) ? $arParams["ADD_TO_BASKET_ACTION"] : ""),
				'SHOW_CLOSE_POPUP' => (isset($arParams["SHOW_CLOSE_POPUP"]) ? $arParams["SHOW_CLOSE_POPUP"] : ""),
				'DISPLAY_COMPARE' => (isset($arParams['DISPLAY_COMPARE']) ? $arParams['DISPLAY_COMPARE'] : ''),
				'COMPARE_PATH' => (isset($arParams['COMPARE_PATH']) ? $arParams['COMPARE_PATH'] : ''),
				"SHOW_DISCOUNT_TIME" => "N",
				"SHOW_DISCOUNT_PERCENT_NUMBER" => "N",
				"SALE_STIKER" => $arParams["SALE_STIKER"],
				"STIKERS_PROP" => $arParams["STIKERS_PROP"],
				"SHOW_MEASURE" => $arParams["SHOW_MEASURE"],
				"DISPLAY_TYPE" => "block",
				"SHOW_RATING" => $arParams["SHOW_RATING"],
				"DISPLAY_WISH_BUTTONS" => $arParams["DISPLAY_WISH_BUTTONS"],
				"DEFAULT_COUNT" => $arParams["DEFAULT_COUNT"],
			)
			+ array(
				'OFFER_ID' => empty($arResult['OFFERS'][$arResult['OFFERS_SELECTED']]['ID']) ? $arResult['ID'] : $arResult['OFFERS'][$arResult['OFFERS_SELECTED']]['ID'],
				'SECTION_ID' => $arResult['SECTION']['ID'],
				'ELEMENT_ID' => $arResult['ID'],
			),
			$component,
			array("HIDE_ICONS" => "Y")
	);
}
?>
</div>
<div id="mobile_video_container"></div>
<?$this->EndViewTarget();?>

<style>
    .product-view--type2 .product-info.product-info--type2>.flexbox .product-detail-gallery__slider{
        max-width:750px;

    }
    .product-container .product-detail-gallery__item.product-detail-gallery__item--middle{
        max-width: 750px;
        width:750px;
        height:550px;

    }
    .product-detail-gallery__slider.owl-carousel .owl-nav{
    	top: calc(70% - 20px);
    }

    @media(max-width: 1200px){
    	.product-view--type2 .product-info.product-info--type2>.flexbox .product-detail-gallery__slider{
    		max-width:550px;
    	}
    	.product-container .product-detail-gallery__item.product-detail-gallery__item--middle{
    		max-width: 550px;
    		width:550px;
    		height:480px;

    	}
    	.product-container .product-detail-gallery__item .product-detail-gallery__picture{
    		max-height: 460px
    	}
    }
    .product-info.product-info--type2>.flexbox>.product-detail-gallery .product-detail-gallery__container{
        padding-top: 2.133rem;
    }
    /*.product-container .owl-carousel .owl-nav{
        left: -5px!important;
        right: -5px!important;
    }*/
</style>


<?


$nav = CIBlockSection::GetNavChain(false, $arResult["SECTION"]['ID']);
   while($v = $nav->GetNext()) {

       if($v['ID']) {
	   Bitrix\Main\Diag\Debug::writeToFile('ID => ' . $v['ID']);
	   Bitrix\Main\Diag\Debug::writeToFile('NAME => ' . $v['NAME']);
	   Bitrix\Main\Diag\Debug::writeToFile('DEPTH_LEVEL => ' . $v['DEPTH_LEVEL']);
	   $resultSections[] = $v['NAME'];
       }
   }
?>

<script>
window.dataLayer = window.dataLayer || [];
dataLayer.push({
 'ecommerce': {
   'currencyCode': "<?=$arResult["CURRENCY_ID"]?>",
   'detail': {
     'actionField': {'list': 'Catalog'},
     'products': [{
       'name': "<?=$arResult["NAME"]?>",
       'id': "<?=$arResult["ID"]?>",
       'price': "<?=$arResult["MIN_PRICE"]['VALUE']?>",
       // 'brand': 'Бренд 1',
       'category': "<?=implode("/", $resultSections);?>"
     }]
   }
 },
 'event': 'gtm-ee-event',
 'gtm-ee-event-category': 'Enhanced Ecommerce',
 'gtm-ee-event-action': 'Product Details',
 'gtm-ee-event-non-interaction': 'True',
});
</script>


<script>
	$(document).on("click", ".to-cart:not(.read_more), .basket_item_add", function (e) {
		window.dataLayer = window.dataLayer || [];
		dataLayer.push({
			'ecommerce': {
				'currencyCode': "<?=$arResult["CURRENCY_ID"]?>",
				'add': {
					'products': [{
						'name': "<?=$arResult["NAME"]?>",
						'id': "<?=$arResult["ID"]?>",
						'price': "<?=$arResult["MIN_PRICE"]['VALUE']?>",
						// 'brand': 'Бренд 1',
						'category': "<?=implode("/", $resultSections);?>",
						'quantity': $(this).attr("data-quantity")
					}]
				}
			},
			'event': 'gtm-ee-event',
			'gtm-ee-event-category': 'Enhanced Ecommerce',
			'gtm-ee-event-action': 'Add to Cart',
			'gtm-ee-event-non-interaction': 'False',
		});
	})
</script>

<script type="text/javascript">
    (window["rrApiOnReady"] = window["rrApiOnReady"] || []).push(function() {
	try{ rrApi.view("<?=$arResult["ID"]?>"); } catch(e) {}
    })
</script>

<?php // print_r('end tamplate.php');?>
