<?php
require_once(dirname(__FILE__).'/dzs_functions.php');

class DZSProgressBars {

    public $theurl = '';
    public $pluginmode = "plugin";
    public $adminpagename = 'dzsprg_builder';
    public $adminpagename_mainoptions = 'dzsprg_builder_mainoptions';
    public $currSkin = 'custom';
    public $currSkin_nr = 0;
    private $noticed = false;
    private $notices = array();
    private $dbname_skins = 'dzsprg_skins';
    private $dbname_mainoptions = 'dzsprg_mo';
    private $db_skins = array();
    private $db_skin_data = array();
    private $db_mainoptions = array();
    public $capability_admin = 'manage_options';
    private $builder_frontend_errors = array();
    public $db_skins_default = '';

    function __construct() {

        if ($this->pluginmode == 'theme') {
            $this->theurl = THEME_URL.'plugins/dzs-progressbars/';
        } else {
            $this->theurl = plugins_url('',__FILE__).'/';
        }


        if (isset($_GET['skin']) == false || $_GET['skin'] == '') {
            $this->currSkin = 'custom';
        } else {
            $this->currSkin = $_GET['skin'];
        }



        $defaultOpts = array(
            'extra_css' => '',
            'always_embed' => 'off',
            'dashboard_content' => '',
        );
        $this->db_mainoptions = get_option($this->dbname_mainoptions);

        //==== default opts / inject into db
        if ($this->db_mainoptions == '') {
            $this->db_mainoptions = $defaultOpts;
            update_option($this->dbname_mainoptions,$this->db_mainoptions);
        }

        $this->db_mainoptions = array_merge($defaultOpts,$this->db_mainoptions);



        $db_skins_aux = get_option($this->dbname_skins);

        if ($db_skins_aux == '') {

            $this->db_skins_default =file_get_contents(dirname(__FILE__).'/sampledata/sampledata.txt');
            if($this->db_skins_default){
                $this->db_skins = unserialize($this->db_skins_default);
            }

        } else {
            $this->db_skins = ($db_skins_aux);

//            print_r($db_skins_aux);
        }

        if (isset($this->db_skins[$this->currSkin])) {
            $this->db_skin_data = $this->db_skins[$this->currSkin];

            $ik=0;
            foreach($this->db_skins as $lab=>$val){
                if($lab===$this->currSkin){
                    $this->currSkin_nr = $ik;
                    break;

                }

                $ik++;
            }

//            echo $this->currSkin_nr;

        }
        
        

//        print_r($_GET);
        if (isset($_GET['generatorpage']) && $_GET['generatorpage'] == 'dzsprg_generatepage') {
            $this->show_generator();
        }

        if (isset($_POST['dzsprg_duplicate']) && $_POST['dzsprg_duplicate'] == 'Duplicate Skin') {
            $this->ajax_duplicate_skin();
        }
        if (isset($_POST['action']) && $_POST['action'] == 'dzsprg_saveskin') {
            $this->ajax_dzsprg_saveskin();
        }

        add_action('wp_ajax_dzsprg_ajax_save_mo',array($this,'ajax_save_mo'));



        add_action('admin_menu',array($this,'handle_admin_menu'));
        add_action('admin_head',array($this,'handle_admin_head'));
        add_action('admin_notices',array($this,'handle_admin_notices'));


        add_shortcode('dzsprogressbar',array($this,'shortcode_dzsprogressbar'));
        add_action('init',array($this,'handle_init'));
        
        if($this->db_mainoptions['dashboard_content']!=''){
            add_action('wp_dashboard_setup', array($this, 'wp_dashboard_setup'));
        }
        
    }

    function ajax_duplicate_skin(){

        $lab_dup = $this->currSkin . '_copy';


        foreach ($this->db_skins as $lab => $val) {
            if ($lab === $lab_dup) {
                return false;

            }


        }



        $aux = $this->db_skins[$this->currSkin];

        $this->db_skins[$lab_dup] = $aux;


        update_option($this->dbname_skins,$this->db_skins);
//        print_r($this->db_skins);
    }



    function ajax_save_mo() {



        $auxarray = array();
        //parsing post data
        parse_str($_POST['postdata'],$auxarray);
        update_option($this->dbname_mainoptions,$auxarray);
        echo 'success - saved';
        die();
    }

