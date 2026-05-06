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
        $this->label = \__('Woo Tweaks', 'woo-tweaks-tools');

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
        if (!isset($_GET['page']) || $_GET['page'] !== 'wc-settings') {
            return;
        }

        // Only load on our specific tab.
        if (!isset($_GET['tab']) || $_GET['tab'] !== $this->id) {
            return;
        }

        $settings = \wp_enqueue_code_editor(['type' => 'text/css']);

        if (false === $settings) {
            return;
        }

        \wp_add_inline_script(
            'code-editor',
            sprintf(
                'jQuery(document).ready(function($) {
                    var textarea = $("#woo_tweaks_custom_css");
                    if (textarea.length) {
                        var editor = wp.codeEditor.initialize(textarea, %s);
                        
                        // Sync CodeMirror to textarea on change
                        editor.codemirror.on("change", function(cm) {
                            editor.codemirror.save();
                            textarea.trigger("change");
                        });
                        
                        // Also force sync on form submit
                        $("form#mainform").on("submit", function() {
                            editor.codemirror.save();
                        });
                    }
                });',
                \wp_json_encode($settings)
            )
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
        
        ?>
        <style>
            .woo-tweaks-layout {
                display: grid;
                grid-template-columns: 1fr 400px;
                gap: 30px;
                align-items: start;
                margin-top: 20px;
            }
            .woo-tweaks-sidebar {
                background: #fff;
                padding: 20px;
                border: 1px solid #c3c4c7;
                border-radius: 4px;
                box-shadow: 0 1px 1px rgba(0,0,0,.04);
            }
            .woo-tweaks-sidebar h2 {
                margin-top: 0;
            }
            .woo-tweaks-sidebar table.form-table {
                width: 100%;
            }
            .woo-tweaks-sidebar table.form-table th {
                display: none;
            }
            .woo-tweaks-sidebar table.form-table td {
                padding: 0;
                width: 100%;
            }
            .CodeMirror {
                height: 400px;
            }
        </style>
        <div class="woo-tweaks-layout">
            <div class="woo-tweaks-main">
                <?php \WC_Admin_Settings::output_fields($main_settings); ?>
            </div>
            <div class="woo-tweaks-sidebar">
                <?php \WC_Admin_Settings::output_fields($sidebar_settings); ?>
            </div>
        </div>
        <?php
    }

    /**
     * Add submenu page under WooCommerce.
     */
    public function add_admin_submenu(): void
    {
        \add_submenu_page(
            'woocommerce',
            \__('Tweaks Tools', 'woo-tweaks-tools'),
            \__('Tweaks Tools', 'woo-tweaks-tools'),
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
        $settings = \apply_filters('woo_tweaks_tools_settings', [
            [
                'title' => \__('Global Custom Labels', 'woo-tweaks-tools'),
                'type'  => 'title',
                'desc'  => \__('These labels will apply globally unless overridden per product.', 'woo-tweaks-tools'),
                'id'    => 'woo_tweaks_labels_section',
            ],
            [
                'title'    => \__('Add to Cart Text', 'woo-tweaks-tools'),
                'id'       => 'woo_tweaks_add_to_cart_text',
                'type'     => 'text',
                'default'  => '',
                'desc'     => \__('Leave empty to use WooCommerce default.', 'woo-tweaks-tools'),
                'desc_tip' => true,
            ],
            [
                'title'    => \__('Sale Badge Text', 'woo-tweaks-tools'),
                'id'       => 'woo_tweaks_sale_badge_text',
                'type'     => 'text',
                'default'  => '',
                'placeholder' => \__('Sale!', 'woocommerce'),
                'desc'     => \__('Text for the "Sale" badge (Promo).', 'woo-tweaks-tools'),
                'desc_tip' => true,
            ],
            [
                'title'    => \__('Out of Stock Text', 'woo-tweaks-tools'),
                'id'       => 'woo_tweaks_out_of_stock_text',
                'type'     => 'text',
                'default'  => '',
                'placeholder' => \__('Out of stock', 'woocommerce'),
                'desc'     => \__('Text for "Out of stock" availability.', 'woo-tweaks-tools'),
                'desc_tip' => true,
            ],
            [
                'title'    => \__('Read More Button Label', 'woo-tweaks-tools'),
                'id'       => 'woo_tweaks_read_more_label',
                'type'     => 'text',
                'default'  => \__('Read More', 'woo-tweaks-tools'),
                'desc'     => \__('Default label for the additional button (Feat2).', 'woo-tweaks-tools'),
                'desc_tip' => true,
            ],
            [
                'title'    => \__('Ouvrir dans un nouvel onglet', 'woo-tweaks-tools'),
                'id'       => 'woo_tweaks_read_more_target_blank',
                'type'     => 'checkbox',
                'default'  => 'no',
                'desc'     => \__('Appliquer target="_blank" au bouton "En Savoir Plus".', 'woo-tweaks-tools'),
            ],
            [
                'title'    => \__('Afficher sur la fiche produit', 'woo-tweaks-tools'),
                'id'       => 'woo_tweaks_read_more_show_on_single',
                'type'     => 'checkbox',
                'default'  => 'no',
                'desc'     => \__('Afficher également le bouton sur les pages de produit seul.', 'woo-tweaks-tools'),
            ],
            [
                'title'    => \__('SKU Prefix', 'woo-tweaks-tools'),
                'id'       => 'woo_tweaks_sku_label',
                'type'     => 'text',
                'default'  => '',
                'placeholder' => 'SKU:',
                'desc'     => \__('Override the default "SKU:" text.', 'woo-tweaks-tools'),
                'desc_tip' => true,
            ],
            [
                'title'    => \__('Category Prefix', 'woo-tweaks-tools'),
                'id'       => 'woo_tweaks_category_label',
                'type'     => 'text',
                'default'  => '',
                'placeholder' => 'Category:',
                'desc'     => \__('Override the default "Category:" text.', 'woo-tweaks-tools'),
                'desc_tip' => true,
            ],
            [
                'type' => 'sectionend',
                'id'   => 'woo_tweaks_labels_section',
            ],
            [
                'title' => \__('General Tweaks', 'woo-tweaks-tools'),
                'type'  => 'title',
                'desc'  => \__('General utility features for WooCommerce.', 'woo-tweaks-tools'),
                'id'    => 'woo_tweaks_general_section',
            ],
            [
                'title'    => \__('Direct Checkout', 'woo-tweaks-tools'),
                'id'       => 'woo_tweaks_direct_checkout',
                'type'     => 'checkbox',
                'default'  => 'no',
                'desc'     => \__('Ajoute un bouton d\'achat direct à côté du bouton d\'ajout au panier.', 'woo-tweaks-tools'),
            ],
            [
                'title'    => \__('Direct Checkout Label', 'woo-tweaks-tools'),
                'id'       => 'woo_tweaks_direct_checkout_label',
                'type'     => 'text',
                'default'  => \__('Acheter maintenant', 'woo-tweaks-tools'),
                'desc'     => \__('Texte affiché sur le bouton d\'achat direct.', 'woo-tweaks-tools'),
                'desc_tip' => true,
            ],
            [
                'title' => \__('Hide Components (Thèmes Classiques uniquement)', 'woo-tweaks-tools'),
                'type'  => 'title',
                'desc'  => \__('Masquez certains éléments natifs de la fiche produit. Inactif sur les thèmes FSE.', 'woo-tweaks-tools'),
                'id'    => 'woo_tweaks_hide_components_section',
            ],
            [
                'title' => \__('Masquer le SKU', 'woo-tweaks-tools'),
                'id'    => 'woo_tweaks_hide_sku',
                'type'  => 'checkbox',
                'default' => 'no',
            ],
            [
                'title' => \__('Masquer les Catégories', 'woo-tweaks-tools'),
                'id'    => 'woo_tweaks_hide_categories',
                'type'  => 'checkbox',
                'default' => 'no',
            ],
            [
                'title' => \__('Masquer les Produits Apparentés', 'woo-tweaks-tools'),
                'id'    => 'woo_tweaks_hide_related_products',
                'type'  => 'checkbox',
                'default' => 'no',
            ],
            [
                'type' => 'sectionend',
                'id'   => 'woo_tweaks_hide_components_section',
            ],
            [
                'title' => \__('Checkout Tweaks', 'woo-tweaks-tools'),
                'type'  => 'title',
                'desc'  => __('Simplifiez votre tunnel de commande.', 'woo-tweaks-tools'),
                'id'    => 'woo_tweaks_checkout_section',
            ],
            [
                'title' => __('Masquer Société', 'woo-tweaks-tools'),
                'id'    => 'woo_tweaks_hide_billing_company',
                'type'  => 'checkbox',
                'default' => 'no',
            ],
            [
                'title' => __('Masquer Adresse (ligne 2)', 'woo-tweaks-tools'),
                'id'    => 'woo_tweaks_hide_billing_address_2',
                'type'  => 'checkbox',
                'default' => 'no',
            ],
            [
                'title' => __('Masquer Téléphone', 'woo-tweaks-tools'),
                'id'    => 'woo_tweaks_hide_billing_phone',
                'type'  => 'checkbox',
                'default' => 'no',
            ],
            [
                'title' => __('Masquer Notes de commande', 'woo-tweaks-tools'),
                'id'    => 'woo_tweaks_hide_order_notes',
                'type'  => 'checkbox',
                'default' => 'no',
            ],
            [
                'type' => 'sectionend',
                'id'   => 'woo_tweaks_checkout_section',
            ],
            [
                'title' => __('Redirection Panier Vide', 'woo-tweaks-tools'),
                'id'       => 'woo_tweaks_empty_cart_redirect',
                'type'     => 'checkbox',
                'default'  => 'no',
                'desc'     => \__('Redirect users to the shop page if they access an empty cart page.', 'woo-tweaks-tools'),
                'desc_tip' => true,
            ],
            [
                'type' => 'sectionend',
                'id'   => 'woo_tweaks_general_section',
            ],
            [
                'title' => \__('Apparence & Custom CSS', 'woo-tweaks-tools'),
                'type'  => 'title',
                'desc'  => \__('Ajoutez votre CSS personnalisé ici pour styliser les éléments du plugin sans surcharger le CSS global de votre site. <br><br><b>Glossaire des classes :</b><br><code>a.woo-tweaks-read-more</code> : Le bouton "Read More" (Feat 2).', 'woo-tweaks-tools'),
                'id'    => 'woo_tweaks_custom_css_section',
            ],
            [
                'title' => \__('CSS Personnalisé', 'woo-tweaks-tools'),
                'id'    => 'woo_tweaks_custom_css',
                'type'  => 'textarea',
                'default' => '',
                'css'   => 'width:100%; height: 300px;',
            ],
            [
                'type' => 'sectionend',
                'id'   => 'woo_tweaks_custom_css_section',
            ],
        ]);

        return $settings;
    }
}
