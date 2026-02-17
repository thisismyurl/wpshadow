<?php
/**
 * Workflow Suggestions Engine - Smart workflow recommendations based on site analysis
 *
 * @package WPShadow
 * @subpackage Workflow
 */

namespace WPShadow\Workflow;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Analyzes site and suggests relevant workflows
 * Philosophy: Helpful Neighbor (#1) - Anticipate needs, suggest solutions
 */
class Workflow_Suggestions {

	/**
	 * Get ALL personalized workflow suggestions based on site analysis (sorted by priority)
	 *
	 * @return array Array of all suggested workflows with reasoning
	 */
	public static function get_all_suggestions(): array {
		$suggestions = array();

		// Analyze site conditions
		$has_ecommerce    = self::has_ecommerce();
		$has_forms        = self::has_forms();
		$high_traffic     = self::is_high_traffic();
		$has_comments     = self::has_comments();
		$multisite        = is_multisite();
		$ssl_issues       = self::has_ssl_issues();
		$debug_on         = defined( 'WP_DEBUG' ) && WP_DEBUG;
		$high_memory      = self::check_memory_usage() > 75;
		$many_plugins     = self::count_active_plugins() > 30;
		$outdated_plugins = self::has_outdated_plugins();

		// 1. Security-Related Suggestions
		if ( $debug_on ) {
			$suggestions[] = array(
				'id'          => 'disable-debug-on-save',
				'title'       => __( 'Auto-Disable Debug Mode', 'wpshadow' ),
				'description' => __( 'Your site has WP_DEBUG enabled. Automatically disable it when publishing content to prevent information leakage.', 'wpshadow' ),
				'icon'        => 'dashicons-shield-alt',
				'color'       => '#d63638',
				'priority'    => 10,
				'reason'      => __( 'WP_DEBUG is currently enabled', 'wpshadow' ),
				'trigger'     => 'pre_publish_review',
				'actions'     => array( 'disable_debug_mode', 'send_admin_notification' ),
			);
		}

		if ( $ssl_issues ) {
			$suggestions[] = array(
				'id'          => 'daily-ssl-check',
				'title'       => __( 'Daily SSL Certificate Monitor', 'wpshadow' ),
				'description' => __( 'Your site has SSL configuration issues. Check SSL health daily and alert you before certificates expire.', 'wpshadow' ),
				'icon'        => 'dashicons-lock',
				'color'       => '#d63638',
				'priority'    => 10,
				'reason'      => __( 'SSL issues detected', 'wpshadow' ),
				'trigger'     => 'time_daily',
				'actions'     => array( 'check_ssl_health', 'send_admin_email' ),
			);
		}

		// 2. Performance-Related Suggestions
		if ( $high_memory || $many_plugins ) {
			$suggestions[] = array(
				'id'          => 'weekly-performance-audit',
				'title'       => __( 'Weekly Performance Health Check', 'wpshadow' ),
				'description' => sprintf(
					__( 'Your site uses %1$s memory and has %2$d active plugins. Run weekly performance audits to catch issues early.', 'wpshadow' ),
					self::get_memory_percentage() . '%',
					self::count_active_plugins()
				),
				'icon'        => 'dashicons-performance',
				'color'       => '#f0b849',
				'priority'    => 8,
				'reason'      => __( 'High resource usage detected', 'wpshadow' ),
				'trigger'     => 'time_weekly',
				'actions'     => array( 'run_performance_scan', 'log_results', 'send_admin_email' ),
			);
		}

		if ( $outdated_plugins ) {
			$suggestions[] = array(
				'id'          => 'plugin-update-reminder',
				'title'       => __( 'Weekly Plugin Update Reminder', 'wpshadow' ),
				'description' => __( 'You have outdated plugins. Get weekly reminders to keep everything up-to-date for security and compatibility.', 'wpshadow' ),
				'icon'        => 'dashicons-update',
				'color'       => '#f0b849',
				'priority'    => 7,
				'reason'      => __( 'Outdated plugins found', 'wpshadow' ),
				'trigger'     => 'time_weekly',
				'actions'     => array( 'check_plugin_updates', 'send_admin_notification' ),
			);
		}

		// 3. Content & Editorial Suggestions
		if ( $has_comments ) {
			$suggestions[] = array(
				'id'          => 'auto-scan-comments',
				'title'       => __( 'Auto-Scan New Comments', 'wpshadow' ),
				'description' => __( 'Your site receives comments. Automatically scan new comments for spam patterns and notify moderators of suspicious activity.', 'wpshadow' ),
				'icon'        => 'dashicons-admin-comments',
				'color'       => '#2271b1',
				'priority'    => 6,
				'reason'      => __( 'Site has active comments', 'wpshadow' ),
				'trigger'     => 'comment_posted',
				'actions'     => array( 'scan_comment_content', 'notify_if_suspicious' ),
			);
		}

		$suggestions[] = array(
			'id'          => 'pre-publish-checklist',
			'title'       => __( 'Pre-Publish Content Checklist', 'wpshadow' ),
			'description' => __( 'Run automated checks before publishing: broken links, missing images, SEO basics, and accessibility. Prevent common mistakes.', 'wpshadow' ),
			'icon'        => 'dashicons-yes-alt',
			'color'       => '#2271b1',
			'priority'    => 9,
			'reason'      => __( 'Recommended for all sites', 'wpshadow' ),
			'trigger'     => 'pre_publish_review',
			'actions'     => array( 'check_broken_links', 'check_images', 'accessibility_scan' ),
		);

		// 4. E-commerce Specific
		if ( $has_ecommerce ) {
			$suggestions[] = array(
				'id'          => 'daily-product-scan',
				'title'       => __( 'Daily Product Health Check', 'wpshadow' ),
				'description' => __( 'Your site sells products. Check daily for broken product images, missing prices, and out-of-stock items.', 'wpshadow' ),
				'icon'        => 'dashicons-cart',
				'color'       => '#00a32a',
				'priority'    => 9,
				'reason'      => __( 'E-commerce site detected', 'wpshadow' ),
				'trigger'     => 'time_daily',
				'actions'     => array( 'scan_products', 'check_stock_levels', 'send_admin_email' ),
			);
		}

		// 5. Forms & Lead Generation
		if ( $has_forms ) {
			$suggestions[] = array(
				'id'          => 'form-submission-backup',
				'title'       => __( 'Backup Form Submissions', 'wpshadow' ),
				'description' => __( 'Your site has forms. Create backups of form submissions to prevent data loss from plugin issues.', 'wpshadow' ),
				'icon'        => 'dashicons-feedback',
				'color'       => '#2271b1',
				'priority'    => 7,
				'reason'      => __( 'Contact forms detected', 'wpshadow' ),
				'trigger'     => 'form_submitted',
				'actions'     => array( 'backup_submission', 'log_activity' ),
			);
		}

		// 6. General Best Practices (Always Shown)
		$suggestions[] = array(
			'id'          => 'weekly-site-health',
			'title'       => __( 'Weekly Site Health Report', 'wpshadow' ),
			'description' => __( 'Get a comprehensive health report every Monday morning. Track trends, catch issues early, and stay informed about your site.', 'wpshadow' ),
			'icon'        => 'dashicons-heart',
			'color'       => '#00a32a',
			'priority'    => 8,
			'reason'      => __( 'Recommended for all sites', 'wpshadow' ),
			'trigger'     => 'time_weekly',
			'actions'     => array( 'run_full_scan', 'generate_report', 'send_admin_email' ),
		);

		if ( $high_traffic ) {
			$suggestions[] = array(
				'id'          => 'traffic-spike-monitor',
				'title'       => __( 'Traffic Spike Monitor', 'wpshadow' ),
				'description' => __( 'Your site gets significant traffic. Monitor for unusual traffic spikes that might indicate attacks or viral content.', 'wpshadow' ),
				'icon'        => 'dashicons-chart-line',
				'color'       => '#2271b1',
				'priority'    => 6,
				'reason'      => __( 'High-traffic site', 'wpshadow' ),
				'trigger'     => 'hourly_check',
				'actions'     => array( 'check_traffic_levels', 'send_admin_notification_if_spike' ),
			);
		}

		// Sort by priority (highest first)
		usort(
			$suggestions,
			function ( $a, $b ) {
				return $b['priority'] - $a['priority'];
			}
		);

		// Ensure we always have at least 3 suggestions (Issue #570)
		if ( count( $suggestions ) < 3 ) {
			// Add fallback suggestions if we don't have enough
			$fallback_suggestions = array(
				array(
					'id'          => 'weekly-site-health-fallback',
					'title'       => __( 'Weekly Site Health Report', 'wpshadow' ),
					'description' => __( 'Get a comprehensive health report every Monday morning. Track trends, catch issues early, and stay informed about your site.', 'wpshadow' ),
					'icon'        => 'dashicons-heart',
					'color'       => '#00a32a',
					'priority'    => 8,
					'reason'      => __( 'Recommended for all sites', 'wpshadow' ),
					'trigger'     => 'time_weekly',
					'actions'     => array( 'run_full_scan', 'generate_report', 'send_admin_email' ),
				),
				array(
					'id'          => 'backup-schedule-fallback',
					'title'       => __( 'Automated Daily Backups', 'wpshadow' ),
					'description' => __( 'Set up daily automatic backups of your entire site. Ensure you can recover quickly from any issue or emergency.', 'wpshadow' ),
					'icon'        => 'dashicons-cloud-saved',
					'color'       => '#2271b1',
					'priority'    => 9,
					'reason'      => __( 'Backup best practice', 'wpshadow' ),
					'trigger'     => 'time_daily_morning',
					'actions'     => array( 'run_full_backup', 'verify_backup', 'log_completion' ),
				),
				array(
					'id'          => 'security-scan-fallback',
					'title'       => __( 'Weekly Security Scan', 'wpshadow' ),
					'description' => __( 'Run comprehensive security scans every Thursday to check for malware, vulnerabilities, and suspicious activities.', 'wpshadow' ),
					'icon'        => 'dashicons-shield-alt',
					'color'       => '#d63638',
					'priority'    => 9,
					'reason'      => __( 'Security best practice', 'wpshadow' ),
					'trigger'     => 'time_weekly_thursday',
					'actions'     => array( 'run_security_scan', 'generate_security_report', 'send_admin_notification' ),
				),
			);

			// Add fallbacks until we have 3
			foreach ( $fallback_suggestions as $fallback ) {
				if ( count( $suggestions ) >= 3 ) {
					break;
				}
				// Only add if not already present
				$exists = array_filter(
					$suggestions,
					function ( $s ) use ( $fallback ) {
						return $s['id'] === $fallback['id'];
					}
				);
				if ( empty( $exists ) ) {
					$suggestions[] = $fallback;
				}
			}

			// Re-sort to maintain priority order
			usort(
				$suggestions,
				function ( $a, $b ) {
					return $b['priority'] - $a['priority'];
				}
			);
		}

		// Apply filter for extensibility (hook #1)
		return apply_filters( 'wpshadow_workflow_suggestions', $suggestions );
	}

	/**
	 * Get top 4 suggestions for initial display
	 *
	 * @return array Top 4 suggestions
	 */
	public static function get_suggestions(): array {
		$all_suggestions = self::get_all_suggestions();
		return array_slice( $all_suggestions, 0, 4 );
	}

	/**
	 * Get the next suggestion after the one just created
	 *
	 * @param string $created_suggestion_id ID of the suggestion that was just created
	 * @return array|null Next suggestion or null if no more available
	 */
	public static function get_next_suggestion( string $created_suggestion_id ): ?array {
		$all_suggestions = self::get_all_suggestions();
		$current_four    = self::get_suggestions();
		
		// Get all suggestion IDs that are currently shown (top 4)
		$current_ids = array_map(
			function ( $s ) {
				return $s['id'];
			},
			$current_four
		);
		
		// Find the index of created suggestion in current 4
		$index = array_search( $created_suggestion_id, $current_ids, true );
		
		if ( $index === false ) {
			return null;
		}
		
		// Get the first suggestion not in the top 4
		foreach ( $all_suggestions as $suggestion ) {
			if ( ! in_array( $suggestion['id'], $current_ids, true ) ) {
				return $suggestion;
			}
		}
		
		return null;
	}

	/**
	 * Check if site has e-commerce functionality
	 *
	 * @return bool
	 */
	private static function has_ecommerce(): bool {
		return class_exists( 'WooCommerce' ) || class_exists( 'Easy_Digital_Downloads' ) || function_exists( 'edd_get_option' );
	}

	/**
	 * Check if site has forms
	 *
	 * @return bool
	 */
	private static function has_forms(): bool {
		return class_exists( 'GFForms' ) || // Gravity Forms
				class_exists( 'WPCF7' ) || // Contact Form 7
				class_exists( 'Ninja_Forms' ) ||
				class_exists( 'FrmAppController' ); // Formidable Forms
	}

	/**
	 * Estimate if site is high traffic
	 *
	 * @return bool
	 */
	private static function is_high_traffic(): bool {
		// Check if popular caching plugins are installed (indicator of traffic)
		return class_exists( 'WP_Rocket' ) ||
				class_exists( 'WP_Cache_Manager' ) ||
				class_exists( 'WPO_Cache_Config' ) ||
				function_exists( 'w3tc_add_action' );
	}

	/**
	 * Check if site has active comments
	 *
	 * @return bool
	 */
	private static function has_comments(): bool {
		global $wpdb;
		$recent_comments = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_date > %s AND comment_approved = '1'",
				gmdate( 'Y-m-d H:i:s', strtotime( '-30 days' ) )
			)
		);
		return (int) $recent_comments > 10;
	}

	/**
	 * Check if site has SSL issues
	 *
	 * @return bool
	 */
	private static function has_ssl_issues(): bool {
		// Check if HTTPS is forced but certificate might have issues
		if ( ! is_ssl() && get_option( 'home' ) && strpos( get_option( 'home' ), 'https://' ) === 0 ) {
			return true; // Mixed content
		}

		// Check if site should use SSL but doesn't
		if ( ! is_ssl() && is_multisite() && is_subdomain_install() ) {
			return true; // Multisite without SSL
		}

		return false;
	}

	/**
	 * Check memory usage percentage
	 *
	 * @return int Memory usage percentage
	 */
	private static function check_memory_usage(): int {
		$memory_limit       = ini_get( 'memory_limit' );
		$memory_limit_bytes = self::convert_to_bytes( $memory_limit );
		$memory_usage       = memory_get_usage( true );

		if ( $memory_limit_bytes > 0 ) {
			return (int) ( ( $memory_usage / $memory_limit_bytes ) * 100 );
		}

		return 0;
	}

	/**
	 * Get memory usage as formatted percentage
	 *
	 * @return string Memory percentage (e.g., "45")
	 */
	private static function get_memory_percentage(): string {
		return (string) self::check_memory_usage();
	}

	/**
	 * Convert memory limit string to bytes
	 *
	 * @param string $size Memory size string
	 * @return int Bytes
	 */
	private static function convert_to_bytes( string $size ): int {
		$unit  = strtoupper( substr( $size, -1 ) );
		$value = (int) substr( $size, 0, -1 );

		switch ( $unit ) {
			case 'G':
				return $value * 1024 * 1024 * 1024;
			case 'M':
				return $value * 1024 * 1024;
			case 'K':
				return $value * 1024;
			default:
				return (int) $size;
		}
	}

	/**
	 * Count active plugins
	 *
	 * @return int Number of active plugins
	 */
	private static function count_active_plugins(): int {
		$active_plugins = get_option( 'active_plugins', array() );
		if ( is_multisite() ) {
			$network_plugins = get_site_option( 'active_sitewide_plugins', array() );
			$active_plugins  = array_merge( $active_plugins, array_keys( $network_plugins ) );
		}
		return count( $active_plugins );
	}

	/**
	 * Check if site has outdated plugins
	 *
	 * @return bool
	 */
	private static function has_outdated_plugins(): bool {
		if ( ! function_exists( 'get_plugin_updates' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
			require_once ABSPATH . 'wp-admin/includes/update.php';
		}

		$updates = get_plugin_updates();
		return ! empty( $updates ) && count( $updates ) > 2;
	}
}
