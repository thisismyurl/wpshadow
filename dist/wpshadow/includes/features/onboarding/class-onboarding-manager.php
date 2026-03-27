<?php
declare(strict_types=1);

namespace WPShadow\Onboarding;

use WPShadow\Core\Hook_Subscriber_Base;

/**
 * Onboarding Manager
 *
 * Helps users transition to WordPress from other platforms with friendly guidance.
 *
 * Philosophy: #1 Helpful Neighbor - Guide without judgment
 * Philosophy: #8 Inspire Confidence - Make WordPress feel approachable
 * Philosophy: #5 Drive to KB - Link to learning resources
 * Philosophy: #6 Drive to Training - Educational journey
 *
 * @since 1.6093.1200
 * @package WPShadow
 */
class Onboarding_Manager extends Hook_Subscriber_Base {

	/**
	 * User meta key for onboarding completion
	 */
	const META_ONBOARDING_COMPLETE = 'wpshadow_onboarding_complete';

	/**
	 * User meta key for selected platform
	 */
	const META_PLATFORM = 'wpshadow_onboarding_platform';

	/**
	 * User meta key for technical comfort level
	 */
	const META_COMFORT_LEVEL = 'wpshadow_onboarding_comfort_level';

	/**
	 * User meta key for dismissed terms
	 */
	const META_DISMISSED_TERMS = 'wpshadow_onboarding_dismissed_terms';

	/**
	 * User meta key for action count
	 */
	const META_ACTION_COUNT = 'wpshadow_onboarding_action_count';

	/**
	 * User meta key for UI simplification preference
	 */
	const META_UI_SIMPLIFIED = 'wpshadow_onboarding_ui_simplified';

	/**
	 * Get hook subscriptions.
	 *
	 * @since 1.6093.1200
	 * @return array Hook subscriptions.
	 */
	protected static function get_hooks(): array {
		return array(
			'save_post'                   => 'track_action',
			'updated_option'              => 'track_action',
			'wp_insert_comment'           => 'track_action',
			'admin_notices'               => 'maybe_show_graduation',
			'wpshadow_settings_sections'  => 'add_settings_section',
		);
	}

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
	 * Initialize onboarding system (deprecated).
	 *
	 * @deprecated1.0 Use Onboarding_Manager::subscribe() instead
	 * @return     void
	 */
	public static function init(): void {
		self::subscribe();
	}

	/**
	 * Check if user needs onboarding
	 *
	 * @param int $user_id User ID (default: current user)
	 * @return bool True if needs onboarding
	 */
	public static function needs_onboarding( int $user_id = 0 ): bool {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		$complete = get_user_meta( $user_id, self::META_ONBOARDING_COMPLETE, true );
		return empty( $complete );
	}

	/**
	 * Get user's selected platform
	 *
	 * @param int $user_id User ID (default: current user)
	 * @return string Platform ID or empty string
	 */
	public static function get_user_platform( int $user_id = 0 ): string {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		return get_user_meta( $user_id, self::META_PLATFORM, true ) ?: '';
	}

	/**
	 * Get user's technical comfort level
	 *
	 * @param int $user_id User ID (default: current user)
	 * @return string Comfort level or empty string
	 */
	public static function get_comfort_level( int $user_id = 0 ): string {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		return get_user_meta( $user_id, self::META_COMFORT_LEVEL, true ) ?: '';
	}

	/**
	 * Check if UI should be simplified for user
	 *
	 * @param int $user_id User ID (default: current user)
	 * @return bool True if UI should be simplified
	 */
	public static function is_ui_simplified( int $user_id = 0 ): bool {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		// If user hasn't completed onboarding, use simplified UI
		if ( self::needs_onboarding( $user_id ) ) {
			return false; // Don't simplify until they choose
		}

		// Check user preference
		$simplified = get_user_meta( $user_id, self::META_UI_SIMPLIFIED, true );

		// Default to simplified if they selected a platform (not WordPress)
		if ( '' === $simplified ) {
			$platform = self::get_user_platform( $user_id );
			return ! empty( $platform ) && 'WordPress' !== $platform;
		}

		return (bool) $simplified;
	}

