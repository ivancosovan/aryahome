<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);
?>
<?if($arResult['ITEMS']){?>
    <section class="maxwidth-theme products">
		<p class="title_section"><?=$arParams['BLOCK_TITLE']?></p>
        <div class="products__grid _flex" data-pagination="container">
            <?foreach($arResult['ITEMS'] as $item){
				$item['DETAIL_PAGE_URL'] = '/catalog/'.$arResult['SECTION_CODES'][$item['IBLOCK_SECTION_ID']].'/'.$item['CODE'].'/'; // по нормальному не отдает урл
				
				$this->AddEditAction($item['ID'], $item['EDIT_LINK'], CIBlock::GetArrayByID($item["IBLOCK_ID"], "ELEMENT_EDIT"));
				$this->AddDeleteAction($item['ID'], $item['DELETE_LINK'], CIBlock::GetArrayByID($item["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
				
				$APPLICATION->IncludeFile($templateFolder."/item.php", Array('ITEM'=>$item, 'this'=>$this), array());
			}?> 
			<span class="fake_pagination"><?=getMessage('SHOW_MORE')?></span>
       </div>         
</section>
<?}?>	