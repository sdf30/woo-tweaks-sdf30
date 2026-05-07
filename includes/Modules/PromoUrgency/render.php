<?php
/**
 * Render for Promo Urgency block.
 *
 * @package WooTweaksTools
 */

declare(strict_types=1);

use WooTweaksTools\Modules\PromoUrgency\PromoUrgencyModule;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
global $product;

// If we're in the editor and no product is set, try to get a dummy or recent one.
if (!$product && is_admin()) {
    $products = wc_get_products(['limit' => 1, 'status' => 'publish']);
    if (!empty($products)) {
        $product = $products[0];
    }
}

if (!$product) {
    return;
}

$module = new PromoUrgencyModule();
// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo \wp_kses_post($module->get_urgency_message_html($product));
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
