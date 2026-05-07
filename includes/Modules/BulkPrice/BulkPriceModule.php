<?php
/**
 * Bulk Price Manager Module.
 *
 * @package WooTweaksTools
 */

declare(strict_types=1);

namespace WooTweaksTools\Modules\BulkPrice;

use WooTweaksTools\Core\AbstractModule;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles bulk price adjustments in the product list.
 */
class BulkPriceModule extends AbstractModule
{
    /**
     * Determine if the module is active.
     */
    public function is_active(): bool
    {
        return true; // Active par défaut, pourrait être lié à une option plus tard.
    }

    /**
     * Initialize module hooks.
     */
    public function init(): void
    {
        \add_filter('bulk_actions-edit-product', [$this, 'add_bulk_action']);
        \add_filter('handle_bulk_actions-edit-product', [$this, 'handle_bulk_action'], 10, 3);
        \add_action('admin_footer-edit.php', [$this, 'inject_modal_and_js']);
        \add_action('admin_notices', [$this, 'display_bulk_action_notices']);
    }

    /**
     * Add the custom bulk action to the dropdown.
     *
     * @param array $bulk_actions
     * @return array
     */
    public function add_bulk_action(array $bulk_actions): array
    {
        $bulk_actions['woo_tweaks_adjust_prices'] = \__('Adjust prices (+/-)', 'tweak-tools-sdf30');
        return $bulk_actions;
    }

