<?php
/**
 * PHP Memory Limit Optimized Diagnostic
 *
 * Checks whether the PHP memory_limit is at least 128 MB, which is the
 * WordPress recommended minimum for reliable operation.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Diagnostics\Helpers\Diagnostic_Server_Environment_Helper as Server_Env;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Php_Memory_Limit_Optimized Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Php_Memory_Limit_Optimized extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'php-memory-limit-optimized';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'PHP Memory Limit Optimized';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether the PHP memory_limit is at least 128 MB. A low memory limit causes fatal errors under complex plugins and prevents WordPress from completing large operations.';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * Reads the PHP memory_limit ini value via Server_Env and flags when it
	 * falls below the 128 MB recommended minimum for WordPress.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array when memory limit is insufficient, null when healthy.
	 */
	public static function check() {
		$mb = Server_Env::get_php_memory_limit_mb();

		// Unlimited (−1) or at or above the recommended minimum of 128 MB.
		if ( -1.0 === $mb || $mb >= 128 ) {
			return null;
		}

		$severity     = $mb < 64 ? 'high' : 'medium';
		$threat_level = $mb < 64 ? 65 : 40;

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: memory limit in MB */
				__( 'PHP memory limit is set to %s MB. WordPress recommends at least 128 MB, and many plugins (WooCommerce, page builders, image processors) require 256 MB or more. A low memory limit causes "Fatal error: Allowed memory size exhausted" errors and can prevent plugins from functioning correctly.', 'wpshadow' ),
				number_format( $mb, 0 )
			),
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/php-memory-limit?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => array(
				'memory_limit_mb'  => $mb,
				'recommended_mb'   => 128,
				'raw_ini_value'    => ini_get( 'memory_limit' ),
			),
		);
	}
}
