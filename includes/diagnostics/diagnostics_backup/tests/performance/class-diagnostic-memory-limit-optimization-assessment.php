<?php
/**
 * Memory Limit Optimization Assessment Diagnostic
 *
 * Validates WP_MEMORY_LIMIT and WP_MAX_MEMORY_LIMIT are adequately configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26029.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Memory Limit Optimization Assessment Class
 *
 * Tests memory limit configuration.
 *
 * @since 1.26029.0000
 */
class Diagnostic_Memory_Limit_Optimization_Assessment extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'memory-limit-optimization-assessment';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Memory Limit Optimization Assessment';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates WP_MEMORY_LIMIT and WP_MAX_MEMORY_LIMIT are adequately configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26029.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$memory_check = self::check_memory_limits();
		
		if ( $memory_check['has_concerns'] ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $memory_check['concerns'] ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/memory-limit-optimization-assessment',
				'meta'         => array(
					'wp_memory_limit'     => $memory_check['wp_memory_limit'],
					'wp_max_memory_limit' => $memory_check['wp_max_memory_limit'],
					'php_memory_limit'    => $memory_check['php_memory_limit'],
				),
			);
		}

		return null;
	}

	/**
	 * Check memory limit configuration.
	 *
	 * @since  1.26029.0000
	 * @return array Check results.
	 */
	private static function check_memory_limits() {
		$check = array(
			'has_concerns'        => false,
			'concerns'            => array(),
			'wp_memory_limit'     => WP_MEMORY_LIMIT,
			'wp_max_memory_limit' => WP_MAX_MEMORY_LIMIT,
			'php_memory_limit'    => ini_get( 'memory_limit' ),
		);

		// Convert to bytes for comparison.
		$wp_limit = wp_convert_hr_to_bytes( WP_MEMORY_LIMIT );
		$wp_max_limit = wp_convert_hr_to_bytes( WP_MAX_MEMORY_LIMIT );
		$php_limit = wp_convert_hr_to_bytes( ini_get( 'memory_limit' ) );

		// Check if WP_MEMORY_LIMIT is too low.
		$recommended_min = 256 * 1024 * 1024; // 256MB.
		
		if ( $wp_limit < $recommended_min ) {
			$check['has_concerns'] = true;
			$check['concerns'][] = sprintf(
				/* translators: 1: current limit, 2: recommended limit */
				__( 'WP_MEMORY_LIMIT is %1$s (recommend 256M+ for modern WordPress)', 'wpshadow' ),
				WP_MEMORY_LIMIT
			);
		}

		// Check if WP_MEMORY_LIMIT exceeds PHP memory_limit.
		if ( $wp_limit > $php_limit ) {
			$check['has_concerns'] = true;
			$check['concerns'][] = sprintf(
				/* translators: 1: WP limit, 2: PHP limit */
				__( 'WP_MEMORY_LIMIT (%1$s) exceeds PHP memory_limit (%2$s), increase PHP limit', 'wpshadow' ),
				WP_MEMORY_LIMIT,
				ini_get( 'memory_limit' )
			);
		}

		// Check WP_MAX_MEMORY_LIMIT.
		if ( $wp_max_limit <= $wp_limit ) {
			$check['has_concerns'] = true;
			$check['concerns'][] = sprintf(
				/* translators: 1: max limit, 2: regular limit */
				__( 'WP_MAX_MEMORY_LIMIT (%1$s) not higher than WP_MEMORY_LIMIT (%2$s)', 'wpshadow' ),
				WP_MAX_MEMORY_LIMIT,
				WP_MEMORY_LIMIT
			);
		}

		return $check;
	}
}
