<?php
/**
 * WooCommerce Settings Page Integration.
 *
 * @package WooTweaksTools
 */

declare(strict_types=1);

namespace WooTweaksTools\Core;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Adds a new settings tab to WooCommerce.
 */
class SettingsPage extends \WC_Settings_Page
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->id    = 'woo_tweaks_tools';
        $this->label = \__('Woo Tweaks', 'tweak-tools-sdf30');

        \add_filter('woocommerce_settings_tabs_array', [$this, 'add_settings_page'], 20);
        \add_action('woocommerce_settings_' . $this->id, [$this, 'output']);
        \add_action('woocommerce_settings_save_' . $this->id, [$this, 'save']);
        \add_action('woocommerce_sections_' . $this->id, [$this, 'output_sections']);

        // Add direct submenu under WooCommerce.
        \add_action('admin_menu', [$this, 'add_admin_submenu'], 20);

        // Enqueue CodeMirror for Custom CSS.
        \add_action('admin_enqueue_scripts', [$this, 'enqueue_code_editor']);
    }

    /**
     * Enqueue CodeMirror scripts and initialize editor.
     *
     * @param string $hook
     */
    public function enqueue_code_editor(string $hook): void
    {
        // Only load on WooCommerce settings page.
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if (!isset($_GET['page']) || \sanitize_text_field(\wp_unslash($_GET['page'])) !== 'wc-settings') {
            return;
        }

        // Only load on our specific tab.
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if (!isset($_GET['tab']) || \sanitize_text_field(\wp_unslash($_GET['tab'])) !== $this->id) {
            return;
        }

        \wp_enqueue_style(
            'tweak-tools-sdf30s-admin-settings',
            plugin_dir_url(dirname(__DIR__)) . 'assets/css/admin-settings.css',
            [],
            filemtime(plugin_dir_path(dirname(__DIR__)) . 'assets/css/admin-settings.css')
        );

        $settings = \wp_enqueue_code_editor(['type' => 'text/css']);

        if (false === $settings) {
            return;
        }

        \wp_enqueue_script(
            'tweak-tools-sdf30s-admin-settings',
            plugin_dir_url(dirname(__DIR__)) . 'assets/js/admin-settings.js',
            ['jquery'],
            filemtime(plugin_dir_path(dirname(__DIR__)) . 'assets/js/admin-settings.js'),
            true
        );

        \wp_add_inline_script(
            'tweak-tools-sdf30s-admin-settings',
            'var wooTweaksCodeMirror = ' . \wp_json_encode($settings) . ';',
            'before'
        );
    }

    /**
     * Custom output for the settings page to create a 2-column layout natively.
     */
    public function output(): void
    {
        $settings = $this->get_settings();
        
        $main_settings = [];
        $sidebar_settings = [];
        $is_sidebar = false;
        
        foreach ($settings as $setting) {
            if (isset($setting['id']) && $setting['id'] === 'woo_tweaks_custom_css_section' && $setting['type'] === 'title') {
                $is_sidebar = true;
            }
            
            if ($is_sidebar) {
                $sidebar_settings[] = $setting;
            } else {
                $main_settings[] = $setting;
            }
            
            if (isset($setting['id']) && $setting['id'] === 'woo_tweaks_custom_css_section' && $setting['type'] === 'sectionend') {
                $is_sidebar = false;
            }
        }
        
        include plugin_dir_path(dirname(__DIR__)) . 'templates/admin/settings-layout.php';
    }

    /**
     * Add submenu page under WooCommerce.
     */
    public function add_admin_submenu(): void
    {
        \add_submenu_page(
            'woocommerce',
            \__('Tweaks Tools', 'tweak-tools-sdf30'),
            \__('Tweaks Tools', 'tweak-tools-sdf30'),
            'manage_woocommerce',
            'admin.php?page=wc-settings&tab=' . $this->id,
            null,
            null
        );
    }

    /**
     * Get settings array.
     *
     * @return array
     */
    public function get_settings(): array
    {
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
        return \apply_filters('woo_tweaks_tools_settings', \apply_filters('woo_tweaks_core_settings', []));
    }
}
