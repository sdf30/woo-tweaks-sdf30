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

                    // Layout Tweaks for the Settings Page
                    var $form = $("form#mainform");
                    var $submit = $form.find(".submit, p.submit");
                    
                    // Identify the CSS section elements
                    var $cssTable = $("#woo_tweaks_custom_css").closest("table");
                    var $cssDesc = $cssTable.prev("p");
                    var $cssTitle = $cssDesc.prev("h2");
                    
                    var $sidebar = $("<div class=\'woo-tweaks-sidebar\'></div>");
                    var $mainContent = $("<div class=\'woo-tweaks-main\'></div>");
                    
                    // Move all children except submit into mainContent initially
                    $form.children().not($submit).appendTo($mainContent);
                    
                    // Move CSS elements from mainContent to sidebar
                    $cssTitle.appendTo($sidebar);
                    $cssDesc.appendTo($sidebar);
                    $cssTable.appendTo($sidebar);
                    
                    // Append wrappers back to form
                    $form.prepend($sidebar);
                    $form.prepend($mainContent);
                    
                    // Apply CSS Grid to the form
                    $form.css({
                        "display": "grid",
                        "grid-template-columns": "1fr 400px",
                        "gap": "30px",
                        "align-items": "start"
                    });
                    
                    // Make the submit button span full width
                    if ($submit.length) {
                        $submit.css({
                            "grid-column": "1 / -1", 
                            "margin-top": "20px", 
                            "padding": "15px",
                            "background": "#fff",
                            "border": "1px solid #c3c4c7",
                            "border-radius": "4px"
                        });
                    }
                    
                    // Style the sidebar
                    $sidebar.css({
                        "background": "#fff",
                        "padding": "20px",
                        "border": "1px solid #c3c4c7",
                        "border-radius": "4px",
                        "box-shadow": "0 1px 1px rgba(0,0,0,.04)"
                    });
                    
                    // Adjust table inside sidebar to fit nicely
                    $cssTable.css("width", "100%");
                    $cssTable.find("th").hide(); // Hide the label "CSS Personnalisé" since title is enough
                    $cssTable.find("td").css({
                        "padding": "0", 
                        "width": "100%"
                    });
                    
                    // Ensure the code editor stretches nicely
                    $(".CodeMirror").css("height", "400px");
                });',
                \wp_json_encode($settings)
            )
        );
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
                'title' => __('Checkout Tweaks', 'woo-tweaks-tools'),
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
