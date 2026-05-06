<?php
/**
 * Direct Checkout Module.
 *
 * @package WooTweaksTools
 */

declare(strict_types=1);

namespace WooTweaksTools\Modules\DirectCheckout;

use WooTweaksTools\Core\AbstractModule;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Redirects users directly to the checkout page after adding a product to the cart.
 */
class DirectCheckoutModule extends AbstractModule
{
    /**
     * Determine if the module is active.
     */
    public function is_active(): bool
    {
        return \get_option('woo_tweaks_direct_checkout') === 'yes';
    }

    /**
     * Initialize module hooks.
     */
    public function init(): void
    {
        // Redirect to checkout after add to cart
        \add_filter('woocommerce_add_to_cart_redirect', [$this, 'redirect_to_checkout']);
        
        // Optionally: Change "Add to Cart" text if requested, 
        // but we already have a CustomLabels module for that.
        
        // Disable "Redirect to the cart page after successful addition" setting if active
        // to avoid double redirects or conflicts.
        \add_filter('option_woocommerce_cart_redirect_after_add', [$this, 'force_cart_redirect_off']);
    }

    /**
     * Redirect to checkout page.
     *
     * @param string $url The URL to redirect to.
     * @return string
     */
    public function redirect_to_checkout(string $url): string
    {
        // If it's a valid checkout URL, return it.
        return \wc_get_checkout_url();
    }

    /**
     * Force the native WC "Redirect to cart" option to be 'no' 
     * to let our custom redirect handle it properly.
     *
     * @param mixed $value
     * @return string
     */
    public function force_cart_redirect_off($value): string
    {
        return 'no';
    }
}
