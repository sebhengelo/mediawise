<?php
/**
 * Plugin Name: BadgeOS Congratulations Modal Add-On
 * Plugin URI: http://www.badgeos.org/
 * Description: This BadgeOS add-on generates a modal congratulating users on each earned achievement.
 * Author: LearningTimes
 * Version: 1.0.1
 * Author URI: http://www.badgeos.org
 * License: GNU AGPL
 */

/**
 * Our main plugin instantiation class
 *
 * This contains important things that our relevant to
 * our add-on running correctly. Things like registering
 * custom post types, taxonomies, posts-to-posts
 * relationships, and the like.
 *
 * @since 1.0.0
 */
class BadgeOS_Congratulations_Modal_Addon {

	/**
	 * Get everything running.
	 *
	 * @since 1.0.0
	 */
	function __construct() {

		// Define plugin constants
		$this->basename       = plugin_basename( __FILE__ );
		$this->directory_path = plugin_dir_path( __FILE__ );
		$this->directory_url  = plugins_url( dirname( $this->basename ) );

		// Load translations
		load_plugin_textdomain( 'badgeos-congrats', false, dirname( $this->basename ) . '/languages' );

		// If BadgeOS is unavailable, deactivate this plugin
		add_action( 'admin_notices', array( $this, 'maybe_disable_plugin' ) );

		// Include plugin files
		add_action( 'init', array( $this, 'updates' ) );
		add_action( 'init', array( $this, 'includes' ) );
		add_action( 'badgeos_settings', array( $this, 'settings' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_frontend_scripts_and_styles' ) );

	} /* __construct() */

	/**
	 * Register our add-on for automatic updates.
	 *
	 * @since 1.0.0
	 */
	public function updates() {
		if ( class_exists( 'BadgeOS_Plugin_Updater' ) ) {
			$badgeos_updater = new BadgeOS_Plugin_Updater( array(
					'plugin_file' => __FILE__,
					'item_name'   => 'Congratulations',
					'author'      => 'LearningTimes',
					'version'     => '1.0.1',
				)
			);
		}
	}

	/**
	 * Include plugin dependencies
	 *
	 * @since 1.0.0
	 */
	public function includes() {
		if ( $this->meets_requirements() ) {
			require_once( $this->directory_path . 'includes/achievement-tracking.php' );
			require_once( $this->directory_path . 'includes/modal-functions.php' );
		}
	} /* includes() */

	/**
	 * Register front-end scripts.
	 *
	 * @since 1.0.0
	 */
	public function register_frontend_scripts_and_styles() {
		wp_register_style( 'badgeos-congrats', $this->directory_url . '/css/badgeos-congrats.css' );
		wp_register_script( 'badgeos-congrats', $this->directory_url . '/js/badgeos-congrats.js' );
		wp_localize_script( 'badgeos-congrats', 'badgeosCongrats', array( 'ajaxUrl' => admin_url( 'admin-ajax.php' ) ) );
	} /* register_frontend_scripts_and_styles() */

	/**
	 * Register add-on settings.
	 *
	 * @since 1.0.0
	 */
	function settings( $settings = array() ) {
		?>
		<tr>
			<td colspan="2">
				<hr/>
				<h2><?php _e( 'Congratulations Modal Settings', 'badgeos-congrats' ); ?></h2>
				<p class="description"><?php _e( 'Select which achievements you would like to display in the congrats modal.', 'badgeos-congrats' ); ?></p>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e( 'Displayed Achievement Types:', 'badgeos-congrats' ); ?></th>
			<td>
				<?php
					$achievement_types = badgeos_get_achievement_types();
					unset( $achievement_types['step'] );
					foreach( $achievement_types as $slug => $data ) {
				?>
				<p>
					<label>
						<input type="checkbox" name="badgeos_settings[badgeos_congrats_post_types][<?php echo $slug; ?>]" value="true" <?php isset( $settings['badgeos_congrats_post_types'][ $slug ] ) ? checked( $settings['badgeos_congrats_post_types'][ $slug ], 'true' ) : ''; ?> />
						<?php echo get_post_type_object( $slug )->labels->name; ?>
					</label>
				</p>
				<?php } ?>
			</td>
		</tr>
		<?php
	} /* settings() */

	/**
	 * Check if BadgeOS is available
	 *
	 * @since  1.0.0
	 * @return bool  True if BadgeOS is available, otherwise false.
	 */
	public static function meets_requirements() {

		if ( class_exists( 'BadgeOS' ) )
			return true;
		else
			return false;

	} /* meets_requirements() */

	/**
	 * Output a custom error message and deactivate
	 * this plugin, if requriements are not met.
	 *
	 * @since 1.0.0
	 */
	public function maybe_disable_plugin() {

		if ( ! $this->meets_requirements() ) {
			// Display our error
			echo '<div id="message" class="error">';
			echo '<p>' . sprintf( __( 'BadgeOS Congratulations Modal requires BadgeOS and has been <a href="%s">deactivated</a>. Please install and activate BadgeOS and then reactivate this plugin.', 'badgeos-addon' ), admin_url( 'plugins.php' ) ) . '</p>';
			echo '</div>';

			// Deactivate our plugin
			deactivate_plugins( $this->basename );
		}

	} /* maybe_disable_plugin() */

} /* BadgeOS_Congratulations_Modal_Addon */
$GLOBALS['badgeos_congratulations_modal_addon'] = new BadgeOS_Congratulations_Modal_Addon();
