<?php
/**
 * Checkout Fields Cleaner Module.
 *
 * @package WooTweaksTools
 */

declare(strict_types=1);

namespace WooTweaksTools\Modules\CheckoutFields;

use WooTweaksTools\Core\AbstractModule;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Checkout Fields Cleaner Module Class.
 */
class CheckoutFieldsModule extends AbstractModule
{
    /**
     * Determine if the module is active.
     */
    public function is_active(): bool
    {
        return true; // Active to listen for specific options.
    }

    public function register_settings(): void
    {
        \add_filter('woo_tweaks_core_settings', [$this, 'add_settings']);
    }

    public function add_settings(array $settings): array
    {
        $settings[] = [
            'title' => \__('Checkout Cleanup', 'tweak-tools-for-woocommerce'),
            'type'  => 'title',
            'desc'  => \__('Disable unnecessary fields on the checkout page.', 'tweak-tools-for-woocommerce'),
            'id'    => 'woo_tweaks_checkout_section',
        ];
        $settings[] = [
            'title'    => \__('Hide Company', 'tweak-tools-for-woocommerce'),
            'id'       => 'woo_tweaks_hide_billing_company',
            'type'     => 'checkbox',
            'default'  => 'no',
            'desc'     => \__('Hides the Company Name field.', 'tweak-tools-for-woocommerce'),
        ];
        $settings[] = [
            'title'    => \__('Hide Address 2', 'tweak-tools-for-woocommerce'),
            'id'       => 'woo_tweaks_hide_billing_address_2',
            'type'     => 'checkbox',
            'default'  => 'no',
            'desc'     => \__('Hides the Address 2 field.', 'tweak-tools-for-woocommerce'),
        ];
        $settings[] = [
            'title'    => \__('Hide Phone', 'tweak-tools-for-woocommerce'),
            'id'       => 'woo_tweaks_hide_billing_phone',
            'type'     => 'checkbox',
            'default'  => 'no',
            'desc'     => \__('Hides the Phone field (be careful if you use a shipping carrier).', 'tweak-tools-for-woocommerce'),
        ];
        $settings[] = [
            'title'    => \__('Hide Order Notes', 'tweak-tools-for-woocommerce'),
            'id'       => 'woo_tweaks_hide_order_notes',
            'type'     => 'checkbox',
            'default'  => 'no',
            'desc'     => \__('Hides the additional notes field.', 'tweak-tools-for-woocommerce'),
        ];
        $settings[] = [
            'type' => 'sectionend',
            'id'   => 'woo_tweaks_checkout_section',
        ];

        return $settings;
    }

    /**
     * Initialize module.
     */
    public function init(): void
    {
        \add_filter('woocommerce_checkout_fields', [$this, 'clean_checkout_fields'], 999);
        \add_filter('woocommerce_enable_order_notes_field', [$this, 'maybe_disable_order_notes'], 999);
    }

    /**
     * Clean checkout fields based on settings.
     *
     * @param array $fields
     * @return array
     */
    public function clean_checkout_fields(array $fields): array
    {
        if (\get_option('woo_tweaks_hide_billing_company', 'no') === 'yes') {
            unset($fields['billing']['billing_company']);
        }

        if (\get_option('woo_tweaks_hide_billing_address_2', 'no') === 'yes') {
            unset($fields['billing']['billing_address_2']);
        }

        if (\get_option('woo_tweaks_hide_billing_phone', 'no') === 'yes') {
            unset($fields['billing']['billing_phone']);
        }

        return $fields;
    }

    /**
     * Disable order notes if configured.
     *
     * @param bool $enable
     * @return bool
     */
    public function maybe_disable_order_notes(bool $enable): bool
    {
        if (\get_option('woo_tweaks_hide_order_notes', 'no') === 'yes') {
            return false;
        }
        return $enable;
    }
}
