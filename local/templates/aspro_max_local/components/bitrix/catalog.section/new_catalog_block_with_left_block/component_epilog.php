<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
use Bitrix\Main\Loader;
Loader::includeModule("sale");
$basket = Bitrix\Sale\Basket::loadItemsForFUser(Bitrix\Sale\Fuser::getId(), Bitrix\Main\Context::getCurrent()->getSite());

foreach ($basket as $basketItem) {
	$pid = $basketItem->getField('PRODUCT_ID');
	   if($basketItem->isDelay() == true){
		   $inwished_arr[$pid] = $pid;
	   } else {
		 $basket_arr[$pid] = $pid; 
	   }
	  
}


foreach($basket_arr as $product){?>
	<script>
		$('.variantz[data-offer="<?=$product?>"]').addClass('inbasket');
		if($('.variantz[data-offer="<?=$product?>"]').hasClass('active')){
			$('.variantz[data-offer="<?=$product?>"].active').closest('.products-product').find('.to-cart').removeClass('to-cart').text('✓ В корзине').attr('onclick','window.location.href="/basket/"');
		}
	</script>
<?}
foreach($inwished_arr as $product){?>
	<script>
		$('.variantz[data-offer="<?=$product?>"]').addClass('inwished');
		if($('.variantz[data-offer="<?=$product?>"]').hasClass('active')){
			$('.variantz[data-offer="<?=$product?>"].active').closest('.products-product').find('.wish_item').addClass('active');
		}
	</script>
<?}