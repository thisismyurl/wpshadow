<?php
/**
 * Smart Suggestions System
 *
 * Analyzes activity logs and feature usage to provide intelligent
 * suggestions for optimizing WordPress sites.
 *
 * @package    WP_Support
 * @subpackage CoreSupport
 * @since      1.2601.76000
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPSHADOW_Smart_Suggestions Class
 *
 * Provides intelligent suggestions based on activity logs and feature usage patterns.
 */
class WPSHADOW_Smart_Suggestions {

	/**
	 * Suggestion types.
	 */
	public const TYPE_PERFORMANCE = 'performance';
	public const TYPE_SECURITY    = 'security';
	public const TYPE_OPTIMIZATION = 'optimization';
	public const TYPE_MAINTENANCE = 'maintenance';
	public const TYPE_FEATURE     = 'feature';

	/**
	 * Initialize the smart suggestions system.
	 *
	 * @return void
	 */
	public static function init(): void {
		// Add suggestions widget to dashboard.
		add_action( 'wp_dashboard_setup', array( __CLASS__, 'add_dashboard_widget' ) );

		// Generate suggestions daily.
		if ( ! wp_next_scheduled( 'wpshadow_generate_suggestions' ) ) {
			wp_schedule_event( time(), 'daily', 'wpshadow_generate_suggestions' );
		}
		add_action( 'wpshadow_generate_suggestions', array( __CLASS__, 'generate_suggestions' ) );

		// AJAX handler for dismissing suggestions.
		add_action( 'wp_ajax_wpshadow_dismiss_suggestion', array( __CLASS__, 'ajax_dismiss_suggestion' ) );
	}

	/**
	 * Add suggestions dashboard widget.
	 *
	 * @return void
	 */
	public static function add_dashboard_widget(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		wp_add_dashboard_widget(
			'wpshadow_smart_suggestions',
			__( 'WPShadow Smart Suggestions', 'plugin-wpshadow' ),
			array( __CLASS__, 'render_dashboard_widget' )
		);
	}

	/**
	 * Render suggestions dashboard widget.
	 *
	 * @return void
	 */
	public static function render_dashboard_widget(): void {
		$suggestions = self::get_active_suggestions();

		if ( empty( $suggestions ) ) {
			echo '<p><em>' . esc_html__( 'No suggestions at this time. Your site is running smoothly!', 'plugin-wpshadow' ) . '</em></p>';
			return;
		}

		echo '<div class="wpshadow-suggestions-list">';
		foreach ( $suggestions as $suggestion ) {
			self::render_suggestion( $suggestion );
		}
		echo '</div>';

		self::enqueue_suggestion_styles();
	}

