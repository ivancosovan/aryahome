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
<?
if($arResult['SECTIONS']){?>
 <section class="maxwidth-theme grid">
        <p class="grid-title">Популярные категории</p>
            <div class="inner-categories__grid">
				<?foreach($arResult['SECTIONS'] as $k=>$section){?>
                    <a href="<?=$section['SECTION_PAGE_URL']?>" class="inner-categories__item" data-item="<?=$k+1?>" style="background-image:url(<?=$section['DETAIL_PICTURE']?>)">
                        <span class="inner-categories__name"><?=$section['NAME']?></span>

                        <span class="inner-categories__bg"></span>
                    </a>
				<?}?>
            </div>
     </section>
<?}?>