	/**
	 * Get user's action count (for graduation tracking)
	 *
	 * @param int $user_id User ID (default: current user)
	 * @return int Action count
	 */
	public static function get_action_count( int $user_id = 0 ): int {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		return (int) get_user_meta( $user_id, self::META_ACTION_COUNT, true );
	}

	/**
	 * Track user action for graduation
	 *
	 * @return void
	 */
	public static function track_action(): void {
		$user_id = get_current_user_id();

		// Only track if user has platform set and UI simplified
		if ( ! self::is_ui_simplified( $user_id ) ) {
			return;
		}

		$count = self::get_action_count( $user_id );
		update_user_meta( $user_id, self::META_ACTION_COUNT, $count + 1 );
	}

	/**
	 * Maybe show graduation notice
	 *
	 * @return void
	 */
	public static function maybe_show_graduation(): void {
		self::enqueue_assets();

		$user_id = get_current_user_id();

		// Check if eligible for graduation
		if ( ! self::is_ui_simplified( $user_id ) ) {
			return;
		}

		$count = self::get_action_count( $user_id );
		if ( $count < 20 ) {
			return;
		}

		// Check if already dismissed
		if ( get_user_meta( $user_id, 'wpshadow_graduation_dismissed', true ) ) {
			return;
		}

		// Show graduation notice
		?>
		<div class="notice notice-success is-dismissible wpshadow-graduation-notice">
			<h3><?php esc_html_e( '🎉 You\'ve Mastered WordPress!', 'wpshadow' ); ?></h3>
			<p>
				<?php
				printf(
					esc_html__( 'You\'ve completed %d actions and you\'re doing great! Ready to see everything WordPress has to offer?', 'wpshadow' ),
					$count
				);
				?>
			</p>
			<p>
				<button type="button" class="wps-btn wps-btn-primary" id="wpshadow-graduate-btn">
					<?php esc_html_e( 'Show Me Everything', 'wpshadow' ); ?>
				</button>
				<button type="button" class="wps-btn wps-btn-secondary" id="wpshadow-graduate-later">
					<?php esc_html_e( 'Maybe Later', 'wpshadow' ); ?>
				</button>
				<a href="<?php echo esc_url( \WPShadow\Core\UTM_Link_Manager::kb_link( 'wordpress-graduation', 'onboarding' ) ); ?>" target="_blank" class="wps-onboarding-link-spaced">
					<?php esc_html_e( 'Learn More', 'wpshadow' ); ?>
				</a>
			</p>
		</div>
		<?php
	}

	/**
	 * AJAX handlers have been migrated to class-based handlers.
	 * See: includes/admin/ajax/class-save-onboarding-handler.php
	 * See: includes/admin/ajax/class-skip-onboarding-handler.php
	 * See: includes/admin/ajax/class-dismiss-term-handler.php
	 * See: includes/admin/ajax/class-show-all-features-handler.php
	 * See: includes/admin/ajax/class-dismiss-graduation-handler.php
	 *
	 * Registered via AJAX_Router in class-ajax-router.php
	 */

