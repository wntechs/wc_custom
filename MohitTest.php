<?php
/*
    Plugin Name: Upwork Test By Mohit Dubey
    Plugin URI: http://wntechs.com
    Description: This is an assigment test
    Author: Mohit Dubey
    Version: 1.0
    Author URI: http://wntechs.com
    */

/**
 * The core plugin class.
 *
 * This is used to define  admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Mohit_Test
 * @subpackage Mohit_Test/includes
 * @author     Mohit Dubey <info@wntechs.com>
 */
class MohitTest
{
    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $plugin_name The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $version The current version of the plugin.
     */
    protected $version;

    /**
     * The actions to be added
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $actions
     */
    protected $actions;

    /**
     * The filters to be added
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $filters
     */
    protected $filters;


    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct()
    {
        if (defined('MOHIT_TEST_VERSION')) {
            $this->version = MOHIT_TEST_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'mohit-test';
        $this->actions = array();
        $this->filters = array();
        $this->define_hooks();;
    }

    public function init_test()
    {
        if (count($this->actions)) {

            foreach ($this->actions as $hook) {
                add_action($hook['hook'], $hook['callback'], $hook['priority']);
            }
        }
    }

    private function define_hooks()
    {
        $this->actions = [
            ['hook' => 'admin_menu', 'callback' => [$this, 'add_menu_items'], 'priority' => 10],
            ['hook' => 'admin_init', 'callback' => [$this, 'admin_initialized'], 'priority' => 10],
            ['hook' => 'wp_head', 'callback' => [$this, 'fire_public_end_hooks'], 'priority' => 10],
            ['hook' => 'document_title_parts', 'callback' => [$this, 'add_single_product_page_title'], 'priority' => 10],
            ['hook' => 'add_meta_boxes', 'callback' => [$this, 'add_noindex_option_meta_box'], 'priority' => 10],
            ['hook' => 'save_post', 'callback' => [$this, 'save_meta_box'], 'priority' => 10],


        ];

        remove_action( 'wp_head', 'noindex', 1 );

    }



    public function admin_initialized()
    {
        if (!$this->is_woocommerce_activated()) {
            add_action('admin_notices', [$this, 'woo_commerce_admin_notice']);
        }
        $this->display_options();
    }

    public function fire_public_end_hooks()
    {
        if($this->is_woocommerce_activated()) {


               if(is_product()) {

                   $noindex = get_post_meta(get_the_ID(), 'mohit-noindex', true);

                   if ($noindex == 1) {
                       ?>
                       <meta name="robots" content="noindex"/>
                       <?php
                   }
               }else{
                  noindex();
               }

        }
    }




    function add_menu_items()
    {
        //add a new menu item. This is a top level menu item i.e., this menu item can have sub menus
        add_menu_page(
            "MohitTest Options", //Required. Text in browser title bar when the page associated with this menu item is displayed.
            "MohitTest Options", //Required. Text to be displayed in the menu.
            "manage_options", //Required. The required capability of users to access this menu item.
            "mohit-test-options", //Required. A unique identifier to identify this menu item.
            [$this, "plug_options_page"], //Optional. This callback outputs the content of the page associated with this menu item.
            "", //Optional. The URL to the menu item icon.
            100 //Optional. Position of the menu item in the menu.
        );

    }

    function plug_options_page()
    {
        ?>
        <div class="wrap">
            <form method="post" action="options.php">
                <?php

                //add_settings_section callback is displayed here. For every new section we need to call settings_fields.
                settings_fields("header_section");

                // all the add_settings_field callbacks is displayed here
                do_settings_sections("plugin-options");

                // Add the submit button to serialize the options
                submit_button();

                ?>
            </form>
        </div>
        <?php
    }

    function display_options()
    {

        //section name, display name, callback to print description of section, page to which section is attached.
        add_settings_section("header_section", "Plugin Options", [$this, "display_header_options_content"], "plugin-options");

        //setting name, display name, callback to print form element, page in which field is displayed, section to which it belongs.
        //last field section is optional.
        add_settings_field("product_title_prefix", "Product Titles Prefix", [$this, "display_product_title_form_element"], "plugin-options", "header_section");

        //section name, form element name, callback for sanitization
        register_setting("header_section", "product_title_prefix");

    }

    function display_header_options_content()
    {
        //echo "The header of the plugin option page";
        //We can display header line if we need
    }

    function display_product_title_form_element()
    {

        ?>
        <input type="text" name="product_title_prefix" id="product_title_prefix"
               value="<?php echo get_option('product_title_prefix'); ?>"/>
        <?php
    }

    function add_single_product_page_title($parts)
    {


        if ($this->is_woocommerce_activated() and is_product()) {

            $prefix = get_option('product_title_prefix');
            $parts['title'] = $prefix . '-' . $parts['title'];
        }
        //Return the normal Titles if conditions aren't met
        return $parts;
    }

    /**
     * Check if WooCommerce is activated
     */

    function is_woocommerce_activated()
    {
        if (class_exists('woocommerce')) {
            return true;
        } else {
            return false;
        }
    }

    function woo_commerce_admin_notice()
    {
        ?>
        <div class="notice notice-warning is-dismissible">
            <p><?php _e('MohitTest Plugin requires woocommerce plugin.!'); ?></p>
        </div>
        <?php
    }


    function add_noindex_option_meta_box()
    {

        add_meta_box('mohit-noindex-meta-box', __('Indexing'), [$this, 'meta_box_display_callback'], 'product');
    }

    function meta_box_display_callback()
    {
        $checked = esc_attr(get_post_meta(get_the_ID(), 'mohit-noindex', true));
       // echo $checked;
        ?>
        <p class="meta-options mohit-noindex-meta-box-field">
            <label for="mohit-noindex-meta-box-no-index">
                <input type="checkbox" name="mohit-noindex" value="1"
                    <?php echo $checked == '1' ? 'checked' : ''; ?>
                       id="mohit-noindex-meta-box-no-index">
                Noindex
            </label>

        </p>
        <?php
    }

    /**
     * Save meta box content.
     *
     * @param int $post_id Post ID
     */
    function save_meta_box($post_id)
    {
        //don't perform any action if it is not a product page
        if (!get_post_type($post_id) == 'product') return;

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if ($parent_id = wp_is_post_revision($post_id)) {
            $post_id = $parent_id;
        }
        update_post_meta($post_id, 'mohit-noindex', sanitize_text_field($_POST['mohit-noindex']));
    }


}
define('MOHIT_TEST_VERSION', '1.00');
$mohit_plugin = new MohitTest();
$mohit_plugin->init_test();