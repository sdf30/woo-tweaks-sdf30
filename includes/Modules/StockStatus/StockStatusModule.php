<?php
/**
 * Stock Status Shortcuts Module.
 *
 * @package WooTweaksTools
 */

declare(strict_types=1);

namespace WooTweaksTools\Modules\StockStatus;

use WooTweaksTools\Core\AbstractModule;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Adds quick stock status toggle actions in the product list.
 */
class StockStatusModule extends AbstractModule
{
    /**
     * Determine if the module is active.
     */
    public function is_active(): bool
    {
        return \get_option('woo_tweaks_stock_status_shortcuts', 'no') === 'yes';
    }

    /**
     * Initialize module hooks.
     */
    public function init(): void
    {
        \add_filter('post_row_actions', [$this, 'add_stock_row_actions'], 10, 2);
        \add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        
        // AJAX Handlers
        \add_action('wp_ajax_woo_tweaks_toggle_stock', [$this, 'handle_ajax_toggle_stock']);
    }

    /**
     * Add quick actions under product titles in the admin list.
     *
     * @param array    $actions
     * @param \WP_Post $post
     * @return array
     */
    public function add_stock_row_actions(array $actions, \WP_Post $post): array
    {
        if ($post->post_type !== 'product') {
            return $actions;
        }

        $product = \wc_get_product($post->ID);
        if (!$product) {
            return $actions;
        }

        $current_status = $product->get_stock_status();
        $nonce = \wp_create_nonce('woo_tweaks_stock_nonce_' . $post->ID);

        if ($current_status === 'instock') {
            $actions['wt_out_of_stock'] = \sprintf(
                '<a href="#" class="wt-toggle-stock" data-id="%d" data-status="outofstock" data-nonce="%s" style="color: #d63638; font-weight: 600;">%s</a>',
                $post->ID,
                $nonce,
                \__('Marquer Hors Stock', 'woo-tweaks-tools')
            );
        } else {
            $actions['wt_in_stock'] = \sprintf(
                '<a href="#" class="wt-toggle-stock" data-id="%d" data-status="instock" data-nonce="%s" style="color: #007b5f; font-weight: 600;">%s</a>',
                $post->ID,
                $nonce,
                \__('Remettre en Stock', 'woo-tweaks-tools')
            );
        }

        return $actions;
    }

    /**
     * Enqueue administrative assets.
     */
    public function enqueue_admin_assets(): void
    {
        $screen = \get_current_screen();
        if ($screen && $screen->id === 'edit-product') {
            \wp_enqueue_script(
                'woo-tweaks-stock-status',
                \plugins_url('stock-status.js', __FILE__),
                ['jquery'],
                '1.0.0',
                true
            );

            \wp_localize_script('woo-tweaks-stock-status', 'wtStockData', [
                'ajax_url' => \admin_url('admin-ajax.php'),
                'i18n'     => [
                    'in_stock'     => \__('En Stock', 'woo-tweaks-tools'),
                    'out_of_stock' => \__('Hors Stock', 'woo-tweaks-tools'),
                    'updating'     => \__('Mise à jour...', 'woo-tweaks-tools'),
                    'error'        => \__('Erreur lors de la mise à jour.', 'woo-tweaks-tools'),
                    'mark_in'      => \__('Remettre en Stock', 'woo-tweaks-tools'),
                    'mark_out'     => \__('Marquer Hors Stock', 'woo-tweaks-tools'),
                ]
            ]);

            \wp_enqueue_style(
                'woo-tweaks-stock-status',
                \plugins_url('stock-status.css', __FILE__),
                [],
                '1.0.0'
            );
        }
    }

    /**
     * Handle the AJAX request to toggle stock status.
     */
    public function handle_ajax_toggle_stock(): void
    {
        $product_id = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;
        $new_status = isset($_POST['status']) ? \sanitize_text_field($_POST['status']) : '';
        $nonce      = isset($_POST['nonce']) ? \sanitize_text_field($_POST['nonce']) : '';

        if (!\wp_verify_nonce($nonce, 'woo_tweaks_stock_nonce_' . $product_id)) {
            \wp_send_json_error(['message' => 'Invalid nonce.']);
        }

        if (!\current_user_can('edit_products')) {
            \wp_send_json_error(['message' => 'Permission denied.']);
        }

        $product = \wc_get_product($product_id);
        if (!$product) {
            \wp_send_json_error(['message' => 'Product not found.']);
        }

        // Update status
        $product->set_stock_status($new_status);
        $product->save();

        // Clear WooCommerce transients for this product
        \wc_delete_product_transients($product_id);

        \wp_send_json_success([
            'new_status' => $new_status,
            'html_badge' => \wc_get_stock_html($product)
        ]);
    }
}
