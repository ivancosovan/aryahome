

<?if($arSeoItem):?>
	<?ob_start();?>
		<?if($arSeoItem["DETAIL_PICTURE"]):?>
			<div class="seo_block">
				<img data-src="<?=CFile::GetPath($arSeoItem["DETAIL_PICTURE"]);?>" src="<?=\Aspro\Functions\CAsproMax::showBlankImg(CFile::GetPath($arSeoItem["DETAIL_PICTURE"]));?>" alt="" title="" class="img-responsive top-big-img lazy"/>
			</div>
		<?endif;?>
		<?if($arSeoItem["PROPERTY_TIZERS_VALUE"]):?>
			<?$GLOBALS["arLandingTizers"] = array("ID" => $arSeoItem["PROPERTY_TIZERS_VALUE"]);?>
			<?$APPLICATION->IncludeComponent(
				"bitrix:news.list",
				"front_tizers",
				array(
					"IBLOCK_TYPE" => "aspro_max_content",
					"IBLOCK_ID" => $arParams['IBLOCK_TIZERS_ID'],
					"NEWS_COUNT" => "4",
					"SORT_BY1" => "SORT",
					"SORT_ORDER1" => "ASC",
					"SORT_BY2" => "ID",
					"SORT_ORDER2" => "DESC",
					// "SMALL_BLOCK" => "Y",
					"FILTER_NAME" => "arLandingTizers",
					"FIELD_CODE" => array(
						0 => "PREVIEW_PICTURE",
						1 => "PREVIEW_TEXT",
						2 => "DETAIL_PICTURE",
						3 => "",
					),
					"PROPERTY_CODE" => array(
						0 => "ICON",
						1 => "URL",
					),
					"CHECK_DATES" => "Y",
					"DETAIL_URL" => "",
					"AJAX_MODE" => "N",
					"AJAX_OPTION_JUMP" => "N",
					"AJAX_OPTION_STYLE" => "Y",
					"AJAX_OPTION_HISTORY" => "N",
					"CACHE_TYPE" => $arParams['CACHE_TYPE'],
					"CACHE_TIME" => "36000000",
					"CACHE_FILTER" => "Y",
					"CACHE_GROUPS" => "N",
					"PREVIEW_TRUNCATE_LEN" => "250",
					"ACTIVE_DATE_FORMAT" => "d F Y",
					"SET_TITLE" => "N",
					"SHOW_DETAIL_LINK" => "N",
					"SET_STATUS_404" => "N",
					"INCLUDE_IBLOCK_INTO_CHAIN" => "N",
					"ADD_SECTIONS_CHAIN" => "N",
					"HIDE_LINK_WHEN_NO_DETAIL" => "N",
					"PARENT_SECTION" => "",
					"PARENT_SECTION_CODE" => "",
					"DISPLAY_TOP_PAGER" => "N",
					"DISPLAY_BOTTOM_PAGER" => "Y",
					"PAGER_TITLE" => "",
					"PAGER_SHOW_ALWAYS" => "N",
					"PAGER_TEMPLATE" => "ajax",
					"PAGER_DESC_NUMBERING" => "N",
					"PAGER_DESC_NUMBERING_CACHE_TIME" => "3600",
					"PAGER_SHOW_ALL" => "N",
					"DISPLAY_DATE" => "Y",
					"DISPLAY_NAME" => "Y",
					"DISPLAY_PICTURE" => "N",
					"DISPLAY_PREVIEW_TEXT" => "N",
					"AJAX_OPTION_ADDITIONAL" => "",
					"COMPONENT_TEMPLATE" => "front_tizers",
					"SET_BROWSER_TITLE" => "N",
					"SET_META_KEYWORDS" => "N",
					"SET_META_DESCRIPTION" => "N",
					"SET_LAST_MODIFIED" => "N",
					"INCLUDE_SUBSECTIONS" => "Y",
					"STRICT_SECTION_CHECK" => "N",
					"TYPE_IMG" => "left",
					"CENTERED" => "Y",
					"SIZE_IN_ROW" => "4",
					"PAGER_BASE_LINK_ENABLE" => "N",
					"SHOW_404" => "N",
					"MESSAGE_404" => ""
				),
				false, array("HIDE_ICONS" => "Y")
			);?>
		<?endif;?>
	<?
	$html = ob_get_clean();
	$APPLICATION->AddViewContent('top_content', $html);
	?>

	<?ob_start();?>
		<?if($arSeoItem["PREVIEW_TEXT"]):?>
			<div class="seo_block"><?=$arSeoItem["PREVIEW_TEXT"]?></div>
		<?endif;?>
	<?
	$html = ob_get_clean();
	$APPLICATION->AddViewContent('top_desc', $html);
	$APPLICATION->AddViewContent('top_content', $html);
	?>

	<?ob_start();?>
		<?if($arSeoItem["PROPERTY_FORM_QUESTION_VALUE"]):?>
			<div class="seo_block">
				<table class="order-block bordered">
					<tbody>
						<tr>
							<td class="col-md-9 col-sm-8 col-xs-7 valign">
								<div class="block-item">
									<div class="flexbox flexbox--row">
										<div class="block-item__image icon_sendmessage"><?=CMax::showIconSvg("sendmessage", SITE_TEMPLATE_PATH."/images/svg/sendmessage.svg", "", "colored_theme_svg", true, false);?></div>
										<div class="text darken">
											<?$APPLICATION->IncludeComponent(
												 'bitrix:main.include',
												 '',
												 Array(
													  'AREA_FILE_SHOW' => 'page',
													  'AREA_FILE_SUFFIX' => 'ask',
													  'EDIT_TEMPLATE' => ''
												 )
											);?>
										</div>
									</div>
								</div>
							</td>
							<td class="col-md-3 col-sm-4 col-xs-5 valign btns-col">
								<div class="btns">
									<span><span class="btn btn-default btn-sm animate-load" data-event="jqm" data-param-form_id="ASK" data-name="question"><span><?=(strlen($arParams['S_ASK_QUESTION']) ? $arParams['S_ASK_QUESTION'] : GetMessage('S_ASK_QUESTION'))?></span></span></span>
								</div>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		<?endif;?>
	<?
	$html = ob_get_clean();
	$APPLICATION->AddViewContent('top_content', $html);
	?>