	/**
	 * Render a single suggestion.
	 *
	 * @param array $suggestion Suggestion data.
	 * @return void
	 */
	private static function render_suggestion( array $suggestion ): void {
		$type = $suggestion['type'] ?? self::TYPE_FEATURE;
		$icon = self::get_type_icon( $type );
		$color = self::get_type_color( $type );
		?>
		<div class="wpshadow-suggestion" data-suggestion-id="<?php echo esc_attr( $suggestion['id'] ?? '' ); ?>" style="border-left: 4px solid <?php echo esc_attr( $color ); ?>; padding: 12px; margin-bottom: 12px; background: #f9f9f9;">
			<div style="display: flex; align-items: start; gap: 10px;">
				<span class="dashicons <?php echo esc_attr( $icon ); ?>" style="color: <?php echo esc_attr( $color ); ?>; font-size: 20px; margin-top: 2px;"></span>
				<div style="flex: 1;">
					<h4 style="margin: 0 0 8px 0; font-size: 14px;">
						<?php echo esc_html( $suggestion['title'] ?? '' ); ?>
					</h4>
					<p style="margin: 0 0 10px 0; color: #666; font-size: 13px;">
						<?php echo esc_html( $suggestion['description'] ?? '' ); ?>
					</p>
					<?php if ( ! empty( $suggestion['action_url'] ) ) : ?>
						<a href="<?php echo esc_url( $suggestion['action_url'] ); ?>" class="button button-small button-primary">
							<?php echo esc_html( $suggestion['action_text'] ?? __( 'Take Action', 'plugin-wpshadow' ) ); ?>
						</a>
					<?php endif; ?>
					<button type="button" class="button button-small button-link wpshadow-dismiss-suggestion" data-suggestion-id="<?php echo esc_attr( $suggestion['id'] ?? '' ); ?>" style="margin-left: 8px;">
						<?php esc_html_e( 'Dismiss', 'plugin-wpshadow' ); ?>
					</button>
					<?php if ( ! empty( $suggestion['evidence'] ) ) : ?>
						<p style="margin: 10px 0 0 0; font-size: 12px; color: #999;">
							<strong><?php esc_html_e( 'Why?', 'plugin-wpshadow' ); ?></strong> <?php echo esc_html( $suggestion['evidence'] ); ?>
						</p>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Get icon for suggestion type.
	 *
	 * @param string $type Suggestion type.
	 * @return string Dashicon class.
	 */
	private static function get_type_icon( string $type ): string {
		$icons = array(
			self::TYPE_PERFORMANCE  => 'dashicons-performance',
			self::TYPE_SECURITY     => 'dashicons-shield-alt',
			self::TYPE_OPTIMIZATION => 'dashicons-chart-line',
			self::TYPE_MAINTENANCE  => 'dashicons-admin-tools',
			self::TYPE_FEATURE      => 'dashicons-lightbulb',
		);

		return $icons[ $type ] ?? 'dashicons-info';
	}

	/**
	 * Get color for suggestion type.
	 *
	 * @param string $type Suggestion type.
	 * @return string Hex color.
	 */
	private static function get_type_color( string $type ): string {
		$colors = array(
			self::TYPE_PERFORMANCE  => '#0073aa',
			self::TYPE_SECURITY     => '#d63638',
			self::TYPE_OPTIMIZATION => '#00a32a',
			self::TYPE_MAINTENANCE  => '#dba617',
			self::TYPE_FEATURE      => '#9b51e0',
		);

		return $colors[ $type ] ?? '#646970';
	}

	/**
	 * Generate suggestions based on activity logs.
	 *
	 * @return array Generated suggestions.
	 */
	public static function generate_suggestions(): array {
		$suggestions = array();

		// Analyze head cleanup activity.
		$suggestions = array_merge( $suggestions, self::analyze_head_cleanup() );

		// Analyze page cache usage.
		$suggestions = array_merge( $suggestions, self::analyze_page_cache() );

		// Analyze security events.
		$suggestions = array_merge( $suggestions, self::analyze_security() );

		// Analyze image optimization.
		$suggestions = array_merge( $suggestions, self::analyze_images() );

		// Analyze database cleanup.
		$suggestions = array_merge( $suggestions, self::analyze_database() );

		// Store suggestions.
		update_option( 'wpshadow_smart_suggestions', $suggestions );

		return $suggestions;
	}

	/**
	 * Analyze head cleanup activity for suggestions.
	 *
	 * @return array Suggestions.
	 */
	private static function analyze_head_cleanup(): array {
		$suggestions = array();

		// Get head cleanup feature activity.
		$events = WPSHADOW_Activity_Logger::get_events_by_module( 'head-cleanup', 100 );

		// Count events in last 24 hours.
		$recent_count = 0;
		$cutoff_time = time() - DAY_IN_SECONDS;

		foreach ( $events as $event ) {
			if ( isset( $event['timestamp'] ) && $event['timestamp'] > $cutoff_time ) {
				++$recent_count;
			}
		}

		// If head cleanup is firing 40+ times per day, suggest caching.
		if ( $recent_count >= 40 ) {
			$cache_enabled = WPSHADOW_Feature_Registry::is_feature_enabled( 'page-cache' );

			if ( ! $cache_enabled ) {
				$suggestions[] = array(
					'id'          => 'enable_page_cache_head_cleanup',
					'type'        => self::TYPE_PERFORMANCE,
					'title'       => __( 'Enable Page Caching', 'plugin-wpshadow' ),
					'description' => __( 'Your site is processing many page requests. Enabling page caching can significantly improve performance and reduce server load.', 'plugin-wpshadow' ),
					'action_text' => __( 'Enable Page Cache', 'plugin-wpshadow' ),
					'action_url'  => admin_url( 'admin.php?page=wpshadow-features&feature=page-cache' ),
					'evidence'    => sprintf(
						/* translators: %d: number of cleanup events */
						__( 'Head cleanup ran %d times in the last 24 hours, indicating high page request volume.', 'plugin-wpshadow' ),
						$recent_count
					),
					'priority'    => 8,
					'created_at'  => time(),
				);
			}
		}

		return $suggestions;
	}

	/**
	 * Analyze page cache usage for suggestions.
	 *
	 * @return array Suggestions.
	 */
	private static function analyze_page_cache(): array {
		$suggestions = array();

		// Check if page cache is enabled.
		$cache_enabled = WPSHADOW_Feature_Registry::is_feature_enabled( 'page-cache' );

		if ( $cache_enabled ) {
			// Check cache hit rate (if available from logs).
			$events = WPSHADOW_Activity_Logger::get_events_by_module( 'page-cache', 100 );

			$hits = 0;
			$misses = 0;

			foreach ( $events as $event ) {
				if ( isset( $event['metadata']['cache_hit'] ) ) {
					if ( $event['metadata']['cache_hit'] ) {
						++$hits;
					} else {
						++$misses;
					}
				}
			}

			$total = $hits + $misses;

			if ( $total > 50 ) {
				$hit_rate = ( $hits / $total ) * 100;

				// If hit rate is below 50%, suggest optimization.
				if ( $hit_rate < 50 ) {
					$suggestions[] = array(
						'id'          => 'optimize_cache_settings',
						'type'        => self::TYPE_OPTIMIZATION,
						'title'       => __( 'Optimize Cache Settings', 'plugin-wpshadow' ),
						'description' => __( 'Your cache hit rate is low. Consider increasing cache expiration time or excluding dynamic pages from caching.', 'plugin-wpshadow' ),
						'action_text' => __( 'Configure Cache', 'plugin-wpshadow' ),
						'action_url'  => admin_url( 'admin.php?page=wpshadow-features&feature=page-cache' ),
						'evidence'    => sprintf(
							/* translators: %s: cache hit rate percentage */
							__( 'Cache hit rate is only %s%%, which means most requests are not being served from cache.', 'plugin-wpshadow' ),
							number_format( $hit_rate, 1 )
						),
						'priority'    => 7,
						'created_at'  => time(),
					);
				}
			}
		}

		return $suggestions;
	}

	/**
	 * Analyze security events for suggestions.
	 *
	 * @return array Suggestions.
	 */
	private static function analyze_security(): array {
		$suggestions = array();

		// Check for failed login attempts.
		$events = WPSHADOW_Activity_Logger::get_events();
		$failed_logins = 0;

		foreach ( $events as $event ) {
			if ( isset( $event['type'] ) && 'failed_login' === $event['type'] ) {
				++$failed_logins;
			}
		}

		// If 10+ failed logins, suggest brute force protection.
		if ( $failed_logins >= 10 ) {
			$brute_force_enabled = WPSHADOW_Feature_Registry::is_feature_enabled( 'brute-force-protection' );

			if ( ! $brute_force_enabled ) {
				$suggestions[] = array(
					'id'          => 'enable_brute_force_protection',
					'type'        => self::TYPE_SECURITY,
					'title'       => __( 'Enable Brute Force Protection', 'plugin-wpshadow' ),
					'description' => __( 'Multiple failed login attempts detected. Enable brute force protection to block malicious login attempts.', 'plugin-wpshadow' ),
					'action_text' => __( 'Enable Protection', 'plugin-wpshadow' ),
					'action_url'  => admin_url( 'admin.php?page=wpshadow-features&feature=brute-force-protection' ),
					'evidence'    => sprintf(
						/* translators: %d: number of failed logins */
						__( '%d failed login attempts detected in recent activity.', 'plugin-wpshadow' ),
						$failed_logins
					),
					'priority'    => 9,
					'created_at'  => time(),
				);
			}
		}

		return $suggestions;
	}

	/**
	 * Analyze image optimization events.
	 *
	 * @return array Suggestions.
	 */
	private static function analyze_images(): array {
		$suggestions = array();

		// Check if image optimizer is enabled.
		$optimizer_enabled = WPSHADOW_Feature_Registry::is_feature_enabled( 'image-optimizer' );

		if ( ! $optimizer_enabled ) {
			// Check media library for large images.
			$large_images = self::count_large_images();

			if ( $large_images > 10 ) {
				$suggestions[] = array(
					'id'          => 'enable_image_optimizer',
					'type'        => self::TYPE_OPTIMIZATION,
					'title'       => __( 'Optimize Your Images', 'plugin-wpshadow' ),
					'description' => __( 'Large unoptimized images can slow down your site. Enable image optimization to reduce file sizes without losing quality.', 'plugin-wpshadow' ),
					'action_text' => __( 'Enable Image Optimizer', 'plugin-wpshadow' ),
					'action_url'  => admin_url( 'admin.php?page=wpshadow-features&feature=image-optimizer' ),
					'evidence'    => sprintf(
						/* translators: %d: number of large images */
						__( 'Found %d images larger than 500KB in your media library.', 'plugin-wpshadow' ),
						$large_images
					),
					'priority'    => 6,
					'created_at'  => time(),
				);
			}
		}

		return $suggestions;
	}

	/**
	 * Analyze database cleanup needs.
	 *
	 * @return array Suggestions.
	 */
	private static function analyze_database(): array {
		$suggestions = array();

		// Check for database bloat.
		global $wpdb;

		// Count revisions.
		$revision_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'revision'" );

		// Count trashed posts.
		$trash_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status = 'trash'" );

		if ( $revision_count > 100 || $trash_count > 50 ) {
			$cleanup_enabled = WPSHADOW_Feature_Registry::is_feature_enabled( 'database-cleanup' );

			if ( ! $cleanup_enabled ) {
				$suggestions[] = array(
					'id'          => 'enable_database_cleanup',
					'type'        => self::TYPE_MAINTENANCE,
					'title'       => __( 'Clean Up Your Database', 'plugin-wpshadow' ),
					'description' => __( 'Your database contains many old revisions and trashed items. Regular cleanup can improve performance.', 'plugin-wpshadow' ),
					'action_text' => __( 'Enable Database Cleanup', 'plugin-wpshadow' ),
					'action_url'  => admin_url( 'admin.php?page=wpshadow-features&feature=database-cleanup' ),
					'evidence'    => sprintf(
						/* translators: 1: revision count, 2: trash count */
						__( 'Found %1$d old revisions and %2$d trashed items in your database.', 'plugin-wpshadow' ),
						$revision_count,
						$trash_count
					),
					'priority'    => 5,
					'created_at'  => time(),
				);
			}
		}

		return $suggestions;
	}

	/**
	 * Count large images in media library.
	 *
	 * @return int Number of large images.
	 */
	private static function count_large_images(): int {
		$upload_dir = wp_upload_dir();
		$base_dir = $upload_dir['basedir'];

		if ( ! is_dir( $base_dir ) ) {
			return 0;
		}

		$large_count = 0;
		$max_size = 500 * 1024; // 500KB

		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $base_dir, \RecursiveDirectoryIterator::SKIP_DOTS )
		);