	/**
	 * Add onboarding settings section
	 *
	 * @return void
	 */
	public static function add_settings_section(): void {
		self::enqueue_assets();

		?>
		<div class="wpshadow-settings-section">
			<h3><?php esc_html_e( 'Onboarding & Learning', 'wpshadow' ); ?></h3>
			<p><?php esc_html_e( 'Customize your WordPress learning experience', 'wpshadow' ); ?></p>
			
			<?php
			$user_id    = get_current_user_id();
			$platform   = self::get_user_platform( $user_id );
			$comfort    = self::get_comfort_level( $user_id );
			$simplified = self::is_ui_simplified( $user_id );
			?>
			
			<div class="wps-settings-section">
				<div class="wps-form-group">
					<label class="wps-label">
						<?php esc_html_e( 'Your Background', 'wpshadow' ); ?>
					</label>
					<div>
						<?php if ( $platform ) : ?>
							<?php
							$platform_labels = array(
								'wordpress'   => __( 'WordPress (experienced)', 'wpshadow' ),
								'word'        => __( 'Microsoft Word', 'wpshadow' ),
								'google-docs' => __( 'Google Docs', 'wpshadow' ),
								'wix'         => __( 'Wix', 'wpshadow' ),
								'squarespace' => __( 'Squarespace', 'wpshadow' ),
								'moodle'      => __( 'Moodle', 'wpshadow' ),
								'notion'      => __( 'Notion', 'wpshadow' ),
								'none'        => __( 'New to all of this', 'wpshadow' ),
							);
							echo esc_html( $platform_labels[ $platform ] ?? $platform );
							?>
						<?php else : ?>
							<em><?php esc_html_e( 'Not set', 'wpshadow' ); ?></em>
						<?php endif; ?>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow&onboarding=restart' ) ); ?>" class="wps-onboarding-link-spaced">
							<?php esc_html_e( 'Change', 'wpshadow' ); ?>
						</a>
					</div>
				</div>

				<div class="wps-form-group">
					<label class="wps-label">
						<?php esc_html_e( 'Simplified Interface', 'wpshadow' ); ?>
					</label>
					<div>
						<label>
							<input type="checkbox" name="wpshadow_ui_simplified" value="1" <?php checked( $simplified ); ?> />
							<?php esc_html_e( 'Show only essential features (recommended for beginners)', 'wpshadow' ); ?>
						</label>
						<span class="wps-help-text">
							<?php esc_html_e( 'Hide advanced WordPress features until you\'re ready. You can toggle this anytime.', 'wpshadow' ); ?>
						</span>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Get user's configuration preferences
	 *
	 * @param int|null $user_id User ID (defaults to current user)
	 * @return array Configuration preferences
	 */
	public static function get_config_preferences( ?int $user_id = null ): array {
		if ( null === $user_id ) {
			$user_id = get_current_user_id();
		}

		$defaults = array(
			'auto_scan'          => true,
			'show_tips'          => true,
			'track_improvements' => true,
		);

		$config = get_user_meta( $user_id, 'wpshadow_config_preferences', true );

		return is_array( $config ) ? wp_parse_args( $config, $defaults ) : $defaults;
	}

	/**
	 * Get user's privacy preferences
	 *
	 * @param int|null $user_id User ID (defaults to current user)
	 * @return array Privacy preferences
	 */
	public static function get_privacy_preferences( ?int $user_id = null ): array {
		if ( null === $user_id ) {
			$user_id = get_current_user_id();
		}

		$defaults = array(
			'email_critical'    => false,
			'email_weekly'      => false,
			'share_diagnostics' => false,
			'newsletter'        => false,
			'newsletter_email'  => '',
		);

		$privacy = get_user_meta( $user_id, 'wpshadow_privacy_preferences', true );

		return is_array( $privacy ) ? wp_parse_args( $privacy, $defaults ) : $defaults;
	}

	/**
	 * Enqueue onboarding manager assets.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	private static function enqueue_assets(): void {
		$version = defined( 'WPSHADOW_VERSION' ) ? WPSHADOW_VERSION : '1.0.0';

		wp_enqueue_style(
			'wpshadow-onboarding-manager',
			WPSHADOW_URL . 'assets/css/onboarding-manager.css',
			array(),
			$version
		);

		wp_enqueue_script(
			'wpshadow-onboarding-manager',
			WPSHADOW_URL . 'assets/js/onboarding-manager.js',
			array( 'jquery' ),
			$version,
			true
		);

		\WPShadow\Core\Admin_Asset_Registry::localize_with_ajax_nonce(
			'wpshadow-onboarding-manager',
			'wpsOnboardingManager',
			'wpshadow_onboarding'
		);
	}
}
