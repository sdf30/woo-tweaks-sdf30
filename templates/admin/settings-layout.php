<?php
/**
 * Admin settings layout template.
 *
 * @var array $main_settings
 * @var array $sidebar_settings
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="woo-tweaks-layout">
    <div class="woo-tweaks-main">
        <?php \WC_Admin_Settings::output_fields($main_settings); ?>
    </div>
    <div class="woo-tweaks-sidebar">
        <?php \WC_Admin_Settings::output_fields($sidebar_settings); ?>
    </div>
</div>