<?endif;?>


<?if($iSectionsCount):?>
	<?$this->SetViewTarget("top_content");?>

		<div class="section-block">
			<?
			if (!$arParams["SECTION_TYPE_VIEW"]) {
				$arParams["SECTION_TYPE_VIEW"] = "FROM_MODULE";
			}
			$sViewElementTemplate = ($arParams["SECTION_TYPE_VIEW"] == "FROM_MODULE" ? $arTheme["CATALOG_PAGE_SECTION"]["VALUE"] : $arParams["SECTION_TYPE_VIEW"]);
			?>
			<?
			$idForFilter = array();
			if(CModule::IncludeModule('iblock')) {
				$arSort= Array("NAME"=>"ASC");
				$arSelect = Array("ID","NAME","IBLOCK_ID","PROPERTY_NAIMENOVANIE_DLYA_SAYTA","PROPERTY_COLOR_FILTER");
				$arFilter2 = Array(
					"IBLOCK_ID" => array($arParams['IBLOCK_ID']),
					"ACTIVE" => 'Y',
					'INCLUDE_SUBSECTIONS' => 'Y',
					'>CATALOG_STORE_AMOUNT_3' => 0,
				);
				
				

				if(!$SMART_FILTER_FILTER['SECTION_ID']){
					$arFilter2["!SECTION_ID"] = 0;
				}else{
					$arFilter2["SECTION_ID"] = $SMART_FILTER_FILTER['SECTION_ID'];
				}


				
				

				$res = CIBlockElement::GetList($arSort, $arFilter2, false, false, $arSelect);
				while($ob = $res->fetch()){
					if(!in_array($ob["PROPERTY_NAIMENOVANIE_DLYA_SAYTA_VALUE"], $arNames)){
						$idForFilter[] = $ob['ID'];
						$arNames[$ob['ID']] = $ob["PROPERTY_NAIMENOVANIE_DLYA_SAYTA_VALUE"];
					}
					$idForSmartFilter[] = $ob['ID'];
				}

				$pageNotFixCustomOffers = $arResult['VARIABLES']['SECTION_CODE'] == 'podarochnye_karty';

				if($idForFilter && !$pageNotFixCustomOffers){
					$GLOBALS['MAX_SMART_FILTER']["=ID"] = $idForFilter;
				}
			}

// исключаем дубликаты END

			if($_POST['offer_ajax'] && ($_POST['product_color'] || $_POST['product_size'])){
				$id = $_POST['product_size'] ? $_POST['product_size'] : $_POST['product_color'];
				$GLOBALS['MAX_SMART_FILTER'] = array("=ID" => $id);
			}
			?>
			<?@include_once($sViewElementTemplate.'.php');?>
			
			
		</div>
	<?$this->EndViewTarget();?>
<?endif;?>

<?global $arTheme;?>
<?$isAjax="N";?>
<?if(isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == "xmlhttprequest"  && isset($_GET["ajax_get"]) && $_GET["ajax_get"] == "Y" || (isset($_GET["ajax_basket"]) && $_GET["ajax_basket"]=="Y")){
	$isAjax="Y";
}?>
<?if(isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == "xmlhttprequest" && isset($_GET["ajax_get_filter"]) && $_GET["ajax_get_filter"] == "Y" ){
	$isAjaxFilter="Y";
}
if(isset($isAjaxFilter) && $isAjaxFilter == "Y")
{
	$isAjax="N";
}
?>

<?$section_pos_top = \Bitrix\Main\Config\Option::get("aspro.max", "TOP_SECTION_DESCRIPTION_POSITION", "UF_SECTION_DESCR", SITE_ID );?>
<?$section_pos_bottom = \Bitrix\Main\Config\Option::get("aspro.max", "BOTTOM_SECTION_DESCRIPTION_POSITION", "DESCRIPTION", SITE_ID );?>


