<?php
/**
 * Feature Tour - Guided Onboarding for New Features
 *
 * Interactive walkthrough system for discovering WPShadow features.
 * Implements #1 Helpful Neighbor and #6 Drive to Free Training.
 *
 * @package    WPShadow
 * @subpackage Onboarding
 * @since      1.2601.2200
 */

declare(strict_types=1);

namespace WPShadow\Onboarding;

use WPShadow\Core\Activity_Logger;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Feature Tour Class
 *
 * Manages guided tours for new features and utilities.
 *
 * @since 1.2601.2200
 */
class Feature_Tour {

	/**
	 * Initialize the feature tour system.
	 *
	 * @since 1.2601.2200
	 * @return void
	 */
	public static function init() {
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
		add_action( 'admin_notices', array( __CLASS__, 'show_tour_prompt' ) );
		add_action( 'wp_ajax_wpshadow_start_tour', array( __CLASS__, 'ajax_start_tour' ) );
		add_action( 'wp_ajax_wpshadow_complete_tour_step', array( __CLASS__, 'ajax_complete_step' ) );
		add_action( 'wp_ajax_wpshadow_dismiss_tour', array( __CLASS__, 'ajax_dismiss_tour' ) );
	}

	/**
	 * Enqueue tour assets.
	 *
	 * @since  1.2601.2200
	 * @param  string $hook Current admin page hook.
	 * @return void
	 */
	public static function enqueue_assets( $hook ) {
		// Only load on WPShadow pages
		if ( strpos( $hook, 'wpshadow' ) === false ) {
			return;
		}

		wp_enqueue_style(
			'wpshadow-feature-tour',
			WPSHADOW_URL . 'assets/css/feature-tour.css',
			array(),
			WPSHADOW_VERSION
		);

		wp_enqueue_script(
			'wpshadow-feature-tour',
			WPSHADOW_URL . 'assets/js/feature-tour.js',
			array( 'jquery' ),
			WPSHADOW_VERSION,
			true
		);

		wp_localize_script(
			'wpshadow-feature-tour',
			'wpShadowTour',
			array(
				'nonce'     => wp_create_nonce( 'wpshadow_feature_tour' ),
				'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
				'tours'     => self::get_available_tours(),
				'completed' => self::get_completed_tours(),
			)
		);
	}