    /**
     * Inject the modal HTML and JavaScript into the footer of the product list.
     */
    public function inject_modal_and_js(): void
    {
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
        global $post_type;
        if ($post_type !== 'product') {
            return;
        }

        ?>
        <!-- Woo Tweaks Bulk Price Modal -->
        <div id="tweak-tools-sdf30s-bulk-modal" class="tweak-tools-sdf30s-modal" style="display:none;">
            <div class="tweak-tools-sdf30s-modal-content">
                <div class="tweak-tools-sdf30s-modal-header">
                    <h3><?php \esc_html_e('Bulk Price Adjustment', 'tweak-tools-sdf30'); ?></h3>
                    <span class="tweak-tools-sdf30s-modal-close">&times;</span>
                </div>
                <div class="tweak-tools-sdf30s-modal-body">
                    <p class="desc"><?php \esc_html_e('Apply a price change to selected products.', 'tweak-tools-sdf30'); ?></p>
                    
                    <div class="tweak-tools-sdf30s-field-group">
                        <label><?php \esc_html_e('Operation', 'tweak-tools-sdf30'); ?></label>
                        <select id="wt-bulk-op">
                            <option value="increase"><?php \esc_html_e('Increase (+)', 'tweak-tools-sdf30'); ?></option>
                            <option value="decrease"><?php \esc_html_e('Decrease (-)', 'tweak-tools-sdf30'); ?></option>
                        </select>
                    </div>

                    <div class="tweak-tools-sdf30s-field-group">
                        <label><?php \esc_html_e('Type', 'tweak-tools-sdf30'); ?></label>
                        <select id="wt-bulk-type">
                            <option value="percent"><?php \esc_html_e('Percentage (%)', 'tweak-tools-sdf30'); ?></option>
                            <option value="fixed"><?php \esc_html_e('Fixed amount (€,$,...)', 'tweak-tools-sdf30'); ?></option>
                        </select>
                    </div>

                    <div class="tweak-tools-sdf30s-field-group">
                        <label><?php \esc_html_e('Value', 'tweak-tools-sdf30'); ?></label>
                        <input type="number" id="wt-bulk-value" step="0.01" min="0" placeholder="0.00">
                    </div>

                    <div class="tweak-tools-sdf30s-field-group checkbox-group">
                        <input type="checkbox" id="wt-bulk-round" value="1">
                        <label for="wt-bulk-round"><?php \esc_html_e('Round to .99', 'tweak-tools-sdf30'); ?></label>
                    </div>
                </div>
                <div class="tweak-tools-sdf30s-modal-footer">
                    <button type="button" class="button" id="wt-bulk-cancel"><?php \esc_html_e('Cancel', 'tweak-tools-sdf30'); ?></button>
                    <button type="button" class="button button-primary" id="wt-bulk-apply"><?php \esc_html_e('Apply', 'tweak-tools-sdf30'); ?></button>
                </div>
            </div>
        </div>

        <style>
            .tweak-tools-sdf30s-modal {
                position: fixed;
                z-index: 100000;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0,0,0,0.5);
                backdrop-filter: blur(4px);
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .tweak-tools-sdf30s-modal-content {
                background: #fff;
                width: 400px;
                border-radius: 8px;
                box-shadow: 0 10px 25px rgba(0,0,0,0.2);
                overflow: hidden;
                animation: wtModalFadeIn 0.3s ease-out;
            }
            @keyframes wtModalFadeIn {
                from { opacity: 0; transform: translateY(-20px); }
                to { opacity: 1; transform: translateY(0); }
            }
            .tweak-tools-sdf30s-modal-header {
                padding: 15px 20px;
                background: #1d2327;
                color: #fff;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            .tweak-tools-sdf30s-modal-header h3 {
                margin: 0;
                color: #fff;
                font-size: 16px;
            }
            .tweak-tools-sdf30s-modal-close {
                cursor: pointer;
                font-size: 24px;
                line-height: 1;
            }
            .tweak-tools-sdf30s-modal-body {
                padding: 20px;
            }
            .tweak-tools-sdf30s-modal-body .desc {
                margin-top: 0;
                color: #646970;
                font-style: italic;
                margin-bottom: 20px;
            }
            .tweak-tools-sdf30s-field-group {
                margin-bottom: 15px;
            }
            .tweak-tools-sdf30s-field-group label {
                display: block;
                font-weight: 600;
                margin-bottom: 5px;
            }
            .tweak-tools-sdf30s-field-group select,
            .tweak-tools-sdf30s-field-group input[type="number"] {
                width: 100%;
            }
            .checkbox-group {
                display: flex;
                align-items: center;
                gap: 8px;
            }
            .checkbox-group label {
                margin-bottom: 0;
            }
            .tweak-tools-sdf30s-modal-footer {
                padding: 15px 20px;
                background: #f6f7f7;
                text-align: right;
                border-top: 1px solid #dcdcde;
            }
        </style>

        <script>
        jQuery(document).ready(function($) {
            var $modal = $('#tweak-tools-sdf30s-bulk-modal');
            var $form = $('#posts-filter');

            // Intercept form submission
            $form.on('submit', function(e) {
                var action = $('select[name="action"]').val();
                var action2 = $('select[name="action2"]').val();
                
                if (action === 'woo_tweaks_adjust_prices' || action2 === 'woo_tweaks_adjust_prices') {
                    // Check if any product is selected
                    if ($('input[name="post[]"]:checked').length === 0) {
                        return; // Let WP handle the "nothing selected" alert
                    }
                    
                    e.preventDefault();
                    $modal.show();
                }
            });

            // Close modal
            $('.tweak-tools-sdf30s-modal-close, #wt-bulk-cancel').on('click', function() {
                $modal.hide();
            });

            // Handle Apply
            $('#wt-bulk-apply').on('click', function() {
                var val = $('#wt-bulk-value').val();
                if (!val || val <= 0) {
                    alert('<?php \esc_html_e('Please enter a valid value.', 'tweak-tools-sdf30'); ?>');
                    return;
                }

                var params = {
                    wt_op: $('#wt-bulk-op').val(),
                    wt_type: $('#wt-bulk-type').val(),
                    wt_val: val,
                    wt_round: $('#wt-bulk-round').is(':checked') ? 1 : 0
                };

                // Append hidden inputs to form
                $.each(params, function(name, value) {
                    $('<input>').attr({
                        type: 'hidden',
                        name: name,
                        value: value
                    }).appendTo($form);
                });

                // Set the action explicitly in case it was the second dropdown
                var action = $('select[name="action"]').val() === 'woo_tweaks_adjust_prices' 
                    ? 'woo_tweaks_adjust_prices' 
                    : $('select[name="action2"]').val();
                
                $form.find('input[name="action"]').val(action);
                
                $modal.hide();
                $form.off('submit').submit(); // Remove interceptor and submit
            });

            // Close on click outside
            $(window).on('click', function(event) {
                if ($(event.target).is($modal)) {
                    $modal.hide();
                }
            });
        });
        </script>
        <?php
    }