<?$sViewElementTemplate = ($arParams["LANDING_TYPE_VIEW"] == "FROM_MODULE" ? $arTheme["CATALOG_PAGE_LANDINGS"]["VALUE"] : $arParams["LANDING_TYPE_VIEW"]);?>

<?$this->SetViewTarget("top_content2");?>
	<?if(isset($arParams['LANDING_POSITION']) && $arParams['LANDING_POSITION'] === 'BEFORE_PRODUCTS'):?>
		<?@include_once($sViewElementTemplate.'.php');?>
	<?endif;?>
<?$this->EndViewTarget();?>

<?if($itemsCnt):?>
	<?if($arParams['AJAX_MODE'] == 'Y' && strpos($_SERVER['REQUEST_URI'], 'bxajaxid') !== false):?>
		<script type="text/javascript">
			/*setTimeout(function(){
				$('.ajax_load .catalog_block .catalog_item_wrapp .catalog_item .item-title').sliceHeight({resize: false});
				$('.ajax_load .catalog_block .catalog_item_wrapp .catalog_item .cost').sliceHeight({resize: false});
				$('.ajax_load .catalog_block .catalog_item_wrapp .item_info .sa_block').sliceHeight({resize: false});
				$('.ajax_load .catalog_block .catalog_item_wrapp').sliceHeight({classNull: '.footer_button', resize: false});
			}, 100);*/
			setStatusButton();
			InitLazyLoad();
			CheckTopMenuFullCatalogSubmenu();
		</script>
	<?endif;?>
	<?if($arTheme["FILTER_VIEW"]["VALUE"]=="VERTICAL" && $arTheme['LEFT_BLOCK_CATALOG_SECTIONS']['VALUE'] == 'Y'){?>
		<?//add filter with ajax?>
		<?if($arParams['AJAX_MODE'] == 'Y' && strpos($_SERVER['REQUEST_URI'], 'bxajaxid') !== false):?>
			<div class="filter_tmp swipeignore">
				<?include_once(__DIR__."/../filter.php")?>
			</div>
			<script type="text/javascript">

				if(typeof window['trackBarOptions'] !== 'undefined' && window['trackBarOptions']){
					window['trackBarValues'] = {}
					for(key in window['trackBarOptions']){
						window['trackBarValues'][key] = {
							'leftPercent': window['trackBar' + key].leftPercent,
							'leftValue': window['trackBar' + key].minInput.value,
							'rightPercent': window['trackBar' + key].rightPercent,
							'rightValue': window['trackBar' + key].maxInput.value,
						}
					}
				}

				if($('.filter_wrapper_ajax').length)
					$('.filter_wrapper_ajax').remove();
				var filter_node = $('.left_block .bx_filter.bx_filter_vertical'),
					new_filter_node = $('<div class="filter_wrapper_ajax"></div>'),
					left_block_node = $('#content .left_block');
				if(!filter_node.length)
				{
					if(left_block_node.find('.menu_top_block').length)
						new_filter_node.insertAfter(left_block_node.find('.menu_top_block'));
				}
				else
				{
					new_filter_node.insertBefore(filter_node);
					filter_node.remove();
				}
				$('.filter_tmp').appendTo($('.filter_wrapper_ajax'));

				if(typeof window['trackBarOptions'] !== 'undefined' && window['trackBarOptions']){
					for(key in window['trackBarOptions']){
						window['trackBarOptions'][key].leftPercent = window['trackBarValues'][key].leftPercent;
						window['trackBarOptions'][key].rightPercent = window['trackBarValues'][key].rightPercent;
						window['trackBarOptions'][key].curMinPrice = window['trackBarValues'][key].leftValue;
						window['trackBarOptions'][key].curMaxPrice = window['trackBarValues'][key].rightValue;
						window['trackBar' + key] = new BX.Iblock.SmartFilter(window['trackBarOptions'][key]);
						if ('leftValue' in window['trackBarValues'][key] && window['trackBar' + key].minInput) {
							window['trackBar' + key].minInput.value = window['trackBarValues'][key].leftValue;
						}
						if ('rightValue' in window['trackBarValues'][key] && window['trackBar' + key].maxInput) {
							window['trackBar' + key].maxInput.value = window['trackBarValues'][key].rightValue;
						}
					}
				}

			</script>
		<?endif;?>

		<?ob_start();?>
			<?include_once(__DIR__."/../filter.php")?>
			<script>
				$('#content > .wrapper_inner > .left_block').addClass('filter_ajax filter_visible');
			</script>
		<?$html=ob_get_clean();?>
		<?$APPLICATION->AddViewContent('left_menu', $html);?>
	<?}?>

	<div class="right_block1 clearfix catalog1 <?=strtolower($arTheme["FILTER_VIEW"]["VALUE"]);?>" id="right_block_ajax">
		<div class="filter-panel-wrapper <?CMax::getVariable('filter_exists');?>">
			<?if($isAjax=="N"){
				$frame = new \Bitrix\Main\Page\FrameHelper("viewtype-block-top");
				$frame->begin();?>
			<?}?>
				<?if (!$bSimpleSectionTemplate):?>
					<?include_once(__DIR__."/../sort.php");?>
					
					<?if($arTheme["FILTER_VIEW"]["VALUE"]=="COMPACT" || $arTheme['LEFT_BLOCK_CATALOG_SECTIONS']['VALUE'] == 'N'):?>
						<div class="filter-compact-block swipeignore">
							<?include(__DIR__."/../filter.php")?>
						</div>
					<?endif;?>
				<?endif;?>
			<?if($isAjax=="N"):?>
				<?$frame->end();?>
			<?endif;?>
		</div>
		<?if($_POST['offer_ajax']){
			$APPLICATION->RestartBuffer();
		}?>
		<?if($arTheme["FILTER_VIEW"]["VALUE"] == 'VERTICAL'):?>
			<div id="filter-helper-wrapper">
				<div id="filter-helper" class="top"></div>
			</div>
		<?endif;?>

		<div class="inner_wrapper">
