jQuery(function($) {
    $('.woo-tweaks-fse-direct-checkout').on('click', function(e) {
        e.preventDefault();
        
        var $form = $('form.cart');
        
        if ($form.length > 0) {
            // Inject hidden input to flag the direct checkout
            if ($form.find('input[name="woo_tweaks_direct_checkout"]').length === 0) {
                $form.append('<input type="hidden" name="woo_tweaks_direct_checkout" value="1" />');
            }
            
            // Trigger native add to cart button click to pass validations (like variations)
            var $submitBtn = $form.find('button[type="submit"]');
            if ($submitBtn.length > 0) {
                $submitBtn.click();
            } else {
                $form.submit();
            }
        }
    });
});
