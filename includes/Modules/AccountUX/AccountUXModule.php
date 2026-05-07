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

    public function register_settings(): void
    {
        \add_filter('woo_tweaks_core_settings', [$this, 'add_settings']);
    }

    public function add_settings(array $settings): array
    {
        $settings[] = [
            'title' => \__('Mon Compte & UX', 'tweak-tools-sdf30'),
            'type'  => 'title',
            'desc'  => \__('Améliorations de l\'expérience utilisateur sur la page Mon Compte.', 'tweak-tools-sdf30'),
            'id'    => 'woo_tweaks_account_ux_section',
        ];
        $settings[] = [
            'title'    => \__('Activer l\'UX Mon Compte', 'tweak-tools-sdf30'),
            'id'       => 'woo_tweaks_enhanced_account_ux',
            'type'     => 'checkbox',
            'default'  => 'no',
            'desc'     => \__('Améliore le formulaire de détails du compte (ex: champ Date de naissance).', 'tweak-tools-sdf30'),
        ];
        $settings[] = [
            'type' => 'sectionend',
            'id'   => 'woo_tweaks_account_ux_section',
        ];

        return $settings;
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
            'label'       => \__('Date de naissance', 'tweak-tools-sdf30'),
            'placeholder' => \__('JJ/MM/AAAA', 'tweak-tools-sdf30'),
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
        // phpcs:disable WordPress.Security.NonceVerification.Missing
        if (isset($_POST['billing_birth_date'])) {
            \update_user_meta($user_id, 'billing_birth_date', \sanitize_text_field(\wp_unslash($_POST['billing_birth_date'])));
        }
        // phpcs:enable WordPress.Security.NonceVerification.Missing
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
            'tweak-tools-sdf30s-account-ux',
            \plugins_url('account-ux.js', __FILE__),
            ['jquery'],
            '1.0.0',
            true
        );

        \wp_localize_script('tweak-tools-sdf30s-account-ux', 'wtAccountData', [
            'i18n' => [
                'edit_email'    => \__('Modifier l\'adresse e-mail', 'tweak-tools-sdf30'),
                'change_pass'   => \__('Changer le mot de passe', 'tweak-tools-sdf30'),
                'hide_email'    => \__('Masquer l\'adresse e-mail', 'tweak-tools-sdf30'),
                'hide_pass'     => \__('Garder le mot de passe actuel', 'tweak-tools-sdf30'),
            ]
        ]);

        \wp_enqueue_style(
            'tweak-tools-sdf30s-account-ux',
            \plugins_url('account-ux.css', __FILE__),
            [],
            '1.0.0'
        );
    }
}
