<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?$this->setFrameMode(true);?>
<?// intro text?>
<?global $isHideLeftBlock, $arTheme;?>
<?
if(isset($arParams["TYPE_LEFT_BLOCK"]) && $arParams["TYPE_LEFT_BLOCK"]!='FROM_MODULE'){
	$arTheme['LEFT_BLOCK']['VALUE'] = $arParams["TYPE_LEFT_BLOCK"];
}

if(isset($arParams["SIDE_LEFT_BLOCK"]) && $arParams["SIDE_LEFT_BLOCK"]!='FROM_MODULE'){
	$arTheme['SIDE_MENU']['VALUE'] = $arParams["SIDE_LEFT_BLOCK"];
}

?>

<?
if(!$isHideLeftBlock && $APPLICATION->GetProperty("HIDE_LEFT_BLOCK_LIST") == "Y"){
	$APPLICATION->SetPageProperty("HIDE_LEFT_BLOCK", "Y");
}
?>
<?$bIsHideLeftBlock = ($APPLICATION->GetProperty("HIDE_LEFT_BLOCK") == "Y");?>

<div class="text_before_items"><?$APPLICATION->IncludeComponent(
		"bitrix:main.include",
		"",
		Array(
			"AREA_FILE_SHOW" => "page",
			"AREA_FILE_SUFFIX" => "inc",
			"EDIT_TEMPLATE" => ""
		)
	);?></div>
<?
$arItemFilter = CMax::GetIBlockAllElementsFilter($arParams);

if($arParams['CACHE_GROUPS'] == 'Y')
{
	$arItemFilter['CHECK_PERMISSIONS'] = 'Y';
	$arItemFilter['GROUPS'] = $GLOBALS["USER"]->GetGroups();
}

$itemsCnt = CMaxCache::CIblockElement_GetList(array("CACHE" => array("TAG" => CMaxCache::GetIBlockCacheTag($arParams["IBLOCK_ID"]))), $arItemFilter, array());?>

<?if(!$itemsCnt):?>
	<div class="alert alert-warning"><?=GetMessage("SECTION_EMPTY")?></div>
