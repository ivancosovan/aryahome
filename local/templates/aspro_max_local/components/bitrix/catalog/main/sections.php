<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?$this->setFrameMode(true);?>
<?global $arTheme, $APPLICATION, $arSectionFilter;?>
<?$APPLICATION->AddViewContent('right_block_class', 'catalog_page ');?>

<?$arSectionFilter = array('IBLOCK_ID' => $arParams['IBLOCK_ID']);
CMax::makeSectionFilterInRegion($arSectionFilter);?>

<?// region filter for to count elements
if(
	$GLOBALS['arRegion'] &&
	$GLOBALS['arTheme']['USE_REGIONALITY']['VALUE'] === 'Y' &&
	$GLOBALS['arTheme']['USE_REGIONALITY']['DEPENDENT_PARAMS']['REGIONALITY_FILTER_ITEM']['VALUE'] === 'Y'
){
	// unrem this for hide empty sections without region`s products
	//$arSectionFilter['PROPERTY'] = array('LINK_REGION' => $GLOBALS['arRegion']['ID']);

	$arSectionFilter['PROPERTY_LINK_REGION'] = $GLOBALS['arRegion']['ID'];
}?>

<?$sViewElementTemplate = ($arParams["SECTIONS_TYPE_VIEW"] == "FROM_MODULE" ? $arTheme["CATALOG_PAGE_SECTIONS"]["VALUE"] : $arParams["SECTIONS_TYPE_VIEW"]);?>
<?$bShowLeftBlock = ($arTheme["LEFT_BLOCK_CATALOG_ROOT"]["VALUE"] == "Y" && !defined("ERROR_404") && !($arTheme['HEADER_TYPE']['VALUE'] == 28 || $arTheme['HEADER_TYPE']['VALUE'] == 29));?>
<?$APPLICATION->SetPageProperty("HIDE_LEFT_BLOCK", ( $bShowLeftBlock ? 'N' : 'Y' ) );?>

<div class="main-catalog-wrapper">
	<div class="section-content-wrapper <?=($bShowLeftBlock ? 'with-leftblock' : '');?>">
		<?
		global $NEW_FILTER;
	$preFilter = array();
	if($GLOBALS[ $_SESSION['SMART_FILTER_VAR'] ]["=PROPERTY_85"]){
		$preFilter = array(
			"PROPERTY_COLOR_FILTER" => $GLOBALS[ $_SESSION['SMART_FILTER_VAR'] ]["=PROPERTY_85"]
		);
	}

	if($GLOBALS[ $_SESSION['SMART_FILTER_VAR'] ]["=PROPERTY_45"]){
		$preFilter = array(
			array(
				'LOGIC' => 'OR',
				"PROPERTY_RAZMER" => $GLOBALS[ $_SESSION['SMART_FILTER_VAR'] ]["=PROPERTY_45"],
				"PROPERTY_OBSHCHIY_RAZMER_DLYA_SAYTA" => $GLOBALS[ $_SESSION['SMART_FILTER_VAR'] ]["=PROPERTY_45"],
			)
		);
		unset($GLOBALS[ $_SESSION['SMART_FILTER_VAR'] ]["=PROPERTY_45"]);
	}

	$arNames = array();
	$idForFilter = array();
	if(CModule::IncludeModule('iblock')) {
		$arSort= Array("NAME"=>"ASC");
		$arSelect = Array("ID","NAME","IBLOCK_ID","PROPERTY_NAIMENOVANIE_DLYA_SAYTA","PROPERTY_COLOR_FILTER");
		$arFilter = Array(
			"IBLOCK_ID" => array($arParams['IBLOCK_ID']),
			"ACTIVE" => 'Y',
			'INCLUDE_SUBSECTIONS' => 'Y',
			'>CATALOG_STORE_AMOUNT_3' => 0,
		);

		if(!$GLOBALS[ $_SESSION['SMART_FILTER_VAR'] ]['SECTION_ID']){
			$arFilter["!SECTION_ID"] = 0;
		}else{
			$arFilter["SECTION_ID"] = $GLOBALS[ $_SESSION['SMART_FILTER_VAR'] ]['SECTION_ID'];
		}


		$idForSmartFilter = array();
		$res = CIBlockElement::GetList($arSort, array_merge($arFilter,$preFilter), false, false, $arSelect);
		while($ob = $res->fetch()){
			if(!in_array($ob["PROPERTY_NAIMENOVANIE_DLYA_SAYTA_VALUE"], $arNames)){
				$idForFilter[] = $ob['ID'];
				$arNames[$ob['ID']] = $ob["PROPERTY_NAIMENOVANIE_DLYA_SAYTA_VALUE"];
			}
			$idForSmartFilter[] = $ob['ID'];
		}

		$pageNotFixCustomOffers = $arResult['VARIABLES']['SECTION_CODE'] == 'podarochnye_karty';

		if($idForFilter && !$pageNotFixCustomOffers){
			$GLOBALS[ $_SESSION['SMART_FILTER_VAR'] ]["=ID"] = $idForFilter;
		}
	}

