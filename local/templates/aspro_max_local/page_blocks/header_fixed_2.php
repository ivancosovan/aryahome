<?
global $arTheme, $arRegion;
$logoClass = ($arTheme['COLORED_LOGO']['VALUE'] !== 'Y' ? '' : ' colored');
?>
<div class="maxwidth-theme">
	<div class="logo-row v2 row margin0 menu-row">

		<?if($arTheme['HEADER_TYPE']['VALUE'] != 29):?>
			<div class="burger inner-table-block"><?=CMax::showIconSvg("burger dark", SITE_TEMPLATE_PATH."/images/svg/burger.svg");?></div>
		<?endif;?>

		<?if($arTheme['HEADER_TYPE']['VALUE'] != 28 && $arTheme['HEADER_TYPE']['VALUE'] != 29):?>
			<div class="inner-table-block nopadding logo-block">
				<div class="logo<?=$logoClass?>">
					<?=CMax::ShowLogoFixed();?>
				</div>
			</div>
		<?endif;?>
		<div class="inner-table-block menu-block">
			<div class="navs table-menu js-nav">
				<?if(CMax::nlo('menu-fixed')):?>
				<!-- noindex -->
				<nav class="mega-menu sliced">
					<?$APPLICATION->IncludeComponent("bitrix:main.include", ".default",
						array(
							"COMPONENT_TEMPLATE" => ".default",
							"PATH" => SITE_DIR."include/menu/menu.top.php",
							"AREA_FILE_SHOW" => "file",
							"AREA_FILE_SUFFIX" => "",
							"AREA_FILE_RECURSIVE" => "Y",
							"EDIT_TEMPLATE" => "include_area.php"
						),
						false, array("HIDE_ICONS" => "Y")
					);?>
				</nav>
				<!-- /noindex -->
				<?endif;?>
				<?CMax::nlo('menu-fixed');?>
			</div>
		</div>
		<div class=" inner-table-block">
			<div class="wrap_icon">
				<button class="top-btn inline-search-show ">
					<?=CMax::showIconSvg("search", SITE_TEMPLATE_PATH."/images/svg/Search.svg");?>
				</button>
			</div>
		</div>
		<div class="inner-table-block nopadding small-block">
			<div class="wrap_icon wrap_cabinet">
				<?=CMax::ShowCabinetLink(true, false, 'big');?>
			</div>
		</div>
		<?/*=CMax::ShowBasketWithCompareLink('inner-table-block', 'big');*/?>
		<?=ShowBasketWithCompareLinkCustom('inner-table-block', 'big');?>
	</div>
</div>
<?=\Aspro\Functions\CAsproMax::showProgressBarBlock();?>
