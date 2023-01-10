<?use Bitrix\Main\Localization\Loc;?>
<?if($templateData['PRODUCT_SET_OPTIONS']['PRODUCT_SET'] && $templateData['PRODUCT_SET_OPTIONS']['PRODUCT_SET_FILTER']):?>
	<?						
	if($templateData['PRODUCT_SET_OPTIONS']['PRODUCT_SET_FILTER']){
		$cond = new CMaxCondition();
		try{
			$arTmpGoods = \Bitrix\Main\Web\Json::decode($templateData['PRODUCT_SET_OPTIONS']['PRODUCT_SET_FILTER']);
			$arExGoodsFilter = $cond->parseCondition($arTmpGoods, $arParams);
		}
		catch(\Exception $e){
			$arExGoodsFilter = array();
		}
		unset($arTmpGoods);

		$GLOBALS['arrProductsSetFilter'] = array($arExGoodsFilter);
		//unset($arParams['CONTENT_LINKED_FILTER_BY_FILTER']);
	}
	
	$bUseSetGroup = $templateData['PRODUCT_SET_OPTIONS']['PRODUCT_SET_GROUP'];
	if($bUseSetGroup){
		$arItemsFilter = array("IBLOCK_ID" => $arParams["IBLOCK_ID"], "ACTIVE" => "Y");
		$arItemsFilter = array_merge($arItemsFilter, $GLOBALS['arrProductsSetFilter']);

		if($GLOBALS['arRegion']){
			if(CMax::GetFrontParametrValue('REGIONALITY_FILTER_ITEM') == 'Y' && CMax::GetFrontParametrValue('REGIONALITY_FILTER_CATALOG') == 'Y'){
				$arItemsFilter['PROPERTY_LINK_REGION'] = $GLOBALS['arRegion']['ID'];
				CMax::makeElementFilterInRegion($arItemsFilter);
			}
		}			

		$arItems = CMaxCache::CIBLockElement_GetList(array('CACHE' => array("MULTI" =>"Y", "TAG" => CMaxCache::GetIBlockCacheTag($arParams["IBLOCK_ID"]))), $arItemsFilter, false, array("nTopCount" => 10000), array("ID", "IBLOCK_ID", "IBLOCK_SECTION_ID"));
		
		if($arItems)
		{
			$arSectionsID = $arProductSection = array();
			foreach($arItems as $arItem)
			{
				if($arItem["IBLOCK_SECTION_ID"])
				{
					if(is_array($arItem["IBLOCK_SECTION_ID"])){
						$arSectionsID = array_merge($arSectionsID, $arItem["IBLOCK_SECTION_ID"]);
						foreach($arItem["IBLOCK_SECTION_ID"] as $sectId){
							$arProductSection[$sectId][] = $arItem["ID"];
						}										
					}										
					else{
						$arSectionsID[] = $arItem["IBLOCK_SECTION_ID"];
						$arProductSection[$arItem["IBLOCK_SECTION_ID"]][] = $arItem["ID"];
					}										
				}
			}

			if($arSectionsID){
				$arSectionsID = array_unique($arSectionsID);
			}
		}

		//get sections title
		$arSectionsName = CMaxCache::CIBlockSection_GetList(array('CACHE' => array("MULTI" =>"Y", "TAG" => CMaxCache::GetIBlockCacheTag($arParams["IBLOCK_ID"]))), array('ACTIVE' => 'Y', "ID" => $arSectionsID, "IBLOCK_ID" => $arParams["IBLOCK_ID"]), false, array("ID", "IBLOCK_ID", "NAME"), array("nTopCount" => 5000));
		$arSectionsName = array_column($arSectionsName, NULL, 'ID');
		//if need seo section names
		// foreach($arSectionsName as $keySect => $arSect){
		// 	$ipropValues = new \Bitrix\Iblock\InheritedProperty\SectionValues($arParams['IBLOCK_ID'], $arSect["ID"]);
		// 	$arSectionsName[$keySect]['IPROPERTY_VALUES'] = $ipropValues->getValues();
		// }
	} else {
		$arProductSection = array('0' => '0');
	}
	?>
	<?if( !empty($arExGoodsFilter) ):?>
		<?
		$arTransferParams = array(
			"SHOW_ABSENT" => $arParams["SHOW_ABSENT"],
			"HIDE_NOT_AVAILABLE_OFFERS" => $arParams["HIDE_NOT_AVAILABLE_OFFERS"],
			"PRICE_CODE" => $arParams["PRICE_CODE"],
			"OFFER_TREE_PROPS" => $arParams["OFFER_TREE_PROPS"],
			"OFFER_SHOW_PREVIEW_PICTURE_PROPS" => $arParams["OFFER_SHOW_PREVIEW_PICTURE_PROPS"],
			"CACHE_TIME" => $arParams["CACHE_TIME"],
			"CONVERT_CURRENCY" => $arParams["CONVERT_CURRENCY"],
			"CURRENCY_ID" => $arParams["CURRENCY_ID"],
			"OFFERS_SORT_FIELD" => $arParams["OFFERS_SORT_FIELD"],
			"OFFERS_SORT_ORDER" => $arParams["OFFERS_SORT_ORDER"],
			"OFFERS_SORT_FIELD2" => $arParams["OFFERS_SORT_FIELD2"],
			"OFFERS_SORT_ORDER2" => $arParams["OFFERS_SORT_ORDER2"],
			"LIST_OFFERS_LIMIT" => $arParams["OFFERS_LIMIT"],
			"CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
			"LIST_OFFERS_PROPERTY_CODE" => $arParams["LIST_OFFERS_PROPERTY_CODE"],
			"SHOW_DISCOUNT_TIME" => $arParams["SHOW_DISCOUNT_TIME"],
			"SHOW_COUNTER_LIST" => $arParams["SHOW_COUNTER_LIST"],
			"PRICE_VAT_INCLUDE" => $arParams["PRICE_VAT_INCLUDE"],
			"USE_PRICE_COUNT" => $arParams["USE_PRICE_COUNT"],
			"SHOW_MEASURE" => $arParams["SHOW_MEASURE"],
			"SHOW_OLD_PRICE" => $arParams["SHOW_OLD_PRICE"],
			"SHOW_DISCOUNT_PERCENT" => $arParams["SHOW_DISCOUNT_PERCENT"],
			"SHOW_DISCOUNT_PERCENT_NUMBER" => $arParams["SHOW_DISCOUNT_PERCENT_NUMBER"],
			"USE_REGION" => $arParams["USE_REGION"],
			"STORES" => $arParams["STORES"],
			"DEFAULT_COUNT" => $arParams["DEFAULT_COUNT"],
			"BASKET_URL" => $arParams["BASKET_URL"],
			"SHOW_GALLERY" => $arParams["SHOW_GALLERY"],
			"MAX_GALLERY_ITEMS" => $arParams["MAX_GALLERY_ITEMS"],
			"OFFERS_CART_PROPERTIES" => $arParams["OFFERS_CART_PROPERTIES"],
			"PRODUCT_PROPERTIES" => $arParams["PRODUCT_PROPERTIES"],
			"PARTIAL_PRODUCT_PROPERTIES" => $arParams["PARTIAL_PRODUCT_PROPERTIES"],
			"ADD_PROPERTIES_TO_BASKET" => $arParams["ADD_PROPERTIES_TO_BASKET"],
			"SHOW_ONE_CLICK_BUY" => $arParams["SHOW_ONE_CLICK_BUY"],
			"SHOW_DISCOUNT_TIME_EACH_SKU" => $arParams["SHOW_DISCOUNT_TIME_EACH_SKU"],
			"SHOW_ARTICLE_SKU" => $arParams["SHOW_ARTICLE_SKU"],
			"SHOW_POPUP_PRICE" => CMax::GetFrontParametrValue('SHOW_POPUP_PRICE'),
			"ADD_PICT_PROP" => $arParams["ADD_PICT_PROP"],
			"ADD_DETAIL_TO_SLIDER" => $arParams["ADD_DETAIL_TO_SLIDER"],
			"OFFER_ADD_PICT_PROP" => $arParams["OFFER_ADD_PICT_PROP"],
			"PRODUCT_QUANTITY_VARIABLE" => $arParams["PRODUCT_QUANTITY_VARIABLE"],
			"DISPLAY_COMPARE" => CMax::GetFrontParametrValue('CATALOG_COMPARE'),
			"DISPLAY_WISH_BUTTONS" => $arParams["DISPLAY_WISH_BUTTONS"],
		);	
		?>
		<div class="ordered-block <?=$code?> js-scroll-complect complect_main_wrap js_wrapper_items" data-params='<?=str_replace('\'', '"', CUtil::PhpToJSObject($arTransferParams, false))?>'>
			<div class="complect_header_block">
				<div class="ordered-block__title option-font-bold font_lg">
					<?=$arParams["DETAIL_SET_PRODUCT_TITLE"];?>
				</div>
				<div class="flexbox flexbox--row align-items-center justify-content-between flex-wrap product-info-headnote opt-buy bordered rounded3 show_on_mobile">
					<div class="col-auto">
						<div class="product-info-headnote__inner">
							<div class="product-info-headnote__check">
								<div class="filter label_block">
									<input type="checkbox" name="select_all_items" id="select_all_items" value="Y">
									<label for="select_all_items"><?=Loc::getMessage("T_COMPLECT_ALL_ON");?></label>
								</div>
							</div>
						</div>
					</div>
					<div class="col-auto">
						<div class="buy_complect_wrap">
							<span data-currency="RUB" class="button_buy_complect opt_action btn btn-default btn-sm no-action" data-action="buy" data-iblock_id="<?=$arParams["IBLOCK_ID"]?>"><span><?=\Bitrix\Main\Config\Option::get("aspro.max", "EXPRESSION_ADDTOBASKET_BUTTON_DEFAULT", GetMessage("EXPRESSION_ADDTOBASKET_BUTTON_DEFAULT"));?></span></span>
						</div>
					</div>
				</div>
			</div>
			<?foreach($arProductSection as $sectionId => $sectionProducts):?>
				<?if(isset($_REQUEST['pagen_data_block']) && $_REQUEST['pagen_data_block'] == 'Y' && isset($_REQUEST['data_block']) && $_REQUEST['data_block'] != 'complect_'.$sectionId)
					continue;
				?>
				<div class="complect-block cur" data-code="block">
					<?if($bUseSetGroup):?>
						<div class="complect-block__title option-font-bold font_lg">
							<?=$arSectionsName[$sectionId]["NAME"];?>
						</div>
					<?endif;?>
					<div class="tabs_slider js-load-block loader_circle nav-data_block " data-block="complect_<?=$sectionId?>" data-file="<?=$APPLICATION->GetCurPage()?>">
						<div class="stub"></div>
						<?
						if(isset($_REQUEST['pagen_data_block']) && $_REQUEST['pagen_data_block'] == 'Y'){
							$APPLICATION->RestartBuffer();
							$arParams["FROM_AJAX"] = 'Y';
						}
						else{
							CMax::checkRestartBuffer(true, 'complect_'.$sectionId);												
						}
						?>										
							<?if(CMax::checkAjaxRequest() || CMax::checkAjaxRequest2()):?>
								<?$APPLICATION->ShowAjaxHead();?>
								<?$GLOBALS["NavNum"]=0;
								if($bUseSetGroup){
									$GLOBALS['arrProductsSetFilter'] = [];
									$GLOBALS['arrProductsSetFilter'] = array('ID' => $sectionProducts);
									$GLOBALS['arrProductsSetFilter']['!PROPERTY_PRODUCT_SET_VALUE'] = 'Y';
								}
								?>
								<?include($_SERVER['DOCUMENT_ROOT'].SITE_DIR.'/include/detail.complect_products_block.php');?>
							<?endif;?>
						<?
						if(isset($_REQUEST['pagen_data_block']) && $_REQUEST['pagen_data_block'] == 'Y'){
							die();
						} else{
							CMax::checkRestartBuffer(true, 'complect_'.$sectionId);
						}
						?>
					</div>
				</div>
			<?endforeach;?>
		</div>
	<?endif;?>
<?endif;?>