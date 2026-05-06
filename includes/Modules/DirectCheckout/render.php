<?php
// Render the direct checkout button for FSE
$label = \get_option('woo_tweaks_direct_checkout_label', \__('Acheter maintenant', 'woo-tweaks-tools'));
$button_text = \esc_html($label);
?>
<div <?php echo get_block_wrapper_attributes(); ?>>
    <button type="button" class="button alt woo-tweaks-fse-direct-checkout" style="background: none; color: #007b5f; font-weight: 600; border: solid 1px; padding: 10px;">
        <?php echo $button_text; ?>
    </button>
</div>