    function shortcode_dzsprogressbar($pargs,$content = null) {
        $fout = '';
        $margs = array(
            'arg1_perc' => '80',
            'arg2_maxnr' => '80',
            'skin' => 'custom',
        );

        if ($pargs == '') {
            $pargs = array();
        }

        $margs = array_merge($margs,$pargs);
        
        $margs['arg1_perc'] = do_shortcode($margs['arg1_perc']);
        $margs['arg2_maxnr'] = do_shortcode($margs['arg2_maxnr']);


//        $content = wpb_js_remove_wpautop($content,true);

        $this->enqueue_main_scripts();
        //


        foreach ($this->db_skins as $lab => $skin) {
            if ($lab == $margs['skin']) {

                $fout.='<div class="dzs-progress-bar auto-init" style="';

                $arr_labels = array('width','height','margin_top','margin_left','margin_bottom','margin_right');

                foreach ($arr_labels as $lab2) {
//                    print_r($skin); print_r($skin['bars']['mainsettings']); print_r($skin['bars']['mainsettings'][$lab2]);
                    if (isset($skin['bars']['mainsettings']) && isset($skin['bars']['mainsettings'][$lab2])) {
//                        $fout.=''.$lab2.':'.$skin['bars']['mainsettings'][$lab2].';';
                        $fout.=''.str_replace('_','-',$lab2).':'.$skin['bars']['mainsettings'][$lab2].';';
                    }
                }

                $fout.='"';

                $firstset = false;

                $fout.=' data-animprops=\'{';

                $arr_labels = array('animation_time','maxperc','maxnr','initon');

                foreach ($arr_labels as $lab2) {

                    if ($firstset) {
                        $fout.=',';
                    }
                    if (isset($skin['bars']['mainsettings']) && isset($skin['bars']['mainsettings'][$lab2])) {
                        $fout.='"'.$lab2.'":"'.$skin['bars']['mainsettings'][$lab2].'"';
                        $firstset = true;
                    }
                }
                $fout.='}\'';



                $fout.='>';

                foreach ($skin['bars'] as $lab3 => $bar) {
                    if ($lab3 === 'mainsettings') {
                        continue;
                    }

//                    print_r($bar);

                    if ($bar['type'] == 'circ') {
                        $fout.='<canvas';
                    } else {
                        $fout.='<div';
                    }

                    $fout.=' class="progress-bars-item progress-bars-item--'.$bar['type'].' '.$bar['extra_classes'].'" data-type="'.$bar['type'].'"';

                    $fout.=' style="';
                    $all_labs = array('position_type','background_color','width','height','top','left','bottom','right','margin_top','margin_left','margin_bottom','margin_right','border_radius','border','opacity','font_size','color','extra_classes');

                    foreach ($all_labs as $lab4) {
                        $auxlab = $lab4;
                        $val = $bar[$lab4];


                        if ($auxlab == 'position_type') {
                            $auxlab = 'position';
                        }

                        if (($auxlab == 'margin_top' || $auxlab == 'margin_right' || $auxlab == 'margin_bottom' || $auxlab == 'margin_left' || $auxlab == 'border_radius' || $auxlab == 'font_size' || $auxlab == 'background_color')) {

                            $auxlab = str_replace('_','-',$auxlab);
                        }

                        if (($auxlab == 'top' || $auxlab == 'right' || $auxlab == 'bottom' || $auxlab == 'left' || $auxlab == 'width' || $auxlab == 'height' || $auxlab == 'margin-top' || $auxlab == 'font-size') && strpos($val,'%') === false && strpos($val,'px') === false && $val !== 'auto') {
                            $val.='px';
                        }


                        if (strpos($val,'{{') === false && $val != '') {

                            $fout.=$auxlab.':'.$val.';';
                        }
                    }




                    $fout.='"';



                    $firstset = false;
                    $all_labs = array('width','height','top','left','bottom','right','margin_top','margin_left','margin_bottom','margin_right','border_radius','border','opacity','font_size','color','extra_classes');
                    $circle_labs = array('circle_outside_fill','circle_inside_fill','circle_outer_width','circle_line_width');

                    $all_labs = array_merge($all_labs,$circle_labs);

//                    print_r($all_labs);


                    $fout.=' data-animprops=\'{';
                    foreach ($all_labs as $lab4) {
                        $auxlab = $lab4;
                        $val = $bar[$lab4];



                        if (($auxlab == 'margin_top' || $auxlab == 'margin_right' || $auxlab == 'margin_bottom' || $auxlab == 'margin_left' || $auxlab == 'border_radius' || $auxlab == 'font_size')) {

                            $auxlab = str_replace('_','-',$auxlab);
                        }


                        if ((strpos($val,'{{') !== false || in_array($auxlab,$circle_labs)) && $val != '') {
                            if ($firstset) {
                                $fout.=',';
                            }
                            $fout.='"'.$auxlab.'":"'.$val.'"';
                            $firstset = true;
                        }
                    }
                    $fout.='}\'';

                    $fout.='>';

                    if ($bar['type'] == 'text') {
                        $fout.=$bar['text'];
                    }

                    if ($bar['type'] == 'circ') {
                        $fout.='</canvas>';
                    } else {
                        $fout.='</div>';
                    }
                }

                $fout.='</div>';

                break;
//                print_r($skin);
            }
        }
        $pat1 = '/{{arg1-.*?}}/i';
        $fout = preg_replace($pat1,$margs['arg1_perc'],$fout);


        $pat2 = '/{{arg2-.*?}}/i';
        $fout = preg_replace($pat2,$margs['arg2_maxnr'],$fout);


        $pat3 = '/{{arg3-.*?}}/i';
        if ($content) {
            $fout = preg_replace($pat3,$content,$fout);
        }
        return $fout;
//        return "<div style='color:{$color};' data-foo='${foo}'>{$content}</div>";
    }
    
    function wp_dashboard_setup(){
        
        wp_add_dashboard_widget(
                'dzsprg_dashboard_widget_comments', // Widget slug.
                'Zoom Progress Bars', // Title.
                array($this, 'dashboard_comments_display') // Display function.
        );
    }
    
    
    function dashboard_comments_display() {

        echo do_shortcode($this->db_mainoptions['dashboard_content']);
    }

    function enqueue_main_scripts() {

        wp_enqueue_script('dzs.progressbars',$this->theurl.'dzsprogressbars/dzsprogressbars.js');
        wp_enqueue_style('dzs.progressbars',$this->theurl.'dzsprogressbars/dzsprogressbars.css');
        wp_enqueue_style('fontawesome',$this->theurl.'fontawesome/font-awesome.min.css');
    }

    function handle_init() {
        $arr_skins = array();
        foreach ($this->db_skins as $lab => $skin) {
            $arr_skins[$lab] = $lab;
        }



        wp_enqueue_script('jquery');
        
        
        if(is_admin()){
            
            if (isset($_GET['page']) && $_GET['page'] == $this->adminpagename_mainoptions) {
                wp_enqueue_script('dzsprg_admin',$this->theurl."admin/admin_mo.js");
                wp_enqueue_style('iphone.checkbox',$this->theurl.'admin/checkbox/checkbox.css');
                wp_enqueue_script('iphone.checkbox',$this->theurl."admin/checkbox/checkbox.dev.js");
            }
            
            wp_enqueue_style('dzs.zoombox', $this->theurl . 'zoombox/zoombox.css');
            wp_enqueue_script('dzs.zoombox', $this->theurl . 'zoombox/zoombox.js');
            wp_enqueue_script('dzsprg_htmleditor_plugin', $this->theurl . 'tinymce/plugin-htmleditor.js');
            wp_enqueue_script('dzstaa_configreceiver', $this->theurl . 'tinymce/receiver.js');
        }





        if(is_admin()){
            if (isset($_GET['page']) && $_GET['page'] == $this->adminpagename_mainoptions) {
                wp_enqueue_script('dzsprg_admin',$this->theurl."admin/admin_mo.js");
                wp_enqueue_style('iphone.checkbox',$this->theurl.'admin/checkbox/checkbox.css');
                wp_enqueue_script('iphone.checkbox',$this->theurl."admin/checkbox/checkbox.dev.js");
            }
        }

        $arr_skins = array();
        foreach ($this->db_skins as $lab => $skin) {
            $arr_skins[$lab] = $lab;
        }

//        print_r($arr_skins);
        if(function_exists('vc_map')){
            vc_map(array(
                "name" => __("Zoom Progress Bar"),
                "base" => "dzsprogressbar",
                "class" => "",
                "front_enqueue_js" => $this->theurl.'js/frontend_backbone.js',
                "category" => __('Content'),
                "params" => array(
                    array(
                        'type' => 'dropdown',
                        'heading' => __('Skin'),
                        'param_name' => 'skin',
                        'value' => $arr_skins,
                        'description' => __('Select a skin from the one\'s you built in the generator.')
                    ),
                    array(
                        "type" => "textfield",
                        "holder" => "div",
                        "class" => "",
                        "heading" => __("Percent"),
                        "param_name" => "arg1_perc",
                        "value" => __("100"),
                        "description" => __("The percent on which the progress bar goes. A value from 0 to 100.")
                    ),
                    array(
                        "type" => "textfield",
                        "holder" => "div",
                        "class" => "",
                        "heading" => __("Percent Number"),
                        "param_name" => "arg2_maxnr",
                        "value" => __("400"),
                        "description" => __("A number that can be increased based on percent. Can be any number.")
                    ),
                    array(
                        "type" => "textarea_html",
                        "holder" => "div",
                        "class" => "",
                        "heading" => __("Text"),
                        "param_name" => "content",
                        "value" => __("<p>I am test text block. Click edit button to change this text.</p>"),
                        "description" => __("Enter your text for skins that receive a text parameter.")
                    )
                )
            ));
        }
    }

