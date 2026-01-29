<?php
/**
 * WordPress Memory Limit Configuration Diagnostic
 *
 * Validates WP_MEMORY_LIMIT is adequately configured for site complexity.
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
 * WordPress Memory Limit Configuration Class
 *
 * Tests memory limit configuration.
 *
 * @since 1.26028.1905
 */
class Diagnostic_WordPress_Memory_Limit_Configuration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'wordpress-memory-limit-configuration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'WordPress Memory Limit Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates WP_MEMORY_LIMIT is adequately configured for site complexity';

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
		$memory_check = self::check_memory_limits();
		
		if ( ! $memory_check['is_adequate'] ) {
			$issues = array();
			
			if ( $memory_check['wp_limit_too_low'] ) {
				$issues[] = sprintf(
					/* translators: 1: current limit, 2: recommended limit */
					__( 'WP_MEMORY_LIMIT is %1$s (recommended: %2$s for this site)', 'wpshadow' ),
					$memory_check['wp_memory_limit'],
					$memory_check['recommended_limit']
				);
			}

			if ( $memory_check['admin_limit_not_set'] ) {
				$issues[] = __( 'WP_MAX_MEMORY_LIMIT not set (admin needs more memory)', 'wpshadow' );
			}

			if ( $memory_check['limit_above_php'] ) {
				$issues[] = sprintf(
					/* translators: 1: WP limit, 2: PHP limit */
					__( 'WP_MEMORY_LIMIT (%1$s) exceeds PHP limit (%2$s) - wasted', 'wpshadow' ),
					$memory_check['wp_memory_limit'],
					$memory_check['php_memory_limit']
				);
			}

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/wordpress-memory-limit-configuration',
				'meta'         => array(
					'wp_memory_limit'      => $memory_check['wp_memory_limit'],
					'php_memory_limit'     => $memory_check['php_memory_limit'],
					'recommended_limit'    => $memory_check['recommended_limit'],
					'wp_max_memory_limit'  => $memory_check['wp_max_memory_limit'],
					'active_plugin_count'  => $memory_check['active_plugin_count'],
				),
			);
		}

		return null;
	}

	/**
	 * Check memory limit configuration.
	 *
	 * @since  1.26028.1905
	 * @return array Check results.
	 */
	private static function check_memory_limits() {
		$check = array(
			'is_adequate'          => true,
			'wp_limit_too_low'     => false,
			'admin_limit_not_set'  => false,
			'limit_above_php'      => false,
			'wp_memory_limit'      => '',
			'php_memory_limit'     => '',
			'wp_max_memory_limit'  => '',
			'recommended_limit'    => '',
			'active_plugin_count'  => 0,
		);

		// Get current limits.
		$check['wp_memory_limit'] = WP_MEMORY_LIMIT;
		$check['php_memory_limit'] = ini_get( 'memory_limit' );

		// Get WP_MAX_MEMORY_LIMIT.
		if ( defined( 'WP_MAX_MEMORY_LIMIT' ) ) {
			$check['wp_max_memory_limit'] = WP_MAX_MEMORY_LIMIT;
		} else {
			$check['admin_limit_not_set'] = true;
			$check['is_adequate'] = false;
		}

		// Count active plugins.
		$active_plugins = get_option( 'active_plugins', array() );
		$check['active_plugin_count'] = count( $active_plugins );

		// Calculate recommended limit based on complexity.
		$recommended_mb = 64; // Base.
		
		if ( class_exists( 'WooCommerce' ) ) {
			$recommended_mb = 256;
		} elseif ( $check['active_plugin_count'] > 20 ) {
			$recommended_mb = 128;
		} elseif ( $check['active_plugin_count'] > 10 ) {
			$recommended_mb = 96;
		}

		$check['recommended_limit'] = $recommended_mb . 'M';

		// Convert current limit to MB for comparison.
		$current_mb = self::convert_to_mb( $check['wp_memory_limit'] );
		
		if ( $current_mb < $recommended_mb ) {
			$check['wp_limit_too_low'] = true;
			$check['is_adequate'] = false;
		}

		// Check if WP limit exceeds PHP limit.
		$php_mb = self::convert_to_mb( $check['php_memory_limit'] );
		if ( $current_mb > $php_mb ) {
			$check['limit_above_php'] = true;
			$check['is_adequate'] = false;
		}

		return $check;
	}

	/**
	 * Convert memory limit string to MB.
	 *
	 * @since  1.26028.1905
	 * @param  string $limit Memory limit string.
	 * @return int Memory in MB.
	 */
	private static function convert_to_mb( $limit ) {
		$limit = trim( $limit );
		$unit = strtoupper( substr( $limit, -1 ) );
		$value = (int) $limit;

		switch ( $unit ) {
			case 'G':
				return $value * 1024;
			case 'M':
				return $value;
			case 'K':
				return $value / 1024;
			default:
				return $value / 1048576; // Bytes to MB.
		}
	}
}