<?endif;?>

			<?if(!$arSeoItem):?>
				<?if(
					$arParams["SHOW_SECTION_DESC"] != 'N' &&
					strpos($_SERVER['REQUEST_URI'], 'PAGEN') === false
				):?>
					<?ob_start();?>
					<?if($posSectionDescr=="BOTH"):?>
						<?if ($arSection[$section_pos_top]):?>
							<div class="group_description_block top muted777">
								<div><?=$arSection[$section_pos_top]?></div>
							</div>
						<?endif;?>
					<?elseif($posSectionDescr=="TOP"):?>
						<?if ($arSection[$arParams["SECTION_PREVIEW_PROPERTY"]]):?>
							<div class="group_description_block top muted777">
								<div><?=$arSection[$arParams["SECTION_PREVIEW_PROPERTY"]]?></div>
							</div>
						<?elseif ($arSection["DESCRIPTION"]):?>
							<div class="group_description_block top muted777">
								<div><?=$arSection["DESCRIPTION"]?></div>
							</div>
						<?elseif($arSection["UF_SECTION_DESCR"]):?>
							<div class="group_description_block top muted777">
								<div><?=$arSection["UF_SECTION_DESCR"]?></div>
							</div>
						<?endif;?>
					<?endif;?>
					<?
					$html = ob_get_clean();
					$APPLICATION->AddViewContent('top_desc', $html);
					$APPLICATION->AddViewContent('top_content', $html);
					?>
				<?endif;?>
			<?endif;?>
<div class="item-cnt" data-count="<?=$itemsCnt;?>"></div>
<?if($itemsCnt):?>
			<?if($isAjax=="N"){
				$frame = new \Bitrix\Main\Page\FrameHelper("viewtype-block");
				$frame->begin();?>
			<?}?>

			<?if($isAjax=="Y"){
				$APPLICATION->RestartBuffer();
			}?>

			<?$show = $arParams["PAGE_ELEMENT_COUNT"];?>
			<?if($isAjax=="N"){?>
				<div class="ajax_load cur <?=$display;?>" data-code="<?=$display;?>">
			<?}?>
				<?

				$arSort = array(
					$sort => $sort_order,
					$arParams['ELEMENT_SORT_FIELD2'] => $arParams['ELEMENT_SORT_ORDER2'],
				);
				$SMART_FILTER_SORT = $arSort;

				// исключаем дубликаты



				
				
				
				?>
				
				<?$APPLICATION->IncludeComponent(
					"bitrix:catalog.section",
					$template,
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
						"ELEMENT_SORT_FIELD" => $sort,
						"ELEMENT_SORT_ORDER" => $sort_order,
						"SHOW_DISCOUNT_TIME_EACH_SKU" => $arParams["SHOW_DISCOUNT_TIME_EACH_SKU"],
						"ELEMENT_SORT_FIELD2" => $arParams["ELEMENT_SORT_FIELD2"],
						"ELEMENT_SORT_ORDER2" => $arParams["ELEMENT_SORT_ORDER2"],
						"FILTER_NAME" => 'NEW_FILTER',
						"INCLUDE_SUBSECTIONS" => $arParams["INCLUDE_SUBSECTIONS"],
						"PAGE_ELEMENT_COUNT" => $show,
						"LINE_ELEMENT_COUNT" => $linerow,
						"SET_LINE_ELEMENT_COUNT" => $bSetElementsLineRow,
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
						"SHOW_PROPS" => $arParams['SHOW_PROPS'],
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
						strtolower($sort) => is_array($sort_order) ? $sort_order : strtolower($sort_order),
						strtolower($arParams["ELEMENT_SORT_FIELD2"]) => strtolower($arParams["ELEMENT_SORT_ORDER2"]),
					];
					$res = CMaxCache::CIblockElement_GetList($arSortFilter, array_merge($arFilter, ${'NEW_FILTER'}, array('SECTION_CODE' => $arResult['VARIABLES']['SECTION_CODE'], 'INCLUDE_SUBSECTIONS' => 'Y')), false, false, $arSelectFilter);
					$elem_id_for_filter = [];
					
					foreach($res as $result){
						$elem_id_for_filter[] = $result['ID'];
					}
					
					
					
					
					/*Что-то не так с доработкой. вытягивает не правильно товары*/
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

			<?if($isAjax!="Y"){?>
				</div>
				<?$frame->end();?>
			<?}?>


