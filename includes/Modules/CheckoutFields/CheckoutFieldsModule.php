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
            'title' => \__('Nettoyage du Checkout', 'tweak-tools-sdf30'),
            'type'  => 'title',
            'desc'  => \__('Désactiver certains champs inutiles sur la page de commande.', 'tweak-tools-sdf30'),
            'id'    => 'woo_tweaks_checkout_section',
        ];
        $settings[] = [
            'title'    => \__('Masquer Société', 'tweak-tools-sdf30'),
            'id'       => 'woo_tweaks_hide_billing_company',
            'type'     => 'checkbox',
            'default'  => 'no',
            'desc'     => \__('Cache le champ Nom de l\'entreprise.', 'tweak-tools-sdf30'),
        ];
        $settings[] = [
            'title'    => \__('Masquer Adresse 2', 'tweak-tools-sdf30'),
            'id'       => 'woo_tweaks_hide_billing_address_2',
            'type'     => 'checkbox',
            'default'  => 'no',
            'desc'     => \__('Cache le champ d\'adresse complémentaire.', 'tweak-tools-sdf30'),
        ];
        $settings[] = [
            'title'    => \__('Masquer Téléphone', 'tweak-tools-sdf30'),
            'id'       => 'woo_tweaks_hide_billing_phone',
            'type'     => 'checkbox',
            'default'  => 'no',
            'desc'     => \__('Cache le champ Téléphone (attention si vous livrez via transporteur).', 'tweak-tools-sdf30'),
        ];
        $settings[] = [
            'title'    => \__('Masquer Notes de Commande', 'tweak-tools-sdf30'),
            'id'       => 'woo_tweaks_hide_order_notes',
            'type'     => 'checkbox',
            'default'  => 'no',
            'desc'     => \__('Cache le champ de notes additionnelles.', 'tweak-tools-sdf30'),
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