    function ajax_dzsprg_saveskin() {
        parse_str($_POST['postdata'],$auxarray);

        $this->currSkin = $_POST['currSkin'];

//        print_r($auxarray);



        $this->db_skins[$this->currSkin] = $auxarray;

        update_option($this->dbname_skins,$this->db_skins);

        echo 'success - skin saved';

        die();
    }

    function handle_admin_menu() {

        global $current_user;


        $admin_cap = $this->capability_admin;


        $dzsvg_page = add_menu_page(__('DZS Progress Bars','dzsvg'),__('Progress Bars','dzsvg'),$admin_cap,$this->adminpagename,array($this,'admin_page_builder'),'div');
        $dzsvg_subpage = add_submenu_page($this->adminpagename,__('Builder','dzsvg'),__('Progress Bars Builder','dzsvg'),$this->capability_admin,$this->adminpagename,array($this,'admin_page_builder'));
        $dzsvg_subpage = add_submenu_page($this->adminpagename,__('Settings','dzsvg'),__('Progress Bars Settings','dzsvg'),$this->capability_admin,$this->adminpagename_mainoptions,array($this,'admin_page_mainoptions'));
    }

    function handle_admin_head() {

        wp_enqueue_style('dzsprg.admin.global',$this->theurl.'style/admin-global.css');


        $struct_item_default = str_replace(array("\r","\r\n","\n"),'',$this->generate_layer_item());
        $struct_item_text = str_replace(array("\r","\r\n","\n"),'',$this->generate_layer_item(array('type' => 'text','background_color' => 'transparent','text' => 'insert text here','height' => 'auto')));
        $struct_item_circ = str_replace(array("\r","\r\n","\n"),'',$this->generate_layer_item(array('type' => 'circ','background_color' => 'transparent','height' => '{{width}}')));


        echo '<script>window.dzsprg_builder_settings = { struct_layer: \''.$struct_item_default.'\''
                . ', struct_layer_text: \''.$struct_item_text.'\''
                . ', struct_layer_circ:\''.$struct_item_circ.'\''
                . ',currSkin : "'.$this->currSkin.'"'
                . ',theurl:"'.$this->theurl.'"'
                . ',adminpageurl:"'.admin_url('admin.php?page='.$this->adminpagename).'"'
                . ',wpurl : "'.site_url().'" '
                . ',adminurl : "'.admin_url().'" '
                . '};';

        echo ' </script>';
    }

    function admin_page_mainoptions() {
        //print_r($this->mainoptions);
        if (isset($_POST['dzsprg_delete_plugin_data']) && $_POST['dzsprg_delete_plugin_data'] == 'on') {
            delete_option($this->dbname_skins);
            delete_option($this->dbname_options);
        }
        //print_r($this->mainoptions);
        ?>

        <div class="wrap">
            <h2><?php echo __('Progress Bars Main Settings','dzsprg'); ?></h2>
            <br/>
            <form class="mainsettings">

                <h3>Admin Options</h3>


                <div class="setting">
                    <div class="label"><?php echo __('Always Embed Scripts?','dzsprg'); ?></div>
        <?php echo DZSHelpers::generate_input_checkbox('always_embed',array('val' => 'on','seekval' => $this->db_mainoptions['always_embed'])); ?>
                    <div class="sidenote"><?php echo __('by default scripts and styles from this gallery are included only when needed for optimizations reasons, but you can choose to always use them ( useful for when you are using a ajax theme that does not reload the whole page on url change )','dzsprg'); ?></div>
                </div>
                <div class="setting">
                    <div class="label"><?php echo __('Extra CSS','dzsprg'); ?></div>
        <?php echo DZSHelpers::generate_input_textarea('extra_css',array('val' => '','seekval' => $this->db_mainoptions['extra_css'])); ?>
                </div>
                <div class="setting">
                    <div class="label"><?php echo __('Dashboard Content','dzsprg'); ?></div>
        <?php $lab = 'dashboard_content'; echo DZSHelpers::generate_input_textarea($lab,array('val' => '','seekval' => $this->db_mainoptions[$lab])); ?>
                    <div class="sidenote"><?php echo __('you can have a dashboard widget with this content','dzsprg'); ?></div>
                </div>
                
                

                <br/>
                <a href='#' class="button-primary save-btn save-mainoptions"><?php echo __('Save Options','dzsprg'); ?></a>
            </form>
            <br/><br/>
            <form class="mainsettings" method="POST">
                <button name="dzsprg_delete_plugin_data" value="on" class="button-secondary"><?php echo __('Delete Plugin Data','dzsprg'); ?></button>
            </form>
            <div class="saveconfirmer" style=""><img alt="" style="" id="save-ajax-loading2" src="<?php echo site_url(); ?>/wp-admin/images/wpspin_light.gif"/></div>
            <script>
                jQuery(document).ready(function($) {
                    page_mainoptions_ready();
                    $('input:checkbox').checkbox();
                })
            </script>
        </div>
        <div class="clear"></div><br/>
        <?php
    }

