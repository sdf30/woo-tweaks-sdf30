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
<div class="tweak-tools-sdf30s-layout">
    <div class="tweak-tools-sdf30s-main">
        <?php \WC_Admin_Settings::output_fields($main_settings); ?>
    </div>
    <div class="tweak-tools-sdf30s-sidebar">
        <?php \WC_Admin_Settings::output_fields($sidebar_settings); ?>
    </div>
</div>
