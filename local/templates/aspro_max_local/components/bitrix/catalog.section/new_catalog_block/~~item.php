<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$item = $arParams['ITEM'];
$this_section_obj = $arParams['this'];
$arParams= $arParams['params'];
$price = current($item['PRICE_MATRIX']['MATRIX'][7])['DISCOUNT_PRICE'];
$price = array(
	'VALUE'=>$price,
	'PRINT_DISCOUNT_VALUE'=> number_format($price, 0, '', ' ') .' ₽'
);
$discount_price = current($item['PRICE_MATRIX']['MATRIX'][8])['DISCOUNT_PRICE'];
$discount_price = array(
	'VALUE'=>$discount_price,
	'PRINT_DISCOUNT_VALUE'=> number_format($discount_price, 0, '', ' ') .' ₽'
);
if($discount_price && $discount_price['VALUE'] < $price['VALUE']){
	$percent = 100-intval($discount_price['VALUE']*100 / $price['VALUE']);
} else {
	$discount_price = false;
}

if($item['DETAIL_PICTURE']){
	$arFileTmp = CFile::ResizeImageGet(
            $item["DETAIL_PICTURE"],
            array("width" => 500, "height" => 500),
            BX_RESIZE_IMAGE_EXACT,
        );
	$item['PICTURE'] = $arFileTmp['src'];
}

if(!is_file($_SERVER['DOCUMENT_ROOT'].$arFileTmp['src'])){
	$item['PICTURE'] = '/local/img/picture.missing_square.png';
}

$available = $item['CATALOG_AVAILABLE'] == 'Y';
if ($fast_view_text_tmp = \CMax::GetFrontParametrValue('EXPRESSION_FOR_FAST_VIEW'))
		$fast_view_text = $fast_view_text_tmp;
	else
		$fast_view_text = Loc::getMessage('FAST_VIEW');
?>

