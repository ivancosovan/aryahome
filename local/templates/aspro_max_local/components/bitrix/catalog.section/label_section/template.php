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
    <section class="products">
		<p class="title_section"><?=$arParams['BLOCK_TITLE']?></p>
        <div class="products__grid _flex" data-pagination="container">
            <?foreach($arResult['ITEMS'] as $item){
				$item['DETAIL_PAGE_URL'] = '/catalog/'.$arResult['SECTION_CODES'][$item['IBLOCK_SECTION_ID']].'/'.$item['CODE'].'/'; // по нормальному не отдает урл
				$APPLICATION->IncludeFile($templateFolder."/item.php", Array('ITEM'=>$item), array());
			}?>                						
       </div>         
</section>
<?}?>	