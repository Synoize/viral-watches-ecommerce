jQuery(function($) {
    $('.product-thumb').on('click', function() {
        var src = $(this).data('src');
        $(this).closest('.card').find('img:first').attr('src', src);
    });
});
