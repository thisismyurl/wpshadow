<?php
/**
 * Transient Cleanup Automation Diagnostic
 *
 * Tests if expired transients are being cleaned automatically.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.7034.1110
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Transient Cleanup Automation Diagnostic Class
 *
 * Validates that expired transients are cleaned automatically to
 * prevent database bloat and performance degradation.
 *
 * @since 1.7034.1110
 */
class Diagnostic_Transient_Cleanup_Automation extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'transient-cleanup-automation';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Transient Cleanup Automation';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if expired transients are being cleaned automatically';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests if transients are accumulating in the database and
	 * if automatic cleanup mechanisms are configured.
	 *
	 * @since  1.7034.1110
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		global $wpdb;

		// Count all transients.
		$total_transients = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->options} 
			 WHERE option_name LIKE '_transient_%'"
		);

		// Count expired transients.
		$expired_transients = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->options} 
				 WHERE option_name LIKE %s 
				 AND option_value < %d",
				'_transient_timeout_%',
				time()
			)
		);

		// Count transient timeout entries.
		$timeout_transients = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->options} 
			 WHERE option_name LIKE '_transient_timeout_%'"
		);

		// Calculate storage size of transients.
		$transient_size = $wpdb->get_var(
			"SELECT SUM(LENGTH(option_value)) FROM {$wpdb->options} 
			 WHERE option_name LIKE '_transient_%'"
		);
		$transient_mb = $transient_size ? round( $transient_size / ( 1024 * 1024 ), 2 ) : 0;

		// Check for cleanup plugins.
		$has_cleanup_plugin = is_plugin_active( 'transient-cleaner/transient-cleaner.php' ) ||
							 is_plugin_active( 'wp-optimize/wp-optimize.php' ) ||
							 is_plugin_active( 'advanced-database-cleaner/advanced-db-cleaner.php' );

		// Check for WP Cron (handles transient cleanup).
		$cron_disabled = defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON;

		// Check for transient cleanup scheduled events.
		$cron_schedules = wp_get_schedules();
		$has_cleanup_cron = wp_next_scheduled( 'delete_expired_transients' ) !== false;

		// Check if object cache is enabled (external transients).
		$uses_object_cache = wp_using_ext_object_cache();

		// Get site transient count (network-wide on multisite).
		$site_transients = 0;
		if ( is_multisite() ) {
			$site_transients = $wpdb->get_var(
				"SELECT COUNT(*) FROM {$wpdb->sitemeta} 
				 WHERE meta_key LIKE '_site_transient_%'"
			);
		}

		// Calculate transient orphans (timeout without value).
		$orphan_timeouts = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->options} t1
			 WHERE t1.option_name LIKE '_transient_timeout_%'
			 AND NOT EXISTS (
				SELECT 1 FROM {$wpdb->options} t2
				WHERE t2.option_name = REPLACE(t1.option_name, '_timeout_', '_')
			 )"
		);

		// Check most common transient prefixes.
		$common_transients = $wpdb->get_results(
			"SELECT SUBSTRING_INDEX(SUBSTRING(option_name, 12), '_', 1) as prefix, COUNT(*) as count
			 FROM {$wpdb->options}
			 WHERE option_name LIKE '_transient_%' 
			 AND option_name NOT LIKE '_transient_timeout_%'
			 GROUP BY prefix
			 ORDER BY count DESC
			 LIMIT 5",
			ARRAY_A
		);

		// Check for issues.
		$issues = array();

		// Issue 1: Excessive transients stored.
		if ( absint( $total_transients ) > 1000 ) {
			$issues[] = array(
				'type'        => 'transient_bloat',
				'description' => sprintf(
					/* translators: %s: number of transients */
					__( '%s transients stored in database; should be under 500', 'wpshadow' ),
					number_format_i18n( absint( $total_transients ) )
				),
			);
		}

		// Issue 2: Many expired transients not cleaned.
		if ( absint( $expired_transients ) > 100 ) {
			$issues[] = array(
				'type'        => 'expired_not_cleaned',
				'description' => sprintf(
					/* translators: %s: number of expired transients */
					__( '%s expired transients not cleaned; automatic cleanup may be failing', 'wpshadow' ),
					number_format_i18n( absint( $expired_transients ) )
				),
			);
		}

		// Issue 3: WP Cron disabled (prevents cleanup).
		if ( $cron_disabled ) {
			$issues[] = array(
				'type'        => 'cron_disabled',
				'description' => __( 'WP Cron disabled; automatic transient cleanup will not run', 'wpshadow' ),
			);
		}

		// Issue 4: No cleanup plugin and no scheduled cleanup.
		if ( ! $has_cleanup_plugin && ! $has_cleanup_cron && ! $uses_object_cache ) {
			$issues[] = array(
				'type'        => 'no_cleanup_mechanism',
				'description' => __( 'No automatic cleanup mechanism configured; transients will accumulate', 'wpshadow' ),
			);
		}

		// Issue 5: Orphaned timeout entries.
		if ( absint( $orphan_timeouts ) > 50 ) {
			$issues[] = array(
				'type'        => 'orphan_timeouts',
				'description' => sprintf(
					/* translators: %s: number of orphaned timeouts */
					__( '%s orphaned timeout entries without corresponding transients', 'wpshadow' ),
					number_format_i18n( absint( $orphan_timeouts ) )
				),
			);
		}

		// Issue 6: Transients consuming excessive database storage.
		if ( $transient_mb > 10 ) {
			$issues[] = array(
				'type'        => 'high_storage',
				'description' => sprintf(
					/* translators: %s: storage size in MB */
					__( 'Transients consuming %s MB of database storage; should be under 5 MB', 'wpshadow' ),
					$transient_mb
				),
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Expired transients are not being cleaned automatically, causing database bloat and performance issues', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/transient-cleanup-automation',
				'details'      => array(
					'total_transients'        => number_format_i18n( absint( $total_transients ) ),
					'expired_transients'      => number_format_i18n( absint( $expired_transients ) ),
					'timeout_transients'      => number_format_i18n( absint( $timeout_transients ) ),
					'transient_size_mb'       => $transient_mb,
					'orphan_timeouts'         => number_format_i18n( absint( $orphan_timeouts ) ),
					'has_cleanup_plugin'      => $has_cleanup_plugin,
					'cron_disabled'           => $cron_disabled,
					'has_cleanup_cron'        => $has_cleanup_cron,
					'uses_object_cache'       => $uses_object_cache,
					'site_transients'         => number_format_i18n( absint( $site_transients ) ),
					'common_transient_prefixes' => $common_transients,
					'issues_detected'         => $issues,
					'recommendation'          => __( 'Install WP-Optimize or schedule automatic transient cleanup via WP Cron', 'wpshadow' ),
					'cleanup_methods'         => array(
						'delete_expired_transients()' => 'WordPress core function (WP 4.9+)',
						'WP-Optimize'                => 'Automated cleanup plugin',
						'WP Cron'                    => 'Schedule daily cleanup task',
						'External object cache'      => 'Redis/Memcached auto-expire',
					),
					'manual_cleanup_sql'      => "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_%'",
					'storage_savings'         => sprintf(
						/* translators: %s: storage size */
						__( 'Clean transients to reclaim %s MB', 'wpshadow' ),
						$transient_mb
					),
				),
			);
		}

		return null;
	}
}
