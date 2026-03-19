<?php
/**
 * Recommendation Engine - Context-Aware Feature Suggestions
 *
 * Provides proactive, contextual recommendations for WPShadow features.
 * Implements #1 Helpful Neighbor and #4 Advice, Not Sales.
 *
 * @package    WPShadow
 * @subpackage Recommendations
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Recommendations;

use WPShadow\Core\Activity_Logger;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Recommendation Engine Class
 *
 * Analyzes WordPress context and suggests relevant WPShadow features.
 *
 * @since 1.6093.1200
 */
class Recommendation_Engine {

	/**
	 * Initialize the recommendation engine.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function init() {
		// Hook into WordPress actions for context detection
		add_action( 'load-update-core.php', array( __CLASS__, 'recommend_before_update' ) );
		add_action( 'load-plugins.php', array( __CLASS__, 'recommend_conflict_detector' ) );
		add_action( 'load-themes.php', array( __CLASS__, 'recommend_theme_workflow' ) );
		add_action( 'admin_notices', array( __CLASS__, 'show_recommendations' ) );

		// AJAX endpoints
		add_action( 'wp_ajax_wpshadow_dismiss_recommendation', array( __CLASS__, 'ajax_dismiss' ) );
		add_action( 'wp_ajax_wpshadow_get_recommendations', array( __CLASS__, 'ajax_get_recommendations' ) );

		// Error detection (disabled - #3881 alert removed)
		// add_action( 'shutdown', array( __CLASS__, 'detect_errors' ) );
	}

	/**
	 * Recommend site cloning before WordPress update.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function recommend_before_update() {
		// Check if user has dismissed this recommendation recently
		$dismissed = \WPShadow\Core\Cache_Manager::get( 'dismissed_clone_before_update_' . get_current_user_id(), 'wpshadow_recommendations' );
		if ( $dismissed ) {
			return;
		}

		// Check if they've used site cloner recently
		$recent_clone = \WPShadow\Core\Cache_Manager::get( 'recent_clone_' . get_current_user_id(), 'wpshadow_recommendations' );
		if ( $recent_clone ) {
			return;
		}

		// Store recommendation
		self::store_recommendation(
			'clone-before-update',
			array(
				'title'       => __( '💡 Smart Tip: Clone Your Site First', 'wpshadow' ),
				'message'     => __( 'Before updating WordPress, create a staging clone to test the update safely. It takes 2 minutes and could save you hours of downtime.', 'wpshadow' ),
				'action_text' => __( 'Clone Site Now', 'wpshadow' ),
				'action_url'  => admin_url( 'admin.php?page=wpshadow-utilities&tab=site-cloner' ),
				'dismiss'     => true,
				'priority'    => 'high',
			)
		);
	}

	/**
	 * Recommend conflict detector on plugins page.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function recommend_conflict_detector() {
		// Only show if user has 10+ plugins
		$all_plugins = get_plugins();
		if ( count( $all_plugins ) < 10 ) {
			return;
		}

		// Check if dismissed
		$dismissed = \WPShadow\Core\Cache_Manager::get( 'dismissed_conflict_detector_' . get_current_user_id(), 'wpshadow_recommendations' );
		if ( $dismissed ) {
			return;
		}

		// Check if they've used it recently
		$used_recently = \WPShadow\Core\Cache_Manager::get( 'used_conflict_detector_' . get_current_user_id(), 'wpshadow_recommendations' );
		if ( $used_recently ) {
			return;
		}

		// Store recommendation
		self::store_recommendation(
			'conflict-detector',
			array(
				'title'       => __( '🔍 Having Issues? Try the Conflict Detector', 'wpshadow' ),
				'message'     => __( 'With 10+ plugins, conflicts can happen. Our Conflict Detector finds the culprit in 5 minutes using smart binary search. Completely free!', 'wpshadow' ),
				'action_text' => __( 'Check for Conflicts', 'wpshadow' ),
				'action_url'  => admin_url( 'admin.php?page=wpshadow-utilities&tab=plugin-conflict' ),
				'dismiss'     => true,
				'priority'    => 'medium',
			)
		);
	}

	/**
	 * Recommend theme workflow on themes page.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function recommend_theme_workflow() {
		// Check if dismissed
		$dismissed = \WPShadow\Core\Cache_Manager::get( 'dismissed_theme_workflow_' . get_current_user_id(), 'wpshadow_recommendations' );
		if ( $dismissed ) {
			return;
		}

		// Store recommendation
		self::store_recommendation(
			'theme-workflow',
			array(
				'title'       => __( '🎨 Changing Themes? Use Our Theme Setup Workflow', 'wpshadow' ),
				'message'     => __( 'Our "New Theme Setup" recipe guides you through cloning, testing, regenerating images, and deploying safely. Saves ~30 minutes.', 'wpshadow' ),
				'action_text' => __( 'Start Theme Workflow', 'wpshadow' ),
				'action_url'  => admin_url( 'admin.php?page=wpshadow-workflows&recipe=new-theme-setup' ),
				'dismiss'     => true,
				'priority'    => 'medium',
			)
		);
	}

	/**
	 * Detect fatal errors and recommend conflict detector.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function detect_errors() {
		$error = error_get_last();

		if ( ! $error || ! in_array( $error['type'], array( E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR ), true ) ) {
			return;
		}

		// Store error context
		update_option(
			'wpshadow_last_fatal_error',
			array(
				'error'     => $error,
				'timestamp' => current_time( 'timestamp' ),
			)
		);

		// Store recommendation (will show on next page load)
		self::store_recommendation(
			'error-detected',
			array(
				'title'       => __( '⚠️ Error Detected! Let\'s Find the Cause', 'wpshadow' ),
				'message'     => __( 'A fatal error was detected. Use our Plugin Conflict Detector to identify which plugin is causing the issue. Takes 5 minutes.', 'wpshadow' ),
				'action_text' => __( 'Find the Problem', 'wpshadow' ),
				'action_url'  => admin_url( 'admin.php?page=wpshadow-utilities&tab=plugin-conflict&auto_start=1' ),
				'dismiss'     => true,
				'priority'    => 'critical',
			)
		);
	}

	/**
	 * Store a recommendation.
	 *
	 * @since 1.6093.1200
	 * @param  string $id   Recommendation identifier.
	 * @param  array  $data Recommendation data.
	 * @return void
	 */
	private static function store_recommendation( $id, $data ) {
		$recommendations = get_option( 'wpshadow_pending_recommendations', array() );

		// Don't add if already exists
		if ( isset( $recommendations[ $id ] ) ) {
			return;
		}

		$recommendations[ $id ] = array_merge(
			$data,
			array(
				'id'         => $id,
				'created_at' => current_time( 'timestamp' ),
			)
		);

		update_option( 'wpshadow_pending_recommendations', $recommendations );
	}

