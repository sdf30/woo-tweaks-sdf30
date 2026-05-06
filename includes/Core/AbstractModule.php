<?php
/**
 * Abstract Module Class.
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
 * Base class for all plugin modules.
 */
abstract class AbstractModule
{
    /**
     * Determine if the module is active.
     *
     * @return bool
     */
    abstract public function is_active(): bool;

    /**
     * Register settings for this module.
     * Called unconditionally on all modules to ensure settings are available.
     *
     * @return void
     */
    public function register_settings(): void
    {
        // Override in child class if needed.
    }

    /**
     * Initialize the module hooks and functionality.
     *
     * @return void
     */
    abstract public function init(): void;
}
