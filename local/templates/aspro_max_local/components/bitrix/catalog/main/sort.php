<?
global $arTheme;
$arDisplays = array("block", "list", "table");
if(array_key_exists("display", $_REQUEST) || (array_key_exists("display", $_SESSION)) || $arParams["DEFAULT_LIST_TEMPLATE"]){
	if($_REQUEST["display"] && (in_array(trim($_REQUEST["display"]), $arDisplays))){
		$display = htmlspecialcharsbx(trim($_REQUEST["display"]));
		$_SESSION["display"]=htmlspecialcharsbx(trim($_REQUEST["display"]));
	}
	elseif($_SESSION["display"] && (in_array(trim($_SESSION["display"]), $arDisplays))){
		$display = $_SESSION["display"];
	}
	elseif($arSection["DISPLAY"]){
		$display = $arSection["DISPLAY"];
	}
	else{
		$display = $arParams["DEFAULT_LIST_TEMPLATE"];
	}
}
else{
	$display = "block";
}
$template = "catalog_".$display;

if($arTheme['HEADER_TYPE']['VALUE'] == 28 || $arTheme['HEADER_TYPE']['VALUE'] == 29)
{
	$APPLICATION->SetPageProperty("HIDE_LEFT_BLOCK", "Y");
	$arTheme['LEFT_BLOCK_CATALOG_SECTIONS']['VALUE'] = 'N';
}

$bHideLeftBlock = ($arTheme['LEFT_BLOCK_CATALOG_SECTIONS']['VALUE'] == 'N');
$bShowCompactHideLeft = ($arTheme['COMPACT_FILTER_HIDE_LEFT_BLOCK']['VALUE'] == 'Y');
if($bHideLeftBlock){
	if($bShowCompactHideLeft){
		$arTheme["FILTER_VIEW"]["VALUE"] = 'COMPACT';
	} else {
		$arTheme["FILTER_VIEW"]["VALUE"] = 'VERTICAL';
	}
	
}
$bShowSortInFilter = ($arParams['SHOW_SORT_IN_FILTER'] != 'N');
?>

