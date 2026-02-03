<?php
/**
 * OPcache Configuration Diagnostic
 *
 * Checks OPcache settings are optimized for WordPress performance.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26033.2050
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * OPcache Configuration Diagnostic Class
 *
 * Verifies OPcache configuration is optimized for WordPress.
 * Checks memory, file limits, and revalidation settings.
 *
 * @since 1.26033.2050
 */
class Diagnostic_OPcache_Configuration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'opcache-configuration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'OPcache Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks OPcache configuration for WordPress optimization';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks OPcache configuration against WordPress best practices.
	 *
	 * @since  1.26033.2050
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if OPcache is available
		if ( ! function_exists( 'opcache_get_configuration' ) ) {
			return null; // OPcache availability checked by separate diagnostic
		}
		
		// Get OPcache configuration
		$config = @opcache_get_configuration();
		
		if ( ! $config || empty( $config['directives'] ) ) {
			return null;
		}
		
		$directives = $config['directives'];
		$issues     = array();
		
		// Check memory_consumption (should be ≥128MB)
		$memory_consumption = isset( $directives['opcache.memory_consumption'] ) ? (int) $directives['opcache.memory_consumption'] : 0;
		$memory_mb          = round( $memory_consumption / 1048576 );
		
		if ( $memory_consumption < 134217728 ) { // 128MB
			$issues[] = sprintf(
				/* translators: %d: current memory in MB */
				__( 'OPcache memory is %dMB (should be ≥128MB)', 'wpshadow' ),
				$memory_mb
			);
		}
		
		// Check max_accelerated_files (should be ≥10000)
		$max_files = isset( $directives['opcache.max_accelerated_files'] ) ? (int) $directives['opcache.max_accelerated_files'] : 0;
		
		if ( $max_files < 10000 ) {
			$issues[] = sprintf(
				/* translators: %d: current max files */
				__( 'OPcache max_accelerated_files is %d (should be ≥10000)', 'wpshadow' ),
				$max_files
			);
		}
		
		// Check validate_timestamps (should be 0 in production)
		if ( isset( $directives['opcache.validate_timestamps'] ) && $directives['opcache.validate_timestamps'] && ! WP_DEBUG ) {
			$issues[] = __( 'OPcache validate_timestamps is enabled in production (should be 0 for best performance)', 'wpshadow' );
		}
		
		// Check interned_strings_buffer (should be ≥16MB)
		$strings_buffer = isset( $directives['opcache.interned_strings_buffer'] ) ? (int) $directives['opcache.interned_strings_buffer'] : 0;
		
		if ( $strings_buffer < 16 ) {
			$issues[] = sprintf(
				/* translators: %d: current buffer size in MB */
				__( 'OPcache interned_strings_buffer is %dMB (should be ≥16MB)', 'wpshadow' ),
				$strings_buffer
			);
		}
		
		// If issues found, return finding
		if ( ! empty( $issues ) ) {
			$severity     = count( $issues ) > 2 ? 'high' : 'medium';
			$threat_level = count( $issues ) * 15;
			
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: list of configuration issues */
					__( 'OPcache configuration needs optimization: %s', 'wpshadow' ),
					implode( '; ', $issues )
				),
				'severity'     => $severity,
				'threat_level' => min( 100, $threat_level ),
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/opcache-optimization',
				'meta'         => array(
					'memory_mb'                    => $memory_mb,
					'max_accelerated_files'        => $max_files,
					'interned_strings_buffer_mb'   => $strings_buffer,
					'validate_timestamps'          => isset( $directives['opcache.validate_timestamps'] ) ? $directives['opcache.validate_timestamps'] : null,
					'issues_found'                 => count( $issues ),
					'recommended_memory_mb'        => 128,
					'recommended_max_files'        => 10000,
					'recommended_strings_buffer_mb' => 16,
				),
			);
		}
		
		return null;
	}
}
