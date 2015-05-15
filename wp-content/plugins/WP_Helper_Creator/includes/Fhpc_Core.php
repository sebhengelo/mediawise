<?php

if (!defined('ABSPATH'))
    exit;

class Fhpc_Core {

    /**
     * The single instance
     * @var 	object
     * @access  private
     * @since 	1.0.0
     */
    private static $_instance = null;

    /**
     * Settings class object
     * @var     object
     * @access  public
     * @since   1.0.0
     */
    public $settings = null;

    /**
     * The version number.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $_version;

    /**
     * The token.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $_token;

    /**
     * The main plugin file.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $file;

    /**
     * The main plugin directory.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $dir;

    /**
     * The plugin assets directory.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $assets_dir;

    /**
     * The plugin assets URL.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $assets_url;

    /**
     * Suffix for Javascripts.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $templates_url;

    /**
     * Suffix for Javascripts.
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $script_suffix;

    /**
     * For menu instance
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $menu;

    /**
     * For template
     * @var     string
     * @access  public
     * @since   1.0.0
     */
    public $plugin_slug;

    /**
     * Constructor function.
     * @access  public
     * @since   1.0.0
     * @return  void
     */
    public function __construct($file = '', $version = '1.2.0') {
        $this->_version = $version;
        $this->_token = 'Fhpc';

        $this->file = $file;
        $this->dir = dirname($this->file);
        $this->assets_dir = trailingslashit($this->dir) . 'assets';
        $this->assets_url = esc_url(trailingslashit(plugins_url('/assets/', $this->file)));
        $this->templates_url = esc_url(trailingslashit(plugins_url('/templates/', $this->file)));

        $this->templates = array('helper_template.php' => __('Helper template', $this->plugin_slug));

        add_action('wp_enqueue_scripts', array($this, 'frontend_enqueue_scripts'), 10, 1);
        add_action('wp_enqueue_scripts', array($this, 'frontend_enqueue_styles'), 10, 1);
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'), 10, 1);
        add_action('admin_enqueue_scripts', array($this, 'frontend_enqueue_styles'), 10, 1);

        if (isset($_GET['page']) && $_GET['page'] == 'helper-template') {
            add_filter('template_include', array($this, 'load_template'));
        }
        $templates = wp_get_theme()->get_page_templates();
        $templates = array_merge($templates, $this->templates);
        add_action('wp_head', array($this, 'options_custom_styles'));

        $this->init_helpers();
    }

    /**
     * Load frontend CSS.
     * @access  public
     * @since   1.0.0
     * @return void
     */
    public function frontend_enqueue_styles($hook = '') {
        wp_register_style($this->_token . '-frontend', esc_url($this->assets_url) . 'css/frontend.css', array(), $this->_version);
        wp_enqueue_style($this->_token . '-frontend');
        wp_register_style($this->_token . '-colors', esc_url($this->assets_url) . 'css/colors.css', array(), $this->_version);
        wp_enqueue_style($this->_token . '-colors');
    }

    /**
     * Load admin Javascript.
     * @access  public
     * @since   1.0.0
     * @return void
     */
    public function admin_enqueue_scripts($hook = '') {
        global $wpdb;
        wp_register_script($this->_token . '-frontend', esc_url($this->assets_url) . 'js/frontend.min.js', 'jquery', $this->_version);
        wp_enqueue_script($this->_token . '-frontend');

        $helpers = array();
        $table_name = $wpdb->prefix . "fhpc_steps";
        $helpers = $wpdb->get_results('SELECT * FROM ' . $table_name . ' ORDER BY ordersort');
        foreach ($helpers as $helper) {
            $table_name = $wpdb->prefix . "fhpc_items";
            $helper->items = $wpdb->get_results('SELECT * FROM ' . $table_name . ' WHERE stepID=' . $helper->id . ' ORDER BY ordersort');            
        }
        wp_localize_script($this->_token . '-frontend', 'helpers', $helpers);
        wp_localize_script($this->_token . '-frontend', 'siteurl', site_url() . '/');
    }