<?else:?>
	<?if(!$iSectionsCount):?>
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
		</div>
	<?endif;?>
<?endif;?>

			<?if($isAjax=="N"){?>
				<?global $arRegion;?>
				<?if($arParams["BLOG_IBLOCK_ID"]):?>
					<?
					$filterName = "MAX_FILTER_LINKED_BLOG";
					$GLOBALS[$filterName] = array(
						array(
							'LOGIC' => 'OR',
							array( "ID" => $linkedArticles ),
							array( "PROPERTY_LINK_GOODS_SECTIONS" => $section['ID'] ),
						),
					);

					if($sectionParent) {
						$GLOBALS[$filterName][0][] = array( "PROPERTY_LINK_GOODS_SECTIONS" => $sectionParent['ID'] );
					}
					if($sectionRoot) {
						$GLOBALS[$filterName][0][] = array( "PROPERTY_LINK_GOODS_SECTIONS" => $sectionRoot['ID'] );
					}

					if ($arParams["FILTER_NAME"] && $arParams["FILTER_NAME"] == "arRegionLink" && $arRegion) {
						$GLOBALS[$filterName]["PROPERTY_LINK_REGION"] = $arRegion['ID'];
					}
					$blogsCount = CMaxCache::CIblockElement_GetList(array("CACHE" => array("TAG" => CMaxCache::GetIBlockCacheTag($arParams["BLOG_IBLOCK_ID"]))), $GLOBALS[$filterName], array());
					if($blogsCount):
					?>
						<div class="linked-blog-list <?=$linkedArticlesPos?>" data-desktop_row="<?=$linkedArticlesRows?>" data-mobile_row="<?=$linkedArticlesRowsMobile?>">
							<?$APPLICATION->IncludeComponent(
								"bitrix:news.list",
								"news-list",
								array(
									"IBLOCK_TYPE" => "aspro_max_content",
									"IBLOCK_ID" => $arParams["BLOG_IBLOCK_ID"],
									"NEWS_COUNT" => "10",
									"SORT_BY1" => "SORT",
									"SORT_ORDER1" => "ASC",
									"SORT_BY2" => "ID",
									"SORT_ORDER2" => "DESC",
									"SLIDER" => ($linkedArticlesPos == "content" ? "Y" : "N"),
									"SLIDER_WAIT" => ($linkedArticlesPos == "content" ? "Y" : "N"),
									"FILTER_NAME" => $filterName,
									"FIELD_CODE" => array(
										0 => "NAME",
										1 => "DETAIL_PAGE_URL",
										2 => "PREVIEW_TEXT",
										3 => "PREVIEW_PICTURE",
										4 => "DATE_ACTIVE_FROM",
									),
									"PROPERTY_CODE" => array(
										0 => "PERIOD",
									),
									"CHECK_DATES" => "Y",
									"DETAIL_URL" => "",
									"AJAX_MODE" => "N",
									"AJAX_OPTION_JUMP" => "N",
									"AJAX_OPTION_STYLE" => "Y",
									"AJAX_OPTION_HISTORY" => "N",
									"CACHE_TYPE" => "Y",
									"CACHE_TIME" => "36000000",
									"CACHE_FILTER" => "Y",
									"HIDE_LINK_WHEN_NO_DETAIL" => "Y",
									"CACHE_GROUPS" => "N",
									"PREVIEW_TRUNCATE_LEN" => "",
									"ACTIVE_DATE_FORMAT" => "j F Y",
									"SET_TITLE" => "N",
									"SET_STATUS_404" => "N",
									"INCLUDE_IBLOCK_INTO_CHAIN" => "N",
									"ADD_SECTIONS_CHAIN" => "N",
									"PARENT_SECTION" => "",
									"PARENT_SECTION_CODE" => "",
									"INCLUDE_SUBSECTIONS" => "Y",
									"PAGER_TEMPLATE" => ".default",
									"DISPLAY_TOP_PAGER" => "N",
									"DISPLAY_BOTTOM_PAGER" => "N",
									"PAGER_TITLE" => "",
									"PAGER_SHOW_ALWAYS" => "N",
									"PAGER_DESC_NUMBERING" => "N",
									"PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
									"PAGER_SHOW_ALL" => "N",
									"VIEW_TYPE" => "list",
									"IMAGE_POSITION" => "left",
									"COUNT_IN_LINE" => "3",
									"SHOW_TITLE" => "Y",
									"AJAX_OPTION_ADDITIONAL" => "",
									"LINKED_MODE" => ($linkedArticlesPos == "content" ? "N" : "Y"),
									"BORDERED" => ($linkedArticlesPos == "content" ? "N" : "Y"),
									"TITLE" => ($arParams["BLOCK_BLOG_NAME"] ? $arParams["BLOCK_BLOG_NAME"] : GetMessage("TAB_BLOG_NAME")),
									"SHOW_TITLE" => "Y",
								),
								false, array("HIDE_ICONS" => "Y")
							);?>
						</div>
					<?endif;?>
				<?endif;?>
				<?
				$linkedBannersIblock = CMaxCache::$arIBlocks[SITE_ID]["aspro_max_adv"]["aspro_max_banners_catalog"][0];
				$filterName = "MAX_FILTER_LINKED_BANNERS";
				$GLOBALS[$filterName] = array(
					array(
						'LOGIC' => 'OR',
						array( "ID" => $linkedBanners ),
						array( "PROPERTY_LINK_GOODS_SECTIONS" => $section['ID'] ),
					),
				);

				if($sectionParent) {
					$GLOBALS[$filterName][0][] = array( "PROPERTY_LINK_GOODS_SECTIONS" => $sectionParent['ID'] );
				}
				if($sectionRoot) {
					$GLOBALS[$filterName][0][] = array( "PROPERTY_LINK_GOODS_SECTIONS" => $sectionRoot['ID'] );
				}

				if ($arParams["FILTER_NAME"] && $arParams["FILTER_NAME"] == "arRegionLink" && $arRegion) {
					$GLOBALS[$filterName]["PROPERTY_LINK_REGION"] = $arRegion['ID'];
				}
				$bannersCount = CMaxCache::CIblockElement_GetList(array("CACHE" => array("TAG" => CMaxCache::GetIBlockCacheTag($linkedBannersIblock))), $GLOBALS[$filterName], array());
				if($bannersCount):
				?>
					<div class="linked-banners-list <?=$linkedBannersPos?>" data-desktop_row="<?=$linkedBannersRows?>" data-mobile_row="<?=$linkedBannersRowsMobile?>">
					<?					
					$APPLICATION->IncludeComponent(
						"bitrix:news.list",
						"banners",
						array(
							"IBLOCK_TYPE" => "aspro_max_adv",
							"IBLOCK_ID" => $linkedBannersIblock,
							//"PAGE"		=> $APPLICATION->GetCurPage(),
							'BANNER_IN_GOODS' => 'Y',
							'SLIDER_MODE' => 'Y',
							'SLIDER_WAIT' => ($linkedBannersPos == "content" ? "Y" : "N"),
							"SLIDER_AUTOPLAY" => $arTheme["ADV_SLIDER_AUTOPLAY"]["VALUE"],
							"SLIDES_SPEED" => $arTheme["ADV_BANNER_SLIDESSHOWSPEED"]["VALUE"],
							"ANIMATION_SPEED" => $arTheme["ADV_BANNER_ANIMATIONSPEED"]["VALUE"],
							"NEWS_COUNT" => "100",
							"SHOW_ALL_ELEMENTS" => 'Y',
							"SORT_BY1" => "SORT",
							"SORT_ORDER1" => "ASC",
							"SORT_BY2" => "ID",
							"SORT_ORDER2" => "ASC",
							"FIELD_CODE" => array(
								0 => "NAME",
								2 => "PREVIEW_PICTURE",
							),
							"PROPERTY_CODE" => array(
								0 => "LINK",
								1 => "TARGET",
								2 => "BGCOLOR",
								3 => "SHOW_SECTION",
								4 => "SHOW_PAGE",
								5 => "HIDDEN_XS",
								6 => "HIDDEN_SM",
								7 => "POSITION",
								8 => "SIZING",
							),
							"CHECK_DATES" => "Y",
							"FILTER_NAME" => $filterName,
							"DETAIL_URL" => "",
							"AJAX_MODE" => "N",
							"AJAX_OPTION_JUMP" => "N",
							"AJAX_OPTION_STYLE" => "Y",
							"AJAX_OPTION_HISTORY" => "N",
							"CACHE_TYPE" => "A",
							"CACHE_TIME" => "3600000",
							"CACHE_FILTER" => "Y",
							"CACHE_GROUPS" => "N",
							"PREVIEW_TRUNCATE_LEN" => "150",
							"ACTIVE_DATE_FORMAT" => "d.m.Y",
							"SET_TITLE" => "N",
							"SET_STATUS_404" => "N",
							"INCLUDE_IBLOCK_INTO_CHAIN" => "N",
							"ADD_SECTIONS_CHAIN" => "N",
							"HIDE_LINK_WHEN_NO_DETAIL" => "N",
							"PARENT_SECTION" => "",
							"PARENT_SECTION_CODE" => "",
							"INCLUDE_SUBSECTIONS" => "Y",
							"PAGER_TEMPLATE" => ".default",
							"DISPLAY_TOP_PAGER" => "N",
							"DISPLAY_BOTTOM_PAGER" => "N",
							"PAGER_TITLE" => "",
							"PAGER_SHOW_ALWAYS" => "N",
							"PAGER_DESC_NUMBERING" => "N",
							"PAGER_DESC_NUMBERING_CACHE_TIME" => "3600000",
							"PAGER_SHOW_ALL" => "N",
							"AJAX_OPTION_ADDITIONAL" => "",
							"SHOW_DETAIL_LINK" => "N",
							"SET_BROWSER_TITLE" => "N",
							"SET_META_KEYWORDS" => "N",
							"SET_META_DESCRIPTION" => "N",
							"COMPONENT_TEMPLATE" => "banners",
							"SET_LAST_MODIFIED" => "N",
							"COMPOSITE_FRAME_MODE" => "A",
							"COMPOSITE_FRAME_TYPE" => "AUTO",
							"PAGER_BASE_LINK_ENABLE" => "N",
							"SHOW_404" => "N",
							"MESSAGE_404" => ""
						),
						false, array('ACTIVE_COMPONENT' => 'Y', 'HIDE_ICONS' => 'Y')
					);
					
					?>
					</div>
				<?endif;?>
				<?if(!$arSeoItem):?>
					<?if(
						$arParams["SHOW_SECTION_DESC"] != 'N' &&
						strpos($_SERVER['REQUEST_URI'], 'PAGEN') === false
					):?>
						<?ob_start();?>
						<?if($posSectionDescr=="BOTH"):?>
							<?if($arSection[$section_pos_bottom]):?>
								<div class="group_description_block bottom muted777">
									<div><?=$arSection[$section_pos_bottom]?></div>
								</div>
							<?endif;?>
						<?elseif($posSectionDescr=="BOTTOM"):?>
							<?if($arSection[$arParams["SECTION_PREVIEW_PROPERTY"]]):?>
								<div class="group_description_block bottom muted777">
									<div><?=$arSection[$arParams["SECTION_PREVIEW_PROPERTY"]]?></div>
								</div>
							<?elseif ($arSection["DESCRIPTION"]):?>
								<div class="group_description_block bottom muted777">
									<div><?=$arSection["DESCRIPTION"]?></div>
								</div>
							<?elseif($arSection["UF_SECTION_DESCR"]):?>
								<div class="group_description_block bottom muted777">
									<div><?=$arSection["UF_SECTION_DESCR"]?></div>
								</div>
							<?endif;?>
						<?endif;?>
						<?
						$html = ob_get_clean();
						$APPLICATION->AddViewContent('bottom_desc', $html);
						$APPLICATION->ShowViewContent('bottom_desc');
						$APPLICATION->ShowViewContent('smartseo_bottom_description');
						$APPLICATION->ShowViewContent('smartseo_additional_description');
						$APPLICATION->ShowViewContent('sotbit_seometa_bottom_desc');
						$APPLICATION->ShowViewContent('sotbit_seometa_add_desc');
						?>
					<?endif;?>

				<?else:?>
					<?ob_start();?>
					<?if($arSeoItem["DETAIL_TEXT"]):?>
						<div class="group_description_block bottom muted777">
							<?=$arSeoItem["DETAIL_TEXT"];?>
						</div>
					<?endif;?>
					<?
					$html = ob_get_clean();
					$APPLICATION->AddViewContent('bottom_desc', $html);
					$APPLICATION->ShowViewContent('bottom_desc');
					$APPLICATION->ShowViewContent('smartseo_bottom_description');
					$APPLICATION->ShowViewContent('sotbit_seometa_bottom_desc');
					?>
				<?endif;?>
				<?if(!isset($arParams['LANDING_POSITION']) || $arParams['LANDING_POSITION'] === 'AFTER_PRODUCTS'):?>
					<div class="<?=$sViewElementTemplate;?>" >
						<?@include_once($sViewElementTemplate.'.php');?>
					</div>
				<?endif;?>
<?if($itemsCnt):?>
				<div class="clear"></div>
<?endif;?>
			<?}?>
