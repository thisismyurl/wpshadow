<?php
/**
 * Transient and Options Table Cleanup
 *
 * Validates transient expiration and options table health.
 *
 * @since 1.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Transient_Options_Cleanup Class
 *
 * Checks transient and options table for bloat and cleanup needs.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Transient_Options_Cleanup extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'transient-options-cleanup';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Transient and Options Table Cleanup';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates transient expiration and options table optimization';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'database';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Count expired transients
		$expired_transients = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->options} 
				WHERE option_name LIKE %s 
				AND option_value < %d",
				$wpdb->esc_like( '_transient_timeout_' ) . '%',
				time()
			)
		);

		// Count all transients
		$total_transients = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->options} 
				WHERE option_name LIKE %s OR option_name LIKE %s",
				$wpdb->esc_like( '_transient_' ) . '%',
				$wpdb->esc_like( '_site_transient_' ) . '%'
			)
		);

		// Get options table size
		$options_size = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT (DATA_LENGTH + INDEX_LENGTH) 
				FROM information_schema.TABLES 
				WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s",
				DB_NAME,
				$wpdb->options
			)
		);

		$options_size_mb = round( intval( $options_size ) / 1048576, 2 );

		// Count autoloaded options
		$autoloaded_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->options} WHERE autoload = 'yes'"
		);

		// Get size of autoloaded data
		$autoloaded_size = $wpdb->get_var(
			"SELECT SUM(LENGTH(option_value)) FROM {$wpdb->options} WHERE autoload = 'yes'"
		);

		$autoloaded_size_kb = round( intval( $autoloaded_size ) / 1024, 2 );

		// Pattern 1: Large number of expired transients not cleaned
		if ( $expired_transients > 100 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Large number of expired transients not cleaned up', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/transient-options-cleanup',
				'details'      => array(
					'issue' => 'expired_transients_accumulation',
					'expired_count' => intval( $expired_transients ),
					'total_transients' => intval( $total_transients ),
					'message' => sprintf(
						/* translators: %d: number of expired transients */
						__( '%d expired transients accumulating in database', 'wpshadow' ),
						intval( $expired_transients )
					),
					'what_are_transients' => __( 'Temporary cached data with expiration times', 'wpshadow' ),
					'why_transients_accumulate' => array(
						'WordPress does not auto-delete expired transients',
						'Only deleted when accessed after expiration',
						'Plugins create transients then get deactivated',
						'No cron job to clean expired transients',
					),
					'performance_impact' => array(
						'Bloats wp_options table',
						'Slows down option queries',
						'Increases database backup size',
						'Wastes disk space',
					),
					'cleanup_methods' => array(
						'Manual: Delete expired transients via SQL',
						'Plugin: WP-Optimize, Advanced Database Cleaner',
						'WP-CLI: wp transient delete --expired',
						'Custom cron: Schedule daily transient cleanup',
					),
					'prevention' => __( 'Schedule weekly transient cleanup to prevent accumulation', 'wpshadow' ),
					'sql_cleanup_command' => "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_%' AND option_value < UNIX_TIMESTAMP()",
					'estimated_space_saved' => sprintf(
						/* translators: %d: estimated KB */
						__( 'Cleaning %d transients could free ~%dKB', 'wpshadow' ),
						intval( $expired_transients ),
						intval( $expired_transients * 2 )
					),
					'recommendation' => __( 'Clean up expired transients to improve database performance', 'wpshadow' ),
				),
			);
		}

		// Pattern 2: Excessive autoloaded data (slows every page load)
		if ( $autoloaded_size_kb > 800 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Excessive autoloaded options (slows every page load)', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/transient-options-cleanup',
				'details'      => array(
					'issue' => 'excessive_autoloaded_data',
					'autoloaded_size_kb' => $autoloaded_size_kb,
					'autoloaded_count' => intval( $autoloaded_count ),
					'message' => sprintf(
						/* translators: %s: size in KB */
						__( '%sKB of autoloaded options (loaded on EVERY page)', 'wpshadow' ),
						$autoloaded_size_kb
					),
					'what_is_autoloaded' => __( 'Options loaded automatically on every WordPress page load', 'wpshadow' ),
					'performance_impact' => array(
						'Loaded on every single page request',
						'Increases memory usage',
						'Slows down page generation',
						'Cannot be cached by object cache',
					),
					'size_benchmarks' => array(
						'< 200KB' => 'Excellent, minimal impact',
						'200-500KB' => 'Acceptable, monitor',
						'500-800KB' => 'High, needs optimization',
						'> 800KB' => 'Critical, major performance issue',
					),
					'page_load_impact' => sprintf(
						/* translators: %s: size in KB */
						__( '%sKB autoloaded = ~50-200ms added to every page load', 'wpshadow' ),
						$autoloaded_size_kb
					),
					'common_culprits' => array(
						'Theme options (large arrays)',
						'Plugin settings (not marked no-autoload)',
						'Cached data mistakenly autoloaded',
						'Deactivated plugin options still autoloading',
					),
					'investigation_query' => "SELECT option_name, LENGTH(option_value) as size FROM {$wpdb->options} WHERE autoload='yes' ORDER BY size DESC LIMIT 20",
					'optimization_steps' => array(
						'1. Identify largest autoloaded options',
						'2. Mark non-essential options as autoload=no',
						'3. Remove options from deactivated plugins',
						'4. Move large data to separate tables',
						'5. Use object cache to reduce database hits',
					),
					'autoload_change_command' => "UPDATE {$wpdb->options} SET autoload='no' WHERE option_name='your_option'",
					'recommendation' => __( 'Optimize autoloaded options immediately (major performance gain)', 'wpshadow' ),
				),
			);
		}

		// Pattern 3: Options table excessively large
		if ( $options_size_mb > 50 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'wp_options table excessively large', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/transient-options-cleanup',
				'details'      => array(
					'issue' => 'oversized_options_table',
					'options_size_mb' => $options_size_mb,
					'message' => sprintf(
						/* translators: %s: size in MB */
						__( 'wp_options table is %sMB (abnormally large)', 'wpshadow' ),
						$options_size_mb
					),
					'typical_size' => __( 'Normal wp_options table: 5-15MB', 'wpshadow' ),
					'size_thresholds' => array(
						'< 10MB' => 'Normal size',
						'10-25MB' => 'Slightly bloated',
						'25-50MB' => 'Bloated, needs cleanup',
						'> 50MB' => 'Severely bloated, critical cleanup needed',
					),
					'common_causes' => array(
						'Thousands of expired transients',
						'Plugin data not cleaned on deactivation',
						'Serialized data stored in options',
						'Session data in options table',
						'Form submissions or logs',
					),
					'performance_consequences' => array(
						'Slow option lookups',
						'get_option() and update_option() delayed',
						'Admin panel sluggish',
						'Database backups slow',
					),
					'cleanup_strategy' => array(
						'Delete expired transients',
						'Remove orphaned options (plugin remnants)',
						'Audit largest option values',
						'Move large data to custom tables',
						'Optimize table after cleanup',
					),
					'orphaned_options_check' => __( 'Look for options from plugins no longer installed', 'wpshadow' ),
					'size_reduction_estimate' => __( 'Proper cleanup typically reduces size by 30-60%', 'wpshadow' ),
					'recommendation' => __( 'Clean up and optimize wp_options table', 'wpshadow' ),
				),
			);
		}

		// Pattern 4: Transient-to-option ratio unhealthy
		$transient_percentage = $total_transients > 0 ? ( $total_transients / ( $autoloaded_count + 1 ) ) * 100 : 0;

		if ( $total_transients > 500 && $transient_percentage > 50 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Transients dominating options table', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/transient-options-cleanup',
				'details'      => array(
					'issue' => 'transient_dominance',
					'total_transients' => intval( $total_transients ),
					'transient_percentage' => round( $transient_percentage, 2 ),
					'message' => sprintf(
						/* translators: 1: transient count, 2: percentage */
						__( '%1$d transients (%2$s%% of options table)', 'wpshadow' ),
						intval( $total_transients ),
						round( $transient_percentage, 2 )
					),
					'why_problematic' => __( 'Transients are temporary but treated as permanent options', 'wpshadow' ),
					'issues_with_transient_bloat' => array(
						'wp_options table grows unnecessarily',
						'Option queries scan transient rows',
						'Transients not designed for high volume',
						'Cleanup becomes harder',
					),
					'better_caching_alternatives' => array(
						'Object cache (Redis, Memcached)',
						'Custom cache tables',
						'File-based cache',
						'External cache service',
					),
					'transient_best_practices' => array(
						'Use short expiration times',
						'Clean up on plugin deactivation',
						'Consider object cache for frequent data',
						'Implement transient cleanup cron',
					),
					'migration_to_object_cache' => __( 'For high-volume caching, use Redis/Memcached instead of transients', 'wpshadow' ),
					'recommendation' => __( 'Review transient usage and consider object caching', 'wpshadow' ),
				),
			);
		}

		// Pattern 5: No transient cleanup in 90+ days
		$last_transient_cleanup = get_option( 'wpshadow_last_transient_cleanup', 0 );
		$days_since_cleanup = $last_transient_cleanup > 0 ? intval( ( time() - $last_transient_cleanup ) / 86400 ) : 999;

		if ( $days_since_cleanup > 90 && $total_transients > 100 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No transient cleanup performed in 90+ days', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/transient-options-cleanup',
				'details'      => array(
					'issue' => 'no_recent_cleanup',
					'days_since_cleanup' => $days_since_cleanup,
					'total_transients' => intval( $total_transients ),
					'message' => sprintf(
						/* translators: %d: days since cleanup */
						__( 'No transient cleanup in %d+ days', 'wpshadow' ),
						$days_since_cleanup
					),
					'maintenance_recommendation' => __( 'Transients should be cleaned monthly', 'wpshadow' ),
					'why_regular_cleanup_matters' => array(
						'Prevents accumulation of expired data',
						'Keeps options table lean',
						'Maintains database performance',
						'Reduces backup size',
					),
					'cleanup_schedule_recommendations' => array(
						'< 100 transients' => 'Quarterly cleanup sufficient',
						'100-500 transients' => 'Monthly cleanup recommended',
						'500+ transients' => 'Weekly cleanup necessary',
					),
					'automation_options' => array(
						'WP-Cron: Schedule weekly cleanup job',
						'Plugin: WP-Optimize auto-cleanup',
						'Server cron: Daily cleanup script',
					),
					'cleanup_script_example' => 'wp transient delete --expired (via WP-CLI)',
					'monitoring' => __( 'Track transient count monthly to adjust cleanup frequency', 'wpshadow' ),
					'recommendation' => __( 'Implement regular transient cleanup schedule', 'wpshadow' ),
				),
			);
		}

		// Pattern 6: Large individual option values
		$large_options = $wpdb->get_results(
			"SELECT option_name, LENGTH(option_value) as size 
			FROM {$wpdb->options} 
			WHERE LENGTH(option_value) > 1048576 
			ORDER BY size DESC 
			LIMIT 5",
			ARRAY_A
		);

		if ( ! empty( $large_options ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Individual option values excessively large', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/transient-options-cleanup',
				'details'      => array(
					'issue' => 'large_option_values',
					'large_options_count' => count( $large_options ),
					'large_options' => array_map(
						function( $opt ) {
							return array(
								'name' => $opt['option_name'],
								'size_mb' => round( intval( $opt['size'] ) / 1048576, 2 ),
							);
						},
						$large_options
					),
					'message' => sprintf(
						/* translators: %d: number of large options */
						__( '%d option values are >1MB each', 'wpshadow' ),
						count( $large_options )
					),
					'why_problematic' => __( 'Options table is not designed for large data storage', 'wpshadow' ),
					'performance_issues' => array(
						'get_option() slow for large values',
						'Serialized large arrays = slow unserialize',
						'Increases memory usage',
						'Slows down database queries',
					),
					'common_large_options' => array(
						'theme_mods_*' => 'Theme customizer data',
						'*_cache' => 'Cached data (should use transients)',
						'*_settings' => 'Plugin settings arrays',
						'*_logs' => 'Log data (should be in files)',
					),
					'better_storage_solutions' => array(
						'Custom database tables for large data',
						'JSON files in uploads directory',
						'External storage (S3, cloud)',
						'Split large options into smaller chunks',
					),
					'max_recommended_size' => __( 'Single option should never exceed 100KB', 'wpshadow' ),
					'refactoring_needed' => __( 'Move large data out of options table to custom tables', 'wpshadow' ),
					'recommendation' => __( 'Refactor large option storage to use appropriate data structures', 'wpshadow' ),
				),
			);
		}

		return null; // No issues found
	}
}
