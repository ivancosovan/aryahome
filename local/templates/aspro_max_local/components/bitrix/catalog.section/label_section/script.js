$(document).on('click', '.fake_pagination', function(){
	$(this).parent().find('.products-product').css('display','flex');
	$(this).hide();
})