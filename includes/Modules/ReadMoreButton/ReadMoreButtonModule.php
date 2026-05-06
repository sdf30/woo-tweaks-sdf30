<?php
/**
 * Read More Button Module.
 *
 * @package WooTweaksTools
 */

declare(strict_types=1);

namespace WooTweaksTools\Modules\ReadMoreButton;

use WooTweaksTools\Core\AbstractModule;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Read More Button Module Class.
 */
class ReadMoreButtonModule extends AbstractModule
{
    /**
     * Determine if the module is active.
     */
    public function is_active(): bool
    {
        return true;
    }

    /**
     * Initialize module.
     */
    public function init(): void
    {
        // Product Meta (Backend)
        \add_action('woocommerce_product_options_general_product_data', [$this, 'add_product_options']);
        \add_action('woocommerce_process_product_meta', [$this, 'save_product_options']);

        // Frontend Legacy display (before Add to Cart button which is priority 10)
        // We only add this if it is NOT a block theme, to prevent duplicates when using FSE.
        if (function_exists('wp_is_block_theme') && !\wp_is_block_theme()) {
            \add_action('woocommerce_after_shop_loop_item', [$this, 'display_legacy_button'], 5);
            
            // Show on single product if enabled
            if (\get_option('woo_tweaks_read_more_show_on_single') === 'yes') {
                \add_action('woocommerce_single_product_summary', [$this, 'display_legacy_button'], 35);
            }
        }

        // FSE Block Registration
        \add_action('init', [$this, 'register_fse_block']);
        \add_action('enqueue_block_editor_assets', [$this, 'enqueue_block_assets']);
    }

    /**
     * Enqueue block editor assets.
     */
    public function enqueue_block_assets(): void
    {
        $script_path = 'includes/Modules/ReadMoreButton/block.js';
        $script_url  = \plugin_dir_url(\dirname(\dirname(__DIR__))) . $script_path;

        \wp_enqueue_script(
            'woo-tweaks-read-more-block-editor',
            $script_url,
            ['wp-blocks', 'wp-element', 'wp-server-side-render', 'wp-editor'],
            '1.0.0',
            true
        );
    }

    /**
     * Add product-specific options.
     */
    public function add_product_options(): void
    {
        echo '<div class="options_group">';
        \woocommerce_wp_text_input([
            'id'          => '_woo_tweaks_read_more_url',
            'label'       => \__('"Read More" URL', 'woo-tweaks-tools'),
            'description' => \__('Enter an internal or external link to display a "Read More" button on the archive page.', 'woo-tweaks-tools'),
            'desc_tip'    => true,
            'placeholder' => 'https://...',
        ]);
        \woocommerce_wp_text_input([
            'id'          => '_woo_tweaks_read_more_label',
            'label'       => \__('"Read More" Label', 'woo-tweaks-tools'),
            'description' => \__('Override the global "Read More" label for this product.', 'woo-tweaks-tools'),
            'desc_tip'    => true,
            'placeholder' => \__('Read More', 'woo-tweaks-tools'),
        ]);
        echo '</div>';
    }

    /**
     * Save product options.
     *
     * @param int $post_id Post ID.
     */
    public function save_product_options(int $post_id): void
    {
        $url = isset($_POST['_woo_tweaks_read_more_url']) ? \sanitize_url(\wp_unslash($_POST['_woo_tweaks_read_more_url'])) : '';
        \update_post_meta($post_id, '_woo_tweaks_read_more_url', $url);

        $label = isset($_POST['_woo_tweaks_read_more_label']) ? \sanitize_text_field(\wp_unslash($_POST['_woo_tweaks_read_more_label'])) : '';
        \update_post_meta($post_id, '_woo_tweaks_read_more_label', $label);
    }

    /**
     * Get the read more HTML for a specific product.
     *
     * @param int $product_id
     * @return string
     */
    public static function get_button_html(int $product_id): string
    {
        $url = \get_post_meta($product_id, '_woo_tweaks_read_more_url', true);
        if (empty($url)) {
            return '';
        }

        // 1. Check for product-specific label.
        $label = \get_post_meta($product_id, '_woo_tweaks_read_more_label', true);

        // 2. Fallback to global label.
        if (empty($label)) {
            $label = \get_option('woo_tweaks_read_more_label', '');
        }

        // 3. Absolute fallback.
        if (empty($label)) {
            $label = \__('Read More', 'woo-tweaks-tools');
        }
        
        
        // 4. Target Blank.
        $target = '';
        if (\get_option('woo_tweaks_read_more_target_blank') === 'yes') {
            $target = ' target="_blank" rel="noopener"';
        }

        // Return standard WooCommerce button HTML
        return \sprintf(
            '<a href="%s" class="button alt wp-element-button woo-tweaks-read-more" style="margin-right: 5px;"%s>%s</a>',
            \esc_url($url),
            $target,
            \esc_html($label)
        );
    }

    /**
     * Display the button in legacy themes.
     */
    public function display_legacy_button(): void
    {
        global $product;
        if (!$product) {
            return;
        }

        echo self::get_button_html($product->get_id());
    }

    /**
     * Register the dynamic FSE block.
     */
    public function register_fse_block(): void
    {
        \register_block_type(__DIR__, [
            'render_callback' => [$this, 'render_fse_block'],
        ]);
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
        $product_id = isset($block->context['postId']) ? (int) $block->context['postId'] : 0;
        
        $html = self::get_button_html($product_id);

        // If HTML is empty but we are in the Site Editor context (REST API), show a preview button.
        if (empty($html) && defined('REST_REQUEST') && REST_REQUEST) {
            $label = \get_option('woo_tweaks_read_more_label', '');
            if (empty($label)) {
                $label = \__('Read More', 'woo-tweaks-tools');
            }
            
            // 4. Target Blank.
            $target = '';
            if (\get_option('woo_tweaks_read_more_target_blank') === 'yes') {
                $target = ' target="_blank" rel="noopener"';
            }
            
            return \sprintf(
                '<a href="#" class="button alt wp-element-button woo-tweaks-read-more" style="margin-right: 5px; opacity: 0.5;"%s>%s</a>',
                $target,
                \esc_html($label)
            );
        }

        return $html;
    }
}
