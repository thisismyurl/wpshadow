<?php
/**
 * ACF Options Page Caching Diagnostic
 *
 * ACF options pages not cached.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.455.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ACF Options Page Caching Diagnostic Class
 *
 * @since 1.455.0000
 */
class Diagnostic_AcfOptionsPageCaching extends Diagnostic_Base {

	protected static $slug = 'acf-options-page-caching';
	protected static $title = 'ACF Options Page Caching';
	protected static $description = 'ACF options pages not cached';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'ACF' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Options pages registered.
		$options_pages = apply_filters( 'acf/get_options_pages', array() );
		if ( empty( $options_pages ) ) {
			return null; // No options pages, no issues to check.
		}

		// Check 2: Object cache available.
		if ( ! wp_using_ext_object_cache() ) {
			$issues[] = 'persistent object cache not enabled (options pages queried repeatedly)';
		}

		// Check 3: Options page data size.
		global $wpdb;
		$large_options = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE 'options_%' AND LENGTH(option_value) > 50000"
		);
		if ( $large_options > 0 ) {
			$issues[] = "{$large_options} large options page values (over 50KB, slow to load)";
		}

		// Check 4: Autoload enabled for options.
		$autoloaded_options = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE %s AND autoload = %s",
				'options_%',
				'yes'
			)
		);
		if ( $autoloaded_options > 0 ) {
			$issues[] = "{$autoloaded_options} options page values set to autoload (increases memory usage)";
		}

		// Check 5: Options page query frequency.
		if ( defined( 'SAVEQUERIES' ) && SAVEQUERIES ) {
			if ( ! empty( $GLOBALS['wpdb']->queries ) ) {
				$options_queries = 0;
				foreach ( $GLOBALS['wpdb']->queries as $query ) {
					if ( false !== strpos( $query[0], "option_name = 'options_" ) ) {
						++$options_queries;
					}
				}
				if ( $options_queries > 10 ) {
					$issues[] = "{$options_queries} options page queries in single request (cache not working)";
				}
			}
		}

		// Check 6: Cache groups configured for ACF.
		$cache_groups = wp_cache_get_non_persistent_groups();
		if ( in_array( 'acf', $cache_groups, true ) ) {
			$issues[] = 'ACF cache group set to non-persistent (options pages not cached between requests)';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 75, 45 + ( count( $issues ) * 6 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'ACF options page caching issues: ' . implode( ', ', $issues ),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/acf-options-page-caching',
			);
		}

		return null;
	}
}
