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
