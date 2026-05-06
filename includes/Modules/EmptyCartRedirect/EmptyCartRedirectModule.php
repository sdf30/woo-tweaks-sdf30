<?php
/**
 * Empty Cart Redirect Module.
 *
 * @package WooTweaksTools
 */

declare(strict_types=1);

namespace WooTweaksTools\Modules\EmptyCartRedirect;

use WooTweaksTools\Core\AbstractModule;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Empty Cart Redirect Module Class.
 */
class EmptyCartRedirectModule extends AbstractModule
{
    /**
     * Determine if the module is active.
     */
    public function is_active(): bool
    {
        return \get_option('woo_tweaks_empty_cart_redirect', 'no') === 'yes';
    }

    /**
     * Initialize module.
     */
    public function init(): void
    {
        \add_action('template_redirect', [$this, 'redirect_empty_cart']);
    }

    /**
     * Redirect empty cart to shop.
     */
    public function redirect_empty_cart(): void
    {
        if (!\is_cart()) {
            return;
        }

        if (WC()->cart->is_empty()) {
            \wp_safe_redirect(\get_permalink(\wc_get_page_id('shop')));
            exit;
        }
    }
}
