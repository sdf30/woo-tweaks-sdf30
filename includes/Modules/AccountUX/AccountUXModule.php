<?php
/**
 * Enhanced Account UX Module.
 *
 * @package WooTweaksTools
 */

declare(strict_types=1);

namespace WooTweaksTools\Modules\AccountUX;

use WooTweaksTools\Core\AbstractModule;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enhances the WooCommerce My Account page with better field management.
 */
class AccountUXModule extends AbstractModule
{
    /**
     * Determine if the module is active.
     */
    public function is_active(): bool
    {
        return \get_option('woo_tweaks_enhanced_account_ux', 'no') === 'yes';
    }

    /**
     * Initialize module hooks.
     */
    public function init(): void
    {
        // Add custom fields to edit account form.
        \add_action('woocommerce_edit_account_form', [$this, 'add_birthday_field']);
        
        // Save custom fields.
        \add_action('woocommerce_save_account_details', [$this, 'save_birthday_field']);
        
        // Enqueue assets.
        \add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    /**
     * Add Birthday field to account details.
     */
    public function add_birthday_field(): void
    {
        $user_id = \get_current_user_id();
        $birthday = \get_user_meta($user_id, 'billing_birth_date', true);

        \woocommerce_form_field('billing_birth_date', [
            'type'        => 'date',
            'class'       => ['form-row-wide'],
            'label'       => \__('Date de naissance', 'woo-tweaks-tools'),
            'placeholder' => \__('JJ/MM/AAAA', 'woo-tweaks-tools'),
            'required'    => false,
        ], $birthday);
    }

    /**
     * Save Birthday field.
     *
     * @param int $user_id
     */
    public function save_birthday_field(int $user_id): void
    {
        if (isset($_POST['billing_birth_date'])) {
            \update_user_meta($user_id, 'billing_birth_date', \sanitize_text_field($_POST['billing_birth_date']));
        }
    }

    /**
     * Enqueue module assets.
     */
    public function enqueue_assets(): void
    {
        if (!\is_account_page() || !\is_user_logged_in()) {
            return;
        }

        \wp_enqueue_script(
            'woo-tweaks-account-ux',
            \plugins_url('account-ux.js', __FILE__),
            ['jquery'],
            '1.0.0',
            true
        );

        \wp_localize_script('woo-tweaks-account-ux', 'wtAccountData', [
            'i18n' => [
                'edit_email'    => \__('Modifier l\'adresse e-mail', 'woo-tweaks-tools'),
                'change_pass'   => \__('Changer le mot de passe', 'woo-tweaks-tools'),
                'hide_email'    => \__('Masquer l\'adresse e-mail', 'woo-tweaks-tools'),
                'hide_pass'     => \__('Garder le mot de passe actuel', 'woo-tweaks-tools'),
            ]
        ]);

        \wp_enqueue_style(
            'woo-tweaks-account-ux',
            \plugins_url('account-ux.css', __FILE__),
            [],
            '1.0.0'
        );
    }
}