<?global $arSite, $arTheme;
$postfix = "";

$bBitrixAjax = (strpos($_SERVER["QUERY_STRING"], "bxajaxid") !== false);
if($arTheme["HIDE_SITE_NAME_TITLE"]["VALUE"] == "N" && ($bBitrixAjax || $isAjaxFilter))
{
	$postfix = " - ".$arSite["NAME"];
}?>



<?if($itemsCnt):?>
			<?if($isAjax=="Y"){
				die();
			}?>
			<?/*
		</div>
	</div>
	*/?>
	<?if($bBitrixAjax)
	{
		$page_title = $arValues['SECTION_META_TITLE'] ? $arValues['SECTION_META_TITLE'] : $arSection["NAME"];
		if($page_title){
			$APPLICATION->SetPageProperty("title", $page_title.$postfix);
		}
	}?>
<?else:?>
	<?if(!$section):?>
		<?\Bitrix\Iblock\Component\Tools::process404(
			trim($arParams["MESSAGE_404"]) ?: GetMessage("T_NEWS_NEWS_NA")
			,true
			,$arParams["SET_STATUS_404"] === "Y"
			,$arParams["SHOW_404"] === "Y"
			,$arParams["FILE_404"]
		);?>
	<?else:?>
		<?
		$ipropValues = new \Bitrix\Iblock\InheritedProperty\SectionValues($arParams["IBLOCK_ID"], IntVal($arSection["ID"]));
		$arValues = $ipropValues->getValues();
		if($arParams["SET_TITLE"] !== 'N'){
			$page_h1 = $arValues['SECTION_PAGE_TITLE'] ? $arValues['SECTION_PAGE_TITLE'] : $arSection["NAME"];
			if($page_h1){
				$APPLICATION->SetTitle($page_h1);
			}
			else{
				$APPLICATION->SetTitle($arSection["NAME"]);
			}
		}
		$page_title = $arValues['SECTION_META_TITLE'] ? $arValues['SECTION_META_TITLE'] : $arSection["NAME"];
		if($page_title){
			$APPLICATION->SetPageProperty("title", $page_title.$postfix);
		}
		if($arValues['SECTION_META_DESCRIPTION']){
			$APPLICATION->SetPageProperty("description", $arValues['SECTION_META_DESCRIPTION']);
		}
		if($arValues['SECTION_META_KEYWORDS']){
			$APPLICATION->SetPageProperty("keywords", $arValues['SECTION_META_KEYWORDS']);
		}
		?>
	<?endif;?>
