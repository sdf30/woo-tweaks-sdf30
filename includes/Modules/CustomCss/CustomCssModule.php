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
            'title' => \__('Appearance & Custom CSS', 'tweak-tools-for-woocommerce'),
            'type'  => 'title',
            'desc'  => \__('Add your custom CSS here to style plugin elements without overriding your global site CSS. <br><br><b>Class Glossary:</b><br>
                <code>a.tweak-tools-sdf30s-read-more</code> : The "Read More" button (Feat 2).<br>
                <code>.button.alt.tweak-tools-sdf30-validatenow</code> : The "Buy now" button (Direct Checkout).<br>
                <code>.stock.wt-smart-stock-active</code> : The dynamic urgency message.<br>
                <code>.wt-account-toggle-btn</code> : Toggle buttons (My Account).<br>
                <code>.wt-retractable-section</code> : Container for hidden Email/Password fields.<br>
                <code>#billing_birth_date_field</code> : Date of birth field.<br>
                <code>.wt-toggle-stock</code> : Stock toggle link (Admin Product List).<br>
                <code>.wt-promo-badge</code> : Discount percentage badge.<br>
                <code>.wt-promo-urgency-msg</code> : Promotion end message.', 'tweak-tools-for-woocommerce'),
            'id'    => 'woo_tweaks_custom_css_section',
        ];
        $settings[] = [
            'title'    => \__('CSS Code', 'tweak-tools-for-woocommerce'),
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
        echo "<style id=\"tweak-tools-sdf30s-custom-css\">\n";
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo $sanitized_css;
        echo "\n</style>\n<!-- /Woo Tweaks Custom CSS -->\n";
    }
}
