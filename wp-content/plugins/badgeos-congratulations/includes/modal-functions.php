<?php
/**
 * Modal Functions
 *
 * @package BadgeOS Referring Link Trigger
 * @author Credly, LLC
 * @license http://www.gnu.org/licenses/agpl.txt GNU AGPL v3.0
 * @link https://credly.com
 */

/**
 * Check if user should see congrats modal and show it if so.
 *
 * @since  1.0.0
 */
function badgeos_congrats_maybe_show_modal() {

	// Bail early if Auto-Messages add-on is showing a message
	// This prevents an unsightly collision between both popup contents
	if ( did_action( 'badgeos_messages_show_message_to_user' ) ) {
		return false;
	}

	$viewable_achievement_types = badgeos_congrats_get_viewable_achievement_types();

	if ( ! empty( $viewable_achievement_types ) ) {
		$earned_achievements = badgeos_get_user_achievements( array(
			'user_id'          => get_current_user_id(),
			'achievement_type' => badgeos_congrats_get_viewable_achievement_types(),
			'since'            => badgeos_congrats_get_modal_since(),
		) );

		if ( ! empty( $earned_achievements ) ) {
			badgeos_congrats_show_modal( $earned_achievements );
			do_action( 'badgeos_congrats_show_modal', $earned_achievements, get_current_user_id() );
		}
	}

}
add_action( 'wp_footer', 'badgeos_congrats_maybe_show_modal', 11 );

/**
 * Show the congratulations modal.
 *
 * @since  1.0.0
 *
 * @param  array  $achievements Achievement objects.
 */
function badgeos_congrats_show_modal( $achievements = array() ) {
	add_thickbox();
	wp_enqueue_style( 'badgeos-congrats' );
	$title = sprintf(
		__( 'Congratulations! You\'ve earned %s!', 'badgeos-congrats' ),
		sprintf( _n( 'an achievement', '%d achievements', count( $achievements ), 'badgeos-congrats' ), count( $achievements ) )
	);
	?>
		<div id="badgeos_congrats_wrap" style="display:none;">
			<div id="badgeos_congrats" class="badgeos-congrats">
				<?php echo badgeos_congrats_render_achievements( $achievements ); ?>
			</div>
		</div>
		<script type="text/javascript">
			jQuery(document).ready(function( $ ) {

				var user_meta_triggered     = false;
				var trigger_since_user_meta = function( callback ) {

					if ( user_meta_triggered ) {
						return;
					}

					user_meta_triggered = true;

					$.ajax({
						type     : 'post',
						dataType : 'json',
						url      : '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',
						data     : {
							'action'  : 'badgeos_async_congrats_set_modal_since',
							'user_id' : '<?php echo absint( get_current_user_id() ); ?>',
						},
						success: function( response ) {
							var debug = <?php echo defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? 'true' : 'false'; ?>;
							if ( window.console && debug ) { console.log( 'badgeos_async_congrats_set_modal_since response: ', response ); }
							if ( 'function' === typeof callback ) {
								callback();
							}
						}
					});
				};

				var click_trigger_since_user_meta = function( evt ) {
					evt.preventDefault();
					var href = $(this).attr('href');
					trigger_since_user_meta( function() { window.location.href = href; } );
				}

				setTimeout( function() {
					tb_show( "<?php echo $title; ?>",'#TB_inline?width=600&height=550&inlineId=badgeos_congrats_wrap', null );
					congrats_modal_setup_thickbox( $('.badgeos-congrats') );
					$('body')
						.on( 'tb_unload', '#TB_window,#TB_overlay,#TB_HideSelect', trigger_since_user_meta )
						.on( 'click', '#TB_ajaxContent a', click_trigger_since_user_meta );
				}, 1000 );

				// Resize congrats thickbox on window resize
				$(window).resize(function() {
					congrats_modal_resize_tb( $('.badgeos-congrats') );
				});

				// Add a custom class to our congrats thickbox, then resize
				function congrats_modal_setup_thickbox( container ) {
					setTimeout( function() {
						$('#TB_window').addClass('badge-congrats-thickbox');
						congrats_modal_resize_tb( container );
					}, 0 );
				}

				// Force congrats thickboxes to our specified width/height
				function congrats_modal_resize_tb( container ) {
					setTimeout( function() {

						var width  = Math.min( 640, ( $(window).width() - 100 ) );
						var height = Math.min( ( container.outerHeight( true ) + $('#TB_title').outerHeight( true ) ), ( $(window).height() - 200 ) );

						$('.badge-congrats-thickbox').css({ 'marginLeft': -(width / 2) });
						$('.badge-congrats-thickbox, .badge-congrats-thickbox #TB_iframeContent').width(width).height(height);
						$('.badge-congrats-thickbox, .badge-congrats-thickbox #TB_ajaxContent').width(width).height(height).css({'padding':'0px'});

					}, 0 );
				}

			});
		</script>
	<?php
}

