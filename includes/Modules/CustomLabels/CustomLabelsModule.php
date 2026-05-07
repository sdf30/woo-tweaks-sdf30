<?php
/**
 * Custom Labels Module.
 *
 * @package WooTweaksTools
 */

declare(strict_types=1);

namespace WooTweaksTools\Modules\CustomLabels;

use WooTweaksTools\Core\AbstractModule;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Custom Labels Module Class.
 */
class CustomLabelsModule extends AbstractModule
{
    /**
     * Determine if the module is active.
     */
    public function is_active(): bool
    {
        return true; // We can add a setting to disable the module entirely later.
    }

    public function register_settings(): void
    {
        \add_filter('woo_tweaks_core_settings', [$this, 'add_settings']);
    }

    public function add_settings(array $settings): array
    {
        $settings[] = [
            'title' => \__('Global Custom Labels', 'tweak-tools-sdf30'),
            'type'  => 'title',
            'desc'  => \__('These labels will apply globally unless overridden per product.', 'tweak-tools-sdf30'),
            'id'    => 'woo_tweaks_labels_section',
        ];
        $settings[] = [
            'title'    => \__('Add to Cart Text', 'tweak-tools-sdf30'),
            'id'       => 'woo_tweaks_add_to_cart_text',
            'type'     => 'text',
            'default'  => '',
            'desc'     => \__('Leave empty to use WooCommerce default.', 'tweak-tools-sdf30'),
            'desc_tip' => true,
        ];
        $settings[] = [
            'title'    => \__('Sale Badge Text', 'tweak-tools-sdf30'),
            'id'       => 'woo_tweaks_sale_badge_text',
            'type'     => 'text',
            'default'  => '',
            'placeholder' => \__('Sale!', 'tweak-tools-sdf30'),
            'desc'     => \__('Text for the "Sale" badge (Promo).', 'tweak-tools-sdf30'),
            'desc_tip' => true,
        ];
        $settings[] = [
            'title'    => \__('Out of Stock Text', 'tweak-tools-sdf30'),
            'id'       => 'woo_tweaks_out_of_stock_text',
            'type'     => 'text',
            'default'  => '',
            'placeholder' => \__('Out of stock', 'tweak-tools-sdf30'),
            'desc'     => \__('Text for "Out of stock" availability.', 'tweak-tools-sdf30'),
            'desc_tip' => true,
        ];
        $settings[] = [
            'title'    => \__('SKU Prefix', 'tweak-tools-sdf30'),
            'id'       => 'woo_tweaks_sku_label',
            'type'     => 'text',
            'default'  => '',
            'placeholder' => 'SKU:',
            'desc'     => \__('Override the default "SKU:" text.', 'tweak-tools-sdf30'),
            'desc_tip' => true,
        ];
        $settings[] = [
            'title'    => \__('Category Prefix', 'tweak-tools-sdf30'),
            'id'       => 'woo_tweaks_category_label',
            'type'     => 'text',
            'default'  => '',
            'placeholder' => 'Category:',
            'desc'     => \__('Override the default "Category:" text.', 'tweak-tools-sdf30'),
            'desc_tip' => true,
        ];
        $settings[] = [
            'type' => 'sectionend',
            'id'   => 'woo_tweaks_labels_section',
        ];

        return $settings;
    }

    /**
     * Initialize module.
     */
    public function init(): void
    {
        // Product Meta (Backend)
        \add_action('woocommerce_product_options_general_product_data', [$this, 'add_product_options']);
        \add_action('woocommerce_process_product_meta', [$this, 'save_product_options']);

        // Frontend filters
        \add_filter('woocommerce_product_add_to_cart_text', [$this, 'custom_add_to_cart_text'], 10, 2);
        \add_filter('woocommerce_product_single_add_to_cart_text', [$this, 'custom_add_to_cart_text'], 10, 2);

        // Sale Badge filters
        \add_filter('woocommerce_sale_flash', [$this, 'custom_sale_flash_text'], 10, 3);
        \add_filter('woocommerce_sale_badge_text', [$this, 'custom_sale_badge_text'], 10, 2);

        // Availability (Stock) filter
        \add_filter('woocommerce_get_availability_text', [$this, 'custom_availability_text'], 10, 2);

        // SKU and Category translation filters
        \add_filter('gettext', [$this, 'custom_gettext_labels'], 20, 3);
    }

