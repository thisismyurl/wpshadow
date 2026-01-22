<?php
declare(strict_types=1);

namespace WPShadow\Onboarding;

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
 * @since 1.2601.2201
 * @package WPShadow
 */
class Onboarding_Manager {
	
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
	 * Initialize onboarding system
	 */
	public static function init(): void {
		// Register AJAX handlers
		add_action( 'wp_ajax_wpshadow_save_onboarding', [ __CLASS__, 'ajax_save_onboarding' ] );
		add_action( 'wp_ajax_wpshadow_skip_onboarding', [ __CLASS__, 'ajax_skip_onboarding' ] );
		add_action( 'wp_ajax_wpshadow_dismiss_term', [ __CLASS__, 'ajax_dismiss_term' ] );
		add_action( 'wp_ajax_wpshadow_show_all_features', [ __CLASS__, 'ajax_show_all_features' ] );
		
		// Track user actions for graduation
		add_action( 'save_post', [ __CLASS__, 'track_action' ] );
		add_action( 'updated_option', [ __CLASS__, 'track_action' ] );
		add_action( 'wp_insert_comment', [ __CLASS__, 'track_action' ] );
		
		// Check if should show graduation
		add_action( 'admin_notices', [ __CLASS__, 'maybe_show_graduation' ] );
		
		// Add settings page integration
		add_action( 'wpshadow_settings_sections', [ __CLASS__, 'add_settings_section' ] );
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
			return ! empty( $platform ) && 'wordpress' !== $platform;
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
				<button type="button" class="button button-primary" id="wpshadow-graduate-btn">
					<?php esc_html_e( 'Show Me Everything', 'wpshadow' ); ?>
				</button>
				<button type="button" class="button" id="wpshadow-graduate-later">
					<?php esc_html_e( 'Maybe Later', 'wpshadow' ); ?>
				</button>
				<a href="https://wpshadow.com/kb/wordpress-graduation/" target="_blank" style="margin-left: 15px;">
					<?php esc_html_e( 'Learn More', 'wpshadow' ); ?>
				</a>
			</p>
		</div>
		<script>
		jQuery(document).ready(function($) {
			$('#wpshadow-graduate-btn').on('click', function() {
				$.post(ajaxurl, {
					action: 'wpshadow_show_all_features',
					nonce: '<?php echo esc_js( wp_create_nonce( 'wpshadow_onboarding' ) ); ?>'
				}, function() {
					location.reload();
				});
			});
			
			$('#wpshadow-graduate-later').on('click', function() {
				$.post(ajaxurl, {
					action: 'wpshadow_dismiss_graduation',
					nonce: '<?php echo esc_js( wp_create_nonce( 'wpshadow_onboarding' ) ); ?>'
				}, function() {
					$('.wpshadow-graduation-notice').fadeOut();
				});
			});
		});
		</script>
		<?php
	}
	
	/**
	 * AJAX: Save onboarding preferences
	 * 
	 * @return void
	 */
	public static function ajax_save_onboarding(): void {
		check_ajax_referer( 'wpshadow_onboarding', 'nonce' );
		
		if ( ! current_user_can( 'read' ) ) {
			wp_send_json_error( __( 'Permission denied', 'wpshadow' ) );
		}
		
		$user_id = get_current_user_id();
		$platform = sanitize_key( $_POST['platform'] ?? '' );
		$comfort_level = sanitize_key( $_POST['comfort_level'] ?? '' );
		
		// Validate platform
		$valid_platforms = [ 'wordpress', 'word', 'wix', 'none' ];
		if ( ! in_array( $platform, $valid_platforms, true ) ) {
			wp_send_json_error( __( 'Invalid platform selected', 'wpshadow' ) );
		}
		
		// Validate comfort level
		$valid_comfort = [ 'learning', 'comfortable', 'expert' ];
		if ( ! in_array( $comfort_level, $valid_comfort, true ) ) {
			wp_send_json_error( __( 'Invalid comfort level selected', 'wpshadow' ) );
		}
		
		// Save preferences
		update_user_meta( $user_id, self::META_PLATFORM, $platform );
		update_user_meta( $user_id, self::META_COMFORT_LEVEL, $comfort_level );
		update_user_meta( $user_id, self::META_ONBOARDING_COMPLETE, time() );
		
		// Set UI simplification based on platform
		$simplified = ( 'wordpress' !== $platform );
		update_user_meta( $user_id, self::META_UI_SIMPLIFIED, $simplified );
		
		// Track KPI
		if ( class_exists( '\WPShadow\Core\KPI_Tracker' ) ) {
			\WPShadow\Core\KPI_Tracker::record_custom_event( 'onboarding_completed', [
				'platform'      => $platform,
				'comfort_level' => $comfort_level,
			] );
		}
		
		// Fire action for Pro module integration
		do_action( 'wpshadow_onboarding_completed', $user_id, $platform, $comfort_level );
		
		wp_send_json_success( [
			'message'    => __( 'Great! Your workspace is ready.', 'wpshadow' ),
			'simplified' => $simplified,
		] );
	}
	
