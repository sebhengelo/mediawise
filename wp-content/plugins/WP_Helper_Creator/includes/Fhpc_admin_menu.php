<?php
if (!defined('ABSPATH'))
    exit;

class Fhpc_admin_menu {

    /**
     * The single instance 
     * @var 	object
     * @access  private
     * @since 	1.0.0
     */
    private static $_instance = null;

    /**
     * The main plugin object.
     * @var 	object
     * @access  public
     * @since 	1.0.0
     */
    public $parent = null;

    /**
     * Prefix for plugin settings.
     * @var     string
     * @access  publicexport
     * 
     * @since   1.0.0
     */
    public $base = '';

    /**
     * Available settings for plugin.
     * @var     array
     * @access  public
     * @since   1.0.0
     */
    public $settings = array();

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

    public function __construct($parent) {
        $this->_token = 'Fhpc';
        $this->parent = $parent;
        $this->dir = dirname($parent->file);
        $this->assets_dir = trailingslashit($this->dir) . 'assets';
        $this->assets_url = esc_url(trailingslashit(plugins_url('/assets/', $parent->file)));
        $this->templates_url = esc_url(trailingslashit(plugins_url('/templates/', $parent->file)));
        if (isset($_GET['page']) && strrpos($_GET['page'], 'fhpc') !== false) {
            //$this->form_checkUpdates();
           // $this->checkUpdates();
        }
        add_action('admin_menu', array($this, 'add_menu_item'));
        add_action('wp_ajax_nopriv_fhpc_item_save', array($this, 'item_save'));
        add_action('wp_ajax_fhpc_item_save', array($this, 'item_save'));
        add_action('wp_ajax_nopriv_fhpc_step_save', array($this, 'step_save'));
        add_action('wp_ajax_fhpc_step_save', array($this, 'step_save'));
        add_action('wp_ajax_nopriv_fhpc_settings_save', array($this, 'settings_save'));
        add_action('wp_ajax_fhpc_settings_save', array($this, 'settings_save'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'), 10, 1);
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_styles'), 10, 1);
        if (isset($_GET['activateWebsite'])) {
            $this->activateLicense();
        }
    }

    /**
     * Load admin CSS.
     * @access  public
     * @since   1.0.0
     * @return void
     */
    public function admin_enqueue_styles($hook = '') {

        wp_register_style($this->_token . '-colpick', esc_url($this->assets_url) . 'css/colpick.css', array(), $this->_version);
        wp_enqueue_style($this->_token . '-colpick');
        wp_register_style($this->_token . '-admin', esc_url($this->assets_url) . 'css/admin.css', array(), $this->_version);
        wp_enqueue_style($this->_token . '-admin');
    }

// End admin_enqueue_styles()

    /**
     * Load admin Javascript.
     * @access  public
     * @since   1.0.0
     * @return void
     */
    public function admin_enqueue_scripts($hook = '') {

        if (isset($_GET['page']) && strrpos($_GET['page'], 'fhpc') !== false) {
            wp_register_script($this->_token . '-colpick', esc_url($this->assets_url) . 'js/colpick.js', array('jquery'), $this->_version);
            wp_enqueue_script($this->_token . '-colpick');
            wp_register_script($this->_token . '-admin', esc_url($this->assets_url) . 'js/admin.js', array('jquery'), $this->_version);
            wp_enqueue_script($this->_token . '-admin');

            $settings = $this->getSettings();
            if ($settings->useHttps) {
                wp_localize_script($this->_token . '-admin', 'homeurl', home_url('', 'https') . '/');
                wp_localize_script($this->_token . '-admin', 'adminurl', admin_url('', 'https') . '/');
            } else {
                wp_localize_script($this->_token . '-admin', 'homeurl', home_url() . '/');
                wp_localize_script($this->_token . '-admin', 'adminurl', admin_url() . '/');
            }
        }
    }

    /*
     * Check plugin state
     */

    public function checkUpdates() {
        if (strpos(get_admin_url(), '?page=fhpc_menu') === false && strpos(get_admin_url(), '?page=fhpc') !== false && !$this->isUpdated()) {
            wp_redirect(admin_url('admin.php?page=fhpc_menu'));
        }
        if (!isset($_COOKIE['pll_updateH']) || $_COOKIE['pll_updateH'] == '0') {
            $this->form_checkUpdates();
        }
    }

    /**
     * Add settings link to plugin list table
     * @param  array $links Existing links
     * @return array 		Modified links
     */
    public function add_settings_link($links) {
        $settings_link = '<a href="admin.php?page=fhpc_menu">' . __('Settings', 'fhpc') . '</a>';
        array_push($links, $settings_link);
        return $links;
    }

    /*
     * Return license verification message
     */

    private function licenseMessage() {
        if (!$this->isUpdated()) {
            if (isset($_COOKIE['pll_updateH']) && ($_COOKIE['pll_updateH'] == '2')) {
                echo '<div id="message" class="error"><h3>Purchase Code already used</h3><p>This purchase code is already used on another website. Would you use this license for this site (the plugin will be disabled on the other site)?</p>'
                . '<p><a href="admin.php?page=fhpc_menu&activateWebsite=1" class="button-primary">YES</a>&nbsp;<a href="admin.php?page=fhpc_menu" class="button-secondary" style="margin-left: 10px;">NO, I have another license</a></p></div>';
            } else {
                echo '<div id="message" class="error"><h3>Purchase Code verification needed</h3><p>Please go to the <a href="admin.php?page=fhpc_menu">settings panel</a>, then enter your License <a href="' . $this->parent->assets_url . 'img/purchase_code_1200.png" target="_blank">Purchase Code</a> .</p></div>';
            }
        }
    }

    /**
     * Add menu to admin
     * @return void
     */
    public function add_menu_item() {
        add_menu_page('Helper Creator', 'Helper Creator', 'manage_options', 'fhpc_menu', array($this, 'submenu_settings'), 'dashicons-lightbulb');
        $menuSlag = 'fhpc_menu';
        if (!$this->isUpdated()) {
            $menuSlag = null;
        }
        add_submenu_page($menuSlag, 'Helpers', 'Helpers', 'manage_options', 'fhpc-steps', array($this, 'submenu_steps'));
        add_submenu_page($menuSlag, 'Edit Helper', 'Edit Helper', 'manage_options', 'fhpc-step-add', array($this, 'submenu_step_add'));
        add_submenu_page($menuSlag, 'Steps', 'Steps', 'manage_options', 'fhpc-items', array($this, 'submenu_items'));
        add_submenu_page($menuSlag, 'Edit Step', 'Edit Step', 'manage_options', 'fhpc-item-add', array($this, 'submenu_item_add'));
        add_submenu_page($menuSlag, 'Import', 'Import', 'manage_options', 'fhpc-import', array($this, 'submenu_import'));
        add_submenu_page($menuSlag, 'Export', 'Export', 'manage_options', 'fhpc-export', array($this, 'submenu_export'));
    }

    /**
     * Menu export render
     * @return void
     */
    function submenu_export() {
        global $wpdb;

        if (!is_dir(plugin_dir_path(__FILE__) . '../tmp')) {
            mkdir(plugin_dir_path(__FILE__) . '../tmp');
            chmod(plugin_dir_path(__FILE__) . '../tmp', 0747);
        }

        $destination = plugin_dir_path(__FILE__) . '../tmp/export_helper_creator.zip';
        $zip = new ZipArchive();
        if (file_exists($destination)) {
            unlink($destination);
        }
        if ($zip->open($destination, ZipArchive::CREATE) !== true) {
            return false;
        }

        $jsonExport = array();
        $table_name = $wpdb->prefix . "fhpc_settings";
        $settings = $wpdb->get_results("SELECT * FROM $table_name WHERE id=1 LIMIT 1");
        $settings->purchaseCode = '';
        $settings->updated = 0;
        $jsonExport['settings'] = $settings;

        $table_name = $wpdb->prefix . "fhpc_steps";
        $steps = array();
        foreach ($wpdb->get_results("SELECT * FROM $table_name") as $key => $row) {
            $steps[] = $row;
        }

        $jsonExport['steps'] = $steps;
        $table_name = $wpdb->prefix . "fhpc_items";
        $items = array();
        foreach ($wpdb->get_results("SELECT * FROM $table_name") as $key => $row) {
            $items[] = $row;
        }

        $jsonExport['items'] = $items;
        $fp = fopen(plugin_dir_path(__FILE__) . '../tmp/export_helper_creator.json', 'w');
        fwrite($fp, json_encode($jsonExport));
        fclose($fp);

        $zip->addfile(plugin_dir_path(__FILE__) . '../tmp/export_helper_creator.json', 'export_helper_creator.json');
        $zip->close();
        ?>
        <div class="wrap wpeExport">
            <h2>Export data</h2>
            <p>
                Export all this plugin datas to a zip file will can be imported on another website.
            </p>
            <p>
                <a download class="button-primary" href="<?php echo esc_url(trailingslashit(plugins_url('/', $this->parent->file))) . 'tmp/export_helper_creator.zip'; ?>">Export</a>
            </p>
        </div>
        <?php
    }

    /**
     * Menu import render
     * @return void
     */
    function submenu_import() {
        global $wpdb;
        ?>
        <div class="wrap wpeImport">
            <h2>Import data</h2>
            <?php
            $displayForm = true;
            $settings = $this->getSettings();
//            $pageID = $settings->form_page_id;
            if (isset($_GET['import']) && isset($_FILES['importFile'])) {
                $error = false;
                if (!is_dir(plugin_dir_path(__FILE__) . '../tmp')) {
                    mkdir(plugin_dir_path(__FILE__) . '../tmp');
                    chmod(plugin_dir_path(__FILE__) . '../tmp', 0747);
                }
                $target_path = plugin_dir_path(__FILE__) . '../tmp/export_helper_creator.zip';
                if (@move_uploaded_file($_FILES['importFile']['tmp_name'], $target_path)) {


                    $upload_dir = wp_upload_dir();
                    if (!is_dir($upload_dir['path'])) {
                        mkdir($upload_dir['path']);
                    }

                    $zip = new ZipArchive;
                    $res = $zip->open($target_path);
                    if ($res === TRUE) {
                        $zip->extractTo(plugin_dir_path(__FILE__) . '../tmp/');
                        $zip->close();

                        $jsonfilename = 'export_helper_creator.json';
                        if (!file_exists(plugin_dir_path(__FILE__) . '../tmp/export_helper_creator.json')) {
                            $jsonfilename = 'export_helper_creator';
                        }

                        $file = file_get_contents(plugin_dir_path(__FILE__) . '../tmp/' . $jsonfilename);
                        $dataJson = json_decode($file, true);

                        $table_name = $wpdb->prefix . "fhpc_settings";
                        $wpdb->query("TRUNCATE TABLE $table_name");
                        $wpdb->insert($table_name, $dataJson['settings'][0]);

                        $table_name = $wpdb->prefix . "fhpc_steps";
                        $wpdb->query("TRUNCATE TABLE $table_name");
                        foreach ($dataJson['steps'] as $key => $value) {
                            foreach ($value as $keyV => $valueV) {
                                if ($keyV == 'page') {
                                    if (strrpos($valueV, site_url()) === false) {
                                        
                                    } else {
                                        $valueV = substr($valueV, strlen(site_url()) + 1);
                                        $value[$keyV] = $valueV;
                                    }
                                }
                            }
                            $wpdb->insert($table_name, $value);
                        }

                        $table_name = $wpdb->prefix . "fhpc_items";
                        $wpdb->query("TRUNCATE TABLE $table_name");
                        foreach ($dataJson['items'] as $key => $value) {
                            foreach ($value as $keyV => $valueV) {
                                if ($keyV == 'page') {
                                    if (strrpos($valueV, site_url()) === false) {
                                        
                                    } else {
                                        $valueV = substr($valueV, strlen(site_url()) + 1);
                                        $value[$keyV] = $valueV;
                                    }
                                }
                            }
                            $wpdb->insert($table_name, $value);
                        }
                        $files = glob(plugin_dir_path(__FILE__) . '../tmp/*');
                        foreach ($files as $file) {
                            if (is_file($file))
                                unlink($file);
                        }

                        //$this->updateCSS();
                    } else {
                        $error = true;
                    }
                } else {
                    $error = true;
                }
                if ($error) {
                    echo '<div class="error">An error occurred during the transfer</div>';
                } else {
                    $displayForm = false;
                    echo '<div class="updated">Data has been imported.</div>';
                }
            }
            if ($displayForm) {
                ?>
                <p>
                    Import here the zip file created using the "Export" tool.
                </p>
                <div class="error" style="color: red;">
                    WARNING: import data will overwrite existing ones!
                </div>
                <form action="admin.php?page=fhpc-import&import=1" method="post" enctype="multipart/form-data">
                    <p>
                        <input id="importFile" type="file" name="importFile" placeholder="Select the .zip file"/>
                        <label for="importFile"> <span class="description">Select the generated .zip file</span> </label>
                    </p>
                    <p>
                        <button type="submit" class="button-primary">
                            Import
                        </button>
                    </p>
                </form>
                <?php
            }
            ?>
        </div>
        <?php
    }

    /*
     * Activate license
     */

    private function activateLicense() {
        global $wpdb;
        $settings = $this->getSettings();
        $url = 'http://ks3000387.kimsufi.com/~pluginsu/update.php?confirmUpdate=7981938&version=' . $settings->purchaseCode . '&ip=' . $_SERVER['SERVER_ADDR'] . '&url=' . get_site_url();
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/38.0.2125.66 Safari/537.36");
        $rep = curl_exec($ch);
        if (!$rep || $rep == '1') {
            $wpdb->update($wpdb->prefix . "fhpc_settings", array('updated' => 0), array('id' => 1));
            setcookie('pll_updateH', 1, time() + 60 * 60 * 24 * 1);
        } else if ($rep == 'needupdate') {
            $wpdb->update($wpdb->prefix . "fhpc_settings", array('updated' => 1), array('id' => 1));
            setcookie('pll_updateH', 2, time() + 60 * 60 * 24 * 1);
        } else {
            $wpdb->update($wpdb->prefix . "fhpc_settings", array('updated' => 1), array('id' => 1));
            setcookie('pll_updateH', 1, time() + 60 * 60 * 24 * 1);
        }
    }

    /**
     * Get specific Item datas
     * @return object
     */
    private function getItemDatas($item_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . "fhpc_items";
        $rows = $wpdb->get_results("SELECT * FROM $table_name WHERE id=$item_id LIMIT 1");
        return $rows[0];
    }

    /**
     * Get specific Step datas
     * @return object
     */
    private function getStepDatas($step_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . "fhpc_steps";
        $rows = $wpdb->get_results("SELECT * FROM $table_name WHERE id=$step_id LIMIT 1");
        return $rows[0];
    }

    /**
     * Get Steps datas
     * @return Array
     */
    private function getStepsData() {
        global $wpdb;
        $table_name = $wpdb->prefix . "fhpc_steps";
        $rows = $wpdb->get_results("SELECT * FROM $table_name");

        $data = array();
        foreach ($rows as $row) {
            $data[] = array('id' => $row->id, 'title' => $row->title, 'order' => $row->ordersort, 'page' => $row->page, 'onAdmin' => $row->onAdmin);
        }
        return $data;
    }

    public function submenu_settings() {
        global $wpdb;
        $table_name = $wpdb->prefix . "fhpc_settings";
        $settings = $wpdb->get_results("SELECT * FROM $table_name WHERE id=1 LIMIT 1");
        $settings = $settings[0];
        ?>
        <div class="wrap wpeSettings">
            <div class="wrap">
                <h2>Settings</h2>
                <div id="fhpc_response"></div>
                <?php
                $this->licenseMessage();
                ?>
                <form id="form_settings" method="post" action="#" onsubmit="qc_process(this);
                                return false;">
                    <input id="id" type="hidden" name="id" value="1">
                    <table class="form-table">
                        <tbody>
                            <tr>
                                <th scope="row">Purchase License Code</th>
                                <td>
                                    <input id="purchaseCode" type="text" name="purchaseCode"  placeholder="Enter the purchase license" value="<?php echo $settings->purchaseCode; ?>">
                                    <label for="purchaseCode">
                                        <span class="description"><a href="<?php echo $this->parent->assets_url; ?>img/purchase_code_1200.png" target="_blank">How to find my purchase code ? </a></span>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Main Color</th>
                                <td>
                                    <input id="colorA" class="colorpick" type="text" name="colorA" placeholder="Choose a color" value="<?php
                                    echo $settings->colorA;
                                    ?>">
                                    <label for="colorA"> <span class="description">This is the main color</span> </label></td>
                            </tr>    
                            <tr>
                                <th scope="row">Color of the dialog title</th>
                                <td>
                                    <input id="colorB" class="colorpick" type="text" name="colorB" placeholder="Choose a color" value="<?php
                                    echo $settings->colorB;
                                    ?>">
                                    <label for="colorB"> <span class="description">This is the dialog title color</span> </label></td>
                            </tr>       
                            <tr>
                                <th scope="row">Color of the "text" steps</th>
                                <td>
                                    <input id="colorC" class="colorpick" type="text" name="colorC" placeholder="Choose a color" value="<?php
                                    echo $settings->colorC;
                                    ?>">
                                    <label for="colorC"> <span class="description">This is the "text" steps color</span> </label></td>
                            </tr>    
                            <tr>
                                <th scope="row">Use theme fonts ?</th>
                                <td>
                                    <select id="useThemeFonts" name="useThemeFonts">
                                        <option value="0">No</option>
                                        <option value="1" <?php
                                        if ($settings->useThemeFonts) {
                                            echo 'selected';
                                        }
                                        ?>>Yes</option>
                                    </select>
                                </td>
                            </tr>  
                            <tr>
                                <th scope="row">Use HTTPS ?</th>
                                <td>
                                    <select id="useHttps" name="useHttps">
                                        <option value="0">No</option>
                                        <option value="1" <?php
                                        if ($settings->useHttps) {
                                            echo 'selected';
                                        }
                                        ?>>Yes</option>
                                    </select>
                                </td>
                            </tr>                               

                            <tr>
                                <th scope="row"></th>
                                <td>
                                    <input type="submit" value="Save" class="button-primary"/>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <script>
                        function qc_process(e) {

                            var error = false;
                            jQuery('#colorA').removeClass('field-error');
                            if (jQuery("#colorA").val().length != 7) {
                                error = true;
                                jQuery('#colorA').addClass('field-error');
                            }
                            jQuery('#colorB').removeClass('field-error');
                            if (jQuery("#colorB").val().length != 7) {
                                error = true;
                                jQuery('#colorB').addClass('field-error');
                            }
                            if (!error) {
                                jQuery("#fhpc_response").hide();
                                var data = {action: "fhpc_settings_save"};
                                jQuery('#form_settings input, #form_settings select, #form_settings textarea').each(function() {
                                    if (jQuery(this).attr('name')) {
                                        eval('data.' + jQuery(this).attr('name') + ' = jQuery(this).val();');
                                    }
                                });
                                jQuery.post(ajaxurl, data, function(response) {
                                    jQuery("#fhpc_response").html('<div id="message" class="updated"><p>Settings <strong>saved</strong>.</p></div>');
                                    // jQuery("#wpefc_response").html(response);
                                    setTimeout(function() {
                                        document.location.href = 'admin.php?page=fhpc_menu';
                                    }, 250);
                                });
                            }
                        }
                    </script>
                </form>
            </div>
        </div>
        <?php
    }

    /**
     * save settings
     * @return void
     */
    public function settings_save() {
        global $wpdb;
        $response = "Error, try again later.";
        $table_name = $wpdb->prefix . "fhpc_settings";
        $sqlDatas = array();
        foreach ($_POST as $key => $value) {
            if ($key != 'action' && $key != 'pll_ajax_backend') {
                $sqlDatas[$key] = stripslashes($value);
            }
        }
        $wpdb->update($table_name, $sqlDatas, array('id' => 1));
        $response = '<div id="message" class="updated"><p>Step <strong>saved</strong>.</p></div>';
        setcookie('pll_updateH', 0);
        $this->form_checkUpdates();
        echo $response;
       // $this->updateCSS();
        die();
    }

    /**
     * remove specific item
     * @return void
     */
    private function remove_item($item_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . "fhpc_items";
        $wpdb->delete($table_name, array('id' => $item_id));
    }

    /**
     * Menu steps items render
     * @return void
     */
    public function submenu_items() {

        if ($this->isUpdated()) {
            if (isset($_GET['remove'])) {
                $this->remove_item($_GET['remove']);
            }
            $helperID = 0;
            if (isset($_GET['helper'])) {
                $helperID = $_GET['helper'];
            }
            $itemsTable = new Fhpc_Items_List_Table();
            $itemsTable->helperID = $helperID;
            $itemsTable->prepare_items();
            ?>
            <div class="wrap">
                <div id="icon-users" class="icon32"></div>
                <h2>Steps list <a href="admin.php?page=fhpc-item-add" class="add-new-h2">Add New</a></h2>

                <?php $itemsTable->display(); ?>
            </div>

            <?php
        } else {
            $this->licenseMessage();
        }
    }

    /**
     * Menu add item render
     * @return void
     */
    public function submenu_item_add() {

        if ($this->isUpdated()) {
            $stepsData = $this->getStepsData();
            $datas = false;
            if (isset($_GET['item'])) {
                $datas = $this->getItemDatas($_GET['item']);
                $helper = $this->getStepDatas($datas->stepID);
            }
            ?>
            <div class="wrap">
                <h2>Edit a step</h2>
                <div id="fhpc_response"></div>
                <form id="form_item" method="post" action="#" onsubmit="qc_process(this);
                                    return false;">
                    <input id="id" type="hidden" name="id" value="<?php
                    if ($datas) {
                        echo $datas->id;
                    } else {
                        echo '0';
                    }
                    ?>"/>
                    <table class="form-table">
                        <tbody>
                            <tr>
                                <th scope="row">Helper</th>
                                <td>
                                    <select id="stepID" name="stepID" placeholder="Select step">
                                        <?php
                                        foreach ($stepsData as $step) {
                                            $sel = '';
                                            if ($datas && $step['id'] == $datas->stepID) {
                                                $sel = 'selected';
                                            }
                                            echo '<option value="' . $step['id'] . '" ' . $sel . ' data-page="' . $step['page'] . '" data-admin="' . $step['onAdmin'] . '">' . $step['title'] . '</value>';
                                        }
                                        ?>
                                    </select>
                                    <label for="stepID">
                                        <span class="description">Choose an helper</span>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Order</th>
                                <td>
                                    <input id="ordersort" type="number" name="ordersort" placeholder="Item order" value="<?php
                                    if ($datas) {
                                        echo $datas->ordersort;
                                    } else {
                                        echo '0';
                                    }
                                    ?>">
                                    <label for="ordersort">
                                        <span class="description">Steps take place according to the order defined</span>
                                    </label>
                                </td>
                            </tr>      
                            <tr>
                                <th scope="row">Title</th>
                                <td>
                                    <input id="title" type="text" name="title" placeholder="Step title" value="<?php
                                    if ($datas) {
                                        echo $datas->title;
                                    }
                                    ?>">
                                    <label for="title"> <span class="description">This is the step title</span> </label></td>
                            </tr>                           
                            <tr>
                                <th scope="row">Type</th>
                                <td>
                                    <select id="type" name="type" placeholder="Select type">
                                        <?php
                                        $sel1 = '';
                                        $sel2 = '';
                                        $sel3 = '';
                                        if ($datas && $datas->type == 'dialog') {
                                            $sel2 = 'selected';
                                        } else if ($datas && $datas->type == 'text') {
                                            $sel3 = 'selected';
                                        } else {
                                            $sel1 = 'selected';
                                        }
                                        echo '<option value="tooltip" ' . $sel1 . '>Tooltip</value>';
                                        echo '<option value="dialog" ' . $sel2 . '>Dialog</value>';
                                        echo '<option value="text" ' . $sel3 . '>Text</value>';
                                        ?>
                                    </select>
                                    <label for="type">
                                        <span class="description">Tooltip, Text or Dialog ?</span>
                                    </label>
                                </td>
                            </tr>                           
                            <tr class="only_tooltip">
                                <th scope="row">Target DOM element</th>
                                <td>
                                    <span>
                                        <?php
                                        if ($datas && $datas->domElement != "") {
                                            echo 'Element selected';
                                        } else {
                                            echo 'No selection';
                                        }
                                        ?>
                                    </span>
                                    <a href="javascript:" onclick="fhpc_chooseItemTarget();" class="button-primary">Selection</a>
                                    <input type="hidden" id="domElement" name="domElement" value="<?php
                                    if ($datas) {
                                        echo $datas->domElement;
                                    }
                                    ?>" />
                                    <label> <span class="description">Select a dom element</span> </label></td>
                            </tr>
                            <tr >
                                <th scope="row">Page</th>
                                <td>

                                    <input id="page" type="text" name="page" placeholder="Page" value="<?php
                                    if ($datas) {
                                        echo $datas->page;
                                    }
                                    ?>">
                                    <label for="page"> <span class="description">Select the page (facultative)</span> </label>
                                </td>
                            </tr>


                            <tr style="display: none;" >
                                <th scope="row">On website or admin ?</th>
                                <td>
                                    <select id="onAdmin" name="onAdmin">
                                        <?php
                                        echo '<option value="0" ' . $sel1 . '>Frontend</value>';
                                        echo '<option value="1" ' . $sel2 . '>Admin</value>';
                                        ?>
                                    </select>
                                    <label for="onAdmin"> <span class="description">For frontend or admin ?</span> </label></td>
                            </tr>   
                            <tr class="only_tooltip">
                                <th scope="row">Position</th>
                                <td>
                                    <select id="position" name="position" >
                                        <?php
                                        $sel1 = '';
                                        $sel2 = '';
                                        if ($datas && $datas->position == 'top') {
                                            $sel2 = 'selected';
                                        } else {
                                            $sel1 = 'selected';
                                        }
                                        echo '<option value="bottom" ' . $sel1 . '>Bottom</value>';
                                        echo '<option value="top" ' . $sel2 . '>Top</value>';
                                        ?>
                                    </select>
                                    <label for="position">
                                        <span class="description">Choose the tooltip position</span>
                                    </label>
                                </td>
                            </tr>
                            <tr class="only_tooltip">
                                <th scope="row">Content</th>
                                <td>

                                    <textarea id="content_tooltip" type="content_tooltip" name="content_tooltip" style="height: 100px;" placeholder="Content"><?php
                                        if ($datas) {
                                            echo $datas->content;
                                        }
                                        ?></textarea>
                                    <label for="content_tooltip"> <span class="description">This is the step content</span> </label>
                                </td>
                            </tr>
                            <tr class="only_dialog">
                                <th scope="row">Content</th>
                                <td>

                                    <?php
                                    $content = "";
                                    if ($datas) {
                                        $content = $datas->content;
                                    }
                                    wp_editor($content, 'content', array(
                                        'tinymce' => array(
                                            'height' => 80
                                        ))
                                    );
                                    ?>
                                    <label for="content"> <span class="description">This is the step content</span> </label></td>
                            </tr>
                            <tr>
                                <th scope="row">Overlay</th>
                                <td>
                                    <select id="overlay" name="overlay">
                                        <?php
                                        $sel1 = '';
                                        $sel2 = '';
                                        if ($datas && !$datas->overlay) {
                                            $sel1 = 'selected';
                                        } else {
                                            $sel2 = 'selected';
                                        }
                                        echo '<option value="0" ' . $sel1 . '>No</value>';
                                        echo '<option value="1" ' . $sel2 . '>Yes</value>';
                                        ?>
                                    </select>
                                    <label for="overlay">
                                        <span class="description">Use overlay mask ?</span>
                                    </label>
                                </td>
                            </tr>   
                            <tr>
                                <th scope="row">Add a button to close the helper ?</th>
                                <td>
                                    <select id="closeHelperBtn" name="closeHelperBtn">
                                        <?php
                                        $sel1 = '';
                                        $sel2 = '';
                                        if ($datas && !$datas->closeHelperBtn) {
                                            $sel1 = 'selected';
                                        } else {
                                            $sel2 = 'selected';
                                        }
                                        echo '<option value="0" ' . $sel1 . '>No</value>';
                                        echo '<option value="1" ' . $sel2 . '>Yes</value>';
                                        ?>
                                    </select>
                                    <label for="closeHelperBtn">
                                        <span class="description">Add a close button to stop the helper</span>
                                    </label>
                                </td>
                            </tr>                    



                            <tr class="only_tooltip">
                                <th scope="row">Action to continue</th>
                                <td>
                                    <select id="actionNeeded" name="actionNeeded" placeholder="Select an action">
                                        <?php
                                        $sel1 = '';
                                        $sel2 = '';
                                        $sel3 = '';
                                        $sel4 = '';
                                        if ($datas && $datas->actionNeeded == 'click') {
                                            $sel1 = 'selected';
                                        } else {
                                            $sel2 = 'selected';
                                        }
                                        echo '<option value="click" ' . $sel1 . '>Click</value>';
                                        echo '<option value="delay" ' . $sel2 . '>Duration</value>';
                                        ?>
                                    </select>
                                    <label for="actionNeeded">
                                        <span class="description">Select an action</span>
                                    </label>
                                </td>
                            </tr>

                            <tr class="only_dialog">
                                <th scope="row">Button "Continue" text</th>
                                <td>
                                    <input id="btnContinue" type="text" name="btnContinue" placeholder="Continue button label" value="<?php
                                    if ($datas) {
                                        echo $datas->btnContinue;
                                    } else {
                                        echo 'Continue';
                                    }
                                    ?>">
                                    <label for="btnContinue">
                                        <span class="description">Let this field empty if you don't want this button</span>
                                    </label>
                                </td>
                            </tr>
                            <tr class="only_dialog">
                                <th scope="row">Button "Stop" text</th>
                                <td>
                                    <input id="btnStop" type="text" name="btnStop" placeholder="Stop button label" value="<?php
                                    if ($datas) {
                                        echo $datas->btnStop;
                                    }
                                    ?>">
                                    <label for="btnStop">
                                        <span class="description">Let this field empty if you don't want this button</span>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Duration</th>
                                <td>
                                    <input id="delay" type="number" name="delay" step="0.1" placeholder="Duration" value="<?php
                                    if ($datas) {
                                        echo $datas->delay;
                                    } else {
                                        echo '5';
                                    }
                                    ?>">
                                    <label for="delay">
                                        <span class="description">Defines a delay in seconds</span>
                                    </label>
                                </td>
                            </tr>
                            <tr class="only_tooltip">
                                <th scope="row">Delay before showing tooltip</th>
                                <td>
                                    <input id="delayStart" type="number" step="0.1" name="delayStart" placeholder="Delay" value="<?php
                                    if ($datas) {
                                        echo $datas->delayStart;
                                    } else {
                                        echo '0';
                                    }
                                    ?>">
                                    <label for="delayStart">
                                        <span class="description">Useful if the item does not appear immediately (effect of appearance...)</span>
                                    </label>
                                </td>
                            </tr>


                            <tr>
                                <th scope="row"></th>
                                <td>
                                    <input type="submit" value="Save" class="button-primary"/>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </form>
                <?php //echo site_url();        ?>
            </div>
            <script>
                function checkStep() {
                    if (jQuery('#page').val() == "") {
                        var page = jQuery('#stepID :selected').data('page');
                        jQuery('#page').val(page);
                    }
                    jQuery('#onAdmin').val(jQuery('#stepID :selected').data('admin'));
                }
                function checkType() {
                    var type = jQuery('#type').val();
                    if (type == 'tooltip') {
                        jQuery('.only_dialog').hide();
                        jQuery('.only_tooltip').show();
                    } else if (type == 'dialog') {
                        jQuery('#delayStart').val(0);
                        jQuery('.only_dialog').show();
                        jQuery('.only_tooltip').hide();
                        checkBtns();
                    } else {
                        jQuery('#delayStart').val(0);
                        jQuery('.only_dialog').hide();
                        jQuery('.only_tooltip').hide();
                        jQuery('#content_tooltip').parent().parent().show();
                        jQuery('#overlay').parent().parent().hide();
                        jQuery('#overlay').val('1');
                        jQuery('#delay').parent().parent().show();
                        jQuery('#actionNeeded').parent().parent().hide();
                        jQuery('#actionNeeded').val('delay');
                    }
                }
                function checkBtns() {
                    if ((jQuery('#type').val() == 'dialog') && (jQuery('#btnContinue').val().length > 0 || jQuery('#btnStop').val().length > 0)) {
                        jQuery('#actionNeeded').val('click');
                        jQuery('#actionNeeded').parent().parent().hide();
                        jQuery('#delay').parent().parent().hide();
                    } else if (jQuery('#type').val() == 'dialog') {
                        jQuery('#actionNeeded').val('delay');
                        jQuery('#delay').parent().parent().show();
                    }
                }
                function checkAction() {
                    if (jQuery('#actionNeeded').val() == 'click' && jQuery('#type').val() != "text") {
                        jQuery('#delay').parent().parent().hide();
                    } else {
                        jQuery('#delay').parent().parent().show();
                    }
                }
                function checkOverlay() {
                    if (jQuery('#overlay').val() == '1') {
                        jQuery('#closeHelperBtn').parent().parent().show();
                    } else {
                        jQuery('#closeHelperBtn').parent().parent().hide();
                    }
                }
                function qc_process(e) {

                    var error = false;

                    jQuery('#domElement').prev().prev('span').css({
                        color: '#000'
                    });
                    jQuery('#title').removeClass('field-error');
                    if (jQuery("#title").val().length < 3) {
                        error = true;
                        jQuery('#title').addClass('field-error');
                    }
                    if (jQuery('#type').val() == 'tooltip' && jQuery('#domElement').val().length < 2) {
                        error = true;
                        jQuery('#domElement').prev().prev('span').css({
                            color: 'red'
                        });
                    }

                    if (!error) {
                        jQuery("#fhpc_response").hide();
                        var data = {action: "fhpc_item_save"};
                        jQuery('#form_item input, #form_item select, #form_item textarea').each(function() {
                            if (jQuery(this).attr('name')) {
                                if (jQuery(this).attr('name') != "content_tooltip" && jQuery(this).attr('name') != 'onAdmin') {
                                    eval('data.' + jQuery(this).attr('name') + ' = jQuery(this).val();');
                                }
                            }
                        });
                        var editor = tinyMCE.get('content');
                        if (editor) {
                            data.content = editor.getContent();
                        } else {
                            data.content = jQuery('#content').val();
                        }
                        if (jQuery('#type').val() != 'dialog') {
                            data.content = jQuery('#content_tooltip').val();
                        }

                        jQuery.post(ajaxurl, data, function(response) {
                            jQuery("#fhpc_response").html('<div id="message" class="updated"><p>Step <strong>saved</strong>.</p></div>');
                            jQuery("#fhpc_response").fadeIn(250);
                            jQuery('#id').val(response);
                            document.location.href = '#wpwrap';
                        });
                    }
                }
                jQuery(document).ready(function() {
                    jQuery('#type').change(checkType);
                    checkType();
                    jQuery('#actionNeeded').change(checkAction);
                    checkAction();
                    jQuery('#overlay').change(checkOverlay);
                    checkOverlay();
                    jQuery('#stepID').change(checkStep);
                    checkStep();
                    jQuery('#btnContinue').keyup(checkBtns);
                    jQuery('#btnStop').keyup(checkBtns);

                });
            </script>
            <?php
        }
    }

    /**
     * save item
     * @return void
     */
    public function item_save() {
        global $wpdb;
        $response = "Error, try again later.";
        $table_name = $wpdb->prefix . "fhpc_items";
        $sqlDatas = array();
        foreach ($_POST as $key => $value) {
            if ($key != 'action' && $key != 'id' && $key != 'pll_ajax_backend') {
                if ($key == 'page') {
                    if (strrpos($value, site_url()) === false) {
                        
                    } else {
                        $value = substr($value, strlen(site_url()) + 1);
                    }
                    if (substr($value, -2, 2) == '//') {
                        $value = substr($value, 0, -1);
                    }
                }
                $sqlDatas[$key] = stripslashes($value);
            }
        }
        if ($_POST['id'] > 0) {
            $wpdb->update($table_name, $sqlDatas, array('id' => $_POST['id']));
            $response = $_POST['id'];
        } else {
            $rows_affected = $wpdb->insert($table_name, $sqlDatas);
            $lastid = $wpdb->insert_id;
            $response = $lastid;
        }


        echo $response;
        die();
    }

    /**
     * Menu steps render
     * @return void
     */
    public function submenu_steps() {

        if ($this->isUpdated()) {
            if (isset($_GET['remove'])) {
                $this->remove_step($_GET['remove']);
            }

            $stepTable = new Fhpc_Steps_List_Table();
            $stepTable->prepare_items();
            ?>
            <div class="wrap">
                <div id="icon-users" class="icon32"></div>
                <h2> Helpers list <a href="admin.php?page=fhpc-step-add" class="add-new-h2">Add New</a></h2>

                <?php $stepTable->display(); ?>
            </div>
            <?php
        } else {
            $this->licenseMessage();
        }
    }

    /**
     * Remove a step
     * @return void
     */
    private function remove_step($step_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . "fhpc_items";
        $wpdb->delete($table_name, array('stepID' => $step_id));

        $table_name = $wpdb->prefix . "fhpc_steps";
        $wpdb->delete($table_name, array('id' => $step_id));
    }

    /*
     * check updates 
     */

    private function form_checkUpdates() {
        global $wpdb;
        if (!isset($_COOKIE['pll_updateH']) || $_COOKIE['pll_updateH'] == '0') {
            $rep = "";
            $settings = $this->getSettings();
            if ($settings) {
                $current = $settings->updated;
                if ($settings->purchaseCode == "") {
                    $wpdb->update($wpdb->prefix . "fhpc_settings", array('updated' => 1), array('id' => 1));
                    setcookie('pll_updateH', 1, time() + 60 * 60 * 24 * 1);
                } else {
                    $url = 'http://ks3000387.kimsufi.com/~pluginsu/update.php?checkUpdates=7981938&version=' . $settings->purchaseCode . '&ip=' . $_SERVER['SERVER_ADDR'] . '&url=' . get_site_url();
                    $ch = curl_init($url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/38.0.2125.66 Safari/537.36");
                    $rep = curl_exec($ch);
                    if ($rep == 'needupdate') {
                        $wpdb->update($wpdb->prefix . "fhpc_settings", array('updated' => 1), array('id' => 1));
                        setcookie('pll_updateH', 2, time() + 60 * 60 * 24 * 1);
                    } else if ($rep == 'updated') {
                        $wpdb->update($wpdb->prefix . "fhpc_settings", array('updated' => 1), array('id' => 1));
                        setcookie('pll_updateH', 1, time() + 60 * 60 * 24 * 1);
                    } else {
                        $wpdb->update($wpdb->prefix . "fhpc_settings", array('updated' => 0), array('id' => 1));
                        setcookie('pll_updateH', 1, time() + 60 * 60 * 24 * 1);
                    }
                }
            }
        }
    }

    private function isUpdated() {
       /* $settings = $this->getSettings();
        if ($settings->updated) {
            return false;
        } else {*/
            return true;
       // }
    }

    /**
     * Menu add step render
     * @return void
     */
    function submenu_step_add() {
        if ($this->isUpdated()) {
            $datas = false;
            if (isset($_GET['step'])) {
                $datas = $this->getStepDatas($_GET['step']);
            }
            ?>
            <div class="wrap">
                <h2>Edit an helper</h2>
                <div id="fhpc_response"></div>
                <form id="form_step" method="post" action="#" onsubmit="qc_process(this);
                                    return false;">
                    <input id="id" type="hidden" name="id" value="<?php
                    if ($datas) {
                        echo $datas->id;
                    } else {
                        echo '0';
                    }
                    ?>">
                    <table class="form-table">
                        <tbody>
                            <tr>
                                <th scope="row">Title</th>
                                <td>
                                    <input id="title" type="text" name="title" placeholder="Helper name" value="<?php
                                    if ($datas) {
                                        echo $datas->title;
                                    }
                                    ?>">
                                    <label for="title"> <span class="description">This is the helper name</span> </label></td>
                            </tr>    
                            <tr>
                                <th scope="row">Start method</th>
                                <td>
                                    <select id="start" name="start" placeholder="Select start method">
                                        <?php
                                        $sel1 = '';
                                        $sel2 = '';
                                        if ($datas && $datas->start == 'click') {
                                            $sel2 = 'selected';
                                        } else {
                                            $sel1 = 'selected';
                                        }
                                        echo '<option value="auto" ' . $sel1 . '>Start automaticly</value>';
                                        echo '<option value="click" ' . $sel2 . '>On click on an element</value>';
                                        ?>
                                    </select>
                                    <label for="start"> <span class="description">How helper starts ?</span> </label></td>
                            </tr>   

                            <tr>
                                <th scope="row">Run once ?</th>
                                <td>
                                    <select id="onceTime" name="onceTime" placeholder="Run only once ?">
                                        <?php
                                        $sel1 = '';
                                        $sel2 = '';
                                        if ($datas && $datas->onceTime) {
                                            $sel2 = 'selected';
                                        } else {
                                            $sel1 = 'selected';
                                        }
                                        echo '<option value="0" ' . $sel1 . '>No</value>';
                                        echo '<option value="1" ' . $sel2 . '>Yes</value>';
                                        ?>
                                    </select>
                                    <label for="onceTime"> <span class="description">Run once the helper ?</span> </label></td>
                            </tr>   


                            <tr>
                                <th scope="row">On website or admin ?</th>
                                <td>
                                    <select id="onAdmin" name="onAdmin">
                                        <?php
                                        $sel1 = '';
                                        $sel2 = '';
                                        if ($datas && $datas->onAdmin) {
                                            $sel2 = 'selected';
                                        } else {
                                            $sel1 = 'selected';
                                        }
                                        echo '<option value="0" ' . $sel1 . '>Frontend</value>';
                                        echo '<option value="1" ' . $sel2 . '>Admin</value>';
                                        ?>
                                    </select>
                                    <label for="onAdmin"> <span class="description">For frontend or admin ?</span> </label></td>
                            </tr>   

                            <tr>
                                <th scope="row">Target DOM element</th>
                                <td>
                                    <span>
                                        <?php
                                        if ($datas && $datas->domElement != "") {
                                            echo 'Element selected';
                                        }
                                        ?>
                                    </span>
                                    <a href="javascript:" onclick="fhpc_chooseItemTarget();" class="button-primary">Selection</a>
                                    <input type="hidden" id="domElement" name="domElement" value="<?php
                                    if ($datas) {
                                        echo $datas->domElement;
                                    }
                                    ?>" />
                                    <label> <span class="description">Select a dom element</span> </label></td>
                            </tr>

                            <tr>
                                <th scope="row">Page url</th>
                                <td>
                                    <input id="page" type="text" name="page" placeholder="http://" value="<?php
                                    if ($datas) {
                                        echo $datas->page;
                                    }
                                    ?>">
                                    <label for="page"> <span class="description">Leave empty to apply helper to all pages</span> </label></td>
                            </tr>  
                            
                            <tr>
                                <th scope="row">Activate on mobile ?</th>
                                <td>
                                    <select id="mobileEnabled" name="mobileEnabled">
                                        <?php
                                        $sel1 = '';
                                        $sel2 = '';
                                        if ($datas && !$datas->mobileEnabled) {
                                            $sel2 = 'selected';
                                        } else {
                                            $sel1 = 'selected';
                                        }
                                        echo '<option value="1" ' . $sel1 . '>Yes</value>';
                                        echo '<option value="0" ' . $sel2 . '>No</value>';
                                        ?>
                                    </select>
                                    <label for="onAdmin"> <span class="description">Is this assistant enabled on mobile ?</span> </label></td>
                            </tr>   

                            <tr>
                                <th scope="row"></th>
                                <td>
                                    <input type="submit" value="Save" class="button-primary"/>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </form>
            </div>
            <script>
                function changeOnAdmin() {
                    if (jQuery('#onAdmin').val() == '1') {
                        jQuery('#form_step #page').val(document.location.href.substr(0, document.location.href.lastIndexOf('/')));
                    } else {
                        jQuery('#form_step #page').val('');
                    }
                }
                function checkStart() {
                    if (jQuery('#start').val() == 'click') {
                        jQuery('#domElement').parent().parent().show();
                        jQuery('#onceTime').parent().parent().hide();
                    } else {
                        jQuery('#domElement').parent().parent().hide();
                        jQuery('#onceTime').parent().parent().show();
                    }
                }
                function qc_process(e) {
                    var error = false;
                    jQuery('#title').removeClass('field-error');
                    if (jQuery("#title").val().length < 3) {
                        error = true;
                        jQuery('#title').addClass('field-error');
                    }
                    if (!error) {
                        jQuery("#fhpc_response").hide();
                        var data = {action: "fhpc_step_save"};
                        jQuery('#form_step input, #form_step select').each(function() {
                            if (jQuery(this).attr('name')) {
                                eval('data.' + jQuery(this).attr('name') + ' = jQuery(this).val();');
                            }
                        });
                        
                        if(jQuery('#id').val() > 0){
                           eval('localStorage.removeItem('+jQuery('#id').val()+');');                            
                        }

                        jQuery.post(ajaxurl, data, function(response) {
                            jQuery("#fhpc_response").html('<div id="message" class="updated"><p>Helper <strong>saved</strong>.</p></div>');
                            jQuery("#fhpc_response").fadeIn(250);
                            jQuery('#id').val(response);
                            document.location.href = '#wpwrap';
                        });

                    }
                }
                jQuery(document).ready(function() {
                    jQuery('#onAdmin').change(changeOnAdmin);
                    jQuery('#start').change(checkStart);
                    checkStart();
                });

            </script>
            <?php
        }
    }

    /**
     * save step
     * @return void
     */
    function step_save() {
        global $wpdb;
        $response = "Error, try again later.";
        $table_name = $wpdb->prefix . "fhpc_steps";
        $sqlDatas = array();
        foreach ($_POST as $key => $value) {
            if ($key != 'action' && $key != 'id' && $key != 'pll_ajax_backend') {
                if ($key == 'page') {
                    if (strrpos($value, site_url()) === false) {
                        
                    } else {
                        if (strlen($value)>0 &&  ($value == site_url()|| $value == site_url().'/' || $value =='/')){
                            $value = '/';
                        } else {
                            $value = substr($value, strlen(site_url()) + 1);                            
                        }
                    }
                    if (substr($value, -2, 2) == '//') {
                        $value = substr($value, 0, -1);
                    }
                    $wpdb->query("UPDATE " . $wpdb->prefix . "fhpc_items SET page='$value' WHERE stepID=" . $_POST['id'] . " AND type!='tooltip' ");
                }
                $sqlDatas[$key] = stripslashes($value);
            }
        }
        if ($_POST['id'] > 0) {
            $wpdb->update($table_name, $sqlDatas, array('id' => $_POST['id']));
            $response = $_POST['id'];
        } else {
            $rows_affected = $wpdb->insert($table_name, $sqlDatas);
            $lastid = $wpdb->insert_id;
            $response = $lastid;
        }
        echo $response;
        die();
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
     * update CSS
     * @return void
     */
    private function updateCSS() {
        $settings = $this->getSettings();
        $colorsStyles = '
        .fhpc_tooltip,.fhpc_button,.fhpc_button:hover  {    
            background-color: ' . $settings->colorA . ';
        }
        #fhpc_closeHelperBtn, .fhpc_text h2 {
            color: ' . $settings->colorA . ' !important;
        }
        .fhpc_dialog h3 {
            color: ' . $settings->colorB . ' !important;
        }
        .fhpc_tooltip[data-position="bottom"] .fhpc_arrow{
            border-color: transparent transparent ' . $settings->colorA . ' transparent !important;            
        }
        .fhpc_tooltip[data-position="top"] .fhpc_arrow{
            border-color: ' . $settings->colorA . ' transparent transparent transparent !important;            
        }
        .fhpc_text {
            color: ' . $settings->colorC . ' !important;
        }';
        if (!$settings->useThemeFonts) {
            $colorsStyles.= '.fhpc_text,.fhpc_text h2,.fhpc_dialog h3 ,.fhpc_tooltip .fhpc_content {font-family: \'Lato\';}';
        }

        $fp = fopen(plugin_dir_path(__FILE__) . '../assets/css/colors.css', 'w');
        fwrite($fp, $colorsStyles);
        fclose($fp);
    }

    /**
     * Main Instance
     *
     *
     * @since 1.0.0
     * @static
     * @return Main instance
     */
    public static function instance($parent) {
        if (is_null(self::$_instance)) {
            self::$_instance = new self($parent);
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
        _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?'), $this->parent->_version);
    }

// End __clone()

    /**
     * Unserializing instances of this class is forbidden.
     *
     * @since 1.0.0
     */
    public function __wakeup() {
        _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?'), $this->parent->_version);
    }

// End __wakeup()
}
