<?php
/**
 * Achievement Tracking Functions
 *
 * @package BadgeOS Referring Link Trigger
 * @author Credly, LLC
 * @license http://www.gnu.org/licenses/agpl.txt GNU AGPL v3.0
 * @link https://credly.com
 */

/**
 * Get timestamp for when user last saw congratulations modal.
 *
 * @since  1.0.0
 *
 * @param  integer $user_id User ID.
 * @return integer          Timestamp.
 */
function badgeos_congrats_get_modal_since( $user_id = 0 ) {
	$user_id = absint( $user_id ) ? $user_id : get_current_user_id();
	return absint( get_user_meta( $user_id, '_badgeos_congrats_since', true ) );
}

/**
 * Update timestamp for when user last saw congratulations modal.
 *
 * @since  1.0.0
 *
 * @param  integer $user_id   User ID.
 * @param  integer $timestamp Timestamp.
 * @return mixed              Meta ID on success, otherwise false.
 */
function badgeos_congrats_set_modal_since( $user_id = 0, $timestamp = 0 ) {
	$user_id = absint( $user_id ) ? $user_id : get_current_user_id();
	$timestamp = absint( $timestamp ) ? $timestamp : time();
	return update_user_meta( $user_id, '_badgeos_congrats_since', $timestamp );
}

/**
 * Get achievement types that may be congratulated.
 *
 * @since  1.0.0
 *
 * @return array Post type slugs.
 */
function badgeos_congrats_get_viewable_achievement_types() {
	$badgeos_settings = get_option( 'badgeos_settings' );
	return isset( $badgeos_settings['badgeos_congrats_post_types'] ) ? array_keys( $badgeos_settings['badgeos_congrats_post_types'] ): array();
}
