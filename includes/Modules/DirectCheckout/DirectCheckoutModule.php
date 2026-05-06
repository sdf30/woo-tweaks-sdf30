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
 * Adds a Direct Checkout ("Acheter maintenant") button to the single product page.
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
        // 1. Classic Theme Support: Output the button inside the form.
        if (!\wp_is_block_theme()) {
            \add_action('woocommerce_after_add_to_cart_button', [$this, 'add_direct_checkout_button']);
        }
        
        // 2. Redirect to checkout if the form was submitted via the Direct Checkout button.
        \add_filter('woocommerce_add_to_cart_redirect', [$this, 'redirect_to_checkout'], 99);
        
        // 3. FSE Block Support
        \add_action('init', [$this, 'register_fse_block']);
    }

    /**
     * Adds the "Buy Now" submit button (Legacy themes).
     */
    public function add_direct_checkout_button(): void
    {
        global $product;
        if (!is_a($product, 'WC_Product')) {
            return;
        }
        
        // Do not render if the product cannot be purchased
        if (!$product->is_purchasable() || !$product->is_in_stock()) {
            return;
        }

        // Output the button
        $label = \get_option('woo_tweaks_direct_checkout_label', \__('Acheter maintenant', 'woo-tweaks-tools'));
        $button_text = \esc_html($label);
        
        echo '<button type="submit" name="woo_tweaks_direct_checkout" value="1" class="button alt sdf-validatenow" style="background: none; color: #007b5f; font-weight: 600; border: solid 1px; padding: 10px; margin-left: 10px;">' . $button_text . '</button>';
    }

    /**
     * Redirect to checkout page if the direct checkout button was clicked.
     *
     * @param string $url The URL to redirect to.
     * @return string
     */
    public function redirect_to_checkout(string $url): string
    {
        // WooCommerce handles POST internally. If our button name is in the $_REQUEST, we should redirect.
        if (isset($_REQUEST['woo_tweaks_direct_checkout']) && $_REQUEST['woo_tweaks_direct_checkout'] === '1') {
            return \wc_get_checkout_url();
        }
        
        return $url;
    }

    /**
     * Registers the FSE Block for Direct Checkout.
     */
    public function register_fse_block(): void
    {
        $dir = \plugin_dir_path(__FILE__);
        
        if (file_exists($dir . 'block.json')) {
            \register_block_type($dir);
        }
        
        \add_action('wp_enqueue_scripts', [$this, 'enqueue_fse_scripts']);
        \add_action('enqueue_block_editor_assets', [$this, 'enqueue_editor_assets']);
    }
    
    /**
     * Enqueue assets for FSE block editor.
     */
    public function enqueue_editor_assets(): void
    {
        $label = \get_option('woo_tweaks_direct_checkout_label', \__('Acheter maintenant', 'woo-tweaks-tools'));
        \wp_add_inline_script(
            'wp-blocks',
            'var wooTweaksDirectCheckoutLabel = "' . \esc_js($label) . '";',
            'after'
        );
    }
    
    /**
     * Enqueue JS for FSE block handling.
     */
    public function enqueue_fse_scripts(): void
    {
        if (\is_product() && \wp_is_block_theme()) {
            $script_path = \plugins_url('direct-checkout.js', __FILE__);
            \wp_enqueue_script('woo-tweaks-direct-checkout', $script_path, ['jquery'], '1.0.0', true);
        }
    }
}
