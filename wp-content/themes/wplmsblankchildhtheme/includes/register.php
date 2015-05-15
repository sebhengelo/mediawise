<?php

add_action('widgets_init','wplms_register_sidebars');
function wplms_register_sidebars(){
if(function_exists('register_sidebar')){     
    register_sidebar( array(
    'name' => 'DashboardSidebar',
    'id' => 'dashboardsidebar',
    'before_widget' => '<div id="%1$s" class="widget %2$s">',
    'after_widget' => '</div>',
    'before_title' => '<h4 class="widget_title">',
    'after_title' => '</h4>',
        'description'   => __('Met deze widget worden gebruikersstatistieken weergegeven op het dashboard','vibe')
  ) );
  }
}

?>