		foreach ( $iterator as $file ) {
			if ( $file->isFile() ) {
				$extension = strtolower( $file->getExtension() );
				if ( in_array( $extension, array( 'jpg', 'jpeg', 'png', 'gif', 'webp' ), true ) ) {
					if ( $file->getSize() > $max_size ) {
						++$large_count;
						// Limit check to 100 files to avoid performance issues.
						if ( $large_count >= 100 ) {
							break;
						}
					}
				}
			}
		}

		return $large_count;
	}

	/**
	 * Get active suggestions (not dismissed).
	 *
	 * @return array Active suggestions sorted by priority.
	 */
	public static function get_active_suggestions(): array {
		$all_suggestions = get_option( 'wpshadow_smart_suggestions', array() );
		$dismissed = get_option( 'wpshadow_dismissed_suggestions', array() );

		// Filter out dismissed suggestions.
		$active = array_filter(
			$all_suggestions,
			function( $suggestion ) use ( $dismissed ) {
				return ! in_array( $suggestion['id'] ?? '', $dismissed, true );
			}
		);

		// Sort by priority (higher first).
		usort(
			$active,
			function( $a, $b ) {
				$priority_a = $a['priority'] ?? 0;
				$priority_b = $b['priority'] ?? 0;
				return $priority_b - $priority_a;
			}
		);

		return array_values( $active );
	}

	/**
	 * AJAX handler for dismissing suggestions.
	 *
	 * @return void
	 */
	public static function ajax_dismiss_suggestion(): void {
		check_ajax_referer( 'wpshadow_suggestions', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You don\'t have permission to do that', 'plugin-wpshadow' ) ) );
		}

		$suggestion_id = isset( $_POST['suggestion_id'] ) ? sanitize_text_field( wp_unslash( $_POST['suggestion_id'] ) ) : '';

		if ( empty( $suggestion_id ) ) {
			wp_send_json_error( array( 'message' => __( 'That suggestion doesn\'t exist', 'plugin-wpshadow' ) ) );
		}

		$dismissed = get_option( 'wpshadow_dismissed_suggestions', array() );
		$dismissed[] = $suggestion_id;
		update_option( 'wpshadow_dismissed_suggestions', array_unique( $dismissed ) );

		wp_send_json_success( array( 'message' => __( 'Suggestion dismissed', 'plugin-wpshadow' ) ) );
	}

	/**
	 * Enqueue styles for suggestions widget.
	 *
	 * @return void
	 */
	private static function enqueue_suggestion_styles(): void {
		?>
		<script type="text/javascript">
		jQuery(document).ready(function($) {
			$('.wpshadow-dismiss-suggestion').on('click', function(e) {
				e.preventDefault();
				var $button = $(this);
				var suggestionId = $button.data('suggestion-id');
				
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'wpshadow_dismiss_suggestion',
						nonce: '<?php echo esc_js( wp_create_nonce( 'wpshadow_suggestions' ) ); ?>',
						suggestion_id: suggestionId
					},
					success: function() {
						$button.closest('.wpshadow-suggestion').fadeOut(300, function() {
							$(this).remove();
							if ($('.wpshadow-suggestion').length === 0) {
								$('.wpshadow-suggestions-list').html('<p><em><?php echo esc_js( __( 'No suggestions at this time.', 'plugin-wpshadow' ) ); ?></em></p>');
							}
						});
					}
				});
			});
		});
		</script>
		<?php
	}
}