	/**
	 * AJAX: Skip onboarding
	 * 
	 * @return void
	 */
	public static function ajax_skip_onboarding(): void {
		check_ajax_referer( 'wpshadow_onboarding', 'nonce' );
		
		if ( ! current_user_can( 'read' ) ) {
			wp_send_json_error( __( 'Permission denied', 'wpshadow' ) );
		}
		
		$user_id = get_current_user_id();
		update_user_meta( $user_id, self::META_ONBOARDING_COMPLETE, time() );
		update_user_meta( $user_id, self::META_UI_SIMPLIFIED, false );
		
		wp_send_json_success();
	}
	
	/**
	 * AJAX: Dismiss terminology term
	 * 
	 * @return void
	 */
	public static function ajax_dismiss_term(): void {
		check_ajax_referer( 'wpshadow_onboarding', 'nonce' );
		
		if ( ! current_user_can( 'read' ) ) {
			wp_send_json_error( __( 'Permission denied', 'wpshadow' ) );
		}
		
		$user_id = get_current_user_id();
		$term = sanitize_key( $_POST['term'] ?? '' );
		
		if ( empty( $term ) ) {
			wp_send_json_error( __( 'Invalid term', 'wpshadow' ) );
		}
		
		$dismissed = get_user_meta( $user_id, self::META_DISMISSED_TERMS, true ) ?: [];
		$dismissed[] = $term;
		update_user_meta( $user_id, self::META_DISMISSED_TERMS, array_unique( $dismissed ) );
		
		wp_send_json_success();
	}
	
	/**
	 * AJAX: Show all features (graduate)
	 * 
	 * @return void
	 */
	public static function ajax_show_all_features(): void {
		check_ajax_referer( 'wpshadow_onboarding', 'nonce' );
		
		if ( ! current_user_can( 'read' ) ) {
			wp_send_json_error( __( 'Permission denied', 'wpshadow' ) );
		}
		
		$user_id = get_current_user_id();
		update_user_meta( $user_id, self::META_UI_SIMPLIFIED, false );
		
		// Track graduation KPI
		if ( class_exists( '\WPShadow\Core\KPI_Tracker' ) ) {
			\WPShadow\Core\KPI_Tracker::record_custom_event( 'onboarding_graduated', [
				'action_count' => self::get_action_count( $user_id ),
			] );
		}
		
		wp_send_json_success();
	}
	
	/**
	 * Add onboarding settings section
	 * 
	 * @return void
	 */
	public static function add_settings_section(): void {
		?>
		<div class="wpshadow-settings-section">
			<h3><?php esc_html_e( 'Onboarding & Learning', 'wpshadow' ); ?></h3>
			<p><?php esc_html_e( 'Customize your WordPress learning experience', 'wpshadow' ); ?></p>
			
			<?php
			$user_id = get_current_user_id();
			$platform = self::get_user_platform( $user_id );
			$comfort = self::get_comfort_level( $user_id );
			$simplified = self::is_ui_simplified( $user_id );
			?>
			
			<table class="form-table">
				<tr>
					<th><?php esc_html_e( 'Your Background', 'wpshadow' ); ?></th>
					<td>
						<?php if ( $platform ) : ?>
							<?php
							$platform_labels = [
								'wordpress' => __( 'WordPress (experienced)', 'wpshadow' ),
								'word'      => __( 'Microsoft Word', 'wpshadow' ),
								'wix'       => __( 'Wix', 'wpshadow' ),
								'none'      => __( 'New to all of this', 'wpshadow' ),
							];
							echo esc_html( $platform_labels[ $platform ] ?? $platform );
							?>
						<?php else : ?>
							<em><?php esc_html_e( 'Not set', 'wpshadow' ); ?></em>
						<?php endif; ?>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow&onboarding=restart' ) ); ?>" style="margin-left: 15px;">
							<?php esc_html_e( 'Change', 'wpshadow' ); ?>
						</a>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Simplified Interface', 'wpshadow' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="wpshadow_ui_simplified" value="1" <?php checked( $simplified ); ?> />
							<?php esc_html_e( 'Show only essential features (recommended for beginners)', 'wpshadow' ); ?>
						</label>
						<p class="description">
							<?php esc_html_e( 'Hide advanced WordPress features until you\'re ready. You can toggle this anytime.', 'wpshadow' ); ?>
						</p>
					</td>
				</tr>
			</table>
		</div>
		<?php
	}
}