    /**
     * Add product-specific options.
     */
    public function add_product_options(): void
    {
        echo '<div class="options_group">';
        \woocommerce_wp_checkbox([
            'id'            => '_woo_tweaks_disable_global_label',
            'label'         => \__('Disable Global Custom Labels', 'tweak-tools-sdf30'),
            'description'   => \__('Check this to use native WooCommerce labels for this product (Add to Cart, Sale, Stock, etc.).', 'tweak-tools-sdf30'),
            'desc_tip'      => true,
        ]);
        echo '</div>';
    }

    /**
     * Save product options.
     *
     * @param int $post_id Post ID.
     */
    public function save_product_options(int $post_id): void
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $disable_global = isset($_POST['_woo_tweaks_disable_global_label']) ? 'yes' : 'no';
        \update_post_meta($post_id, '_woo_tweaks_disable_global_label', $disable_global);
    }

    /**
     * Check if global labels are disabled for a product.
     *
     * @param mixed $product
     * @return bool
     */
    private function is_disabled_for_product($product): bool
    {
        if (!$product instanceof \WC_Product) {
            return false;
        }
        return $product->get_meta('_woo_tweaks_disable_global_label') === 'yes';
    }

    /**
     * Filter the add to cart text.
     *
     * @param string $text The default text.
     * @param mixed $product The product object.
     * @return string
     */
    public function custom_add_to_cart_text(string $text, $product): string
    {
        if (!$product instanceof \WC_Product || $this->is_disabled_for_product($product)) {
            return $text;
        }

        $global_label = \get_option('woo_tweaks_add_to_cart_text', '');
        return !empty($global_label) ? $global_label : $text;
    }

    /**
     * Filter the sale badge (Flash/Classic).
     */
    public function custom_sale_flash_text(string $html, $post, $product): string
    {
        if (!$product instanceof \WC_Product || $this->is_disabled_for_product($product)) {
            return $html;
        }

        $label = \get_option('woo_tweaks_sale_badge_text', '');
        if (empty($label)) {
            return $html;
        }

        return '<span class="onsale">' . \esc_html($label) . '</span>';
    }

    /**
     * Filter the sale badge text (Blocks/FSE).
     */
    public function custom_sale_badge_text(string $text, $product): string
    {
        if (!$product instanceof \WC_Product || $this->is_disabled_for_product($product)) {
            return $text;
        }

        $label = \get_option('woo_tweaks_sale_badge_text', '');
        return !empty($label) ? $label : $text;
    }

    /**
     * Filter the availability text (Stock).
     */
    public function custom_availability_text(string $availability, $product): string
    {
        if (!$product instanceof \WC_Product || $this->is_disabled_for_product($product)) {
            return $availability;
        }

        // Only override if out of stock.
        if (!$product->is_in_stock()) {
            $label = \get_option('woo_tweaks_out_of_stock_text', '');
            if (!empty($label)) {
                return $label;
            }
        }

        return $availability;
    }

    /**
     * Filter SKU and Category labels via gettext.
     */
    public function custom_gettext_labels(string $translated_text, string $text, string $domain): string
    {
        if ('woocommerce' !== $domain) {
            return $translated_text;
        }

        // Global disabling per product is tricky here because gettext doesn't have the product context easily.
        // But for SKU/Category, it's generally a global site preference.

        switch ($text) {
            case 'SKU:':
                $sku_label = \get_option('woo_tweaks_sku_label', '');
                if (!empty($sku_label)) {
                    return $sku_label;
                }
                break;
            case 'Category:':
            case 'Categories:':
                $cat_label = \get_option('woo_tweaks_category_label', '');
                if (!empty($cat_label)) {
                    return $cat_label;
                }
                break;
        }

        return $translated_text;
    }
}
