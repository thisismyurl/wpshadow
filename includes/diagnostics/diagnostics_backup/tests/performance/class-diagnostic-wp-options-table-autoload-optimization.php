<?php
/**
 * wp_options Table Autoload Optimization Diagnostic
 *
 * Identifies wp_options rows incorrectly set to autoload slowing every page load.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26028.1905
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * wp_options Table Autoload Optimization Class
 *
 * Tests options autoload.
 *
 * @since 1.26028.1905
 */
class Diagnostic_Wp_Options_Table_Autoload_Optimization extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'wp-options-table-autoload-optimization';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'wp_options Table Autoload Optimization';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Identifies wp_options rows incorrectly set to autoload slowing every page load';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26028.1905
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$autoload_check = self::check_autoload_size();
		
		if ( $autoload_check['is_excessive'] ) {
			$issues = array();
			
			if ( $autoload_check['autoload_count'] > 500 ) {
				$issues[] = sprintf(
					/* translators: %d: number of autoloaded options */
					__( '%d autoloaded options (should be <500)', 'wpshadow' ),
					$autoload_check['autoload_count']
				);
			}

			if ( $autoload_check['autoload_size'] > 1048576 ) {
				$issues[] = sprintf(
					/* translators: %s: autoload size in MB */
					__( '%sMB of autoloaded data (should be <1MB)', 'wpshadow' ),
					number_format( $autoload_check['autoload_size'] / 1048576, 2 )
				);
			}

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $issues ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/wp-options-table-autoload-optimization',
				'meta'         => array(
					'autoload_count' => $autoload_check['autoload_count'],
					'autoload_size'  => $autoload_check['autoload_size'],
					'large_options'  => $autoload_check['large_options'],
				),
			);
		}

		return null;
	}

	/**
	 * Check autoload size.
	 *
	 * @since  1.26028.1905
	 * @return array Check results.
	 */
	private static function check_autoload_size() {
		global $wpdb;

		$check = array(
			'is_excessive'   => false,
			'autoload_count' => 0,
			'autoload_size'  => 0,
			'large_options'  => array(),
		);

		// Count autoloaded options.
		$check['autoload_count'] = (int) $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->options}
			WHERE autoload = 'yes'"
		);

		// Calculate total autoload size.
		$check['autoload_size'] = (int) $wpdb->get_var(
			"SELECT SUM(LENGTH(option_value))
			FROM {$wpdb->options}
			WHERE autoload = 'yes'"
		);

		// Get large autoloaded options (>100KB).
		$large_options = $wpdb->get_results(
			"SELECT option_name, LENGTH(option_value) as size
			FROM {$wpdb->options}
			WHERE autoload = 'yes'
			AND LENGTH(option_value) > 102400
			ORDER BY size DESC
			LIMIT 10"
		);

		if ( ! empty( $large_options ) ) {
			foreach ( $large_options as $option ) {
				$check['large_options'][] = array(
					'name' => $option->option_name,
					'size' => (int) $option->size,
				);
			}
		}

		// Flag as excessive if count >500 OR size >1MB.
		if ( $check['autoload_count'] > 500 || $check['autoload_size'] > 1048576 ) {
			$check['is_excessive'] = true;
		}

		return $check;
	}
}
