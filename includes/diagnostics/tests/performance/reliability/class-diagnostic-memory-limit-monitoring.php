<?php
/**
 * Memory Limit Monitoring Diagnostic
 *
 * Issue #4939: PHP Memory Limit Too Low
 * Pillar: ⚙️ Murphy's Law
 *
 * Checks if PHP memory limit is adequate.
 * Low memory limits cause white screens and failed operations.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Memory_Limit_Monitoring Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Memory_Limit_Monitoring extends Diagnostic_Base {

	protected static $slug = 'memory-limit-monitoring';
	protected static $title = 'PHP Memory Limit Too Low';
	protected static $description = 'Checks if PHP memory limit is adequate for WordPress';
	protected static $family = 'reliability';

	public static function check() {
		$memory_limit = wp_convert_hr_to_bytes( ini_get( 'memory_limit' ) );
		$wp_memory_limit = wp_convert_hr_to_bytes( WP_MEMORY_LIMIT );

		// Recommended: 256MB minimum
		$recommended = 256 * 1024 * 1024; // 256MB in bytes

		if ( $memory_limit < $recommended ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: current memory limit, 2: recommended limit */
					__( 'Current memory limit is %1$s. WordPress recommends at least %2$s for optimal performance.', 'wpshadow' ),
					size_format( $memory_limit ),
					size_format( $recommended )
				),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/memory-limit?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'current_limit'           => size_format( $memory_limit ),
					'wp_limit'                => size_format( $wp_memory_limit ),
					'recommended_limit'       => size_format( $recommended ),
					'increase_method'         => 'Add to wp-config.php: define("WP_MEMORY_LIMIT", "256M");',
					'symptoms'                => 'White screen, failed uploads, plugin crashes',
				),
			);
		}

		return null;
	}
}