// исключаем дубликаты END

	if($_POST['offer_ajax'] && ($_POST['product_color'] || $_POST['product_size'])){
		$id = $_POST['product_size'] ? $_POST['product_size'] : $_POST['product_color'];
		$GLOBALS[ $_SESSION['SMART_FILTER_VAR'] ] = array("ID" => $id);
	}
	$NEW_FILTER = $GLOBALS[ $_SESSION['SMART_FILTER_VAR'] ];



		?>
		
		<?@include_once('page_blocks/'.$sViewElementTemplate.'.php');?>
		<br/><br/>
		<?if(isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == "xmlhttprequest"  && isset($_GET["ajax_get"]) && $_GET["ajax_get"] == "Y" || (isset($_GET["ajax_basket"]) && $_GET["ajax_basket"]=="Y")){
			$isAjax="Y";
		}

		?>
		<div class="ajax_load cur block">
		<?if($isAjax=="Y"){
			$APPLICATION->RestartBuffer();
		}?>
<?

$sort_elem = 'show';
$sort_elem_order = 'asc';


?>
<script type="text/javascript">
    (window["rrApiOnReady"] = window["rrApiOnReady"] || []).push(function() {
        try { rrApi.categoryView("<?$arResult["VARIABLES"]["SECTION_ID"]?>"); } catch(e) {}
    })
