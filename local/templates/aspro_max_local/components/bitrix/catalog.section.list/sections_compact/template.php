<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<? $this->setFrameMode( true ); ?>
<?use \Bitrix\Main\Localization\Loc;?>
<?if($arResult["SECTIONS"]){?>
	<?global $arTheme;
	$bSlick = ($arParams['NO_MARGIN'] == 'Y');
	$bIcons = ($arParams['SHOW_ICONS'] == 'Y');?>
	<style>
		.section-block .bx_filter_sect{
			padding-left: 0px !important; 
    		padding-right: 0px !important;
			border-right: 0px !important;
			border-left: 0px !important;
			font-family: 'Helvetica';
			font-style: normal;
			font-weight: 400;
			font-size: 15px;
			line-height: 17px;
			text-transform: capitalize;
		}

		@media (min-width: 768px) {
			.bx_filter_sect.compact {
				position: relative;
				top: 0!important;
				padding: 11px 0 11px;
			}

			.bx_filter_sect.compact .bx_filter_sect_section {
				margin: 0;
				background: 0 0;
				border: none;
			}

			.bx_filter_sect.compact .bx_filter_sect_parameters_box {
				margin: 4px 4px 4px;
				position: relative;
				float: left;
				padding: 0;
				border: none;
				user-select: none;
			}

			.bx_filter_sect.compact .bx_filter_sect_parameters_box_title {
				font-size: 13px;
			}

			.bx_filter_sect.compact .bx_filter_sect_block:not(.limited_block) {
				position: absolute;
				padding: 19px 19px 0;
				display: none;
				min-width: 232px;
				z-index: 3;
				border-radius: 3px;
				background: #fff;
				background: var(--card_bg_black);
				-webkit-box-shadow: 0 5px 25px 0 rgb(0 0 0 / 10%);
				-moz-box-shadow: 0 5px 25px 0 rgba(0,0,0,.1);
				box-shadow: 0 5px 25px 0 rgb(0 0 0 / 10%);
			}

			.bx_filter_sect.compact .bx_filter_sect_parameters_box .title.bx_filter_sect_parameters_box_title:not(.filter_title) {
				border: 1px solid #eee;
			}

		}

		

		.bx_filter_sect .bx_filter_sect_parameters_box {
			padding: 13px 18px 16px;
			border-bottom: 1px solid #eee;
			border-color: var(--stroke_black);
			position: relative;
			user-select: none;
			overflow: visible;
		}

		.bx_filter_sect .bx_filter_sect_parameters_box_title {
			font-size: 13px;
			display: block;
			font-weight: 400;
			cursor: pointer;
			position: relative;
			padding: 0 35px 0 0;
			color: #333;
			color: var(--white_text_black);
		}

		.bx_filter_sect .bx_filter_sect_parameters_box_title>div, .bx_filter_sect .bx_filter_sect_parameters_box_title>span {
			position: relative;
			display: inline-block;
		}

		.bx_filter_sect .bx_filter_sect_section {
			position: relative;
		}

		.section-block .bx_filter_sect.compact .bx_filter_sect_parameters_box>.bx_filter_sect_parameters_box_title {
			padding: 7px 31px 7px 20px !important;
			white-space: nowrap;
		}

		.section-block .bx_filter_sect.compact .bx_filter_sect_parameters_box .bx_filter_sect_parameters_box_title:not(.filter_title):not(:hover) {
			border-radius: 16px;
		}

		.section-block .bx_filter_sect.compact .bx_filter_sect_parameters_box .bx_filter_sect_parameters_box_title:not(.filter_title):not(:hover) .bx_filter_sect_block{
			display: none;
			opacity: 0;
		}

		.section-block .bx_filter_sect.compact .bx_filter_sect_parameters_box:hover .bx_filter_sect_block{
			display: block;
			opacity: 1;
			z-index: 390;
		}

		.section-block .bx_filter_sect.compact .bx_filter_sect_parameters_box:hover .svg {
			transform: rotate(180deg);
		}

		.section-block .bx_filter_sect.compact .bx_filter_sect_parameters_box>.bx_filter_sect_parameters_box_title:not(.filter_title)>.svg-inline-down {
			right: -6px !important;
		}

		.section-block .bx_filter_sect.compact .bx_filter_sect_parameters_box_title:hover {
			border-radius: 16px;
		}

		.bx_filter_sect.compact .bx_filter_sect_block:not(.limited_block) {
			padding: 19px 0px !important;
		}

		.bx_filter_sect.compact .bx_filter_sect_block .section-compact-list__link {
			padding: 0px 19px;
		}

		.bx_filter_sect.compact .bx_filter_sect_block .section-compact-list__link:hover {
			background: rgba(0,0,0,.1);
		}
	</style>
	<div class="bx_filter_sect compact">
		<div class=" bx_filter_sect_section clearfix">
			<?foreach( $arResult["SECTIONS"] as $arItems ){
				$this->AddEditAction($arItems['ID'], $arItems['EDIT_LINK'], CIBlock::GetArrayByID($arItems["IBLOCK_ID"], "SECTION_EDIT"));
				$this->AddDeleteAction($arItems['ID'], $arItems['DELETE_LINK'], CIBlock::GetArrayByID($arItems["IBLOCK_ID"], "SECTION_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_SECTION_DELETE_CONFIRM')));
			
				$arOrder = [
					'CACHE' => [
						'MULTI' => 'N', 
						'TAG' => CMaxCache::GetIBlockCacheTag($arParams["IBLOCK_ID"])
					]
				];
		
					
				
				$elementCountFilter = [
					'IBLOCK_ID' => $arItems['IBLOCK_ID'],
					'SECTION_ID' => $arItems['ID'],
					'CHECK_PERMISSIONS' => 'Y',
					'MIN_PERMISSION' => 'R',
					'INCLUDE_SUBSECTIONS' => 'Y',
					'ACTIVE' => 'Y',
				];
		
				if( $arParams['HIDE_NOT_AVAILABLE'] === 'Y' )
					$elementCountFilter['AVAILABLE'] = 'Y';
		
				$arFilter = $elementCountFilter;
		
				CMax::makeElementFilterInRegion($arFilter);
		
				if( is_array($GLOBALS['arRegionLink']) && CMax::GetFrontParametrValue('REGIONALITY_FILTER_ITEM') == 'Y' && CMax::GetFrontParametrValue('REGIONALITY_FILTER_CATALOG') == 'Y' ){
					$arFilter = array_merge($GLOBALS['arRegionLink'], $arFilter);
				}
		
				if( $arRegion ){			
					if( $arRegion['LIST_STORES'] && $arParams['HIDE_NOT_AVAILABLE'] === 'Y' ){
						$arStoresFilter = [];
		
						if(CMax::checkVersionModule('18.6.200', 'iblock')){
							$arStoresFilter = [
								'STORE_NUMBER' => $arParams['STORES'],
								'>STORE_AMOUNT' => 0,
							];
						}else{
							if(count($arParams['STORES']) > 1){
								$arStoresFilter = ['LOGIC' => 'OR'];
		
								foreach($arParams['STORES'] as $storeID){
									$arStoresFilter[] = [">CATALOG_STORE_AMOUNT_".$storeID => 0];
								}
							}else{
								foreach($arParams['STORES'] as $storeID){
									$arStoresFilter = [">CATALOG_STORE_AMOUNT_".$storeID => 0];
								}
							}
						}
		
						$arTmpFilter = [ '!TYPE' => ['2', '3'] ];
						
						if($arStoresFilter){
							if(!CMax::checkVersionModule('18.6.200', 'iblock') && count($arStoresFilter) > 1){
								$arTmpFilter[] = $arStoresFilter;
							}else{
								$arTmpFilter = array_merge($arTmpFilter, $arStoresFilter);
							}
						}
		
						$arFilter[] = [
							'LOGIC' => 'OR',
							['TYPE' => ['2', '3']],
							$arTmpFilter,
						];
					}
				}
		
				$countElements = CMaxCache::CIBlockElement_GetList($arOrder, $arFilter, []);
				$arSubSections = Array();
				$arItems['ELEMENT_CNT'] = $countElements;
				$arFilter = array('SECTION_ID' => $arItems['ID'],'ACTIVE' => 'Y'); // выберет потомков без учета активности
				$rsSect = CIBlockSection::GetList(array('ID' => 'asc'),$arFilter);
				while ($arSect = $rsSect->getNext())
				{
					$arSubSections[$arSect['ID']] = $arSect;
				}
				
			?>
			
				<div class="bx_filter_sect_parameters_box">
					<div class="bx_filter_sect_parameters_box_title title rounded3" id="<?=$this->GetEditAreaId($arItems['ID']);?>">
						<div class="text">
							<a href="<?=$arItems["SECTION_PAGE_URL"]?>" class="section-compact-list__link dark_link option-font-bold"><span style="font-family: 'Helvetica' !important;"><?=$arItems["NAME"]?> (<?=$arItems['ELEMENT_CNT']?>)</span></a>		
						</div>
						<?if(count($arSubSections) > 0):?>
							<i class="svg  svg-inline-down colored_theme_hover_bg-el" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" width="5" height="3" viewBox="0 0 5 3"><path class="cls-1" d="M250,80h5l-2.5,3Z" transform="translate(-250 -80)"></path></svg></i>
						<?endif;?>
					</div>
					<?if(count($arSubSections) > 0):?>
						<div class="bx_filter_sect_block">
							<?
								foreach($arSubSections as $subsection){
									?>
									<a href="<?=$subsection['SECTION_PAGE_URL']?>" class="section-compact-list__link dark_link"><span style="font-family: 'Helvetica' !important;"><?=$subsection['NAME']?></span></a>
									<?
								}
								
								
							?>
						</div>
					<?endif;?>
				</div>
				
			<?}?>
			
		</div>
		</div>
<?}?>
