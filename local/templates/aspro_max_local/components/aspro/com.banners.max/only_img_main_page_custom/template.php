<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<? $this->setFrameMode( true ); ?>

<?if($arResult['ITEMS']):?>
	<?foreach($arResult['ITEMS'] as $arItem):?>
	<?
	$this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arResult['IBLOCK_ID'], 'ELEMENT_EDIT'));
	$this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arResult['IBLOCK_ID'], 'ELEMENT_DELETE'), array('CONFIRM' => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
	?>
	

	<div id="<?=$this->GetEditAreaId($arItem['ID']);?>" class="content_wrapper_block">
					<div class="maxwidth-theme">
						<div class="item-views  ">
							<div class="items">
								<div class="row flexbox">
									<div class="col-lg-7 col-md-12 col-xs-12 col-xxs-12">
										<div class="bnr_clickable" style="background-color: <?if(!empty($arItem["PROPERTY_COLOR_BG_VALUE"])):?><?=$arItem["PROPERTY_COLOR_BG_VALUE"]?><?else:?> #647271 <?endif;?>; color: #E5DBD3;">
											<?
											if(!empty($arItem["PROPERTY_BG_BANNER_1_VALUE"])){
												$srcbg = CFile::GetPath($arItem["PROPERTY_BG_BANNER_1_VALUE"]);
											}
											if(!empty($arItem["PROPERTY__ACTIVE_BANNER_1_VALUE"])){
												$srcActive = CFile::GetPath($arItem["PROPERTY__ACTIVE_BANNER_1_VALUE"]);
											}
											?>
											<a class="bnr_clickable__image" href="<?=$arItem["PROPERTY_LINK_BANNER_1_VALUE"]?>" style="background-image: url(<?=$srcbg;?>)"></a>
											<div class="bnr_clickable__content">
												<a class="bnr_clickable__title" href="<?=$arItem["PROPERTY_LINK_BANNER_1_VALUE"]?>"><?=$arItem["PROPERTY_TITLE_BANNER_1_VALUE"]?></a>
												<a class="bnr_clickable__button" href="<?=$arItem["PROPERTY_LINK_BANNER_1_VALUE"]?>"><?=$arItem["PROPERTY_TEXT_BTN_BANNER_1_VALUE"]?></a>
											</div>
										</div>
										<div class="special_procucts_wrapper">
												<?//список товаров?>

												<?
												$GLOBALS['arrFilterProduct'] = array('!PROPERTY_SHOW_PRODUCT_MAIN' => false);?>
												<?$APPLICATION->IncludeComponent(
													"bitrix:catalog.section", 
													"catalog_block_main", 
													array(
														"USE_REGION" => ($arRegion?"Y":"N"),
														"STORES" => array(
															0 => "3",
															1 => "",
														),
														"SHOW_BIG_BLOCK" => "N",
														"IS_CATALOG_PAGE" => "Y",
														"SHOW_UNABLE_SKU_PROPS" => "Y",
														"ALT_TITLE_GET" => "NORMAL",
														"IBLOCK_TYPE" => "aspro_max_catalog",
														"IBLOCK_ID" => "3",
														"SHOW_COUNTER_LIST" => "Y",
														"SECTION_ID" => "",
														"SECTION_CODE" => "",
														"AJAX_REQUEST" => $isAjax,
														"ELEMENT_SORT_FIELD" => "shows",
														"ELEMENT_SORT_ORDER" => "desc",
														"SHOW_DISCOUNT_TIME_EACH_SKU" => "Y",
														"ELEMENT_SORT_FIELD2" => "sort",
														"ELEMENT_SORT_ORDER2" => "asc",
														"FILTER_NAME" => 'arrFilterProduct',
														"INCLUDE_SUBSECTIONS" => "Y",
														"PAGE_ELEMENT_COUNT" => "4",
														"LINE_ELEMENT_COUNT" => "3",
														"SET_LINE_ELEMENT_COUNT" => $bSetElementsLineRow,
														"DISPLAY_TYPE" => $display,
														"TYPE_SKU" => ($typeSKU?$typeSKU:$arTheme["TYPE_SKU"]["VALUE"]),
														"SET_SKU_TITLE" => ((($typeSKU=="TYPE_1"||$arTheme["TYPE_SKU"]["VALUE"]=="TYPE_1")&&$arTheme["CHANGE_TITLE_ITEM_LIST"]["VALUE"]=="Y")?"Y":""),
														"LIST_PROPERTY_CODE" => array(
															0 => "HIT",
															1 => "BRAND",
															2 => "CML2_ARTICLE",
															3 => "PROP_2104",
															4 => "PODBORKI",
															5 => "PROP_2033",
															6 => "COLOR_REF2",
															7 => "PROP_305",
															8 => "PROP_352",
															9 => "PROP_317",
															10 => "PROP_357",
															11 => "PROP_2102",
															12 => "PROP_318",
															13 => "PROP_159",
															14 => "PROP_349",
															15 => "PROP_327",
															16 => "PROP_2052",
															17 => "PROP_370",
															18 => "PROP_336",
															19 => "PROP_2115",
															20 => "PROP_346",
															21 => "PROP_2120",
															22 => "PROP_2053",
															23 => "PROP_363",
															24 => "PROP_320",
															25 => "PROP_2089",
															26 => "PROP_325",
															27 => "PROP_2103",
															28 => "PROP_2085",
															29 => "PROP_300",
															30 => "PROP_322",
															31 => "PROP_362",
															32 => "PROP_365",
															33 => "PROP_359",
															34 => "PROP_284",
															35 => "PROP_364",
															36 => "PROP_356",
															37 => "PROP_343",
															38 => "PROP_2083",
															39 => "PROP_314",
															40 => "PROP_348",
															41 => "PROP_316",
															42 => "PROP_350",
															43 => "PROP_333",
															44 => "PROP_332",
															45 => "PROP_360",
															46 => "PROP_353",
															47 => "PROP_347",
															48 => "PROP_25",
															49 => "PROP_2114",
															50 => "PROP_301",
															51 => "PROP_2101",
															52 => "PROP_2067",
															53 => "PROP_323",
															54 => "PROP_324",
															55 => "PROP_355",
															56 => "PROP_304",
															57 => "PROP_358",
															58 => "PROP_319",
															59 => "PROP_344",
															60 => "PROP_328",
															61 => "PROP_338",
															62 => "PROP_2065",
															63 => "PROP_366",
															64 => "PROP_302",
															65 => "PROP_303",
															66 => "PROP_2054",
															67 => "PROP_341",
															68 => "PROP_223",
															69 => "PROP_283",
															70 => "PROP_354",
															71 => "PROP_313",
															72 => "PROP_2066",
															73 => "PROP_329",
															74 => "PROP_342",
															75 => "PROP_367",
															76 => "PROP_2084",
															77 => "PROP_340",
															78 => "PROP_351",
															79 => "PROP_368",
															80 => "PROP_369",
															81 => "PROP_331",
															82 => "PROP_337",
															83 => "PROP_345",
															84 => "PROP_339",
															85 => "PROP_310",
															86 => "PROP_309",
															87 => "PROP_330",
															88 => "PROP_2017",
															89 => "PROP_335",
															90 => "PROP_321",
															91 => "PROP_308",
															92 => "PROP_206",
															93 => "PROP_334",
															94 => "PROP_2100",
															95 => "PROP_311",
															96 => "PROP_2132",
															97 => "SHUM",
															98 => "PROP_361",
															99 => "PROP_326",
															100 => "PROP_315",
															101 => "PROP_2091",
															102 => "PROP_2026",
															103 => "PROP_307",
															104 => "PROP_2027",
															105 => "PROP_2098",
															106 => "PROP_2122",
															107 => "PROP_24",
															108 => "PROP_2049",
															109 => "PROP_22",
															110 => "PROP_2095",
															111 => "PROP_2044",
															112 => "PROP_162",
															113 => "PROP_2055",
															114 => "PROP_2069",
															115 => "PROP_2062",
															116 => "PROP_2061",
															117 => "CML2_LINK",
															118 => "",
														),
														"SHOW_ARTICLE_SKU" => "Y",
														"SHOW_MEASURE_WITH_RATIO" => "Y",
														"MAX_SCU_COUNT_VIEW" => $arTheme["MAX_SCU_COUNT_VIEW"]["VALUE"],
														"OFFERS_FIELD_CODE" => array(
															0 => "NAME",
															1 => "CML2_LINK",
															2 => "DETAIL_PAGE_URL",
															3 => "",
														),
														"OFFERS_PROPERTY_CODE" => array(
															0 => "ARTICLE",
															1 => "SPORT",
															2 => "SIZES2",
															3 => "MORE_PHOTO",
															4 => "VOLUME",
															5 => "SIZES",
															6 => "SIZES5",
															7 => "SIZES4",
															8 => "SIZES3",
															9 => "COLOR_REF",
															10 => "",
														),
														"OFFERS_SORT_FIELD" => $arParams["OFFERS_SORT_FIELD"],
														"OFFERS_SORT_ORDER" => $arParams["OFFERS_SORT_ORDER"],
														"OFFERS_SORT_FIELD2" => $arParams["OFFERS_SORT_FIELD2"],
														"OFFERS_SORT_ORDER2" => $arParams["OFFERS_SORT_ORDER2"],
														"OFFER_TREE_PROPS" => $arParams["OFFER_TREE_PROPS"],
														"OFFER_SHOW_PREVIEW_PICTURE_PROPS" => $arParams["OFFER_SHOW_PREVIEW_PICTURE_PROPS"],
														"OFFERS_LIMIT" => $arParams["LIST_OFFERS_LIMIT"],
														//"SECTION_URL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["section"],
														//"DETAIL_URL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["element"],
														"BASKET_URL" => "/basket/",
														"ACTION_VARIABLE" => "action",
														"PRODUCT_ID_VARIABLE" => "id",
														"PRODUCT_QUANTITY_VARIABLE" => "quantity",
														"PRODUCT_PROPS_VARIABLE" => "prop",
														"MAX_GALLERY_ITEMS" => "5",
														"SHOW_GALLERY" => "Y",
														"SHOW_PROPS" => "N",
														"SHOW_POPUP_PRICE" => (CMax::GetFrontParametrValue("SHOW_POPUP_PRICE")=="Y"?"Y":"N"),
														"TYPE_VIEW_BASKET_BTN" => CMax::GetFrontParametrValue("TYPE_VIEW_BASKET_BTN"),
														"TYPE_VIEW_CATALOG_LIST" => CMax::GetFrontParametrValue("TYPE_VIEW_CATALOG_LIST"),
														"SHOW_STORES_POPUP" => (CMax::GetFrontParametrValue("STORES_SOURCE")=="STORES"&&$arParams["STORES"]),
														"SECTION_ID_VARIABLE" => "SECTION_ID",
														"SET_LAST_MODIFIED" => "N",
														"AJAX_MODE" => "N",
														"AJAX_OPTION_JUMP" => "N",
														"AJAX_OPTION_STYLE" => "N",
														"AJAX_OPTION_HISTORY" => "N",
														"CACHE_TYPE" => "A",
														"CACHE_TIME" => $arParams["CACHE_TIME"],
														"CACHE_GROUPS" => "N",
														"CACHE_FILTER" => "N",
														"META_KEYWORDS" => $arParams["LIST_META_KEYWORDS"],
														"META_DESCRIPTION" => $arParams["LIST_META_DESCRIPTION"],
														"BROWSER_TITLE" => "-",
														"ADD_SECTIONS_CHAIN" => "N",
														"HIDE_NOT_AVAILABLE" => "Y",
														"HIDE_NOT_AVAILABLE_OFFERS" => "Y",
														"DISPLAY_COMPARE" => "N",
														"USE_FAST_VIEW" => CMax::GetFrontParametrValue("USE_FAST_VIEW_PAGE_DETAIL"),
														"MANY_BUY_CATALOG_SECTIONS" => CMax::GetFrontParametrValue("MANY_BUY_CATALOG_SECTIONS"),
														"SET_TITLE" => "N",
														"SET_STATUS_404" => "N",
														"SHOW_404" => "N",
														"MESSAGE_404" => $arParams["MESSAGE_404"],
														"FILE_404" => $arParams["FILE_404"],
														"PRICE_CODE" => array(
															0 => "Онлайн Розница для ИНТЕРНЕТ МАГАЗИНА WMS",
															1 => "Онлайн Розница со скидкой для ИНТЕРНЕТ МАГАЗИНА WMS",
														),
														
														"USE_PRICE_COUNT" => "Y",
														"SHOW_PRICE_COUNT" =>"1",
														"PRICE_VAT_INCLUDE" => "Y",
														"USE_PRODUCT_QUANTITY" => "N",
														"OFFERS_CART_PROPERTIES" => "",
														"DISPLAY_TOP_PAGER" => "N",
														"DISPLAY_BOTTOM_PAGER" => "N",
														"PAGER_TITLE" => $arParams["PAGER_TITLE"],
														"PAGER_SHOW_ALWAYS" => "N",
														"PAGER_TEMPLATE" => $arParams["PAGER_TEMPLATE"],
														"PAGER_DESC_NUMBERING" => "N",
														"PAGER_DESC_NUMBERING_CACHE_TIME" => $arParams["PAGER_DESC_NUMBERING_CACHE_TIME"],
														"PAGER_SHOW_ALL" => "N",
														"AJAX_OPTION_ADDITIONAL" => "",
														"ADD_CHAIN_ITEM" => "N",
														"SHOW_QUANTITY" => $arParams["SHOW_QUANTITY"],
														"DETAIL_ADD_DETAIL_TO_SLIDER" => "Y",
														"OFFER_ADD_PICT_PROP" => "MORE_PHOTO",
														"SHOW_QUANTITY_COUNT" => $arParams["SHOW_QUANTITY_COUNT"],
														"SHOW_DISCOUNT_PERCENT_NUMBER" => "Y",
														"SHOW_DISCOUNT_PERCENT" => "Y",
														"SHOW_DISCOUNT_TIME" => "Y",
														"SHOW_ONE_CLICK_BUY" => $arParams["SHOW_ONE_CLICK_BUY"],
														"SHOW_OLD_PRICE" => "Y",
														"CONVERT_CURRENCY" => "N",
														"CURRENCY_ID" => $arParams["CURRENCY_ID"],
														"USE_STORE" => $arParams["USE_STORE"],
														"MAX_AMOUNT" => $arParams["MAX_AMOUNT"],
														"MIN_AMOUNT" => $arParams["MIN_AMOUNT"],
														"USE_MIN_AMOUNT" => $arParams["USE_MIN_AMOUNT"],
														"USE_ONLY_MAX_AMOUNT" => $arParams["USE_ONLY_MAX_AMOUNT"],
														"DISPLAY_WISH_BUTTONS" => $arParams["DISPLAY_WISH_BUTTONS"],
														"LIST_DISPLAY_POPUP_IMAGE" => "Y",
														"DEFAULT_COUNT" => "1",
														"SHOW_MEASURE" => "N",
														"SHOW_HINTS" => "Y",
														"USE_CUSTOM_RESIZE_LIST" => $arTheme["USE_CUSTOM_RESIZE_LIST"]["VALUE"],
														"OFFER_HIDE_NAME_PROPS" => "N",
														"SHOW_SECTIONS_LIST_PREVIEW" => "Y",
														"SECTIONS_LIST_PREVIEW_PROPERTY" => "UF_SECTION_DESCR",
														"SHOW_SECTION_LIST_PICTURES" => "Y",
														"USE_MAIN_ELEMENT_SECTION" => "N",
														"ADD_PROPERTIES_TO_BASKET" => "N",
														"PARTIAL_PRODUCT_PROPERTIES" => "N",
														"PRODUCT_PROPERTIES" => "",
														"SALE_STIKER" => "SALE_TEXT",
														"STIKERS_PROP" => "HIT",
														"SHOW_RATING" => "Y",
														"REVIEWS_VIEW" => (isset($arTheme["REVIEWS_VIEW"]["VALUE"])&&$arTheme["REVIEWS_VIEW"]["VALUE"]=="EXTENDED")||(!isset($arTheme["REVIEWS_VIEW"]["VALUE"])&&isset($arTheme["REVIEWS_VIEW"])&&$arTheme["REVIEWS_VIEW"]=="EXTENDED"),
														"ADD_PICT_PROP" => "MORE_PHOTO",
														"IBINHERIT_TEMPLATES" => $arSeoItem?$arIBInheritTemplates:array(),
														"FIELDS" => array(
															0 => "",
															1 => "",
														),
														"USER_FIELDS" => array(
															0 => "",
															1 => "UF_CATALOG_ICON",
															2 => "",
														),
														"SECTION_COUNT_ELEMENTS" => $arParams["SECTION_COUNT_ELEMENTS"],
														"SHOW_PROPS_TABLE" => $typeTableProps??strtolower(CMax::GetFrontParametrValue("SHOW_TABLE_PROPS")),
														"SHOW_OFFER_TREE_IN_TABLE" => CMax::GetFrontParametrValue("SHOW_OFFER_TREE_IN_TABLE"),
														"SHOW_FAST_VIEW_BTN_SEPARATELY" => "Y",
														"COMPONENT_TEMPLATE" => "catalog_block",
														"SECTION_USER_FIELDS" => array(
															0 => "",
															1 => "",
														),
														"SHOW_ALL_WO_SECTION" => "N",
														//"CUSTOM_FILTER" => "{\"CLASS_ID\":\"CondGroup\",\"DATA\":{\"All\":\"AND\",\"True\":\"True\"},\"CHILDREN\":[]}",
														"BACKGROUND_IMAGE" => "-",
														//"SEF_MODE" => "N",
														"SEF_URL_TEMPLATES" => array(
															"sections" => "",
															"section" => "#SECTION_CODE#/",
															"element" => "#SECTION_CODE#/#ELEMENT_CODE#/",
															"compare" => "compare.php?action=#ACTION_CODE#",
															"smart_filter" => "#SECTION_CODE_PATH#/filter/#SMART_FILTER_PATH#/apply/",
														),
														"SET_BROWSER_TITLE" => "N",
														"SET_META_KEYWORDS" => "N",
														"SET_META_DESCRIPTION" => "N",
														"PAGER_BASE_LINK_ENABLE" => "N",
														"COMPATIBLE_MODE" => "Y",
														"DISABLE_INIT_JS_IN_COMPONENT" => "N"
													),
													$component,
													array(
														"HIDE_ICONS" => $isAjax
													)
												);?>
												<?//список товаров?>
											<!-- put product list here -->
											
										</div>
									</div>
									<div class="col-lg-5 col-md-12 col-xs-12 col-xxs-12 visible-lg">
											<?
											if(!empty($arItem["PROPERTY_BG_BANNER_2_VALUE"])){
												$srcbg2 = CFile::GetPath($arItem["PROPERTY_BG_BANNER_2_VALUE"]);
											}
											if(!empty($arItem["PROPERTY__ACTIVE_BANNER_2_VALUE"])){
												$srcActive2 = CFile::GetPath($arItem["PROPERTY__ACTIVE_BANNER_2_VALUE"]);
											}
											?>
										<div class="bnr_clickable bnr_clickable--vertical" style="background-color:<?if(!empty($arItem["PROPERTY_COLOR_BG_2_VALUE"])):?><?=$arItem["PROPERTY_COLOR_BG_2_VALUE"]?><?else:?> #315577 <?endif;?> ; color: #E5DBD3;">
											<a class="bnr_clickable__image" href="<?=$arItem["PROPERTY_LINK_BANNER_2_VALUE"]?>" style="background-image: url(<?=$srcbg2;?>)"></a>
											<div class="bnr_clickable__content">
												<a class="bnr_clickable__button" href="<?=$arItem["PROPERTY_LINK_BANNER_2_VALUE"]?>"><?=$arItem["PROPERTY_TEXT_BTN_BANNER_2_VALUE"]?></a>
											</div>
										</div>
									</div>
									<div class="col-md-12 col-xs-12 col-xxs-12">
									<?
											if(!empty($arItem["PROPERTY_BG_BANNER_3_VALUE"])){
												$srcbg3 = CFile::GetPath($arItem["PROPERTY_BG_BANNER_3_VALUE"]);
											}
											if(!empty($arItem["PROPERTY__ACTIVE_BANNER_3_VALUE"])){
												$srcActive3 = CFile::GetPath($arItem["PROPERTY__ACTIVE_BANNER_3_VALUE"]);
											}
											?>
										<div class="bnr_clickable bnr_clickable--bottom" style="background-color: <?if(!empty($arItem["PROPERTY_COLOR_BG_3_VALUE"])):?><?=$arItem["PROPERTY_COLOR_BG_3_VALUE"]?><?else:?> #67696D <?endif;?> ; color: #E5DBD3;">
											<a class="bnr_clickable__image" href="<?=$arItem["PROPERTY_LINK_BANNER_3_VALUE"]?>" style="background-image: url(<?=$srcbg3;?>)"></a>
											<div class="bnr_clickable__content">
												<div class="bnr_clickable__pretitle"><?=$arItem["PROPERTY_DOP_TEXT_BANNER_3_VALUE"]?></div>
												<a class="bnr_clickable__title" href="<?=$arItem["PROPERTY_LINK_BANNER_3_VALUE"]?>"><?=$arItem["PROPERTY_TITLE_BANNER_3_VALUE"]?></a>
												<a class="bnr_clickable__button" href="<?=$arItem["PROPERTY_LINK_BANNER_3_VALUE"]?>"><?=$arItem["PROPERTY_TEXT_BTN_BANNER_3_VALUE"]?></a>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
	<?endforeach;?>
<?endif;?>	



