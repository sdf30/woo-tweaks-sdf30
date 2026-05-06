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

    /**
     * Initialize module hooks.
     */
    public function init(): void
    {
        \add_filter('woocommerce_get_price_html', [$this, 'add_percentage_badge'], 20, 2);
        \add_action('woocommerce_single_product_summary', [$this, 'display_sale_expiry_message'], 15);
        \add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    /**
     * Enqueue module styles.
     */
    public function enqueue_assets(): void
    {
        if (\is_product()) {
            \wp_enqueue_style(
                'woo-tweaks-promo-urgency',
                \plugins_url('promo-urgency.css', __FILE__),
                [],
                '1.0.0'
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
     * Display a message if the sale has an expiry date.
     */
    public function display_sale_expiry_message(): void
    {
        if (\get_option('woo_tweaks_promo_urgency_show_date', 'no') !== 'yes') {
            return;
        }

        global $product;
        if (!$product || !$product->is_on_sale()) {
            return;
        }

        $date_to = $product->get_date_on_sale_to();
        if (!$date_to) {
            return;
        }

        $now = new \DateTime();
        $diff = $now->diff($date_to->getTimestamp() > $now->getTimestamp() ? new \DateTime('@' . $date_to->getTimestamp()) : $now);
        
        if ($date_to->getTimestamp() <= $now->getTimestamp()) {
            return;
        }

        $days = (int) $diff->format('%a');
        $hours = (int) $diff->format('%h');

        echo '<div class="wt-promo-urgency-msg">';
        echo '<span class="wt-icon">⏳</span> ';
        
        if ($days > 0) {
            \printf(
                \__('L\'offre se termine dans %d jours et %d heures', 'woo-tweaks-tools'),
                $days,
                $hours
            );
        } else {
            \printf(
                \__('L\'offre se termine dans %d heures et %d minutes', 'woo-tweaks-tools'),
                $hours,
                (int) $diff->format('%i')
            );
        }
        echo '</div>';
    }
}
