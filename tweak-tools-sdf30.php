<?php
/**
 * Plugin Name:       Tweak Tools for WooCommerce
 * Plugin URI:        https://open.sdf30.com
 * Description:       A collection of performance, UI/UX, and administrative utilities for WooCommerce.
 * Version:           1.3.3
 * Author:            OPEN-SDF
 * Author URI:        https://open.sdf30.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       tweak-tools-sdf30
 * Domain Path:       /languages
 * Requires at least: 6.0
 * Requires PHP:      8.1
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
    public const VERSION = '1.3.3';

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
        
        // Add settings link to plugin action links.
        $plugin_basename = \plugin_basename(__FILE__);
        \add_filter("plugin_action_links_{$plugin_basename}", [$this, 'add_plugin_action_links']);
    }

    /**
     * Add settings link to plugin action links.
     *
     * @param array $links Array of links.
     * @return array
     */
    public function add_plugin_action_links(array $links): array
    {
        $settings_url = \admin_url('admin.php?page=wc-settings&tab=woo_tweaks_tools');
        $settings_link = \sprintf('<a href="%s">%s</a>', \esc_url($settings_url), \__('Settings', 'tweak-tools-sdf30'));
        \array_unshift($links, $settings_link);
        return $links;
    }

    /**
     * Load plugin text domain for translation.
     */
    public function load_textdomain(): void
    {
        // phpcs:ignore PluginCheck.CodeAnalysis.DiscouragedFunctions.load_plugin_textdomainFound
        \load_plugin_textdomain(
            'tweak-tools-sdf30',
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
        $message = \__('Woo Tweaks Tools requires WooCommerce to be installed and active.', 'tweak-tools-sdf30');
        \printf('<div class="%1$s"><p>%2$s</p></div>', \esc_attr($class), \esc_html($message));
    }

    /**
     * Load the plugin modules.
     */
    private function load_modules(): void
    {
        $this->register_autoloader();
        new \WooTweaksTools\Core\ModuleManager();
        
        if (\is_admin()) {
            \add_filter('woocommerce_get_settings_pages', function (array $settings) {
                $settings[] = new \WooTweaksTools\Core\SettingsPage();
                return $settings;
            });
        }
    }

    /**
     * Simple PSR-4 Autoloader for the plugin.
     */
    private function register_autoloader(): void
    {
        \spl_autoload_register(function (string $class) {
            $prefix = 'WooTweaksTools\\';
            $base_dir = __DIR__ . '/includes/';

            $len = \strlen($prefix);
            if (\strncmp($prefix, $class, $len) !== 0) {
                return;
            }

            $relative_class = \substr($class, $len);
            $file = $base_dir . \str_replace('\\', '/', $relative_class) . '.php';

            if (\file_exists($file)) {
                require $file;
            }
        });
    }
}

// Bootstrap the plugin.
function run(): void
{
    Plugin::get_instance();
}

run();
