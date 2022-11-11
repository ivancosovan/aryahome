<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<? $this->setFrameMode( true ); ?>
<?use \Bitrix\Main\Localization\Loc;?>
<?if($arResult["SECTIONS"]){?>
	<?global $arTheme;
	$bSlick = ($arParams['NO_MARGIN'] == 'Y');
	$bIcons = ($arParams['SHOW_ICONS'] == 'Y');?>
	<?foreach( $arResult["SECTIONS"] as $arItems ){
		$this->AddEditAction($arItems['ID'], $arItems['EDIT_LINK'], CIBlock::GetArrayByID($arItems["IBLOCK_ID"], "SECTION_EDIT"));
		$this->AddDeleteAction($arItems['ID'], $arItems['DELETE_LINK'], CIBlock::GetArrayByID($arItems["IBLOCK_ID"], "SECTION_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_SECTION_DELETE_CONFIRM')));	
		?>
		<div class="col-lg-3 col-md-4 col-xs-6 col-xxs-12">
			<div class="section-compact-list__item item bordered box-shadow flexbox flexbox--row" id="<?=$this->GetEditAreaId($arItems['ID']);?>">
				<?if($arParams["SHOW_SECTION_LIST_PICTURES"]=="Y"):?>
					<div class="section-compact-list__image<?=($bIcons && $arItems["UF_CATALOG_ICON"] ? ' with-icons colored_theme_svg' : '');?> flexbox flexbox--row">
						<?\Aspro\Functions\CAsproMaxItem::showSectionImg($arParams, $arItems, $bIcons);?>
					</div>
				<?endif;?>
				<div class="section-compact-list__info">
					<a href="<?=$arItems["SECTION_PAGE_URL"]?>" class="section-compact-list__link dark_link option-font-bold"><span><?=$arItems["NAME"]?></span></a>
					<?if($arItems["ELEMENT_CNT"]):?>
						<span class="element-count2 muted font_upper"><?=\Aspro\Functions\CAsproMax::declOfNum($arItems["ELEMENT_CNT"], array(Loc::getMessage('COUNT_ELEMENTS_TITLE'), Loc::getMessage('COUNT_ELEMENTS_TITLE_2'), Loc::getMessage('COUNT_ELEMENTS_TITLE_3')))?></span>
					<?endif;?>
				</div>
			</div>
		</div>
		<?}?>	
	</div>
</div>
