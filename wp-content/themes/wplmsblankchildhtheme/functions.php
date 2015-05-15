<?php

if ( !defined( 'VIBE_URL' ) )
define('VIBE_URL',get_template_directory_uri());

//include_once('includes/widgets/competentie-stats.php');

?>

<?php
 
// Get user's rank progress
function get_mycred_users_rank_progress( $user_id, $show_rank ) {
	global $wpdb;
 
	if ( ! function_exists( 'mycred' ) ) return '';
	
	// Change rank data to displayed user when on a user's profile
	if ( function_exists( 'bp_is_user' ) && bp_is_user() && empty( $user_id ) ) {
		$user_id = bp_displayed_user_id();
	}
 
	// Load myCRED
	$mycred = mycred();
 
	// Ranks are based on a total
	if ( $mycred->rank['base'] == 'total' )
		$key = $mycred->get_cred_id() . '_total';
			
	// Ranks are based on current balance
	else
		$key = $mycred->get_cred_id();
 
	// Get Balance
	$users_balance = $mycred->get_users_cred( $user_id, $key );
   
	// Rank Progress
   
	// Get the users current rank post ID
	$users_rank = (int) mycred_get_users_rank( $user_id, 'ID' );
	
	// Get the name of the users current rank
	$users_rank_name = get_the_title( $users_rank );
   
	// Get the ranks set max
	$max = get_post_meta( $users_rank, 'mycred_rank_max', true );
	
	$tabl_name = $wpdb->prefix . 'postmeta';
	
	// Get the users next rank post ID
	$next_ranks = $wpdb->get_results( $wpdb->prepare( "SELECT post_id FROM {$tabl_name} WHERE meta_key = %s AND meta_value > %d ORDER BY meta_value * 1 LIMIT 1;", 'mycred_rank_min', $max ) );
 
    foreach( $next_ranks as $next_rank ) {
 
        $next_rank = $next_rank->post_id;
    }
	
	// Get the name of the users next rank
	$next_rank_name = get_the_title( $next_rank );
	
	// Get the ranks set min
	$next_rank_min = get_post_meta( $next_rank, 'mycred_rank_min', true );
   
	// Calculate progress. We need a percentage with 1 decimal
	$progress = number_format( ( ( $users_balance / $max ) * 100 ), 0 );
 
	// Display rank progress bar
	echo '<div class="mycred-rank-progress">';
		echo '<h6 class="rank-progress-label" style="font-weight:bold;">Rank Progress ('. $progress .'%)</h6>';
		echo '<progress max="'. $max .'" value="'. $users_balance .'" class="rank-progress-bar">';
		echo '</progress>';
		if( $show_rank == 'yes' ){
			echo '<span class="current-rank" style="float:left;padding-top:1%;">'. $users_rank_name .'</span>';	
			echo '<span class="next-rank" style="float:right;padding-top:1%;">'. $next_rank_name .'</span>';
			echo '<span class="points-progress" style="width:100%;float:left;margin-top: -4.5%;padding-top:5%;text-align:center;">Nog '. $users_balance .' van '. $next_rank_min .' te gaan</span>';
		}
	echo '</div>';
}
 
/**
 * myCRED Shortcode: mycred_users_rank_progress
 * @since 1.0
 * @version 1.0
 */
function mycred_users_rank_progress( $atts ){
	extract( shortcode_atts( array(
		'user_id' => get_current_user_id(),
		'show_rank' => 'yes'
	), $atts ) );
 
	ob_start();
	
	get_mycred_users_rank_progress( $user_id, $show_rank );
 
	$output = ob_get_contents();
	ob_end_clean();
 
	return $output;
 
}

add_shortcode( 'mycred_users_rank_progress', 'mycred_users_rank_progress' );

?>

<?php
/**
 * Register our sidebars and widgetized areas.
 *
 */
function arphabet_widgets_init() {

	register_sidebar( array(
		'name'          => 'Dashboard',
		'id'            => 'dashboard',
		'before_widget' => '<div>',
		'after_widget'  => '</div>',
		'before_title'  => '<h4>',
		'after_title'   => '</h4>',
	) );

	register_sidebar( array(
		'name'          => 'Community',
		'id'            => 'community',
		'before_widget' => '<div>',
		'after_widget'  => '</div>',
		'before_title'  => '<h4>',
		'after_title'   => '</h4>',
	) );

}

add_action( 'widgets_init', 'arphabet_widgets_init' );
?>

<?php 
/** 
 * Allow SVG uploads 
 */
function cc_mime_types($mimes) {
  $mimes['svg'] = 'image/svg+xml';
  return $mimes;
}
add_filter('upload_mimes', 'cc_mime_types');

?>