<?endif;?>
<?
if($arSeoItem)
{
	$langing_seo_h1 = ($arSeoItem["IPROPERTY_VALUES"]["ELEMENT_PAGE_TITLE"] != "" ? $arSeoItem["IPROPERTY_VALUES"]["ELEMENT_PAGE_TITLE"] : $arSeoItem["NAME"]);

	$APPLICATION->SetTitle($langing_seo_h1);

	if($arSeoItem["IPROPERTY_VALUES"]["ELEMENT_META_TITLE"])
		$APPLICATION->SetPageProperty("title", $arSeoItem["IPROPERTY_VALUES"]["ELEMENT_META_TITLE"]);
	else
		$APPLICATION->SetPageProperty("title", $arSeoItem["NAME"].$postfix);

	if($arSeoItem["IPROPERTY_VALUES"]["ELEMENT_META_DESCRIPTION"])
		$APPLICATION->SetPageProperty("description", $arSeoItem["IPROPERTY_VALUES"]["ELEMENT_META_DESCRIPTION"]);

	if($arSeoItem["IPROPERTY_VALUES"]['ELEMENT_META_KEYWORDS'])
		$APPLICATION->SetPageProperty("keywords", $arSeoItem["IPROPERTY_VALUES"]['ELEMENT_META_KEYWORDS']);
	?>
<?}?>
<?
if($arParams["AJAX_MODE"] !== "Y" && isset($isAjaxFilter) && $isAjaxFilter){
	Aspro\Max\Smartseo\General\SmartseoEngine::replaceSeoPropertyOnPage();
}
?>
<?if($arParams["AJAX_MODE"] !== "Y" && isset($isAjaxFilter) && $isAjaxFilter ):?>
	<div class="hidden ajax_breadcrumb">
		<?$APPLICATION->IncludeComponent("bitrix:breadcrumb", "main", array(
			"START_FROM" => "0",
			"PATH" => "",
			"SITE_ID" => SITE_ID,
			"SHOW_SUBSECTIONS" => "N"
			),
			false
		);?>
	</div>
