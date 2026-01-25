<?php
/**
 * Onboarding Wizard
 *
 * Displays and manages the onboarding wizard for new users.
 * Helps users transition from other platforms to WordPress.
 *
 * Philosophy: #1 Helpful Neighbor - Guide without judgment
 * Philosophy: #8 Inspire Confidence - Make WordPress approachable
 * Philosophy: #5 Drive to KB - Educational journey
 * Philosophy: #6 Drive to Training - Progressive learning
 *
 * @package WPShadow
 * @subpackage Onboarding
 * @since 1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Onboarding;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Onboarding Wizard Class
 *
 * Manages the display and flow of the onboarding wizard to help users
 * transition from other platforms (Word, Wix, Moodle, etc.) to WordPress.
 */
class Onboarding_Wizard {

	/**
	 * Initialize onboarding wizard
	 *
	 * Sets up hooks to display the wizard when needed.
	 *
	 * @return void
	 */
	public static function init(): void {
		// Load manager and translator
		require_once WPSHADOW_PATH . 'includes/onboarding/class-onboarding-manager.php';
		require_once WPSHADOW_PATH . 'includes/onboarding/class-platform-translator.php';

		// Initialize manager hooks
		Onboarding_Manager::init();

		// Add admin hooks for wizard display
		add_action( 'admin_init', array( __CLASS__, 'maybe_redirect_to_onboarding' ) );
		add_action( 'admin_notices', array( __CLASS__, 'render_wizard' ), 1 );
		add_action( 'admin_footer', array( __CLASS__, 'enqueue_wizard_assets' ) );
	}

	/**
	 * Maybe redirect user to onboarding on first admin visit
	 *
	 * Only redirects once per user, and only if they need onboarding.
	 *
	 * @return void
	 */
	public static function maybe_redirect_to_onboarding(): void {
		// Skip if not needed
		if ( ! self::should_show_wizard() ) {
			return;
		}

		// Skip if already on WPShadow page
		$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
		if ( 'wpshadow' === $page ) {
			return;
		}

		// Check if we've already shown the wizard this session
		$user_id = get_current_user_id();
		if ( get_transient( 'wpshadow_onboarding_shown_' . $user_id ) ) {
			return;
		}

		// Set transient to prevent redirect loop
		set_transient( 'wpshadow_onboarding_shown_' . $user_id, true, HOUR_IN_SECONDS );

		// Don't redirect during AJAX or REST requests
		if ( wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return;
		}

		// Redirect to WPShadow dashboard with onboarding flag
		$redirect_url = add_query_arg(
			array(
				'page'       => 'wpshadow',
				'onboarding' => '1',
			),
			admin_url( 'admin.php' )
		);

		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Check if wizard should be shown
	 *
	 * @return bool True if wizard should display
	 */
	private static function should_show_wizard(): bool {
		// Only show to logged-in users with appropriate capabilities
		if ( ! current_user_can( 'edit_posts' ) ) {
			return false;
		}

		// Check if user needs onboarding
		if ( ! Onboarding_Manager::needs_onboarding() ) {
			return false;
		}

		// Check if explicitly requesting onboarding
		$onboarding_param = isset( $_GET['onboarding'] ) ? sanitize_text_field( wp_unslash( $_GET['onboarding'] ) ) : '';
		if ( '1' === $onboarding_param || 'restart' === $onboarding_param ) {
			return true;
		}

		// Only auto-show on first visit (when needs_onboarding is true and no explicit skip)
		$user_id     = get_current_user_id();
		$shown_count = (int) get_user_meta( $user_id, 'wpshadow_onboarding_shown_count', true );

		// Show wizard on first admin page load
		return $shown_count === 0;
	}

	/**
	 * Render the onboarding wizard
	 *
	 * Outputs the wizard HTML as an admin notice.
	 *
	 * @return void
	 */
	public static function render_wizard(): void {
		// Only show if needed
		if ( ! self::should_show_wizard() ) {
			return;
		}

		// Increment shown count
		$user_id     = get_current_user_id();
		$shown_count = (int) get_user_meta( $user_id, 'wpshadow_onboarding_shown_count', true );
		update_user_meta( $user_id, 'wpshadow_onboarding_shown_count', $shown_count + 1 );

		// Include the wizard view
		$wizard_view = WPSHADOW_PATH . 'includes/views/onboarding/wizard.php';
		if ( file_exists( $wizard_view ) ) {
			include $wizard_view;
		}
	}

	/**
	 * Enqueue wizard assets
	 *
	 * Adds any additional JavaScript or CSS needed for the wizard.
	 *
	 * @return void
	 */
	public static function enqueue_wizard_assets(): void {
		// Only enqueue if wizard is showing
		if ( ! self::should_show_wizard() ) {
			return;
		}

		// The wizard view includes inline CSS and JavaScript
		// This method is a hook point for future enhancements

		/**
		 * Fires after onboarding wizard assets are enqueued
		 *
		 * Allows extensions to add additional assets for the wizard.
		 *
		 * @since 1.2601.2148
		 */
		do_action( 'wpshadow_onboarding_wizard_assets' );
	}

	/**
	 * Get onboarding status for current user
	 *
	 * Useful for AJAX endpoints and status checks.
	 *
	 * @return array Status information
	 */
	public static function get_status(): array {
		$user_id = get_current_user_id();

		return array(
			'needs_onboarding' => Onboarding_Manager::needs_onboarding( $user_id ),
			'platform'         => Onboarding_Manager::get_user_platform( $user_id ),
			'comfort_level'    => Onboarding_Manager::get_comfort_level( $user_id ),
			'ui_simplified'    => Onboarding_Manager::is_ui_simplified( $user_id ),
			'action_count'     => Onboarding_Manager::get_action_count( $user_id ),
			'can_graduate'     => Onboarding_Manager::get_action_count( $user_id ) >= 20,
		);
	}

	/**
	 * Restart onboarding for current user
	 *
	 * Clears onboarding completion flag to allow user to go through wizard again.
	 *
	 * @return bool True on success
	 */
	public static function restart_onboarding(): bool {
		$user_id = get_current_user_id();

		if ( ! $user_id ) {
			return false;
		}

		// Clear completion flag
		delete_user_meta( $user_id, Onboarding_Manager::META_ONBOARDING_COMPLETE );

		// Reset shown count
		delete_user_meta( $user_id, 'wpshadow_onboarding_shown_count' );

		// Clear transient
		delete_transient( 'wpshadow_onboarding_shown_' . $user_id );

		return true;
	}
}
