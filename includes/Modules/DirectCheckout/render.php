<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

// Render the direct checkout button for FSE
$label = \get_option('woo_tweaks_direct_checkout_label', \__('Acheter maintenant', 'tweak-tools-sdf30'));
$button_text = \esc_html($label);
global $product;
$current_product = $product;
if (!$current_product) {
    $current_product = \wc_get_product(\get_the_ID());
}
$product_id = $current_product ? $current_product->get_id() : 0;
?>
<div <?php echo \wp_kses_post(get_block_wrapper_attributes()); ?>>
    <button type="button" class="button alt tweak-tools-sdf30s-fse-direct-checkout" data-product_id="<?php echo esc_attr($product_id); ?>" style="background: none; color: #007b5f; font-weight: 600; border: solid 1px; padding: 10px;">
        <?php echo \esc_html($button_text); ?>
    </button>
</div>
<?php
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
?>
