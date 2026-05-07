jQuery(function($) {
    $(document).on('click', '.tweak-tools-sdf30s-fse-direct-checkout', function(e) {
        e.preventDefault();
        
        var $btn = $(this);
        
        // Try to find a related form.cart
        var $form = $btn.closest('form.cart');
        
        if ($form.length === 0) {
            $form = $btn.closest('.product, .wc-block-grid__product, .type-product, .wp-block-post').find('form.cart');
        }
        
        if ($form.length === 0 && $('form.cart').length === 1) {
            $form = $('form.cart');
        }
        
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
        } else {
            // Fallback for archive pages without form.cart
            var $addToCartLink = $btn.closest('.product, .wc-block-grid__product, .type-product, .wp-block-post').find('.add_to_cart_button');
            
            if ($addToCartLink.length > 0 && $addToCartLink.attr('href')) {
                var href = $addToCartLink.attr('href');
                if (href.indexOf('?add-to-cart=') !== -1 || href.indexOf('&add-to-cart=') !== -1) {
                    var separator = href.indexOf('?') !== -1 ? '&' : '?';
                    window.location.href = href + separator + 'woo_tweaks_direct_checkout=1';
                    return;
                }
            }
            
            // Last resort: fallback to product ID
            var productId = $btn.data('product_id');
            if (productId) {
                window.location.href = '?add-to-cart=' + productId + '&woo_tweaks_direct_checkout=1';
            }
        }
    });
});
