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

    public function register_settings(): void
    {
        \add_filter('woo_tweaks_core_settings', [$this, 'add_settings']);
    }

    public function add_settings(array $settings): array
    {
        $settings[] = [
            'title' => \__('Smart Stock Messaging', 'tweak-tools-sdf30'),
            'type'  => 'title',
            'desc'  => \__('Affiche un message d\'urgence personnalisé quand le stock est bas.', 'tweak-tools-sdf30'),
            'id'    => 'woo_tweaks_smart_stock_section',
        ];
        $settings[] = [
            'title'    => \__('Enable Smart Stock', 'tweak-tools-sdf30'),
            'id'       => 'woo_tweaks_smart_stock_messaging',
            'type'     => 'checkbox',
            'default'  => 'no',
            'desc'     => \__('Check to enable dynamic stock messages.', 'tweak-tools-sdf30'),
        ];
        $settings[] = [
            'title'    => \__('Trigger threshold', 'tweak-tools-sdf30'),
            'id'       => 'woo_tweaks_smart_stock_threshold',
            'type'     => 'number',
            'default'  => 10,
            'desc'     => \__('Quantité en stock à partir de laquelle le message s\'affiche.', 'tweak-tools-sdf30'),
            'desc_tip' => true,
        ];
        $settings[] = [
            'title'    => \__('Low stock message', 'tweak-tools-sdf30'),
            'id'       => 'woo_tweaks_smart_stock_message',
            'type'     => 'text',
            'default'  => \__('🔥 Only {stock} items left in stock!', 'tweak-tools-sdf30'),
            'desc'     => \__('Use {stock} to display the remaining quantity.', 'tweak-tools-sdf30'),
            'desc_tip' => true,
        ];
        $settings[] = [
            'type' => 'sectionend',
            'id'   => 'woo_tweaks_smart_stock_section',
        ];

        return $settings;
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
            $message_template = \get_option('woo_tweaks_smart_stock_message', \__('🔥 Only {stock} items left in stock!', 'tweak-tools-sdf30'));
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
            'tweak-tools-sdf30s-smart-stock',
            \plugins_url('smart-stock.css', __FILE__),
            [],
            '1.0.0'
        );
    }
}