/**
 * Asynchronously handle setting the 'get_modal_since' time usermeta (triggered by modal closing)
 * @since  1.0.1
 */
function badgeos_async_congrats_set_modal_since() {

	if ( ! isset( $_REQUEST['user_id'] ) ) {
		wp_send_json_error();
	}

	wp_send_json_success( badgeos_congrats_set_modal_since( absint( $_REQUEST['user_id'] ) ) );

}
add_action( 'wp_ajax_badgeos_async_congrats_set_modal_since', 'badgeos_async_congrats_set_modal_since' );
add_action( 'wp_ajax_nopriv_badgeos_async_congrats_set_modal_since', 'badgeos_async_congrats_set_modal_since' );

/**
 * Render list of achievements to congratulate.
 *
 * @since  1.0.0
 *
 * @param  array  $achievements Achievement objects.
 * @return string               HTML Markup.
 */
function badgeos_congrats_render_achievements( $achievements = array() ) {
	$output = '';
	if ( ! empty( $achievements ) ) {
		foreach ( $achievements as $achievement ) {
			$output .= badgeos_congrats_render_achievement( $achievement->ID );
		}
	}
	return $output;
}

/**
 * Render a single achievement to congratulate.
 *
 * @since  1.0.0
 *
 * @param  integer $achievement_id Achievement ID.
 * @return string                  HTML Markup.
 */
function badgeos_congrats_render_achievement( $achievement_id = 0 ) {

	$points = get_post_meta( $achievement_id, '_badgeos_points', true );

	$output = '';
	$output .= '<div class="badgeos-congrats-achievement">';
	$output .= '<p class="title">' . get_the_title( $achievement_id ) . '</p>';
	$output .= '<div class="image">' . badgeos_get_achievement_post_thumbnail( $achievement_id ) . '</div>';
	$output .= '<div class="content">';
	if ( $points ) { $output .= '<strong class="points">' . sprintf( __( '%d Points', 'badgeos-congrats' ), $points ) . '</strong>'; }
	$output .= '<p>' . get_post_meta( $achievement_id, '_badgeos_congratulations_text', true ) . '</p>';
	$output .= '<p><a href="' . get_permalink( $achievement_id ) . '">' . __( 'View Details', 'badgeos-congrats' ) .'</a></p>';
	$output .= badgeos_congrats_render_send_to_credly( $achievement_id );
	$output .= '</div>';
	$output .= '</div>';

	return apply_filters( 'badgeos_congrats_render_achievement', $output, $achievement_id );
}

/**
 * Render a "Send to Credly" button for a given achievement.
 *
 * @since  1.0.0
 *
 * @param  object $achievement Achievement object.
 * @return string              HTML Markup
 */
function badgeos_congrats_render_send_to_credly( $achievement_id = 0 ) {
	if ( credly_is_achievement_giveable( $achievement_id, get_current_user_id() ) ) {
		wp_enqueue_script( 'badgeos-congrats' );
		return '<p><a href="#nogo" class="button send-to-credly" data-achievement-id="' . $achievement_id . '">' . __( 'Send to Credly', 'badgeos-congrats' ) . '</a></p>';
	}
}