<div class="filter-panel sort_header view_<?=$display?> <?=($bShowCompactHideLeft && $bHideLeftBlock ? 'show-compact' : '' );?> <?=(!$bShowSortInFilter ? 'show-normal-sort' : '' );?>">
	<?if('Y' === $arParams['USE_FILTER']):?>
		<?$bActiveFilter = \Aspro\Functions\CAsproMax::checkActiveFilterPage($arParams["SEF_URL_TEMPLATES"]['smart_filter']);?>
		<div class="filter-panel__filter pull-left filter-<?=strtolower($arTheme['FILTER_VIEW']['VALUE']);?>  <?=($bHideLeftBlock && !$bShowCompactHideLeft ? 'filter-panel__filter--visible' : '');?>">
			<div class="bx-filter-title filter_title <?=($bActiveFilter && $bActiveFilter[1] != 'clear' ? 'active-filter' : '')?>">
				<?=CMax::showIconSvg("icon", SITE_TEMPLATE_PATH.'/images/svg/catalog/filter.svg', '', '', true, false);?>
				<span class="font_upper_md font-bold darken dotted"><?=\Bitrix\Main\Localization\Loc::getMessage("CATALOG_SMART_FILTER_TITLE");?></span>
			</div>
			<div class="controls-hr"></div>
		</div>
	<?endif;?>
	<!--noindex-->
		<div class="filter-panel__sort pull-left hidden-xs">
			<?
			$arAvailableSort = array();
			$arSorts = $arParams["SORT_BUTTONS"];

			if(in_array("POPULARITY", $arSorts)){
				$arAvailableSort["SHOWS"] = array("SHOWS", "desc");
			}
			if(in_array("NAME", $arSorts)){
				$arAvailableSort["NAME"] = array("NAME", "asc");
			}
			if(in_array("PRICE", $arSorts)){
				$arSortPrices = $arParams["SORT_PRICES"];
				if($arSortPrices == "MINIMUM_PRICE" || $arSortPrices == "MAXIMUM_PRICE"){
					$arAvailableSort["PRICE"] = array("PROPERTY_".$arSortPrices, "desc");
				}
				else{
					if($arSortPrices == "REGION_PRICE")
					{
						global $arRegion;
						if($arRegion)
						{
							if(!$arRegion["PROPERTY_SORT_REGION_PRICE_VALUE"] || $arRegion["PROPERTY_SORT_REGION_PRICE_VALUE"] == "component")
							{
								$price = CCatalogGroup::GetList(array(), array("NAME" => $arParams["SORT_REGION_PRICE"]), false, false, array("ID", "NAME"))->GetNext();
								$arAvailableSort["PRICE"] = array("CATALOG_PRICE_".$price["ID"], "desc");
							}
							else
							{
								$arAvailableSort["PRICE"] = array("CATALOG_PRICE_".$arRegion["PROPERTY_SORT_REGION_PRICE_VALUE"], "desc");
							}
						}
						else
						{
							$price_name = ($arParams["SORT_REGION_PRICE"] ? $arParams["SORT_REGION_PRICE"] : "BASE");
							$price = CCatalogGroup::GetList(array(), array("NAME" => $price_name), false, false, array("ID", "NAME"))->GetNext();
							$arAvailableSort["PRICE"] = array("CATALOG_PRICE_".$price["ID"], "desc");
						}
					}
					else
					{
						$price = CCatalogGroup::GetList(array(), array("NAME" => $arParams["SORT_PRICES"]), false, false, array("ID", "NAME"))->GetNext();
						$arAvailableSort["PRICE"] = array("CATALOG_PRICE_".$price["ID"], "desc");
					}
				}
			}
			if(in_array("QUANTITY", $arSorts)){
				$arAvailableSort["CATALOG_AVAILABLE"] = array("QUANTITY", "desc");
			}			

			$defaulSortButtons = array("SORT","POPULARITY", "NAME", "PRICE", "QUANTITY", "CUSTOM");
			$propsInSort = array();
			$propsInSortName = array();
			foreach($arSorts as $sort_prop){
				if(!in_array($sort_prop, $defaulSortButtons)){
					$arAvailableSort['PROPERTY_'.$sort_prop] = array('PROPERTY_'.$sort_prop, "desc");
					$propsInSort[] = $sort_prop;
				}
			}
			if(is_array($propsInSort) && count($propsInSort)>0 ){
				foreach($propsInSort as $propSortCode){
					$dbRes = CIBlockProperty::GetList(array(), array('ACTIVE' => 'Y', 'IBLOCK_ID' => $arParams['IBLOCK_ID'], 'CODE' => $propSortCode));
					while($arPropperty = $dbRes->Fetch()){
						$propsInSortName['PROPERTY_'.$arPropperty['CODE']] = $arPropperty['NAME'];
					}
				}
				
			}
						
			$sortElementField = ToUpper($arParams["ELEMENT_SORT_FIELD"]);
			if(in_array("CUSTOM", $arSorts) && !array_key_exists($sortElementField, $arAvailableSort) ){
				$arAvailableSort[$sortElementField] = array("CUSTOM", ToLower($arParams["ELEMENT_SORT_ORDER"]));
			}			

			$sort = "SHOWS";
			$customSort = false;
			if((array_key_exists("sort", $_REQUEST) && array_key_exists(ToUpper($_REQUEST["sort"]), $arAvailableSort)) || (array_key_exists("sort", $_SESSION) && array_key_exists(ToUpper($_SESSION["sort"]), $arAvailableSort)) || $arParams["ELEMENT_SORT_FIELD"]){
				if($_REQUEST["sort"]){
					$sort = htmlspecialcharsbx(ToUpper($_REQUEST["sort"]));
					$_SESSION["sort"] = htmlspecialcharsbx(ToUpper($_REQUEST["sort"]));
				}
				elseif($_SESSION["sort"]){
					$sort = ToUpper($_SESSION["sort"]);
				}
				else{
					$sort = ToUpper($arParams["ELEMENT_SORT_FIELD"]);					
					$sort = (strpos($sort, 'SCALED_PRICE_') === 0) ? 'PRICE' : $sort;					
				}
			}

			if( $sort === $sortElementField ){
				if(!array_key_exists($sortElementField, $arAvailableSort) || $arAvailableSort[$sortElementField][0] === 'CUSTOM'  ){
					$customSort = true;
				}				
			} 			
			
			$sort_order=$arAvailableSort[$sort][1];
			if((array_key_exists("order", $_REQUEST) && in_array(ToLower($_REQUEST["order"]), Array("asc", "desc"))) || (array_key_exists("order", $_REQUEST) && in_array(ToLower($_REQUEST["order"]), Array("asc", "desc")) ) || $arParams["ELEMENT_SORT_ORDER"]){
				if($_REQUEST["order"]){
					$sort_order = htmlspecialcharsbx($_REQUEST["order"]);
					$_SESSION["order"] = htmlspecialcharsbx($_REQUEST["order"]);
				}
				elseif($_SESSION["order"]){
					$sort_order = $_SESSION["order"];
				}
				else{
					$sort_order = ToLower($arParams["ELEMENT_SORT_ORDER"]);
				}
			}
			$arDelUrlParams = array('sort', 'order', 'control_ajax', 'ajax_get_filter', 'linerow', 'display');
			?>
			<?if($arAvailableSort):?>
				<div class="dropdown-select">
					<div class="dropdown-select__title font_xs darken">
						<span>
							<?if($sort_order && $sort):?>								
								<?if( in_array($sort, array_keys($propsInSortName)) ):?>
									<?=\Bitrix\Main\Localization\Loc::getMessage('SORT_TITLE_PROPETY', array('#CODE#' => $propsInSortName[$sort])).\Bitrix\Main\Localization\Loc::getMessage('SECT_ORDER_'.$sort_order)?>
								<?else:?>
									<?=\Bitrix\Main\Localization\Loc::getMessage('SECT_SORT_'.($customSort ? 'CUSTOM' : $sort)).\Bitrix\Main\Localization\Loc::getMessage('SECT_ORDER_'.$sort_order)?>
								<?endif;?>
							<?else:?>
								<?=\Bitrix\Main\Localization\Loc::getMessage('NOTHING_SELECTED');?>
							<?endif;?>
						</span>
						<?=CMax::showIconSvg("down", SITE_TEMPLATE_PATH.'/images/svg/trianglearrow_down.svg', '', '', true, false);?>
					</div>
					<div class="dropdown-select__list dropdown-menu-wrapper" role="menu">
						<div class="dropdown-menu-inner rounded3">
							<?$arOrder = ['desc', 'asc']?>
							<?foreach($arAvailableSort as $key => $arVals):?>
								<?foreach($arOrder as $value):?>
									<div class="dropdown-select__list-item font_xs">
										<?$newSort = $sort_order == 'desc' ? 'asc' : 'desc';
										$current_url = $APPLICATION->GetCurPageParam('sort='.$key.'&order='.$value, $arDelUrlParams);
										$url = str_replace('+', '%2B', $current_url);?>
										<?if($bCurrentLink = ($sort == $key && $sort_order == $value)):?>
											<span class="dropdown-select__list-link dropdown-select__list-link--current">
										<?else:?>
											<a href="<?=$url;?>" class="dropdown-select__list-link <?=$value?> <?=$key?> darken <?=($arParams['AJAX_CONTROLS'] == 'Y' ? ' js-load-link' : '');?>" data-url="<?=$url;?>" rel="nofollow">
										<?endif;?>
											<?if( in_array($key, array_keys($propsInSortName)) ):?>
												<span><?=\Bitrix\Main\Localization\Loc::getMessage('SORT_TITLE_PROPETY', array('#CODE#' => $propsInSortName[$key])).\Bitrix\Main\Localization\Loc::getMessage('SECT_ORDER_'.$value)?></span>
											<?else:?>
												<span><?=\Bitrix\Main\Localization\Loc::getMessage('SECT_SORT_'.($arAvailableSort[$key][0] === 'CUSTOM' ? 'CUSTOM' : $key)).\Bitrix\Main\Localization\Loc::getMessage('SECT_ORDER_'.$value)?></span>												
											<?endif;?>
										<?if($bCurrentLink):?>
											</span>
										<?else:?>
											</a>
										<?endif;?>
									</div>
								<?endforeach?>
							<?endforeach;?>
						</div>
					</div>
				</div>
			<?endif;?>
			<?
			if($sort == "PRICE"){
				$sort = $arAvailableSort["PRICE"][0];
			}
			if($sort == "CATALOG_AVAILABLE"){
				$sort = CMax::checkVersionModule('20.100.0', 'main') ? "CATALOG_AVAILABLE" : "CATALOG_QUANTITY";
			}
			?>
		</div>
		<div class="filter-panel__view controls-view pull-right">
			<?foreach($arDisplays as $displayType):?>
				<?
				$current_url = '';
				$current_url = $APPLICATION->GetCurPageParam('display='.$displayType, $arDelUrlParams);
				$url = str_replace('+', '%2B', $current_url);
				?>
				<?if($display == $displayType):?>
					<span title="<?=\Bitrix\Main\Localization\Loc::getMessage("SECT_DISPLAY_".strtoupper($displayType))?>" class="controls-view__link controls-view__link--<?=$displayType?> controls-view__link--current"><?=CMax::showIconSvg("type", SITE_TEMPLATE_PATH.'/images/svg/catalog/'.$displayType.'type.svg', '', '', true, false);?></span>
				<?else:?>
					<a rel="nofollow" href="<?=$url;?>" data-url="<?=$url?>" title="<?=\Bitrix\Main\Localization\Loc::getMessage("SECT_DISPLAY_".strtoupper($displayType))?>" class="controls-view__link controls-view__link--<?=$displayType?> muted<?=($arParams['AJAX_CONTROLS'] == 'Y' ? ' js-load-link' : '');?>"><?=CMax::showIconSvg("type", SITE_TEMPLATE_PATH.'/images/svg/catalog/'.$displayType.'type.svg', '', '', true, false);?></a>
				<?endif;?>
			<?endforeach;?>
		</div>
		<?if($display == 'block'):?>
			<div class="filter-panel__view controls-linecount pull-right">
				<?$arLineCount = [3,4];?>
				<?if(array_key_exists("linerow", $_REQUEST) || (array_key_exists("linerow", $_SESSION)) || $arParams["LINE_ELEMENT_COUNT"])
				{
					if($_REQUEST["linerow"] && (in_array(trim($_REQUEST["linerow"]), $arLineCount)))
					{
						$linerow = htmlspecialcharsbx(trim($_REQUEST["linerow"]));
						$_SESSION["linerow"]=htmlspecialcharsbx(trim($_REQUEST["linerow"]));
					}
					elseif($_SESSION["linerow"] && (in_array(trim($_SESSION["linerow"]), $arLineCount)))
					{
						$linerow = $_SESSION["linerow"];
					}
					elseif($arParams["LINE_ELEMENT_COUNT"] && (in_array(trim($arParams["LINE_ELEMENT_COUNT"]), $arLineCount)))
					{
						$linerow = $arParams["LINE_ELEMENT_COUNT"];
					}
					else
					{
						$linerow = 4;
					}

				}
				else
				{
					$linerow = 4;
				}?>
				<?foreach($arLineCount as $value):?>
					<?
					$current_url = '';
					$current_url = $APPLICATION->GetCurPageParam('linerow='.$value, $arDelUrlParams);
					$url = str_replace('+', '%2B', $current_url);
					?>
					<?if($linerow == $value):?>
						<span title="<?=\Bitrix\Main\Localization\Loc::getMessage("SECT_DISPLAY_".$value)?>" class="controls-view__link controls-view__link--current"><?=CMax::showIconSvg("type", SITE_TEMPLATE_PATH.'/images/svg/catalog/'.$value.'inarow.svg', '', '', true, false);?></span>
					<?else:?>
						<a rel="nofollow" href="<?=$url;?>" data-url="<?=$url?>" title="<?=\Bitrix\Main\Localization\Loc::getMessage("SECT_DISPLAY_".$value)?>" class="controls-view__link muted<?=($arParams['AJAX_CONTROLS'] == 'Y' ? ' js-load-link' : '');?>"><?=CMax::showIconSvg("type", SITE_TEMPLATE_PATH.'/images/svg/catalog/'.$value.'inarow.svg', '', '', true, false);?></a>
					<?endif;?>
				<?endforeach;?>
				<div class="controls-hr"></div>
			</div>
		<?endif;?>
		<div class="clearfix"></div>
	<!--/noindex-->
</div>