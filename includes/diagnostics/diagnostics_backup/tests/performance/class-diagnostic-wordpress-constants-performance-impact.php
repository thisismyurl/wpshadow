<?php
/**
 * WordPress Constants Performance Impact Diagnostic
 *
 * Identifies performance-impacting constants and misconfigurations.
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
 * WordPress Constants Performance Impact Class
 *
 * Tests performance constants.
 *
 * @since 1.26028.1905
 */
class Diagnostic_WordPress_Constants_Performance_Impact extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'wordpress-constants-performance-impact';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'WordPress Constants Performance Impact';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Identifies performance-impacting constants and misconfigurations';

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
		$constants_check = self::check_performance_constants();
		
		if ( $constants_check['has_issues'] ) {
			$issues = array();
			
			if ( $constants_check['concatenate_disabled'] ) {
				$issues[] = __( 'CONCATENATE_SCRIPTS disabled (increases HTTP requests)', 'wpshadow' );
			}

			if ( $constants_check['cache_not_enabled'] ) {
				$issues[] = __( 'WP_CACHE not enabled (object cache not active)', 'wpshadow' );
			}

			if ( $constants_check['autosave_too_frequent'] ) {
				$issues[] = sprintf(
					/* translators: %d: autosave interval in seconds */
					__( 'AUTOSAVE_INTERVAL set to %ds (too frequent, causes performance issues)', 'wpshadow' ),
					$constants_check['autosave_interval']
				);
			}

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/wordpress-constants-performance-impact',
				'meta'         => array(
					'concatenate_disabled'   => $constants_check['concatenate_disabled'],
					'cache_not_enabled'      => $constants_check['cache_not_enabled'],
					'autosave_too_frequent'  => $constants_check['autosave_too_frequent'],
					'autosave_interval'      => $constants_check['autosave_interval'],
				),
			);
		}

		return null;
	}

	/**
	 * Check performance constants.
	 *
	 * @since  1.26028.1905
	 * @return array Check results.
	 */
	private static function check_performance_constants() {
		$check = array(
			'has_issues'           => false,
			'concatenate_disabled' => false,
			'cache_not_enabled'    => false,
			'autosave_too_frequent' => false,
			'autosave_interval'    => 60,
		);

		// Check CONCATENATE_SCRIPTS.
		if ( defined( 'CONCATENATE_SCRIPTS' ) && ! CONCATENATE_SCRIPTS ) {
			$check['concatenate_disabled'] = true;
			$check['has_issues'] = true;
		}

		// Check WP_CACHE.
		if ( ! defined( 'WP_CACHE' ) || ! WP_CACHE ) {
			// Only flag if object cache is actually available.
			if ( wp_using_ext_object_cache() ) {
				$check['cache_not_enabled'] = true;
				$check['has_issues'] = true;
			}
		}

		// Check AUTOSAVE_INTERVAL.
		if ( defined( 'AUTOSAVE_INTERVAL' ) ) {
			$check['autosave_interval'] = AUTOSAVE_INTERVAL;
			
			// Less than 30 seconds is too frequent.
			if ( AUTOSAVE_INTERVAL < 30 ) {
				$check['autosave_too_frequent'] = true;
				$check['has_issues'] = true;
			}
		}

		return $check;
	}
}