	/**
	 * Get pending recommendations.
	 *
	 * @since 1.6093.1200
	 * @return array Pending recommendations sorted by priority.
	 */
	public static function get_recommendations() {
		$recommendations = get_option( 'wpshadow_pending_recommendations', array() );

		// Sort by priority
		$priority_order = array(
			'critical' => 1,
			'high'     => 2,
			'medium'   => 3,
			'low'      => 4,
		);

		usort(
			$recommendations,
			function ( $a, $b ) use ( $priority_order ) {
				$a_priority = $priority_order[ $a['priority'] ?? 'low' ] ?? 99;
				$b_priority = $priority_order[ $b['priority'] ?? 'low' ] ?? 99;
				return $a_priority - $b_priority;
			}
		);

		return $recommendations;
	}

	/**
	 * Show recommendation notices.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function show_recommendations() {
		// Check if another notice has already been shown (only one at a time)
		$shown_notice = get_transient( 'wpshadow_active_notice_' . get_current_user_id() );
		if ( ! empty( $shown_notice ) ) {
			return;
		}

		// Only show on WPShadow pages or dashboard
		$screen = get_current_screen();
		if ( ! $screen ) {
			return;
		}

		$show_on = array( 'dashboard', 'update-core', 'plugins', 'themes' );
		$is_wpshadow = strpos( $screen->id, 'wpshadow' ) !== false;

		if ( ! $is_wpshadow && ! in_array( $screen->base, $show_on, true ) ) {
			return;
		}

		$recommendations = self::get_recommendations();

		// Show only the top recommendation
		if ( empty( $recommendations ) ) {
			return;
		}

		$rec = $recommendations[0];

		// Mark this notice as active (blocks other notices)
		set_transient( 'wpshadow_active_notice_' . get_current_user_id(), 'recommendation_' . $rec['id'], HOUR_IN_SECONDS );

		// Determine notice class based on priority
		$notice_class = 'notice-info';
		if ( $rec['priority'] === 'critical' ) {
			$notice_class = 'notice-error';
		} elseif ( $rec['priority'] === 'high' ) {
			$notice_class = 'notice-warning';
		}

		?>
		<div class="notice <?php echo esc_attr( $notice_class ); ?> wpshadow-recommendation" data-recommendation-id="<?php echo esc_attr( $rec['id'] ); ?>">
			<div style="display: flex; align-items: center; padding: 10px 0;">
				<div style="flex: 1;">
					<h3 style="margin: 0 0 8px 0;"><?php echo esc_html( $rec['title'] ); ?></h3>
					<p style="margin: 0 0 10px 0;"><?php echo esc_html( $rec['message'] ); ?></p>
					<p style="margin: 0;">
						<a href="<?php echo esc_url( $rec['action_url'] ); ?>" class="button button-primary">
							<?php echo esc_html( $rec['action_text'] ); ?>
						</a>
						<?php if ( $rec['dismiss'] ) : ?>
						<button type="button" class="button wpshadow-dismiss-recommendation" data-recommendation-id="<?php echo esc_attr( $rec['id'] ); ?>">
							<?php esc_html_e( 'Not Now', 'wpshadow' ); ?>
						</button>
						<?php endif; ?>
					</p>
				</div>
			</div>
		</div>

		<script>
		jQuery(document).ready(function($) {
			$('.wpshadow-dismiss-recommendation').on('click', function() {
				var $button = $(this);
				var recId = $button.data('recommendation-id');
				var $notice = $button.closest('.wpshadow-recommendation');

				$.post(ajaxurl, {
					action: 'wpshadow_dismiss_recommendation',
					nonce: '<?php echo esc_js( wp_create_nonce( 'wpshadow_recommendation' ) ); ?>',
					recommendation_id: recId
				}, function(response) {
					if (response.success) {
						$notice.fadeOut();
					}
				});
			});
		});
		</script>
		<?php
	}

	/**
	 * Dismiss a recommendation.
	 *
	 * @since 1.6093.1200
	 * @param  string $id Recommendation identifier.
	 * @return void
	 */
	public static function dismiss_recommendation( $id ) {
		$recommendations = get_option( 'wpshadow_pending_recommendations', array() );

		// Remove recommendation
		if ( isset( $recommendations[ $id ] ) ) {
			unset( $recommendations[ $id ] );
			update_option( 'wpshadow_pending_recommendations', $recommendations );
		}

		// Set dismissal cache (don't show again for 30 days)
		\WPShadow\Core\Cache_Manager::set(
			'dismissed_' . $id . '_' . get_current_user_id(),
			true,
			30 * DAY_IN_SECONDS,
			'wpshadow_recommendations'
			);

		// Clear the active notice transient so other notices can show
		delete_transient( 'wpshadow_active_notice_' . get_current_user_id() );

		// Log dismissal
		Activity_Logger::log(
			'recommendation_dismissed',
			array(
				'recommendation_id' => $id,
			)
		);
	}

	/**
	 * AJAX: Dismiss recommendation.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function ajax_dismiss() {
		\WPShadow\Core\Security_Validator::verify_request( 'wpshadow_recommendation', 'manage_options', 'nonce' );

		$rec_id = isset( $_POST['recommendation_id'] ) ? sanitize_key( $_POST['recommendation_id'] ) : '';

		if ( empty( $rec_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid recommendation ID', 'wpshadow' ) ) );
		}

		self::dismiss_recommendation( $rec_id );

		wp_send_json_success(
			array(
				'message' => __( 'Recommendation dismissed', 'wpshadow' ),
			)
		);
	}

	/**
	 * AJAX: Get recommendations.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function ajax_get_recommendations() {
		\WPShadow\Core\Security_Validator::verify_request( 'wpshadow_recommendation', 'manage_options', 'nonce' );

		$recommendations = self::get_recommendations();

		wp_send_json_success(
			array(
				'recommendations' => $recommendations,
			)
		);
	}
}