    /*
    * Styles integration
    */
    public function options_custom_styles()
    {
        $output = '';
        $settings = $this->getSettings();

        $output .= '.fhpc_tooltip,.fhpc_button,.fhpc_button:hover  {';
        $output .= ' background-color:' . $settings->colorA . '; ';
        $output .= '}';
        $output .= "\n";
        $output .= '.fhpc_text h2  {';
        $output .= ' color:' . $settings->colorB . ' !important; ';
        $output .= '}';
        $output .= "\n";
        $output .= '.fhpc_dialog h3{';
        $output .= '    color:' . $settings->colorB . ' !important; ';
        $output .= '}';
        $output .= "\n";
        $output .= '.fhpc_tooltip[data-position="bottom"] .fhpc_arrow {';
        $output .= ' border-color: transparent transparent ' . $settings->colorA . ' transparent !important; ';
        $output .= '}';
        $output .= "\n";
        $output .= '.fhpc_tooltip[data-position="top"] .fhpc_arrow {';
        $output .= ' border-color: ' . $settings->colorA . ' transparent transparent transparent !important;   ';
        $output .= '}';
        $output .= "\n";
        $output .= '.fhpc_text{';
        $output .= ' color:' . $settings->colorC . ' !important; ';
        $output .= '}';
        $output .= "\n";
        $output .= '.fhpc_closeHelperBtn{';
        $output .= ' color:' . $settings->colorA . ' !important; ';
        $output .= '}';
        $output .= "\n";


        if ($output != '') {
            $output = "\n<style id=\"fhpc_styles\" >\n" . $output . "</style>\n";
            echo $output;
        }
    }

    /**
     * Return settings.
     * @access  public
     * @since   1.0.0
     * @return  void
     */
    public function getSettings() {
        global $wpdb;
        $table_name = $wpdb->prefix . "fhpc_settings";
        $settings = $wpdb->get_results("SELECT * FROM $table_name WHERE id=1 LIMIT 1");
        if (count($settings) > 0) {
            return $settings[0];
        } else {
            return false;
        }
    }

    /**
     * Load frontend Javascript.
     * @access  public
     * @since   1.0.0
     * @return void
     */
    public function frontend_enqueue_scripts($hook = '') {
        global $wpdb;

        wp_register_script($this->_token . '-jquery-ui', esc_url($this->assets_url) . 'js/jquery-ui-1.9.2.custom.min.js', array('jquery'), $this->_version);
        wp_enqueue_script($this->_token . '-jquery-ui');

        wp_register_script($this->_token . '-frontend', esc_url($this->assets_url) . 'js/frontend.min.js', array($this->_token . '-jquery-ui'), $this->_version);
        wp_enqueue_script($this->_token . '-frontend');

        $helpers = array();
        $table_name = $wpdb->prefix . "fhpc_steps";
        $helpers = $wpdb->get_results('SELECT * FROM ' . $table_name . ' ORDER BY ordersort');
        foreach ($helpers as $helper) {
            $table_name = $wpdb->prefix . "fhpc_items";
            $helper->items = $wpdb->get_results('SELECT * FROM ' . $table_name . ' WHERE stepID=' . $helper->id . ' ORDER BY ordersort');
        }
        wp_localize_script($this->_token . '-frontend', 'helpers', $helpers);
        $settings = $this->getSettings();
        if ($settings->useHttps) {
            wp_localize_script($this->_token . '-frontend', 'siteurl', site_url('', 'https') . '/');
        } else {
            wp_localize_script($this->_token . '-frontend', 'siteurl', site_url() . '/');
        }
    }

    /**
     * Load popup template.
     * @access  public
     * @since   1.0.0
     * @return void
     */
    public function load_template($template) {
        $file = plugin_dir_path(__FILE__) . '../templates/helper_template.php';
        if (file_exists($file)) {
            return $file;
        }
    }

    /**
     * Initialise helpers
     * @access  private
     * @since   1.0.0
     * @return void
     */
    private function init_helpers() {
        
    }

    /**
     * Main WPE_Tools Instance
     *
     *
     * @since 1.0.0
     * @static
     * @see WPE_Tools()
     * @return Main WPE_Tools instance
     */
    public static function instance($file = '', $version = '1.0.0') {
        if (is_null(self::$_instance)) {
            self::$_instance = new self($file, $version);
        }
        return self::$_instance;
    }

// End instance()

    /**
     * Cloning is forbidden.
     *
     * @since 1.0.0
     */
    public function __clone() {
        _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?'), $this->_version);
    }

// End __clone()

    /**
     * Unserializing instances of this class is forbidden.
     *
     * @since 1.0.0
     */
    public function __wakeup() {
        _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?'), $this->_version);
    }

// End __wakeup()

    /**
     * Log the plugin version number.
     * @access  public
     * @since   1.0.0
     * @return  void
     */
    private function _log_version_number() {
        update_option($this->_token . '_version', $this->_version);
    }

}
