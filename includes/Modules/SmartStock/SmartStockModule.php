<?php
/**
 * Smart Stock Messaging Module.
 *
 * @package WooTweaksTools
 */

declare(strict_types=1);

namespace WooTweaksTools\Modules\SmartStock;

use WooTweaksTools\Core\AbstractModule;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Displays custom urgency messages when stock levels are low.
 */
class SmartStockModule extends AbstractModule
{
    /**
     * Determine if the module is active.
     */
    public function is_active(): bool
    {
        return \get_option('woo_tweaks_smart_stock_messaging', 'no') === 'yes';
    }

    /**
     * Initialize module hooks.
     */
    public function init(): void
    {
        // Filter availability text for simple and variable products.
        \add_filter('woocommerce_get_availability', [$this, 'filter_availability'], 10, 2);
        
        // Enqueue frontend styles.
        \add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    /**
     * Filter the stock availability text to inject our custom urgency message.
     *
     * @param array      $availability
     * @param \WC_Product $product
     * @return array
     */
    public function filter_availability(array $availability, \WC_Product $product): array
    {
        if (!$product->is_in_stock()) {
            return $availability;
        }

        // Use get_stock_quantity which works for both simple and variation products.
        $stock_quantity = $product->get_stock_quantity();
        
        // If stock management is not enabled, or quantity is null, skip.
        if (null === $stock_quantity) {
            return $availability;
        }

        $threshold = (int) \get_option('woo_tweaks_smart_stock_threshold', 10);
        
        if ($stock_quantity > 0 && $stock_quantity <= $threshold) {
            $message_template = \get_option('woo_tweaks_smart_stock_message', \__('🔥 Plus que {stock} articles en stock !', 'woo-tweaks-tools'));
            $final_message = \str_replace('{stock}', (string) $stock_quantity, $message_template);
            
            $availability['availability'] = $final_message;
            $availability['class'] .= ' wt-smart-stock-active';
        }

        return $availability;
    }

    /**
     * Enqueue module styles.
     */
    public function enqueue_assets(): void
    {
        if (!\is_product()) {
            return;
        }

        \wp_enqueue_style(
            'woo-tweaks-smart-stock',
            \plugins_url('smart-stock.css', __FILE__),
            [],
            '1.0.0'
        );
    }
}
