<?php
/**
 * Module Manager Class.
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
 * Manages the loading and initialization of all modules.
 */
class ModuleManager
{
    /**
     * Registered modules.
     *
     * @var AbstractModule[]
     */
    private array $modules = [];

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->register_modules();
        $this->init_modules();
    }

    /**
     * Register all available modules.
     */
    private function register_modules(): void
    {
        // Require modules here. We will use a simple autoloader or manual includes.
        // For now, we will add modules to this array as they are created.
        $module_classes = [
            \WooTweaksTools\Modules\CustomLabels\CustomLabelsModule::class,
            \WooTweaksTools\Modules\ReadMoreButton\ReadMoreButtonModule::class,
            \WooTweaksTools\Modules\EmptyCartRedirect\EmptyCartRedirectModule::class,
            \WooTweaksTools\Modules\CheckoutFields\CheckoutFieldsModule::class,
        ];

        foreach ($module_classes as $module_class) {
            if (\class_exists($module_class)) {
                $this->modules[] = new $module_class();
            }
        }
    }

    /**
     * Initialize active modules.
     */
    private function init_modules(): void
    {
        foreach ($this->modules as $module) {
            if ($module->is_active()) {
                $module->init();
            }
        }
    }
}
