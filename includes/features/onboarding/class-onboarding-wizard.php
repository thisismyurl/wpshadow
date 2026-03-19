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
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Onboarding;

use WPShadow\Core\Form_Param_Helper;
use WPShadow\Core\Hook_Subscriber_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Onboarding Wizard Class
 *
 * Manages the display and flow of the onboarding wizard to help users
 * transition from other platforms (Word, Wix, Moodle, etc.) to WordPress.
 */
class Onboarding_Wizard extends Hook_Subscriber_Base {

	/**
	 * Get the minimum required version for this feature.
	 *
	 * @since 1.6093.1200
	 * @return string Minimum required version.
	 */
	protected static function get_required_version(): string {
		return '1.6089';
	}

	/**
	 * Get hook subscriptions.
	 *
	 * @since 1.6093.1200
	 * @return array Hook subscriptions.
	 */
	protected static function get_hooks(): array {
		return array(
			'admin_init'    => 'maybe_redirect_to_onboarding',
			'admin_notices' => array( 'render_wizard', 1 ),
			'admin_footer'  => 'enqueue_wizard_assets',
		);
	}

	/**
	 * Initialize onboarding wizard (deprecated - use ::subscribe() instead).
	 *
	 * Sets up hooks to display the wizard when needed.
	 *
	 * @deprecated1.0 Use Onboarding_Wizard::subscribe() instead
	 * @return     void
	 */
	public static function init(): void {
		// Load manager and translator
		require_once WPSHADOW_PATH . 'includes/onboarding/class-onboarding-manager.php';
		require_once WPSHADOW_PATH . 'includes/onboarding/class-platform-translator.php';

		// Initialize manager hooks
		Onboarding_Manager::init();

		// Subscribe to hooks (backwards compatibility)
		self::subscribe();
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
		$page = Form_Param_Helper::get( 'page', 'text', '' );
		if ( 'wpshadow' === $page ) {
			return;
		}

		// Check if we've already shown the wizard this session
		$user_id = get_current_user_id();
		if ( \WPShadow\Core\Cache_Manager::get(
			'onboarding_shown_' . $user_id,
			'wpshadow_onboarding'
		) ) {
			return;
		}

		// Set transient to prevent redirect loop
		\WPShadow\Core\Cache_Manager::set(
			'onboarding_shown_' . $user_id,
			true,
			HOUR_IN_SECONDS,
			'wpshadow_onboarding'
		);

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
		$onboarding_param = Form_Param_Helper::get( 'onboarding', 'text', '' );
		if ( '1' === $onboarding_param || 'restart' === $onboarding_param ) {
			return true;
		}

		// Only auto-show on first visit (when needs_onboarding is true and no explicit skip)
		$user_id     = get_current_user_id();
		$shown_count = (int) get_user_meta( $user_id, 'wpshadow_onboarding_shown_count', true );

		// Show wizard on first admin page load
		return 0 === $shown_count;
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
		 * @since 1.6093.1200
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
		\WPShadow\Core\Cache_Manager::delete(
			'onboarding_shown_' . $user_id,
			'wpshadow_onboarding'
		);

		return true;
	}
}
