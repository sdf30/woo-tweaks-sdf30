<?php
/**
 * Hide Components Module.
 *
 * @package WooTweaksTools
 */

declare(strict_types=1);

namespace WooTweaksTools\Modules\HideComponents;

use WooTweaksTools\Core\AbstractModule;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles hiding native WooCommerce components.
 */
class HideComponentsModule extends AbstractModule
{
    /**
     * Determine if the module is active.
     */
    public function is_active(): bool
    {
        return true;
    }

    public function register_settings(): void
    {
        \add_filter('woo_tweaks_core_settings', [$this, 'add_settings']);
    }

    public function add_settings(array $settings): array
    {
        $settings[] = [
            'title' => \__('Masquer des éléments', 'tweak-tools-sdf30'),
            'type'  => 'title',
            'desc'  => \__('Masquer certains éléments par défaut de WooCommerce sur la page produit.', 'tweak-tools-sdf30'),
            'id'    => 'woo_tweaks_hide_components_section',
        ];
        $settings[] = [
            'title'    => \__('Masquer le SKU (UGS)', 'tweak-tools-sdf30'),
            'id'       => 'woo_tweaks_hide_sku',
            'type'     => 'checkbox',
            'default'  => 'no',
            'desc'     => \__('Cache la référence du produit.', 'tweak-tools-sdf30'),
        ];
        $settings[] = [
            'title'    => \__('Masquer les Catégories/Étiquettes', 'tweak-tools-sdf30'),
            'id'       => 'woo_tweaks_hide_categories',
            'type'     => 'checkbox',
            'default'  => 'no',
            'desc'     => \__('Cache les catégories et mots-clés dans les métadonnées du produit.', 'tweak-tools-sdf30'),
        ];
        $settings[] = [
            'title'    => \__('Masquer les Produits Apparentés', 'tweak-tools-sdf30'),
            'id'       => 'woo_tweaks_hide_related_products',
            'type'     => 'checkbox',
            'default'  => 'no',
            'desc'     => \__('Désactive l\'affichage des produits suggérés.', 'tweak-tools-sdf30'),
        ];
        $settings[] = [
            'type' => 'sectionend',
            'id'   => 'woo_tweaks_hide_components_section',
        ];

        return $settings;
    }

    /**
     * Initialize module hooks.
     */
    public function init(): void
    {
        \add_action('wp', [$this, 'hide_components']);
        \add_action('wp_head', [$this, 'hide_components_css']);
    }

    /**
     * Remove actions or add filters to hide components.
     */
    public function hide_components(): void
    {
        // Désactivé sur les thèmes FSE pour éviter les conflits
        if (\function_exists('wp_is_block_theme') && \wp_is_block_theme()) {
            return;
        }

        if (!\is_product()) {
            return;
        }

        if (\get_option('woo_tweaks_hide_sku') === 'yes') {
            \add_filter('wc_product_sku_enabled', '__return_false');
        }

        if (\get_option('woo_tweaks_hide_related_products') === 'yes') {
            \remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20);
        }
    }

    /**
     * Inject CSS to hide components that cannot be unhooked easily.
     */
    public function hide_components_css(): void
    {
        // Désactivé sur les thèmes FSE pour éviter les conflits
        if (\function_exists('wp_is_block_theme') && \wp_is_block_theme()) {
            return;
        }

        if (!\is_product()) {
            return;
        }

        $css = '';

        if (\get_option('woo_tweaks_hide_categories') === 'yes') {
            // Hides category and tag outputs in the product meta section.
            $css .= '.product_meta .posted_in, .product_meta .tagged_as { display: none !important; }' . "\n";
        }

        if (!empty($css)) {
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo '<style id="tweak-tools-sdf30s-hide-components">' . "\n" . \wp_strip_all_tags($css) . "\n" . '</style>' . "\n";
        }
    }
}