<?else:?>

	<?$this->SetViewTarget('product_share');?>
		<?if($arParams['USE_RSS'] !== 'N'):?>
			<div class="colored_theme_hover_bg-block">
				<?=CMax::ShowRSSIcon($arResult['FOLDER'].$arResult['URL_TEMPLATES']['rss']);?>
			</div>
		<?endif;?>
	<?$this->EndViewTarget();?>

	<?
	$arAllSections = $aMenuLinksExt = [];
	$arSections = CMaxCache::CIBLockSection_GetList(array('SORT' => 'ASC', 'NAME' => 'ASC', 'CACHE' => array('TAG' => CMaxCache::GetIBlockCacheTag($arParams['IBLOCK_ID']), 'GROUP' => array('ID'), 'MULTI' => 'N', 'URL_TEMPLATE' => $arResult['FOLDER'].$arResult['URL_TEMPLATES']['section'])), array_merge($arItemFilter, array(/*'<=DEPTH_LEVEL' => 2,*/ 'CNT_ACTIVE' => "Y")), false, array('ID', 'SECTION_PAGE_URL', 'DEPTH_LEVEL', 'IBLOCK_SECTION_ID'));
	$arSectionsByParentSectionID = CMaxCache::GroupArrayBy($arSections, array('MULTI' => 'Y', 'GROUP' => array('IBLOCK_SECTION_ID')));
	if ($arSections) {
		CMax::getSectionChilds(false, $arSections, $arSectionsByParentSectionID, $arItemsBySectionID, $aMenuLinksExt, true);
	}
	
	$arAllSections = CMax::getChilds2($aMenuLinksExt);
	
	if (isset($arItemFilter['CODE'])) {
		unset($arItemFilter['CODE']);
		unset($arItemFilter['SECTION_CODE']);
	}
	if (isset($arItemFilter['ID'])) {
		unset($arItemFilter['ID']);
		unset($arItemFilter['SECTION_ID']);
	}
	?>
	<?
	$arTags = array();
	if ($arAllSections) {
		foreach ($arAllSections as $key => $arSection) {
			$arElements = CMaxCache::CIblockElement_GetList(array('CACHE' => array('TAG' => CMaxCache::GetIBlockCacheTag($arParams['IBLOCK_ID']), 'MULTI' => 'Y')), array_merge($arItemFilter, array("SECTION_ID" => $arSection["PARAMS"]["ID"], "INCLUDE_SUBSECTIONS" => "Y")), false, false, array('ID', 'TAGS'));
			if (!$arElements) {
				unset($arAllSections[$key]);
			} else {
				foreach ($arElements as $arElement) {
					if ($arElement['TAGS']) {
						$arTags[] = explode(',', $arElement['TAGS']);
					}
				}
				$arAllSections[$key]['ELEMENT_COUNT'] = count($arElements);
			}
		}
	} else {
		$arElements = CMaxCache::CIblockElement_GetList(array('CACHE' => array('TAG' => CMaxCache::GetIBlockCacheTag($arParams['IBLOCK_ID']), 'MULTI' => 'Y')), $arItemFilter, false, false, array('ID', 'TAGS'));

		foreach ($arElements as $arElement) {
			if ($arElement['TAGS']) {
				$arTags[] = explode(',', $arElement['TAGS']);
			}
		}
	}
	?>
	<?$this->__component->__template->SetViewTarget('under_sidebar_content');?>
		<?if($arAllSections):?>
			<div class="categories_block menu_top_block">
				<ul class="categories left_menu dropdown">
					<?foreach($arAllSections as $arSection):
						if(isset($arSection['TEXT']) && $arSection['TEXT']):?>
							<li class="categories_item item v_bottom <?=($arSection['CHILD'] ? 'has-child' : '')?>">
								<a href="<?=$arSection['LINK'];?>" class="categories_link bordered rounded2">
									<span class="categories_name darken"><?=$arSection['TEXT'];?></span>
									<span class="categories_count muted"><?=$arSection['ELEMENT_COUNT'];?></span>
									<?if ($arSection['CHILD']):?>
										<?=CMax::showIconSvg("down", SITE_TEMPLATE_PATH.'/images/svg/trianglearrow_down.svg', '', '', true, false);?>
										<span class="toggle_block"></span>
									<?endif;?>
								</a>
								<?if ($arSection['CHILD']):?>
									<div class="child_container dropdown">
										<div class="child_wrapp">
											<ul class="child">
												<?foreach ($arSection['CHILD'] as $arChild):?>
													<li class="menu_item hover_color_theme ">
														<a href="<?=$arChild['LINK'];?>">
															<?=$arChild['TEXT'];?>
														</a>
													</li>
												<?endforeach;?>
											</ul>
										</div>
									</div>
								<?endif;?>
							</li>
						<?endif;?>
					<?endforeach;?>
				</ul>
			</div>
		<?endif;?>
		<?$APPLICATION->IncludeComponent(
			"bitrix:search.tags.cloud",
			"main",
			Array(
				"CACHE_TIME" => "86400",
				"CACHE_TYPE" => "A",
				"CHECK_DATES" => "Y",
				"COLOR_NEW" => "3E74E6",
				"COLOR_OLD" => "C0C0C0",
				"COLOR_TYPE" => "N",
				"TAGS_ELEMENT" => $arTags,
				"FILTER_NAME" => $arParams["FILTER_NAME"],
				"FONT_MAX" => "50",
				"FONT_MIN" => "10",
				"PAGE_ELEMENTS" => "150",
				"PERIOD" => "",
				"PERIOD_NEW_TAGS" => "",
				"SHOW_CHAIN" => "N",
				"SORT" => "NAME",
				"TAGS_INHERIT" => "Y",
				"URL_SEARCH" => SITE_DIR."search/index.php",
				"WIDTH" => "100%",
				"arrFILTER" => array("iblock_aspro_max_content"),
				"arrFILTER_iblock_aspro_max_content" => array($arParams["IBLOCK_ID"])
			), $component, array('HIDE_ICONS' => 'Y')
		);?>
	<?$this->__component->__template->EndViewTarget();?>


	<?/*years block*/?>
	<?$arItems = CMaxCache::CIBLockElement_GetList(array('SORT' => 'ASC', 'NAME' => 'ASC', 'CACHE' => array('TAG' => CMaxCache::GetIBlockCacheTag($arParams['IBLOCK_ID']))), $arItemFilter, false, false, array('ID', 'NAME', 'ACTIVE_FROM'));
	$arYears = array();
	if($arItems)
	{
		foreach($arItems as $arItem)
		{
			if($arItem['ACTIVE_FROM'])
			{
				if($arDateTime = ParseDateTime($arItem['ACTIVE_FROM'], FORMAT_DATETIME)){}
					$arYears[$arDateTime['YYYY']] = $arDateTime['YYYY'];
			}
		}
		if($arYears)
		{
			if($arParams['USE_FILTER'] != 'N')
			{
				rsort($arYears);
				$bHasYear = (isset($_GET['year']) && (int)$_GET['year']);
				$year = ($bHasYear ? (int)$_GET['year'] : 0);?>
				<div class="select_head_wrap">
					<div class="menu_item_selected font_upper_md rounded3 bordered visible-xs font_xs darken"><span></span>
						<?=CMax::showIconSvg("down", SITE_TEMPLATE_PATH.'/images/svg/trianglearrow_down.svg', '', '', true, false);?>
					</div>
					<div class="head-block top bordered-block rounded3 clearfix srollbar-custom">
						<div class="item-link font_upper_md  <?=($bHasYear ? '' : 'active');?>">
							<div class="title">
								<?if($bHasYear):?>
									<a class="btn-inline dark_link" href="<?=$arResult['FOLDER'];?>"><?=GetMessage('ALL_TIME');?></a>
								<?else:?>
									<span class="btn-inline darken"><?=GetMessage('ALL_TIME');?></span>
								<?endif;?>
							</div>
						</div>
						<?foreach($arYears as $value):
							$bSelected = ($bHasYear && $value == $year);?>
							<div class="item-link font_upper_md <?=($bSelected ? 'active' : '');?>">
								<div class="title btn-inline darken">
									<?if($bSelected):?>
										<span class="btn-inline darken"><?=$value;?></span>
									<?else:?>
										<a class="btn-inline dark_link" href="<?=$APPLICATION->GetCurPageParam('year='.$value, array('year'));?>"><?=$value;?></a>
									<?endif;?>
								</div>
							</div>
						<?endforeach;?>
					</div>
				</div>
				<?
				if($bHasYear)
				{
					$GLOBALS[$arParams["FILTER_NAME"]][] = array(
						">=DATE_ACTIVE_FROM" => ConvertDateTime("01.01.".$year, FORMAT_DATE,''),
						"<DATE_ACTIVE_FROM" => ConvertDateTime("01.01.".($year+1), FORMAT_DATE,''),
					);
				}
				?>
			<?}
		}
	}?>
	<?/* end years block*/?>


	<?// section elements?>
	<?if((isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == "xmlhttprequest") || (strtolower($_REQUEST['ajax']) == 'y'))
	{
		$APPLICATION->RestartBuffer();
	}?>
	<?$sViewElementsTemplate = ($arParams["SECTION_ELEMENTS_TYPE_VIEW"] == "FROM_MODULE" ? $arTheme["BLOG_PAGE"]["VALUE"] : $arParams["SECTION_ELEMENTS_TYPE_VIEW"]);?>
	<?@include_once('page_blocks/'.$sViewElementsTemplate.'.php');?>

	<?if((isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == "xmlhttprequest") || (strtolower($_REQUEST['ajax']) == 'y'))
	{
		die();
	}?>
<?endif;?>
