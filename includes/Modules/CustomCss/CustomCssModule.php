<?php
/**
 * Custom CSS Module.
 *
 * @package WooTweaksTools
 */

declare(strict_types=1);

namespace WooTweaksTools\Modules\CustomCss;

use WooTweaksTools\Core\AbstractModule;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Custom CSS Module Class.
 */
class CustomCssModule extends AbstractModule
{
    /**
     * Determine if the module is active.
     */
    public function is_active(): bool
    {
        return !empty(\get_option('woo_tweaks_custom_css', ''));
    }

    public function register_settings(): void
    {
        \add_filter('woo_tweaks_core_settings', [$this, 'add_settings']);
    }

    public function add_settings(array $settings): array
    {
        $settings[] = [
            'title' => \__('Apparence & Custom CSS', 'woo-tweaks-tools'),
            'type'  => 'title',
            'desc'  => \__('Ajoutez votre CSS personnalisé ici pour styliser les éléments du plugin sans surcharger le CSS global de votre site. <br><br><b>Glossaire des classes :</b><br>
                <code>a.woo-tweaks-read-more</code> : Le bouton "Read More" (Feat 2).<br>
                <code>.button.alt.woo-tweak-validatenow</code> : Le bouton "Acheter maintenant" (Direct Checkout).<br>
                <code>.stock.wt-smart-stock-active</code> : Le message d\'urgence dynamique.<br>
                <code>.wt-account-toggle-btn</code> : Les boutons de toggle (Mon Compte).<br>
                <code>.wt-retractable-section</code> : Le conteneur des champs Email/Password rétractés.<br>
                <code>#billing_birth_date_field</code> : Le champ Date de naissance.<br>
                <code>.wt-toggle-stock</code> : Le lien de bascule de stock (Admin Product List).<br>
                <code>.wt-promo-badge</code> : Le badge de pourcentage de remise.<br>
                <code>.wt-promo-urgency-msg</code> : Le message de fin de promotion.', 'woo-tweaks-tools'),
            'id'    => 'woo_tweaks_custom_css_section',
        ];
        $settings[] = [
            'title'    => \__('Code CSS', 'woo-tweaks-tools'),
            'id'       => 'woo_tweaks_custom_css',
            'type'     => 'textarea',
            'default'  => '',
            'css'      => 'width: 100%; height: 300px;',
            'desc_tip' => true,
        ];
        $settings[] = [
            'type' => 'sectionend',
            'id'   => 'woo_tweaks_custom_css_section',
        ];

        return $settings;
    }

    /**
     * Initialize module.
     */
    public function init(): void
    {
        // Inject CSS late to ensure it overrides theme styles.
        \add_action('wp_head', [$this, 'inject_custom_css'], 999);
    }

    /**
     * Inject the custom CSS into the head.
     */
    public function inject_custom_css(): void
    {
        $custom_css = \get_option('woo_tweaks_custom_css', '');
        
        if (empty($custom_css)) {
            return;
        }

        // Basic sanitization to prevent script injection.
        $sanitized_css = \wp_strip_all_tags($custom_css);

        echo "\n<!-- Woo Tweaks Custom CSS -->\n";
        echo "<style id=\"woo-tweaks-custom-css\">\n";
        echo $sanitized_css;
        echo "\n</style>\n<!-- /Woo Tweaks Custom CSS -->\n";
    }
}