    function admin_page_builder() {
        wp_enqueue_script('tinymce.js',$this->theurl.'tinymce/tinymce/tinymce.min.js');
        wp_enqueue_script('tinymce.jquery',$this->theurl.'tinymce/tinymce/jquery.tinymce.min.js');
        wp_enqueue_script('dzs.progressbars',$this->theurl.'dzsprogressbars/dzsprogressbars.js');
        wp_enqueue_style('dzs.progressbars',$this->theurl.'dzsprogressbars/dzsprogressbars.css');
        wp_enqueue_style('fontawesome',$this->theurl.'fontawesome/font-awesome.min.css');
        wp_enqueue_script('farbtastic.colorpicker',$this->theurl.'colorpicker/farbtastic.js');
        wp_enqueue_style('farbtastic.colorpicker',$this->theurl.'colorpicker/farbtastic.css');
        wp_enqueue_style('dzsprg.builder',$this->theurl.'style/builder.css');
        wp_enqueue_script('dzsprg.builder',$this->theurl.'js/builder.js');
        wp_enqueue_script('dzs.scroller',$this->theurl.'dzsscroller/scroller.js');
        wp_enqueue_style('dzs.scroller',$this->theurl.'dzsscroller/scroller.css');
        wp_enqueue_script('dzs.tabsandaccordions',$this->theurl.'dzstabsandaccordions/dzstabsandaccordions.js');
        wp_enqueue_style('dzs.tabsandaccordions',$this->theurl.'dzstabsandaccordions/dzstabsandaccordions.css');
        wp_enqueue_script('jquery.ui',$this->theurl.'jqueryui/jquery-ui.min.js');
        wp_enqueue_style('jquery.ui',$this->theurl.'jqueryui/jquery-ui.min.css');
        ?>
        <div class="wrap wrap-dzsprg-builder">
            <h2>DZS <?php echo __('Progress Bars Builder'); ?></h2>
            <section class="mcon-maindemo" style="position: relative; padding-top:0px; padding-bottom:50px;">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <br/>
                            <br/>
        <?php
        foreach ($this->builder_frontend_errors as $err) {
            echo $err;
        }
        ?>
                        </div>
                        <div class="col-md-6">
                            <h3 style="margin-top: 0; padding-top: 0;">Customize <strong>skin-<?php echo $this->currSkin; ?></strong> - <span class='btn-preview'>preview</span></h3>
                                <form method="post"><input type="hidden" name="dzsprg_skin_name" value="<?php echo $this->currSkin; ?>"/><input type="submit" class="button-secondary" name="dzsprg_duplicate" value="Duplicate Skin"/></form>
                        </div>
                        <div class="col-md-6">
                            <div class="super-select float-right">
                                <span class="btn-show-select"><span class='arrow-symbol'>↳</span> Current Skin <strong>skin-<?php echo $this->currSkin; ?></strong> </span>
                                <div class="super-select--inner">
                                    <div class='scroller-con super-select--options'>
                                        <div class="inner">
                                            <div class='skin-option button-secondary'><a href="<?php echo admin_url('admin.php?page='.$this->adminpagename); ?>">skin-custom</a></div><?php
                            foreach ($this->db_skins as $lab => $skin) {
                                if ($lab == 'custom') {
                                    continue;
                                }
                                echo '<div class="skin-option button-secondary"><a href="'.admin_url('admin.php?page='.$this->adminpagename).'&skin='.$lab.'">skin-'.$lab.'</a></div>';
                            }
                            ?><div class='skin-option button-secondary'>skin-<form id='create-custom-skin' method="POST" action="" style="display: inline-block; opacity: 0.5; width: 90px;"><input class="subtile" type="text" name="builder_skin_name" placeholder="skin name" style="width: 100%;"/></form></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <form name="builder-form" class="builder-form">

                        <div class="row">
                            <div class="dzsprg-builder-con">
                                <div class="col-md-8">
                                    <div class="dzsprg-builder-con--canvas-area dzs-progress-bar" style="opacity: 0;"></div>
                                    <div class="dzsprg-builder-con--add-area">
                                        <span class="dzs-button builder-add-rect">
                                            Add Rectangle
                                        </span>
                                        <span class="dzs-button builder-add-circ">
                                            Add Circle
                                        </span>
                                        <span class="dzs-button builder-add-text">
                                            Add Text
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="dzsprg-builder-con--layers-area"><!--begin layers area-->
        <?php
        if (isset($this->db_skin_data['bars'])) {
            $bars = $this->db_skin_data['bars'];
//                                    print_r($bars);
            foreach ($bars as $lab_bar => $val_bar) {
                if ($lab_bar !== 0 && $lab_bar == 'mainsettings') {
                    continue;
                }
//                                        print_r($val_bar);
                $aux = $val_bar;
                echo $this->generate_layer_item($aux);
            }
        }
        ?>
                                        <!--end layers area--></div>
                                </div>

                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                        <?php
                                        $mainsettings = array(
                                            'position_type' => 'relative',
                                            'index' => '0',
                                            'width' => '100%',
                                            'height' => 'auto',
                                            'animation_time' => '2000',
                                            'maxperc' => '100',
                                            'margin_top' => '0',
                                            'margin_right' => '0',
                                            'margin_bottom' => '0',
                                            'margin_left' => '0',
                                            'color' => '#eeeeee',
                                            'background_color' => '#285e8e',
                                            'type' => 'rect',
                                            'initon' => 'scroll',
                                            'maxnr' => '100',
                                        );

                                        $mainsettings_fromdb = array();

                                        if (isset($this->db_skin_data['bars']['mainsettings'])) {
                                            $mainsettings_fromdb = $this->db_skin_data['bars']['mainsettings'];
                                        }
                                        $mainsettings = array_merge($mainsettings,$mainsettings_fromdb);

//                        $mainsettings = array_merge();
                                        ?>
                                <div id="tabs-mainsettings" class="dzs-tabs skin-box">

                                    <div class="dzs-tab-tobe">
                                        <div class="tab-menu with-tooltip">
                                            Position
                                        </div>
                                        <div class="tab-content">

                                            <div class="one-half">
                                                <div class="setting-label">Width</div>
                                                <div class="setting"><input class="builder-field" type="text" name="bars[mainsettings][width]" value="<?php echo $mainsettings['width']; ?>">
                                                </div>
                                            </div>
                                            <div class="one-half">
                                                <div class="setting-label">Height</div>
                                                <div class="setting"><input class="builder-field" type="text" name="bars[mainsettings][height]" value="<?php echo $mainsettings['height']; ?>">
                                                </div>
                                            </div>
                                            <div class="clear"></div>


                                            <hr>
                                            <div class="one-half" style="float:none; margin: 0 auto;">
                                                <div class="setting-label">Margin Top</div>
                                                <div class="setting"><input class="builder-field" type="text" name="bars[mainsettings][margin_top]" value="<?php echo $mainsettings['margin_top']; ?>">
                                                </div>
                                            </div>
                                            <div class="clear"></div>
                                            <div class="one-half">
                                                <div class="setting-label">Margin Left</div>
                                                <div class="setting"><input class="builder-field" type="text" name="bars[mainsettings][margin_left]" value="<?php echo $mainsettings['margin_left']; ?>">
                                                </div>
                                            </div>
                                            <div class="one-half">
                                                <div class="setting-label">Margin Right</div>
                                                <div class="setting"><input class="builder-field" type="text" name="bars[mainsettings][margin_right]" value="<?php echo $mainsettings['margin_right']; ?>">
                                                </div>
                                            </div>
                                            <div class="clear"></div>

                                            <div class="one-half" style="float:none; margin: 0 auto;">
                                                <div class="setting-label">Margin Bottom</div>
                                                <div class="setting"><input class="builder-field" type="text" name="bars[mainsettings][margin_bottom]" value="<?php echo $mainsettings['margin_bottom']; ?>">
                                                </div>
                                            </div>
                                            <div class="clear"></div>

                                        </div>


                                    </div>


                                    <div class="dzs-tab-tobe">
                                        <div class="tab-menu with-tooltip">
                                            Animation
                                        </div>
                                        <div class="tab-content">
                                            <div class="setting">
                                                <div class="setting-label">Animation Time</div>
                                                <input class="builder-field" type="text" name="bars[mainsettings][animation_time]" value="<?php echo $mainsettings['animation_time']; ?>">
                                                <div class="sidenote">Animation Time in ms - 1000 ms = 1 second</div>    
                                            </div>
                                            <div class="setting">
                                                <div class="setting-label">Percent</div>
                                                <input class="builder-field" type="text" name="bars[mainsettings][maxperc]" value="<?php echo $mainsettings['maxperc']; ?>">

                                                <div class="jqueryui-slider for-perc"></div>
                                                <div class="sidenote">Percent on which the animation goes - from 1 to 100</div>    
                                            </div>
                                            <div class="setting">
                                                <div class="setting-label">Animation Number</div>
                                                <input class="builder-field" type="text" name="bars[mainsettings][maxnr]" value="<?php echo $mainsettings['maxnr']; ?>">
                                                <div class="sidenote">You can have a progress number which increments as the progress goes on. You insert it via {{percmaxnr}} in the text block ideally.</div>    
                                            </div>
                                            <div class="setting">
                                                <div class="setting-label">Animation Starts on ...</div>
        <?php
        $lab = 'initon';
        echo DZSHelpers::generate_select('bars[mainsettings]['.$lab.']',array('options' => array('init','scroll'),'class' => 'styleme builder-field','seekval' => $mainsettings[$lab]));
        ?><div class="sidenote">init - page load, scroll - when the page scrolls to it's location.</div>    
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                <br>
                                <div class="sidenote"><p>You can have variables that are replaced dinamically while the progress bars animate. 
                                        Might be overwhelming to understand them all but you can search 
                                        the examples for easily understanding. A cheatsheet can be found below</p>
                                    {{perc}} - outputs the current percentage, for example if the progress is at 47% it will output 47%
                                    <br>{{perc-decimal}} - outputs the current percentage in decimal form, for example if the progress is at 47% it will output 0.47
                                    <br>{{perc100-decimal}} - it's the same as perc-decimal but the difference is it will go up until 1 even if the <strong>Percent</strong> is set to lower then 100%
                                    <br>{{center}} - it will center the element, currently available only for the <strong>Top</strong> property
                                    <br>{{percmaxnr}} - outputs the current number relative the percent, you set the number in the <strong>Animation Number</strong> field. 
                                    For example if progress is at 47% and the Animation Number is 500 - the output will be <strong>235</strong>
                                    <br><br>{{arg1-default60}} - this field will be replaced by the value you place in the editor for the percent
                                    <br>{{arg2-default60}} - this field will be replaced by the value you place in the editor for the max number
                                    <br>{{arg3-defaulttext}} - this field will be replaced by the value you place in the editor for the text content
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <br/>
                            <br/>
                            <div class="col-md-6">
                                <button class="button-primary btn-save-skin">Save Changes</button>
                            </div>
                            <div class="col-md-6" style="text-align: right;">
                                version 1.0
                            </div>
                        </div>
                        <div class="row">
                            <br><br>
                            <div class="col-md-12">
                                <h3>Preview Examples</h3>
                            </div>
                            <br>
                            <div class="col-md-2">
                                <a href='?page=dzsprg_builder&skin=prev1'>
                                    <div class='divimage preview-example' style='background-image: url(<?php echo $this->theurl; ?>img/skin1.png);'></div>
                                </a>
                            </div>
                            <div class="col-md-2">
                                <a href='?page=dzsprg_builder&skin=prev2'>
                                    <div class='divimage preview-example' style='background-image: url(<?php echo $this->theurl; ?>img/skin2.png);'></div>
                                </a>
                            </div>
                            <div class="col-md-2">
                                <a href='?page=dzsprg_builder&skin=prev3'>
                                    <div class='divimage preview-example' style='background-image: url(<?php echo $this->theurl; ?>img/skin3.png);'></div>
                                </a>
                            </div>
                            <div class="col-md-2">
                                <a href='?page=dzsprg_builder&skin=prev4'>
                                    <div class='divimage preview-example' style='background-image: url(<?php echo $this->theurl; ?>img/skin4.png);'></div>
                                </a>
                            </div>
                            <div class="col-md-2">
                                <a href='?page=dzsprg_builder&skin=prev5'>
                                    <div class='divimage preview-example' style='background-image: url(<?php echo $this->theurl; ?>img/skin5.png);'></div>
                                </a>
                            </div>
                            <div class="col-md-2">
                                <a href='?page=dzsprg_builder&skin=prev6'>
                                    <div class='divimage preview-example' style='background-image: url(<?php echo $this->theurl; ?>img/skin6.png);'></div>
                                </a>
                            </div>

                        </div>
                    </form>
                </div>
            </section>
            <div class="saveconfirmer active" style=""><span alt="" style="" id="save-ajax-loading2" ></span></div>
        </div>
        <?php
    }

