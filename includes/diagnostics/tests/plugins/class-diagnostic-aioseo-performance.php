<?php
/**
 * AIOSEO Performance Diagnostic
 *
 * Analyzes database and performance impact.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5029.1805
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AIOSEO Performance Class
 *
 * Checks performance and database impact.
 *
 * @since 1.5029.1805
 */
class Diagnostic_AIOSEO_Performance extends Diagnostic_Base {

	protected static $slug        = 'aioseo-performance';
	protected static $title       = 'AIOSEO Performance Impact';
	protected static $description = 'Analyzes performance and database';
	protected static $family      = 'plugins';

	public static function check() {
		if ( ! function_exists( 'aioseo' ) ) {
			return null;
		}

		$cache_key = 'wpshadow_aioseo_performance';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		global $wpdb;
		$issues = array();

		// Check AIOSEO posts table size.
		$posts_table = $wpdb->prefix . 'aioseo_posts';
		$posts_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$posts_table}" );
		$table_size = $wpdb->get_var( "SELECT ROUND((data_length + index_length) / 1024 / 1024, 2) FROM information_schema.TABLES WHERE table_schema = DATABASE() AND table_name = '{$posts_table}'" );

		if ( $table_size > 50 ) {
			$issues[] = sprintf( 'AIOSEO posts table is %sMB - consider cleanup', $table_size );
		}

		// Check for orphaned post meta.
		$orphaned_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$posts_table} ap 
			LEFT JOIN {$wpdb->posts} p ON ap.post_id = p.ID 
			WHERE p.ID IS NULL"
		);

		if ( $orphaned_count > 100 ) {
			$issues[] = sprintf( '%d orphaned AIOSEO post entries', $orphaned_count );
		}

		// Check option autoload.
		$autoloaded = $wpdb->get_var( "SELECT SUM(LENGTH(option_value)) FROM {$wpdb->options} WHERE option_name LIKE 'aioseo_%' AND autoload = 'yes'" );
		if ( $autoloaded > 100000 ) {
			$issues[] = sprintf( 'AIOSEO autoloaded data: %s bytes - impacts page load', number_format( $autoloaded ) );
		}

		// Check sitemap generation frequency.
		$options = get_option( 'aioseo_options', array() );
		$sitemap_frequency = isset( $options['sitemap']['general']['advancedSettings']['dynamic'] ) 
			? $options['sitemap']['general']['advancedSettings']['dynamic'] 
			: true;

		if ( ! $sitemap_frequency ) {
			// Static sitemap generation can impact performance.
			$issues[] = 'Using static sitemap generation - can impact performance on large sites';
		}

		if ( ! empty( $issues ) ) {
			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: count */
					__( '%d AIOSEO performance issues detected.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/plugins-aioseo-performance',
				'data'         => array(
					'performance_issues' => $issues,
					'total_issues' => count( $issues ),
					'posts_count' => $posts_count,
					'table_size_mb' => $table_size,
				),
			);

			set_transient( $cache_key, $result, 12 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
		return null;
	}
}
