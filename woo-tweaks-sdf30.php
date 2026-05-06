<?php
/**
 * Plugin Name:       Woo Tweaks Tools (by OPEN-SDF)
 * Plugin URI:        https://open.sdf30.com
 * Description:       A collection of performance, UI/UX, and administrative utilities for WooCommerce.
 * Version:           1.0.0
 * Author:            Elk @ OPEN-SDF
 * Author URI:        https://sdf30.com
 * Text Domain:       woo-tweaks-tools
 * Domain Path:       /languages
 * Requires at least: 6.0
 * Requires PHP:      8.0
 *
 * @package WooTweaksTools
 */

declare(strict_types=1);

namespace WooTweaksTools;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main plugin class.
 */
final class Plugin
{
    /**
     * Plugin instance.
     *
     * @var Plugin|null
     */
    private static ?Plugin $instance = null;

    /**
     * Plugin version.
     */
    public const VERSION = '1.0.0';

    /**
     * Constructor.
     */
    private function __construct()
    {
        $this->init_hooks();
    }

    /**
     * Get the singleton instance.
     *
     * @return self
     */
    public static function get_instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialize WordPress hooks.
     */
    private function init_hooks(): void
    {
        \add_action('plugins_loaded', [$this, 'load_textdomain']);
        \add_action('plugins_loaded', [$this, 'check_dependencies']);
        // Further initialization will go here.
    }

    /**
     * Load plugin text domain for translation.
     */
    public function load_textdomain(): void
    {
        \load_plugin_textdomain(
            'woo-tweaks-tools',
            false,
            \dirname(\plugin_basename(__FILE__)) . '/languages'
        );
    }

    /**
     * Check if WooCommerce is active.
     */
    public function check_dependencies(): void
    {
        if (!\class_exists('WooCommerce')) {
            \add_action('admin_notices', [$this, 'woocommerce_missing_notice']);
            return;
        }

        $this->load_modules();
    }

    /**
     * Admin notice if WooCommerce is missing.
     */
    public function woocommerce_missing_notice(): void
    {
        $class = 'notice notice-error';
        $message = \__('Woo Tweaks Tools requires WooCommerce to be installed and active.', 'woo-tweaks-tools');
        \printf('<div class="%1$s"><p>%2$s</p></div>', \esc_attr($class), \esc_html($message));
    }

    /**
     * Load the plugin modules.
     */
    private function load_modules(): void
    {
        // Core module loading logic will be implemented here.
    }
}

// Bootstrap the plugin.
function run(): void
{
    Plugin::get_instance();
}

run();