    function handle_admin_notices() {
        if ($this->noticed) {
            return;
        }
        foreach ($this->notices as $notice) {
            echo $notice;
        }

        $this->noticed = true;
    }

    function generate_layer_item($pargs = array()) {

        $margs = array(
            'position_type' => 'relative',
            'index' => '0',
            'width' => '100%',
            'height' => '40',
            'top' => '0',
            'right' => 'auto',
            'bottom' => 'auto',
            'left' => '0',
            'margin_top' => '0',
            'margin_right' => '0',
            'margin_bottom' => '0',
            'margin_left' => '0',
            'border_radius' => '0',
            'border' => '0',
            'color' => '#111111',
            'background_color' => '#285e8e',
            'type' => 'rect',
            'animation_brake' => '',
            'font_size' => '12',
            'text_align' => 'left',
            'color' => '#ffffff',
            'opacity' => '1',
            'text' => '',
            'font_size' => '12',
            'circle_outside_fill' => '#fb1919',
            'circle_inside_fill' => 'transparent',
            'circle_outer_width' => '{{perc-decimal}}',
            'circle_line_width' => '10',
            'extra_classes' => '',
        );

        $margs = array_merge($margs,$pargs);

        $struct_item = '';


        $struct_item = '<div class="builder-layer type-'.$margs['type'].'"><div class="builder-layer--head">'
                .'<input type="hidden" name="bars['.$margs['index'].'][type]" value="'.$margs['type'].'"/>'
                .'<span class="the-title">'.$margs['type'].'</span><span class="sortable-handle-con"><span class="sortable-handle"></span></span>'
                .'</div>';

        $struct_item.='<div class="builder-layer--inside">';

        $struct_item.= '<div class="dzs-tabs skin-box">';

        if ($margs['type'] == 'text') {
            $struct_item.='<div class="setting type-text">
<textarea class="builder-field with-tinymce" name="bars['.$margs['index'].'][text]">'.$margs['text'].'</textarea>
</div>';
        }
        $struct_item.='<div class="dzs-tab-tobe">
            <div class="tab-menu with-tooltip">
            Position
            </div>
            <div class="tab-content">
            <div class="setting">
            <div class="setting-label">Type</div>';
        $lab = 'position_type';
        $struct_item.=DZSHelpers::generate_select('bars['.$margs['index'].']['.$lab.']',array('options' => array('relative','absolute'),'class' => 'styleme builder-field','seekval' => $margs[$lab]));

        $struct_item.='</div>
            <div class="one-half">
            <div class="setting">
            <div class="setting-label">Width</div>
            <input class="builder-field" type="text" name="bars['.$margs['index'].'][width]" value="'.$margs['width'].'">
                </div>
                </div>
            <div class="one-half">
            <div class="setting">
            <div class="setting-label">Height</div>
            <input class="builder-field" type="text" name="bars['.$margs['index'].'][height]" value="'.$margs['height'].'">
                </div>
                </div>
    <div class="clear"></div>


            <hr>
            <div class="one-half" style="float:none; margin: 0 auto;">
            <div class="setting">
            <div class="setting-label">Top</div>
            <input class="builder-field" type="text" name="bars['.$margs['index'].'][top]" value="'.$margs['top'].'">
                </div>
                </div>
    <div class="clear"></div>

            <div class="one-half">
            <div class="setting">
            <div class="setting-label">Left</div>
            <input class="builder-field" type="text" name="bars['.$margs['index'].'][left]" value="'.$margs['left'].'">
                </div>
                </div>
            <div class="one-half">
            <div class="setting">
            <div class="setting-label">Right</div>
            <input class="builder-field" type="text" name="bars['.$margs['index'].'][right]" value="'.$margs['right'].'">
                </div>
                </div>
    <div class="clear"></div>

            <div class="one-half" style="float:none; margin: 0 auto;">
            <div class="setting">
            <div class="setting-label">Bottom</div>
            <input class="builder-field" type="text" name="bars['.$margs['index'].'][bottom]" value="'.$margs['bottom'].'">
                </div>
                </div>
    <div class="clear"></div>

            <hr>
            <div class="one-half" style="float:none; margin: 0 auto;">
            <div class="setting">
            <div class="setting-label">Margin Top</div>
            <input class="builder-field" type="text" name="bars['.$margs['index'].'][margin_top]" value="'.$margs['margin_top'].'">
                </div>
                </div>
    <div class="clear"></div>
            <div class="one-half">
            <div class="setting">
            <div class="setting-label">Margin Left</div>
            <input class="builder-field" type="text" name="bars['.$margs['index'].'][margin_left]" value="'.$margs['margin_left'].'">
                </div>
                </div>
            <div class="one-half">
            <div class="setting">
            <div class="setting-label">Margin Right</div>
            <input class="builder-field" type="text" name="bars['.$margs['index'].'][margin_right]" value="'.$margs['margin_right'].'">
                </div>
                </div>
    <div class="clear"></div>

            <div class="one-half" style="float:none; margin: 0 auto;">
            <div class="setting">
            <div class="setting-label">Margin Bottom</div>
            <input class="builder-field" type="text" name="bars['.$margs['index'].'][margin_bottom]" value="'.$margs['margin_bottom'].'">
                </div>
                </div>
    <div class="clear"></div>

            </div>


        </div>

            <div class="dzs-tab-tobe">
                <div class="tab-menu with-tooltip">
                Styling
                </div>
                <div class="tab-content">
                <div class="setting ">
            <div class="setting-label">Background Color</div>
            <input class="builder-field with-colorpicker" type="text" name="bars['.$margs['index'].'][background_color]" value="'.$margs['background_color'].'"><span class="picker-con picker-left"><span class="the-icon"></span><span class="picker"></span></span>
                </div>
                <div class="setting type-text">
            <div class="setting-label">Color</div>
            <input class="builder-field with-colorpicker" type="text" name="bars['.$margs['index'].'][color]" value="'.$margs['color'].'"><span class="picker-con picker-left"><span class="the-icon"></span><span class="picker"></span></span>
                </div>
                <div class="setting type-circ">
            <div class="setting-label">Outer Circle Color</div>
            <input class="builder-field with-colorpicker" type="text" name="bars['.$margs['index'].'][circle_outside_fill]" value="'.$margs['circle_outside_fill'].'"><span class="picker-con picker-left"><span class="the-icon"></span><span class="picker"></span></span>
                </div>
                <div class="setting type-circ">
            <div class="setting-label">Inner Circle Color</div>
            <input class="builder-field with-colorpicker" type="text" name="bars['.$margs['index'].'][circle_inside_fill]" value="'.$margs['circle_inside_fill'].'"><span class="picker-con picker-left"><span class="the-icon"></span><span class="picker"></span></span>
                </div>
                
                <div class="setting type-circ">
            <div class="setting-label">Arc Percentage</div>
            <input class="builder-field" type="text" name="bars['.$margs['index'].'][circle_outer_width]" value="'.$margs['circle_outer_width'].'">
                </div>
                <div class="setting type-circ">
            <div class="setting-label">Outer Circle Width</div>
            <input class="builder-field" type="text" name="bars['.$margs['index'].'][circle_line_width]" value="'.$margs['circle_line_width'].'">
                </div>
                <div class="setting type-rect">
            <div class="setting-label">Border Radius</div>
            <input class="builder-field" type="text" name="bars['.$margs['index'].'][border_radius]" value="'.$margs['border_radius'].'">
                </div>
                <div class="setting">
            <div class="setting-label">Border</div>
            <input class="builder-field" type="text" name="bars['.$margs['index'].'][border]" value="'.$margs['border'].'">
                </div>
                <div class="setting">
            <div class="setting-label">Opacity</div>
            <input class="builder-field" type="text" name="bars['.$margs['index'].'][opacity]" value="'.$margs['opacity'].'">
                </div>
                <div class="setting">
            <div class="setting-label">Font Size</div>
            <input class="builder-field" type="text" name="bars['.$margs['index'].'][font_size]" value="'.$margs['font_size'].'">
                <div class="jqueryui-slider for-fontsize"></div>
                </div>
                <div class="setting ">
                    <div class="setting-label">Extra Classes</div>
                    <input class="builder-field" type="text" name="bars['.$margs['index'].'][extra_classes]" value="'.$margs['extra_classes'].'">
                </div>
                <!--
            <div class="setting type-text">
            <div class="setting-label">Text Align</div>';
        $lab = 'text_align';
        $struct_item.=DZSHelpers::generate_select('bars['.$margs['index'].']['.$lab.']',array('options' => array('left','center','right'),'class' => 'styleme builder-field','seekval' => $margs[$lab]));

        $struct_item.='</div>
-->
                <br>
                </div>
            </div>
            <div class="dzs-tab-tobe">
                <div class="tab-menu with-tooltip">
                Animation
                </div>
                <div class="tab-content">
                
                <div class="setting">
                    <div class="setting-label">Animation Brake</div>
                    <input class="builder-field" type="text" name="bars['.$margs['index'].'][animation_brake]" value="'.$margs['animation_brake'].'">
                    <div class="sidenote">'.__('Test','dzsprg').'</div>
                </div>
            
                </div>
            </div>

        </div>';

        $struct_item.='<a href="#" class="builder-layer--btn-delete">Delete Item</a>';
        $struct_item.='</div>';
        $struct_item.='</div>';
        return $struct_item;
    }