<?endif;?>
<?if(isset($isAjaxFilter) && $isAjaxFilter):?>
	<?global $APPLICATION;?>
	<?$arAdditionalData['TITLE'] = htmlspecialcharsback($APPLICATION->GetTitle());
	if($arSeoItem)
	{
		$postfix = '';
	}
	$arAdditionalData['WINDOW_TITLE'] = htmlspecialcharsback($APPLICATION->GetTitle('title').$postfix);?>
	<script type="text/javascript">
		BX.removeCustomEvent("onAjaxSuccessFilter", function tt(e){});
		BX.addCustomEvent("onAjaxSuccessFilter", function tt(e){
			var arAjaxPageData = <?=CUtil::PhpToJSObject($arAdditionalData);?>;			
			if($('.element-count-wrapper .element-count').length){
				//$('.element-count-wrapper .element-count').text($('.js_append').closest('.ajax_load.cur').find('.bottom_nav').attr('data-all_count'));
				var cntFromNav = $('.js_append').closest('.ajax_load.cur').find('.bottom_nav').attr('data-all_count');
				if(cntFromNav){
					$('.element-count-wrapper .element-count').text(cntFromNav);
				} else {
					$('.element-count-wrapper .element-count').text($('.js_append > div.item:not(.flexbox)').length)
				}				
			}
			<?if( $arParams["AJAX_MODE"] !== "Y" ):?>	
				if (arAjaxPageData.TITLE)
					BX.ajax.UpdatePageTitle(arAjaxPageData.TITLE);
				if (arAjaxPageData.WINDOW_TITLE || arAjaxPageData.TITLE)
					BX.ajax.UpdateWindowTitle(arAjaxPageData.WINDOW_TITLE || arAjaxPageData.TITLE);

				var ajaxBreadCrumb = $('.ajax_breadcrumb .breadcrumbs');
				if(ajaxBreadCrumb.length){
					$('#navigation').html(ajaxBreadCrumb);
					$('.ajax_breadcrumb').remove();
				}
					
			<?endif;?>

		});
	</script>
<?endif;?>

<?if($itemsCnt):?>
		</div>
	</div>
<?endif;?>