</script>
		<?//дополнительные элементы?>
			<?$APPLICATION->IncludeComponent(
					"bitrix:catalog.section",
					"catalog_block",
					Array(
						"USE_REGION" => ($arRegion ? "Y" : "N"),
						"STORES" => $arParams['STORES'],
						"SHOW_BIG_BLOCK" => 'N',
						"IS_CATALOG_PAGE" => 'Y',
						"SHOW_UNABLE_SKU_PROPS"=>$arParams["SHOW_UNABLE_SKU_PROPS"],
						"ALT_TITLE_GET" => $arParams["ALT_TITLE_GET"],
						"SEF_URL_TEMPLATES" => $arParams["SEF_URL_TEMPLATES"],
						"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
						"IBLOCK_ID" => $arParams["IBLOCK_ID"],
						"SHOW_COUNTER_LIST" => $arParams["SHOW_COUNTER_LIST"],
						"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
						"SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"],
						"AJAX_REQUEST" => $isAjax,
						"ELEMENT_SORT_FIELD" => $sort_elem,
						"ELEMENT_SORT_ORDER" => $sort_elem_order,
						"SHOW_DISCOUNT_TIME_EACH_SKU" => $arParams["SHOW_DISCOUNT_TIME_EACH_SKU"],
						"ELEMENT_SORT_FIELD2" => $arParams["ELEMENT_SORT_FIELD2"],
						"ELEMENT_SORT_ORDER2" => $arParams["ELEMENT_SORT_ORDER2"],
						"FILTER_NAME" => 'NEW_FILTER',
						"INCLUDE_SUBSECTIONS" => $arParams["INCLUDE_SUBSECTIONS"],
						"PAGE_ELEMENT_COUNT" => $show,
						"LINE_ELEMENT_COUNT" => $arParams["LINE_ELEMENT_COUNT"],
						"SET_LINE_ELEMENT_COUNT" => $arParams["SET_LINE_ELEMENT_COUNT"],
						"DISPLAY_TYPE" => $display,
						"TYPE_SKU" => ($typeSKU ? $typeSKU : $arTheme["TYPE_SKU"]["VALUE"]),
						"SET_SKU_TITLE" => ((($typeSKU == "TYPE_1" || $arTheme["TYPE_SKU"]["VALUE"] == "TYPE_1") && $arTheme["CHANGE_TITLE_ITEM_LIST"]["VALUE"] == "Y") ? "Y" : ""),
						"PROPERTY_CODE" => $arParams["LIST_PROPERTY_CODE"],
						"SHOW_ARTICLE_SKU" => $arParams["SHOW_ARTICLE_SKU"],
						"SHOW_MEASURE_WITH_RATIO" => $arParams["SHOW_MEASURE_WITH_RATIO"],
						"MAX_SCU_COUNT_VIEW" => $arTheme['MAX_SCU_COUNT_VIEW']['VALUE'],
						"OFFERS_FIELD_CODE" => $arParams["LIST_OFFERS_FIELD_CODE"],
						"OFFERS_PROPERTY_CODE" => $arParams["LIST_OFFERS_PROPERTY_CODE"],
						"OFFERS_SORT_FIELD" => $arParams["OFFERS_SORT_FIELD"],
						"OFFERS_SORT_ORDER" => $arParams["OFFERS_SORT_ORDER"],
						"OFFERS_SORT_FIELD2" => $arParams["OFFERS_SORT_FIELD2"],
						"OFFERS_SORT_ORDER2" => $arParams["OFFERS_SORT_ORDER2"],
						'OFFER_TREE_PROPS' => $arParams['OFFER_TREE_PROPS'],
						'OFFER_SHOW_PREVIEW_PICTURE_PROPS' => $arParams['OFFER_SHOW_PREVIEW_PICTURE_PROPS'],
						"OFFERS_LIMIT" => $arParams["LIST_OFFERS_LIMIT"],
						"SECTION_URL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["section"],
						"DETAIL_URL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["element"],
						"BASKET_URL" => $arParams["BASKET_URL"],
						"ACTION_VARIABLE" => $arParams["ACTION_VARIABLE"],
						"PRODUCT_ID_VARIABLE" => $arParams["PRODUCT_ID_VARIABLE"],
						"PRODUCT_QUANTITY_VARIABLE" => "quantity",
						"PRODUCT_PROPS_VARIABLE" => "prop",
						"MAX_GALLERY_ITEMS" => $arParams["MAX_GALLERY_ITEMS"],
						"SHOW_GALLERY" => $arParams["SHOW_GALLERY"],
						"SHOW_PROPS" => "N",
						'SHOW_POPUP_PRICE' => (CMax::GetFrontParametrValue('SHOW_POPUP_PRICE') == 'Y' ? "Y" : "N"),
						'TYPE_VIEW_BASKET_BTN' => CMax::GetFrontParametrValue('TYPE_VIEW_BASKET_BTN'),
						'TYPE_VIEW_CATALOG_LIST' => CMax::GetFrontParametrValue('TYPE_VIEW_CATALOG_LIST'),
						'SHOW_STORES_POPUP' => (CMax::GetFrontParametrValue('STORES_SOURCE') == 'STORES' && $arParams['STORES']),
						"SECTION_ID_VARIABLE" => $arParams["SECTION_ID_VARIABLE"],
						"SET_LAST_MODIFIED" => $arParams["SET_LAST_MODIFIED"],
						"AJAX_MODE" => $arParams["AJAX_MODE"],
						"AJAX_OPTION_JUMP" => $arParams["AJAX_OPTION_JUMP"],
						"AJAX_OPTION_STYLE" => $arParams["AJAX_OPTION_STYLE"],
						"AJAX_OPTION_HISTORY" => $arParams["AJAX_OPTION_HISTORY"],
						"CACHE_TYPE" => $arParams["CACHE_TYPE"],
						"CACHE_TIME" => $arParams["CACHE_TIME"],
						"CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
						"CACHE_FILTER" => $arParams["CACHE_FILTER"],
						"META_KEYWORDS" => $arParams["LIST_META_KEYWORDS"],
						"META_DESCRIPTION" => $arParams["LIST_META_DESCRIPTION"],
						"BROWSER_TITLE" => $arParams["LIST_BROWSER_TITLE"],
						"ADD_SECTIONS_CHAIN" => ($iSectionsCount && $arParams['INCLUDE_SUBSECTIONS'] == "N") ? 'N' : $arParams["ADD_SECTIONS_CHAIN"],
						"HIDE_NOT_AVAILABLE" => $arParams["HIDE_NOT_AVAILABLE"],
						'HIDE_NOT_AVAILABLE_OFFERS' => $arParams["HIDE_NOT_AVAILABLE_OFFERS"],
						"DISPLAY_COMPARE" => CMax::GetFrontParametrValue('CATALOG_COMPARE'),
						"USE_FAST_VIEW" => CMax::GetFrontParametrValue('USE_FAST_VIEW_PAGE_DETAIL'),
						"MANY_BUY_CATALOG_SECTIONS" => CMax::GetFrontParametrValue('MANY_BUY_CATALOG_SECTIONS'),
						"SET_TITLE" => $arParams["SET_TITLE"],
						"SET_STATUS_404" => $arParams["SET_STATUS_404"],
						"SHOW_404" => $arParams["SHOW_404"],
						"MESSAGE_404" => $arParams["MESSAGE_404"],
						"FILE_404" => $arParams["FILE_404"],
						"PRICE_CODE" => $arParams['PRICE_CODE'],
						"USE_PRICE_COUNT" => $arParams["USE_PRICE_COUNT"],
						"SHOW_PRICE_COUNT" => $arParams["SHOW_PRICE_COUNT"],
						"PRICE_VAT_INCLUDE" => $arParams["PRICE_VAT_INCLUDE"],
						"USE_PRODUCT_QUANTITY" => $arParams["USE_PRODUCT_QUANTITY"],
						"OFFERS_CART_PROPERTIES" => $arParams["OFFERS_CART_PROPERTIES"],
						"DISPLAY_TOP_PAGER" => $arParams["DISPLAY_TOP_PAGER"],
						"DISPLAY_BOTTOM_PAGER" => $arParams["DISPLAY_BOTTOM_PAGER"],
						"PAGER_TITLE" => $arParams["PAGER_TITLE"],
						"PAGER_SHOW_ALWAYS" => $arParams["PAGER_SHOW_ALWAYS"],
						"PAGER_TEMPLATE" => $arParams["PAGER_TEMPLATE"],
						"PAGER_DESC_NUMBERING" => $arParams["PAGER_DESC_NUMBERING"],
						"PAGER_DESC_NUMBERING_CACHE_TIME" => $arParams["PAGER_DESC_NUMBERING_CACHE_TIME"],
						"PAGER_SHOW_ALL" => $arParams["PAGER_SHOW_ALL"],
						"AJAX_OPTION_ADDITIONAL" => "",
						"ADD_CHAIN_ITEM" => "N",
						"SHOW_QUANTITY" => $arParams["SHOW_QUANTITY"],
						"ADD_DETAIL_TO_SLIDER" => $arParams["DETAIL_ADD_DETAIL_TO_SLIDER"],
						"OFFER_ADD_PICT_PROP" => $arParams["OFFER_ADD_PICT_PROP"],
						"SHOW_QUANTITY_COUNT" => $arParams["SHOW_QUANTITY_COUNT"],
						"SHOW_DISCOUNT_PERCENT_NUMBER" => $arParams["SHOW_DISCOUNT_PERCENT_NUMBER"],
						"SHOW_DISCOUNT_PERCENT" => $arParams["SHOW_DISCOUNT_PERCENT"],
						"SHOW_DISCOUNT_TIME" => $arParams["SHOW_DISCOUNT_TIME"],
						"SHOW_ONE_CLICK_BUY" => $arParams["SHOW_ONE_CLICK_BUY"],
						"SHOW_OLD_PRICE" => $arParams["SHOW_OLD_PRICE"],
						"CONVERT_CURRENCY" => $arParams["CONVERT_CURRENCY"],
						"CURRENCY_ID" => $arParams["CURRENCY_ID"],
						"USE_STORE" => $arParams["USE_STORE"],
						"MAX_AMOUNT" => $arParams["MAX_AMOUNT"],
						"MIN_AMOUNT" => $arParams["MIN_AMOUNT"],
						"USE_MIN_AMOUNT" => $arParams["USE_MIN_AMOUNT"],
						"USE_ONLY_MAX_AMOUNT" => $arParams["USE_ONLY_MAX_AMOUNT"],
						"DISPLAY_WISH_BUTTONS" => $arParams["DISPLAY_WISH_BUTTONS"],
						"LIST_DISPLAY_POPUP_IMAGE" => $arParams["LIST_DISPLAY_POPUP_IMAGE"],
						"DEFAULT_COUNT" => $arParams["DEFAULT_COUNT"],
						"SHOW_MEASURE" => $arParams["SHOW_MEASURE"],
						"SHOW_HINTS" => $arParams["SHOW_HINTS"],
						"USE_CUSTOM_RESIZE_LIST" => $arTheme['USE_CUSTOM_RESIZE_LIST']['VALUE'],
						"OFFER_HIDE_NAME_PROPS" => $arParams["OFFER_HIDE_NAME_PROPS"],
						"SHOW_SECTIONS_LIST_PREVIEW" => $arParams["SHOW_SECTIONS_LIST_PREVIEW"],
						"SECTIONS_LIST_PREVIEW_PROPERTY" => $arParams["SECTIONS_LIST_PREVIEW_PROPERTY"],
						"SHOW_SECTION_LIST_PICTURES" => $arParams["SHOW_SECTION_LIST_PICTURES"],
						"USE_MAIN_ELEMENT_SECTION" => $arParams["USE_MAIN_ELEMENT_SECTION"],
						"ADD_PROPERTIES_TO_BASKET" => (isset($arParams["ADD_PROPERTIES_TO_BASKET"]) ? $arParams["ADD_PROPERTIES_TO_BASKET"] : ''),
						"PARTIAL_PRODUCT_PROPERTIES" => (isset($arParams["PARTIAL_PRODUCT_PROPERTIES"]) ? $arParams["PARTIAL_PRODUCT_PROPERTIES"] : ''),
						"PRODUCT_PROPERTIES" => $arParams["PRODUCT_PROPERTIES"],
						"SALE_STIKER" => $arParams["SALE_STIKER"],
						"STIKERS_PROP" => $arParams["STIKERS_PROP"],
						"SHOW_RATING" => $arParams["SHOW_RATING"],
						"REVIEWS_VIEW" => (isset($arTheme['REVIEWS_VIEW']['VALUE']) && $arTheme['REVIEWS_VIEW']['VALUE'] == 'EXTENDED') || (!isset($arTheme['REVIEWS_VIEW']['VALUE']) && isset($arTheme['REVIEWS_VIEW']) && $arTheme['REVIEWS_VIEW'] ==  'EXTENDED'),
						"ADD_PICT_PROP" => $arParams["ADD_PICT_PROP"],
						"IBINHERIT_TEMPLATES" => $arSeoItem ? $arIBInheritTemplates : array(),
						"FIELDS" => $arParams['FIELDS'],
						"USER_FIELDS" => $arParams['USER_FIELDS'],
						"SECTION_COUNT_ELEMENTS" => $arParams["SECTION_COUNT_ELEMENTS"],
						"SHOW_PROPS_TABLE" => $typeTableProps ?? strtolower(CMax::GetFrontParametrValue('SHOW_TABLE_PROPS')),
						"SHOW_OFFER_TREE_IN_TABLE" => CMax::GetFrontParametrValue('SHOW_OFFER_TREE_IN_TABLE'),
                        "SHOW_FAST_VIEW_BTN_SEPARATELY" => $arParams['SHOW_FAST_VIEW_BTN_SEPARATELY'],
					), $component, array("HIDE_ICONS" => $isAjax)
				);?>
				
				<?
					$arSelectFilter = ['ID'];
					$arSortFilter = [
						strtolower($sort_elem) => strtolower($sort_elem_order),
						strtolower($arParams["ELEMENT_SORT_FIELD2"]) => strtolower($arParams["ELEMENT_SORT_ORDER2"]),
					];
					$res = CMaxCache::CIblockElement_GetList($arSortFilter, array_merge($arFilter, ${'NEW_FILTER'}), false, false, $arSelectFilter);
					$elem_id_for_filter = [];
					
					
					
					foreach($res as $result){
						$elem_id_for_filter[] = $result['ID'];
					}
				?>
				
				<!--noindex-->
					<script class="smart-filter-filter" data-skip-moving="true">
					
						<?if($elem_id_for_filter) {?>
							var filter = <?=\Bitrix\Main\Web\Json::encode($elem_id_for_filter);?>
							
						<?}?>
						
					</script>

					<?if($SMART_FILTER_SORT):?>
						<script class="smart-filter-sort" data-skip-moving="true">
							var filter = <?=\Bitrix\Main\Web\Json::encode($SMART_FILTER_SORT)?>
						</script>
					<?endif;?>
				<!--/noindex-->
				
		</div>

		<?if($isAjax=="Y"){
				die();
			}?>
		<?CMax::get_banners_position('CONTENT_BOTTOM');
		global $bannerContentBottom;
		$bannerContentBottom = true;
		?>
	</div>
	<?if($bShowLeftBlock):?>
		<?CMax::ShowPageType('left_block');?>
	<?endif;?>
</div>