    function show_generator() {

        $arr_skins = array();
        foreach ($this->db_skins as $lab => $skin) {
//            $arr_skins[$lab] = $lab;
            array_push($arr_skins, $lab);
        }
        
//        print_r($arr_skins);

// some total cache vars that needs to be like this
        define('DONOTCACHEPAGE',true);
        define('DONOTMINIFY',true);
        ?>
        <!doctype html>
        <html lang="en" style="">
            <head>
                <meta charset="utf-8" />
                <title>DZS ZoomProgress Bars Generator</title>
                <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js" type="text/javascript"></script>
                <!--[if IE]>
                <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
                                <![endif]-->

                <script>
                        /*
                         * 
                         */
                        //console.log(window.tinyMCE)
                        var dzsprg_builder_settings = {
                            theurl: "<?php echo $this->theurl; ?>"
                            
                        }
                        if (window.tinyMCE && window.wptinyMCE == undefined) {
                            window.wptinyMCE = window.tinyMCE;
                        }
                        var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
                        window.theme_url = "<?php echo $this->theurl; ?>";

                        var pagenow = 'dzs_portfolio',
                                typenow = 'dzs_portfolio',
                                adminpage = 'post-php',
                                thousandsSeparator = ',',
                                decimalPoint = '.',
                                isRtl = 0;
                        var wordCountL10n = {"type": "w"};
                        var thickboxL10n = {"next": "Next >", "prev": "< Prev", "image": "Image", "of": "of", "close": "Close", "noiframes": "This feature requires inline frames. You have iframes disabled or your browser does not support them.", "loadingAnimation": "<?php echo site_url(); ?>\/wp-includes\/js\/thickbox\/loadingAnimation.gif", "closeImage": "<?php echo site_url(); ?>\/wp-includes\/js\/thickbox\/tb-close.png"};
                        var commonL10n = {"warnDelete": "You are about to permanently delete the selected items.\n  'Cancel' to stop, 'OK' to delete."};
                        var wpAjax = {"noPerm": "You do not have permission to do that.", "broken": "An unidentified error has occurred."};
                        var autosaveL10n = {"autosaveInterval": "60", "savingText": "Saving Draft\u2026", "saveAlert": "The changes you made will be lost if you navigate away from this page.", "blog_id": "1"};
                        var quicktagsL10n = {"closeAllOpenTags": "Close all open tags", "closeTags": "close tags", "enterURL": "Enter the URL", "enterImageURL": "Enter the URL of the image", "enterImageDescription": "Enter a description of the image", "fullscreen": "fullscreen", "toggleFullscreen": "Toggle fullscreen mode", "textdirection": "text direction", "toggleTextdirection": "Toggle Editor Text Direction"};
                        var adminCommentsL10n = {"hotkeys_highlight_first": "", "hotkeys_highlight_last": "", "replyApprove": "Approve and Reply", "reply": "Reply"};
                        var heartbeatSettings = {"nonce": "ecc15b5e95"};
                        var postL10n = {"ok": "OK", "cancel": "Cancel", "publishOn": "Publish on:", "publishOnFuture": "Schedule for:", "publishOnPast": "Published on:", "dateFormat": "%1$s %2$s, %3$s @ %4$s : %5$s", "showcomm": "Show more comments", "endcomm": "No more comments found.", "publish": "Publish", "schedule": "Schedule", "update": "Update", "savePending": "Save as Pending", "saveDraft": "Save Draft", "private": "Private", "public": "Public", "publicSticky": "Public, Sticky", "password": "Password Protected", "privatelyPublished": "Privately Published", "published": "Published", "comma": ","};
                        var _wpUtilSettings = {"ajax": {"url": "<?php echo $url_admin; ?>admin-ajax.php"}};
                        var _wpMediaModelsL10n = {"settings": {"ajaxurl": "<?php echo $url_admin; ?>admin-ajax.php", "post": {"id": 0}}};
                        var pluploadL10n = {"queue_limit_exceeded": "You have attempted to queue too many files.", "file_exceeds_size_limit": "%s exceeds the maximum upload size for this site.", "zero_byte_file": "This file is empty. Please try another.", "invalid_filetype": "This file type is not allowed. Please try another.", "not_an_image": "This file is not an image. Please try another.", "image_memory_exceeded": "Memory exceeded. Please try another smaller file.", "image_dimensions_exceeded": "This is larger than the maximum size. Please try another.", "default_error": "An error occurred in the upload. Please try again later.", "missing_upload_url": "There was a configuration error. Please contact the server administrator.", "upload_limit_exceeded": "You may only upload 1 file.", "http_error": "HTTP error.", "upload_failed": "Upload failed.", "big_upload_failed": "Please try uploading this file with the %1$sbrowser uploader%2$s.", "big_upload_queued": "%s exceeds the maximum upload size for the multi-file uploader when used in your browser.", "io_error": "IO error.", "security_error": "Security error.", "file_cancelled": "File canceled.", "upload_stopped": "Upload stopped.", "dismiss": "Dismiss", "crunching": "Crunching\u2026", "deleted": "moved to the trash.", "error_uploading": "\u201c%s\u201d has failed to upload."};
                        var _wpPluploadSettings = {"defaults": {"runtimes": "html5,silverlight,flash,html4", "file_data_name": "async-upload", "multiple_queues": true, "max_file_size": "33554432b", "url": "<?php echo $url_admin; ?>async-upload.php", "flash_swf_url": "<?php echo site_url(); ?>\/wp-includes\/js\/plupload\/plupload.flash.swf", "silverlight_xap_url": "<?php echo site_url(); ?>\/wp-includes\/js\/plupload\/plupload.silverlight.xap", "filters": [{"title": "Allowed Files", "extensions": "*"}], "multipart": true, "urlstream_upload": true, "multipart_params": {"action": "upload-attachment", "_wpnonce": "773ef53e9b"}}, "browser": {"mobile": false, "supported": true}, "limitExceeded": false};
                        var _wpMediaViewsL10n = {"url": "URL", "addMedia": "Add Media", "search": "Search", "select": "Select", "cancel": "Cancel", "selected": "%d selected", "dragInfo": "Drag and drop to reorder images.", "uploadFilesTitle": "Upload Files", "uploadImagesTitle": "Upload Images", "mediaLibraryTitle": "Media Library", "insertMediaTitle": "Insert Media", "createNewGallery": "Create a new gallery", "returnToLibrary": "\u2190 Return to library", "allMediaItems": "All media items", "noItemsFound": "No items found.", "insertIntoPost": "Insert into post", "uploadedToThisPost": "Uploaded to this post", "warnDelete": "You are about to permanently delete this item.\n  'Cancel' to stop, 'OK' to delete.", "insertFromUrlTitle": "Insert from URL", "setFeaturedImageTitle": "Set Featured Image", "setFeaturedImage": "Set featured image", "createGalleryTitle": "Create Gallery", "editGalleryTitle": "Edit Gallery", "cancelGalleryTitle": "\u2190 Cancel Gallery", "insertGallery": "Insert gallery", "updateGallery": "Update gallery", "addToGallery": "Add to gallery", "addToGalleryTitle": "Add to Gallery", "reverseOrder": "Reverse order", "settings": {"tabs": [], "tabUrl": "<?php echo $url_admin; ?>media-upload.php?chromeless=1", "mimeTypes": {"image": "Images", "audio": "Audio", "video": "Video"}, "captions": true, "nonce": {"sendToEditor": "0aef7a9d93"}, "post": {"id": 3905, "nonce": "0ba07d0c8c", "featuredImageId": "3577"}, "defaultProps": {"link": "", "align": "", "size": ""}, "embedExts": ["mp3", "ogg", "wma", "m4a", "wav", "mp4", "m4v", "webm", "ogv", "wmv", "flv"]}};
                        var authcheckL10n = {"beforeunload": "Your session has expired. You can log in again from this page or go to the login page.", "interval": "180"};
                        var wordCountL10n = {"type": "w"};
                        var wpLinkL10n = {"title": "Insert\/edit link", "update": "Update", "save": "Add Link", "noTitle": "(no title)", "noMatchesFound": "No matches found."};/* ]]> */
                </script>
                <link rel="stylesheet" type="text/css" href="<?php echo $this->theurl; ?>tinymce/popup.css"/>
                <script src="<?php echo $this->theurl; ?>tinymce/popup.js"></script>
<!--                <script type='text/javascript' src='<?php echo $url_admin; ?>load-scripts.php?c=1&amp;load%5B%5D=utils,plupload,plupload-html5,plupload-flash,plupload-silverlight,plupload-html4,json2&amp;ver=3.6.1'></script>
                <script type='text/javascript' src='<?php echo $url_admin; ?>load-scripts.php?c=1&amp;load%5B%5D=thickbox,jquery-ui-core,jquery-ui-widget,jquery-ui-mouse,jquery-ui-sortable,jquery-ui-draggable,jquery-ui-droppable,&amp;load%5B%5D=common,admin-bar,schedule,wp-ajax-response,autosave,wp-lists,admin-comments,suggest,postbox,&amp;load%5B%5D=heartbeat,underscore,shortcode,backbone,wp-util,wp-backbone,media-models,wp-plupload,media-views,media-editor,wp-auth-check&amp;load%5B%5D=,word-count,editor,jquery-ui-resizable,jquery-ui-button,jquery-ui-position,jquery-ui-dialog,wpdialogs,wplink,wpdialogs-popup,wp-&amp;load%5B%5D=fullscreen,media-upload&amp;ver=3.6.1'></script>-->

<!--                <script src="<?php echo $this->theurl; ?>tinymce/tinymce/tinymce.min.js"></script>
                <script src="<?php echo $this->theurl; ?>tinymce/tinymce/jquery.tinymce.min.js"></script>-->
                <!--
                <script type="text/javascript" src="http://localhost/tinymce_jquery/jscripts/tiny_mce/jquery.tinymce.js"></script>
                <script src="<?php echo $this->theurl; ?>tinymce/js/tiny_mce.js"></script>
                -->
        <?php //wp_head();  ?>

            </head>
            <body class="popup-admin-wrapper">
                <div class="maincon">

                        <h2>DZS <?php echo __('Progress Bars Generator','dzsvg'); ?></h2>
                    <div class="setting type_any">
                        <div class="setting-label"><?php echo __('Skin'); ?></div>
                        <?php echo DZSHelpers::generate_select('skin', array('options' => $arr_skins, 'class' => 'styleme textinput'));
                        ?>
                    </div>


                    <div class="setting type_any">
                        <div class="setting-label"><?php echo __('Percent'); ?></div>
                        <input type="text" class="textinput small-input" name="arg1_perc" value="100"/>
                        <div class="sidenote"><?php echo __(''); ?></div>
                    </div>
                    <div class="setting type_any">
                        <div class="setting-label"><?php echo __('Percent Number'); ?></div>
                        <input type="text" class="textinput small-input" name="arg2_maxnr" value="100"/>
                        <div class="sidenote"><?php echo __(''); ?></div>
                    </div>
                    <div class="setting type_any">
                        <div class="setting-label"><?php echo __('Percent Number'); ?></div>
                        <textarea class="textinput medium-textarea with-tinymce" name="content" ></textarea>
                        <div class="sidenote"><?php echo __(''); ?></div>
                    </div>





                    <p><span style="display:inline-block; text-align: center;"class="button-style-noir btn-primary btn-master-generate">Generate</span></p>
                    <div class="clear"></div>
                </div>

                <div class="output-div"></div>
                <link rel='stylesheet' href='<?php echo $this->theurl; ?>fontawesome/font-awesome.min.css'></link>
                <link rel="stylesheet" href="<?php echo $this->theurl; ?>colorpicker/farbtastic.css">
                <script src="<?php echo $this->theurl; ?>colorpicker/farbtastic.js"></script>
                <link rel="stylesheet" href="<?php echo $this->theurl; ?>dzstabsandaccordions/dzstabsandaccordions.css">
                <script src="<?php echo $this->theurl; ?>dzstabsandaccordions/dzstabsandaccordions.js"></script>
        <script src="<?php echo $this->theurl; ?>tinymce/tinymce/tinymce.min.js"></script>
        <script src="<?php echo $this->theurl; ?>tinymce/tinymce/jquery.tinymce.min.js"></script>
            </body>
        </html><?php
        die();
    }

}