<div class="products-product"  data-product="<?=$item['ID']?>" id="<?=$this_section_obj->GetEditAreaID($item['ID'])?>">
	<div>
		<div class="products-product__wrapp">
		 <div class="products-product__btn _flex">
			<span class="favorite-btn favorite-js wish_item" data-item="<?=$item['ID']?>">
				   <svg width="32" height="31" viewBox="0 0 32 31" fill="none" xmlns="http://www.w3.org/2000/svg">
						<g filter="url(#filter0_d_751_9596)">
						<path d="M21 5.39062C18.5 5.39062 16 7.39062 16 9.39062C16 7.39062 13.5 5.39062 11 5.39062C8.5 5.39062 6.5 7.39062 6.5 10.3906C6.5 13.3906 8.5 19.3906 16 22.3906C23.5 19.3906 25.5 13.3906 25.5 10.3906C25.5 7.39062 23.5 5.39062 21 5.39062Z" fill="white"/>
						<path d="M21 5.39062C18.5 5.39062 16 7.39062 16 9.39062C16 7.39062 13.5 5.39062 11 5.39062C8.5 5.39062 6.5 7.39062 6.5 10.3906C6.5 13.3906 8.5 19.3906 16 22.3906C23.5 19.3906 25.5 13.3906 25.5 10.3906C25.5 7.39062 23.5 5.39062 21 5.39062Z" stroke="white" stroke-linecap="round" stroke-linejoin="round"/>
						</g>
						<defs>
						<filter id="filter0_d_751_9596" x="0" y="0.890625" width="32" height="30" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
						<feFlood flood-opacity="0" result="BackgroundImageFix"/>
						<feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha"/>
						<feOffset dy="2"/>
						<feGaussianBlur stdDeviation="3"/>
						<feComposite in2="hardAlpha" operator="out"/>
						<feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.16 0"/>
						<feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_751_9596"/>
						<feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_751_9596" result="shape"/>
						</filter>
						</defs>
				</svg>
			</span>
			
		</div>
		<div class="fast_view_button 3 fast_view_button--full fast_view_button_cus">
			<span title="<?= $fast_view_text ?>" class="rounded2 font_upper_xs" data-sort-elems="<?=strtolower($arParams['ELEMENT_SORT_FIELD'])?>:<?=strtolower($arParams['ELEMENT_SORT_ORDER'])?>,<?=strtolower($arParams['ELEMENT_SORT_FIELD2'])?>:<?=strtolower($arParams['ELEMENT_SORT_ORDER2'])?>" data-event="jqm" data-param-form_id="fast_view" data-param-iblock_id="<?= $arParams["IBLOCK_ID"]; ?>" data-param-id="<?= $item["ID"]; ?>" data-param-item_href="<?= urlencode($item["DETAIL_PAGE_URL"]); ?>" data-name="fast_view"><?= \CMax::showIconSvg("fw ", SITE_TEMPLATE_PATH . "/images/svg/quickview" . $typeSvg . ".svg"); ?><?= $fast_view_text ?></span>
		</div>
		<?if($percent){?>
			<div class="products-product-prices__discount" data-discount="<?=$percent?>">-<?=$percent?>%</div>
		<?}?>
			<?if($item['PROPERTIES']['HIT']['VALUE']){?>
				<div class="stickerz">
					<?foreach($item['PROPERTIES']['HIT']['VALUE'] as $val){?>
						<div class="stickerz__sticker">
							<span><?=$val?></span>
						</div>
					<?}?>
				</div>
			<?}?>
			<a href="<?=$item['DETAIL_PAGE_URL']?>" class="products-product__thumb">
				<span class="products-product__img">
					<img src="<?=$item['PICTURE']?>" alt="<?=$item['NAME']?>" class="lozad">
				</span>
			</a>
		</div>
		 <div class="products-product__box">
			 <div class="products-product__name">
				<a href="<?=$item['DETAIL_PAGE_URL']?>"><?=$item['NAME']?></a>
			</div>
			<?if($item['SIZES']){
				$cur_size = $item['PROPERTIES']['OBSHCHIY_RAZMER_DLYA_SAYTA']['VALUE'] ? $item['PROPERTIES']['OBSHCHIY_RAZMER_DLYA_SAYTA']['VALUE'] : $item['PROPERTIES']['RAZMER']['VALUE']?>
				<div class="products-product_sizes">
					<?if(count($item['SIZES']) == 1){?>
						<span><?=current($item['SIZES'])['RAZMER']?></span>
					<?} else {
						foreach($item['SIZES'] as $size){?>
							<a data-offer="<?=$size['ID']?>" href="javascript:void(0)" class="variantz <?if($cur_size == $size['RAZMER']){?>active<?}?>" data-json-size='<?=json_encode($size, JSON_UNESCAPED_UNICODE)?>'><?=$size['RAZMER']?></a>
						<?}
					}?>
				</div>
			<?}?>
		</div>
	</div>
    <div class="products-product__box">
       
       
			<div class="products-product-prices _flex">
				<div class="products-product-prices__price" data-price="<?=$discount_price['VALUE'] ? $discount_price['VALUE'] : $price['VALUE']?>" ><?=($discount_price['PRINT_DISCOUNT_VALUE'] ? $discount_price['PRINT_DISCOUNT_VALUE'] : $price['PRINT_DISCOUNT_VALUE'])?></div>
				<?if($discount_price && $discount_price['VALUE']<$price['VALUE']){?>
					<div class="products-product-prices__oldprice" data-oldprice="<?=$price['VALUE']?>" ><?=$price['PRINT_DISCOUNT_VALUE']?></div>
					
				<?}?>
			</div>
   
		<?if($available){?>
			<div class="products-product__bot _flex">
				<button type="button" class="products-product__incart to-cart" data-item="<?=$item['ID']?>">
					<span>В корзину</span>
				</button>
			</div>
		<?}?>
	 </div>
</div>