    /**
     * Handle the bulk action on the server side.
     *
     * @param string $redirect_to The redirect URL.
     * @param string $action      The action name.
     * @param array  $post_ids    Selected post IDs.
     * @return string
     */
    public function handle_bulk_action(string $redirect_to, string $action, array $post_ids): string
    {
        if ($action !== 'woo_tweaks_adjust_prices') {
            return $redirect_to;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $op    = isset($_REQUEST['wt_op']) ? sanitize_text_field(\wp_unslash($_REQUEST['wt_op'])) : 'increase';
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $type  = isset($_REQUEST['wt_type']) ? sanitize_text_field(\wp_unslash($_REQUEST['wt_type'])) : 'percent';
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $val   = isset($_REQUEST['wt_val']) ? (float) \sanitize_text_field(\wp_unslash($_REQUEST['wt_val'])) : 0;
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $round = isset($_REQUEST['wt_round']) && \sanitize_text_field(\wp_unslash($_REQUEST['wt_round'])) == 1;

        if ($val <= 0) {
            return $redirect_to;
        }

        $updated_count = 0;

        foreach ($post_ids as $post_id) {
            $product = \wc_get_product($post_id);
            if (!$product) {
                continue;
            }

            // Get current price
            $current_price = (float) $product->get_regular_price();
            if ($current_price <= 0) {
                continue;
            }

            $new_price = $current_price;

            // Calculate new price
            if ($type === 'percent') {
                $change = $current_price * ($val / 100);
            } else {
                $change = $val;
            }

            if ($op === 'increase') {
                $new_price += $change;
            } else {
                $new_price -= $change;
            }

            // Ensure price is not negative
            if ($new_price < 0) {
                $new_price = 0;
            }

            // Round to .99 if requested
            if ($round && $new_price > 0) {
                $new_price = floor($new_price) + 0.99;
            } else {
                $new_price = round($new_price, 2);
            }

            // Save new price
            $product->set_regular_price((string) $new_price);
            
            // If it's a simple product, also update the price (WC syncs them usually, but being explicit is safer)
            $product->set_price((string) $new_price);
            
            $product->save();
            $updated_count++;
        }

        return \add_query_arg([
            'wt_bulk_updated' => $updated_count,
            'wt_bulk_op'      => $op
        ], $redirect_to);
    }

    /**
     * Display admin notices after the bulk action.
     */
    public function display_bulk_action_notices(): void
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if (isset($_GET['wt_bulk_updated'])) {
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $count = (int) \sanitize_text_field(\wp_unslash($_GET['wt_bulk_updated']));
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $op = isset($_GET['wt_bulk_op']) && \sanitize_text_field(\wp_unslash($_GET['wt_bulk_op'])) === 'increase' ? \__('increased', 'tweak-tools-sdf30') : \__('decreased', 'tweak-tools-sdf30');
            
            $message = \sprintf(
                /* translators: 1: Count of products, 2: Operation applied */
                \__('%1$d products were successfully %2$s.', 'tweak-tools-sdf30'),
                $count,
                $op
            );

            echo '<div class="notice notice-success is-dismissible"><p>' . \esc_html($message) . '</p></div>';
        }
    }
}
