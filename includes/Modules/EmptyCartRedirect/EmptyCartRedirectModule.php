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

    public function register_settings(): void
    {
        \add_filter('woo_tweaks_core_settings', [$this, 'add_settings']);
    }

    public function add_settings(array $settings): array
    {
        $settings[] = [
            'title' => \__('Redirection Panier Vide', 'tweak-tools-sdf30'),
            'type'  => 'title',
            'id'    => 'woo_tweaks_empty_cart_redirect_section',
        ];
        $settings[] = [
            'title'    => \__('Redirection Panier Vide', 'tweak-tools-sdf30'),
            'id'       => 'woo_tweaks_empty_cart_redirect',
            'type'     => 'checkbox',
            'default'  => 'no',
            'desc'     => \__('Redirect users to the shop page if they access an empty cart page.', 'tweak-tools-sdf30'),
            'desc_tip' => true,
        ];
        $settings[] = [
            'type' => 'sectionend',
            'id'   => 'woo_tweaks_empty_cart_redirect_section',
        ];

        return $settings;
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
