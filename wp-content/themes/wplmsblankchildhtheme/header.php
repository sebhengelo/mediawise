<?php
//Header File
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<title>
<?php echo wp_title('|',true,'right'); ?>
</title>
<?php

$layout = vibe_get_option('layout');
if(!isset($layout) || !$layout)
    $layout = '';

wp_head();
?>
</head>
<body <?php body_class($layout); ?>>
<div id="global" class="global">
    <div class="pagesidebar">
        <div class="sidebarcontent">    
            <h2 id="sidelogo">
            <a href="<?php echo vibe_site_url(); ?>"><img src="<?php  echo apply_filters('wplms_logo_url',VIBE_URL.'/images/logo.png'); ?>" alt="<?php echo get_bloginfo('name'); ?>" /></a>
            </h2>
            <?php
                $args = apply_filters('wplms-mobile-menu',array(
                    'theme_location'  => 'mobile-menu',
                    'container'       => '',
                    'menu_class'      => 'sidemenu',
                    'fallback_cb'     => 'vibe_set_menu',
                ));

                wp_nav_menu( $args );
            ?>
        </div>
        <a class="sidebarclose"><span></span></a>
    </div>  
    <div class="pusher">
        <?php
            $fix=vibe_get_option('header_fix');
        ?>
        <header class="<?php if(isset($fix) && $fix){echo 'fix';} ?>">
            <div class="container">
                <div id="searchdiv">
                    <form role="search" method="get" id="searchform" action="<?php echo home_url( '/' ); ?>">
                        <div><label class="screen-reader-text" for="s">Search for:</label>
                            <input type="text" value="<?php the_search_query(); ?>" name="s" id="s" placeholder="<?php _e('Hit enter to search...','vibe'); ?>" />
                            <?php 
                                $course_search=vibe_get_option('course_search');
                                if(isset($course_search) && $course_search)
                                    echo '<input type="hidden" value="course" name="post_type" />';
                            ?>
                            <input type="submit" id="searchsubmit" value="Search" />
                        </div>
                    </form>
                </div>
                <div class="row">
                    <div class="col-md-12 col-sm-12 col-xs-12 header-nav">

                        
                            <a class="header-logo" href="<?php echo vibe_site_url(); ?>">mediawise</a>
                        
                        <div id="searchicon"><i class="icon-search-2"></i></div>
                        <?php
                            if ( function_exists('bp_loggedin_user_link') && is_user_logged_in() ) :
                                ?>
                                <ul class="topmenu topmenu-login">
                                    <li><a href="<?php bp_loggedin_user_link(); ?>" class="smallimg vbplogin"><?php $n=vbp_current_user_notification_count(); echo ((isset($n) && $n)?'<em></em>':''); bp_loggedin_user_avatar( 'type=full' ); ?><span><?php bp_loggedin_user_fullname(); ?></span></a></li>
                                    <?php
                                    if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) || (function_exists('is_plugin_active_for_network') && is_plugin_active_for_network( 'woocommerce/woocommerce.php'))) { global $woocommerce;
                                    ?>
                                    <?php
                                    }
                                    ?>
                                </ul>
                            <?php
                            else :
                                ?>
                                <ul class="topmenu">
                                    <li><a href="#login" class="smallimg vbplogin"><span><?php _e('LOG IN','vibe'); ?></span></a></li>
                                    <?php
                                    if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) || (function_exists('is_plugin_active_for_network') && is_plugin_active_for_network( 'woocommerce/woocommerce.php'))) { global $woocommerce;
                                    ?>
                                    <?php
                                    }
                                    ?>
                                </ul>
                            <?php
                            endif;
                        ?>
                        <div id="vibe_bp_login">
                        <?php
                            if ( function_exists('bp_get_signup_allowed')){
                                the_widget('vibe_bp_login',array(),array());   
                            }
                        ?>
                       </div> 
                       <?php


                            $args = apply_filters('wplms-main-menu',array(
                                 'theme_location'  => 'main-menu',
                                 'container'       => 'nav',
                                 'menu_class'      => 'menu',
                                 'walker'          => new vibe_walker,
                                 'fallback_cb'     => 'vibe_set_menu'
                             ));
                            wp_nav_menu( $args ); 
                        ?>
                    </div>
                    <a id="trigger">
                        <span class="lines"></span>
                    </a>
                </div>
            </div>
        </header>
