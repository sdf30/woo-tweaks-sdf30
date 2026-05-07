<?php
/**
 * Promo Urgency Badges Module.
 *
 * @package WooTweaksTools
 */

declare(strict_types=1);

namespace WooTweaksTools\Modules\PromoUrgency;

use WooTweaksTools\Core\AbstractModule;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Adds dynamic discount percentages and sale urgency messages to product pages.
 */
class PromoUrgencyModule extends AbstractModule
{
    /**
     * Determine if the module is active.
     */
    public function is_active(): bool
    {
        return \get_option('woo_tweaks_promo_urgency', 'no') === 'yes';
    }

    public function register_settings(): void
    {
        \add_filter('woo_tweaks_core_settings', [$this, 'add_settings']);
    }

    public function add_settings(array $settings): array
    {
        $settings[] = [
            'title' => \__('Promo Urgency Badges', 'tweak-tools-sdf30'),
            'type'  => 'title',
            'desc'  => \__('Displays the discount percentage and a promo countdown.', 'tweak-tools-sdf30'),
            'id'    => 'woo_tweaks_promo_urgency_section',
        ];
        $settings[] = [
            'title'    => \__('Enable Promo Urgency', 'tweak-tools-sdf30'),
            'id'       => 'woo_tweaks_promo_urgency',
            'type'     => 'checkbox',
            'default'  => 'no',
            'desc'     => \__('Displays the -X% badge instead of the "Sale!" text.', 'tweak-tools-sdf30'),
        ];
        $settings[] = [
            'title'    => \__('Show end date', 'tweak-tools-sdf30'),
            'id'       => 'woo_tweaks_promo_urgency_show_date',
            'type'     => 'checkbox',
            'default'  => 'no',
            'desc'     => \__('Displays the countdown below the price (if an end date is configured).', 'tweak-tools-sdf30'),
        ];
        $settings[] = [
            'type' => 'sectionend',
            'id'   => 'woo_tweaks_promo_urgency_section',
        ];

        return $settings;
    }

    /**
     * Initialize module hooks.
     */
    public function init(): void
    {
        // Badges in lists and single product
        \add_filter('woocommerce_get_price_html', [$this, 'add_percentage_badge'], 10, 2);

        // Sale expiry message via hook (Legacy / Default FSE Summary)
        \add_action('woocommerce_single_product_summary', [$this, 'display_sale_expiry_message'], 25);

        // Enqueue styles
        \add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);

        // Register FSE Block
        $this->register_fse_block();
    }

    /**
     * Registers the FSE Block for Promo Urgency.
     */
    public function register_fse_block(): void
    {
        $dir = \plugin_dir_path(__FILE__);
        if (\file_exists($dir . 'block.json')) {
            \register_block_type($dir, [
                'render_callback' => [$this, 'render_fse_block'],
            ]);
        }
        
        // Ensure CSS is available in the editor
        \add_action('enqueue_block_editor_assets', [$this, 'enqueue_editor_assets']);
    }

    /**
     * Render callback for the FSE block.
     *
     * @param array    $attributes Block attributes.
     * @param string   $content    Block content.
     * @param \WP_Block $block     Block instance.
     * @return string
     */
    public function render_fse_block(array $attributes, string $content, \WP_Block $block): string
    {
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
        global $product;

        if (!$product && isset($block->context['postId'])) {
            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
            $product = \wc_get_product($block->context['postId']);
        }

        if (!$product) {
            return '';
        }

        return $this->get_urgency_message_html($product);
    }

    /**
     * Enqueue assets for block editor.
     */
    public function enqueue_editor_assets(): void
    {
        // CSS
        \wp_enqueue_style(
            'tweak-tools-sdf30s-promo-urgency-editor',
            \plugin_dir_url(\dirname(\dirname(\dirname(__FILE__)))) . 'assets/css/index.css',
            [],
            \WooTweaksTools\Plugin::VERSION
        );

        // JS
        $script_path = 'includes/Modules/PromoUrgency/block.js';
        $script_url  = \plugin_dir_url(\dirname(\dirname(\dirname(__FILE__)))) . $script_path;

        \wp_enqueue_script(
            'tweak-tools-sdf30s-promo-urgency-block-editor',
            $script_url,
            ['wp-blocks', 'wp-element', 'wp-server-side-render', 'wp-editor'],
            \WooTweaksTools\Plugin::VERSION,
            true
        );
    }

    /**
     * Enqueue module assets.
     */
    public function enqueue_assets(): void
    {
        if (\is_product() || \is_shop() || \is_product_category()) {
            \wp_enqueue_style(
                'tweak-tools-sdf30s-promo-urgency',
                \plugins_url('promo-urgency.css', __FILE__),
                [],
                '1.3.0'
            );
        }
    }

    /**
     * Add percentage discount badge to the price HTML.
     *
     * @param string      $price_html
     * @param \WC_Product $product
     * @return string
     */
    public function add_percentage_badge(string $price_html, \WC_Product $product): string
    {
        if (\is_admin() || !$product->is_on_sale()) {
            return $price_html;
        }

        $percentage = 0;

        if ($product->is_type('variable')) {
            $percentages = [];
            foreach ($product->get_visible_children() as $variation_id) {
                $variation = \wc_get_product($variation_id);
                if ($variation && $variation->is_on_sale()) {
                    $regular_price = (float) $variation->get_regular_price();
                    $sale_price    = (float) $variation->get_sale_price();
                    if ($regular_price > 0) {
                        $percentages[] = \round((($regular_price - $sale_price) / $regular_price) * 100);
                    }
                }
            }
            $percentage = !empty($percentages) ? \max($percentages) : 0;
        } else {
            $regular_price = (float) $product->get_regular_price();
            $sale_price    = (float) $product->get_sale_price();
            if ($regular_price > 0) {
                $percentage = \round((($regular_price - $sale_price) / $regular_price) * 100);
            }
        }

        if ($percentage > 0) {
            $badge = \sprintf(
                '<span class="wt-promo-badge">-%d%%</span>',
                $percentage
            );
            $price_html .= $badge;
        }

        return $price_html;
    }

    /**
     * Display the sale expiry message.
     */
    public function display_sale_expiry_message(): void
    {
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
        global $product;
        if (!$product) {
            return;
        }

        echo \wp_kses_post($this->get_urgency_message_html($product));
    }

    /**
     * Get the urgency message HTML.
     *
     * @param \WC_Product $product
     * @return string
     */
    public function get_urgency_message_html($product): string
    {
        if (\get_option('woo_tweaks_promo_urgency_show_date', 'no') !== 'yes' || !$product->is_on_sale()) {
            return '';
        }

        $date_to = $product->get_date_on_sale_to();
        if (!$date_to) {
            return '';
        }

        $remaining_time = $date_to->getTimestamp() - time();
        if ($remaining_time <= 0) {
            return '';
        }

        $days = floor($remaining_time / (24 * 3600));
        $hours = floor(($remaining_time % (24 * 3600)) / 3600);

        $message = '';
        if ($days > 0) {
            /* translators: %s: number of days */
            $message = \sprintf(\_n('Only %s day left!', 'Only %s days left!', (int) $days, 'tweak-tools-sdf30'), $days);
        } else {
            /* translators: %s: number of hours */
            $message = \sprintf(\__('Only %s hours left!', 'tweak-tools-sdf30'), $hours);
        }

        return \sprintf(
            '<div class="wt-promo-urgency-msg"><span>⏱️</span> %s</div>',
            \esc_html($message)
        );
    }
}
