$( document ).ready(function() {
    $('.delivery-cancel').on('click', function(e){
        e.preventDefault();
        var thisClaim = $(this).attr('data-claim');
        var data = {
                    id: thisClaim,
                    version:$(this).attr('data-version'), 
                    state:$(this).attr('data-state')
                };
        BX.ajax({
            url: '/statusy-dostavki/ajax-cancel.php',
            method: 'POST',
            dataType: 'json',
            async: false,
            data: data,
            onsuccess: function (result) {
                if(result.SUCCESS){ 
                    $('a[data-claim="'+thisClaim+'"]').css("text-decoration","line-through");
                    $('a[data-claim="'+thisClaim+'"] .error').css('visibility', 'hidden');
                }
                else $('a[data-claim="'+thisClaim+'"] .error').css('visibility', 'visible');
            }
        });
    });
});