	/**
	 * Show tour prompt for new features.
	 *
	 * @since 1.2601.2200
	 * @return void
	 */
	public static function show_tour_prompt() {
		// Only show on WPShadow pages
		$screen = get_current_screen();
		if ( ! $screen || strpos( $screen->id, 'wpshadow' ) === false ) {
			return;
		}

		// Check if user has seen the new features tour
		$completed_tours = self::get_completed_tours();
		if ( in_array( 'killer-utilities', $completed_tours, true ) ) {
			return;
		}

		// Check if dismissed
		$dismissed = get_user_meta( get_current_user_id(), 'wpshadow_tour_dismissed_killer_utilities', true );
		if ( $dismissed ) {
			return;
		}

		?>
		<div class="notice notice-info is-dismissible wpshadow-tour-prompt" data-tour="killer-utilities">
			<div style="display: flex; align-items: center; padding: 10px 0;">
				<div style="font-size: 48px; margin-right: 20px;">🚀</div>
				<div style="flex: 1;">
					<h3 style="margin: 0 0 10px 0;"><?php esc_html_e( 'New in WPShadow: 5 Killer Utilities!', 'wpshadow' ); ?></h3>
					<p style="margin: 0 0 10px 0;">
						<?php esc_html_e( 'Save hours with Site Cloner, Code Snippets, Plugin Conflict Detector, Bulk Find/Replace, and Regenerate Thumbnails.', 'wpshadow' ); ?>
					</p>
					<p style="margin: 0;">
						<button type="button" class="button button-primary wpshadow-start-tour" data-tour="killer-utilities">
							<span class="dashicons dashicons-welcome-learn-more" style="margin-top: 3px;"></span>
							<?php esc_html_e( 'Take the 3-Minute Tour', 'wpshadow' ); ?>
						</button>
						<button type="button" class="button wpshadow-dismiss-tour" data-tour="killer-utilities">
							<?php esc_html_e( 'Maybe Later', 'wpshadow' ); ?>
						</button>
						<a href="https://wpshadow.com/kb/killer-utilities" target="_blank" style="margin-left: 10px;">
							<?php esc_html_e( 'Read Documentation', 'wpshadow' ); ?>
						</a>
					</p>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Get available tours.
	 *
	 * @since  1.2601.2200
	 * @return array Available tours configuration.
	 */
	private static function get_available_tours() {
		return array(
			'killer-utilities' => array(
				'title'       => __( 'Discover 5 Killer Utilities', 'wpshadow' ),
				'description' => __( 'Learn how to save hours with powerful site management tools', 'wpshadow' ),
				'steps'       => array(
					array(
						'id'          => 'utilities-overview',
						'title'       => __( 'Welcome to Killer Utilities!', 'wpshadow' ),
						'content'     => __( 'WPShadow now includes 5 professional-grade utilities that save you an average of 9.5 hours per month. Let\'s explore them!', 'wpshadow' ),
						'target'      => '.wpshadow-utilities-link',
						'placement'   => 'bottom',
						'action_text' => __( 'Show Me', 'wpshadow' ),
						'action_url'  => admin_url( 'admin.php?page=wpshadow-utilities' ),
					),
					array(
						'id'          => 'site-cloner',
						'title'       => __( '🌐 Site Cloner', 'wpshadow' ),
						'content'     => __( 'Create staging sites in minutes with one-click cloning. Perfect for testing updates before going live. Saves ~45 minutes per clone.', 'wpshadow' ),
						'target'      => '[data-tool="site-cloner"]',
						'placement'   => 'right',
						'action_text' => __( 'Try Site Cloner', 'wpshadow' ),
						'action_url'  => admin_url( 'admin.php?page=wpshadow-utilities&tab=site-cloner' ),
					),
					array(
						'id'          => 'code-snippets',
						'title'       => __( '📝 Smart Code Snippets', 'wpshadow' ),
						'content'     => __( 'Add custom PHP, JavaScript, and CSS safely without editing theme files. Includes syntax validation and sandboxed testing. Saves ~20 minutes per snippet.', 'wpshadow' ),
						'target'      => '[data-tool="code-snippets"]',
						'placement'   => 'right',
						'action_text' => __( 'Explore Snippets', 'wpshadow' ),
						'action_url'  => admin_url( 'admin.php?page=wpshadow-utilities&tab=code-snippets' ),
					),
					array(
						'id'          => 'plugin-conflict',
						'title'       => __( '🔍 Plugin Conflict Detector', 'wpshadow' ),
						'content'     => __( 'Find conflicting plugins in minutes using binary search algorithm. What used to take 2-3 hours now takes 5 minutes. Completely free!', 'wpshadow' ),
						'target'      => '[data-tool="plugin-conflict"]',
						'placement'   => 'right',
						'action_text' => __( 'See How It Works', 'wpshadow' ),
						'action_url'  => admin_url( 'admin.php?page=wpshadow-utilities&tab=plugin-conflict' ),
					),
					array(
						'id'          => 'find-replace',
						'title'       => __( '🔎 Bulk Find & Replace', 'wpshadow' ),
						'content'     => __( 'Update domain names, migrate to HTTPS, or replace content across your entire site. Includes dry-run preview for safety. Saves ~60 minutes per operation.', 'wpshadow' ),
						'target'      => '[data-tool="bulk-find-replace"]',
						'placement'   => 'right',
						'action_text' => __( 'Explore Find/Replace', 'wpshadow' ),
						'action_url'  => admin_url( 'admin.php?page=wpshadow-utilities&tab=bulk-find-replace' ),
					),
					array(
						'id'          => 'regenerate-thumbnails',
						'title'       => __( '🖼️ Regenerate Thumbnails', 'wpshadow' ),
						'content'     => __( 'Batch regenerate image thumbnails after theme changes or adding new image sizes. Saves ~50 minutes on average.', 'wpshadow' ),
						'target'      => '[data-tool="regenerate-thumbnails"]',
						'placement'   => 'right',
						'action_text' => __( 'Try It Now', 'wpshadow' ),
						'action_url'  => admin_url( 'admin.php?page=wpshadow-utilities&tab=regenerate-thumbnails' ),
					),
					array(
						'id'          => 'tour-complete',
						'title'       => __( '🎉 You\'re All Set!', 'wpshadow' ),
						'content'     => __( 'These utilities save users an average of 9.5 hours per month. Want to learn more? Check out our knowledge base for detailed guides and video tutorials.', 'wpshadow' ),
						'target'      => 'body',
						'placement'   => 'center',
						'action_text' => __( 'View Knowledge Base', 'wpshadow' ),
						'action_url'  => 'https://wpshadow.com/kb/',
					),
				),
			),
		);
	}

	/**
	 * Get completed tours for current user.
	 *
	 * @since  1.2601.2200
	 * @return array Completed tour IDs.
	 */
	private static function get_completed_tours() {
		return get_user_meta( get_current_user_id(), 'wpshadow_completed_tours', true ) ?: array();
	}

	/**
	 * AJAX: Start a tour.
	 *
	 * @since 1.2601.2200
	 * @return void
	 */
	public static function ajax_start_tour() {
		check_ajax_referer( 'wpshadow_feature_tour', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wpshadow' ) ) );
		}

		$tour_id = isset( $_POST['tour_id'] ) ? sanitize_key( $_POST['tour_id'] ) : '';

		if ( empty( $tour_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid tour ID', 'wpshadow' ) ) );
		}

		// Log tour start
		Activity_Logger::log(
			'tour_started',
			array(
				'tour_id' => $tour_id,
			)
		);

		// Update user meta
		update_user_meta( get_current_user_id(), 'wpshadow_current_tour', $tour_id );
		update_user_meta( get_current_user_id(), 'wpshadow_current_tour_step', 0 );

		wp_send_json_success(
			array(
				'message' => __( 'Tour started', 'wpshadow' ),
				'tour_id' => $tour_id,
			)
		);
	}

	/**
	 * AJAX: Complete a tour step.
	 *
	 * @since 1.2601.2200
	 * @return void
	 */
	public static function ajax_complete_step() {
		check_ajax_referer( 'wpshadow_feature_tour', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wpshadow' ) ) );
		}

		$tour_id = isset( $_POST['tour_id'] ) ? sanitize_key( $_POST['tour_id'] ) : '';
		$step_id = isset( $_POST['step_id'] ) ? sanitize_key( $_POST['step_id'] ) : '';

		if ( empty( $tour_id ) || empty( $step_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid parameters', 'wpshadow' ) ) );
		}

		// Get tours config
		$tours = self::get_available_tours();
		if ( ! isset( $tours[ $tour_id ] ) ) {
			wp_send_json_error( array( 'message' => __( 'Tour not found', 'wpshadow' ) ) );
		}

		// Check if this is the last step
		$is_last_step = false;
		$steps        = $tours[ $tour_id ]['steps'];
		$step_index   = array_search( $step_id, array_column( $steps, 'id' ), true );

		if ( false !== $step_index && $step_index === count( $steps ) - 1 ) {
			$is_last_step = true;

			// Mark tour as completed
			$completed_tours   = self::get_completed_tours();
			$completed_tours[] = $tour_id;
			update_user_meta( get_current_user_id(), 'wpshadow_completed_tours', array_unique( $completed_tours ) );

			// Log tour completion
			Activity_Logger::log(
				'tour_completed',
				array(
					'tour_id' => $tour_id,
				)
			);
		}

		wp_send_json_success(
			array(
				'message'      => __( 'Step completed', 'wpshadow' ),
				'is_last_step' => $is_last_step,
			)
		);
	}

	/**
	 * AJAX: Dismiss a tour.
	 *
	 * @since 1.2601.2200
	 * @return void
	 */
	public static function ajax_dismiss_tour() {
		check_ajax_referer( 'wpshadow_feature_tour', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wpshadow' ) ) );
		}

		$tour_id = isset( $_POST['tour_id'] ) ? sanitize_key( $_POST['tour_id'] ) : '';

		if ( empty( $tour_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid tour ID', 'wpshadow' ) ) );
		}

		// Mark as dismissed
		update_user_meta( get_current_user_id(), 'wpshadow_tour_dismissed_' . $tour_id, true );

		// Log dismissal
		Activity_Logger::log(
			'tour_dismissed',
			array(
				'tour_id' => $tour_id,
			)
		);

		wp_send_json_success(
			array(
				'message' => __( 'Tour dismissed', 'wpshadow' ),
			)
		);
	}
}
