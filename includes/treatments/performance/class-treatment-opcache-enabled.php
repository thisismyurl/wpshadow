<?php
/**
 * OPcache Enabled Treatment
 *
 * Verifies that PHP OPcache is enabled for optimal performance.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6033.2049
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * OPcache Enabled Treatment Class
 *
 * Checks if PHP OPcache is installed and enabled. OPcache dramatically
 * improves PHP performance by caching compiled bytecode.
 *
 * @since 1.6033.2049
 */
class Treatment_OPcache_Enabled extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'opcache-enabled';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'OPcache Enabled';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if PHP OPcache is enabled';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * Checks for OPcache availability and enabled status.
	 * OPcache can improve performance by 30-50%.
	 *
	 * @since  1.6033.2049
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if OPcache extension is available
		if ( ! function_exists( 'opcache_get_status' ) ) {
			return array(
				'id'           => 'opcache-not-available',
				'title'        => __( 'OPcache Extension Not Available', 'wpshadow' ),
				'description'  => __( 'PHP OPcache extension is not installed or enabled. OPcache significantly improves PHP performance by caching precompiled script bytecode, typically resulting in 30-50% performance improvement.', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/opcache-installation',
				'meta'         => array(
					'php_version'      => PHP_VERSION,
					'extension_loaded' => false,
					'recommendation'   => 'Install and enable OPcache extension',
				),
			);
		}
		
		// Get OPcache status
		$status = @opcache_get_status( false );
		
		// Check if OPcache is enabled
		if ( ! $status || empty( $status['opcache_enabled'] ) ) {
			return array(
				'id'           => 'opcache-not-enabled',
				'title'        => __( 'OPcache Not Enabled', 'wpshadow' ),
				'description'  => __( 'OPcache extension is installed but not enabled. Enable OPcache in php.ini to improve performance by 30-50%.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/opcache-configuration',
				'meta'         => array(
					'php_version'      => PHP_VERSION,
					'extension_loaded' => true,
					'opcache_enabled'  => false,
					'recommendation'   => 'Set opcache.enable=1 in php.ini',
				),
			);
		}
		
		// OPcache is available and enabled
		return null;
	}
}
