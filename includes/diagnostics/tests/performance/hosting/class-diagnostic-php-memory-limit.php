<?php
/**
 * PHP Memory Limit Diagnostic
 *
 * Checks if PHP memory limit is sufficient for WordPress operations.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1530
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PHP Memory Limit Diagnostic Class
 *
 * Verifies PHP memory limit is adequate. Memory is like RAM on your computer—
 * too little causes slowdowns and errors.
 *
 * @since 1.6035.1530
 */
class Diagnostic_Php_Memory_Limit extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'php-memory-limit';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'PHP Memory Limit';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if PHP memory limit is sufficient for WordPress operations';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'hosting';

	/**
	 * Run the PHP memory limit diagnostic check.
	 *
	 * @since  1.6035.1530
	 * @return array|null Finding array if memory issues detected, null otherwise.
	 */
	public static function check() {
		$memory_limit = ini_get( 'memory_limit' );
		
		// Handle unlimited memory.
		if ( '-1' === $memory_limit ) {
			return null;
		}

		// Convert to bytes.
		$memory_bytes = wp_convert_hr_to_bytes( $memory_limit );
		$memory_mb    = $memory_bytes / 1024 / 1024;

		// WordPress absolute minimum.
		$min_required   = 64;
		$recommended    = 256;
		$woocommerce_recommended = 512;

		// Check for WooCommerce (needs more memory).
		$has_woocommerce = class_exists( 'WooCommerce' );

		if ( $memory_mb < $min_required ) {
			return array(
				'id'           => self::$slug . '-critical',
				'title'        => __( 'PHP Memory Critically Low', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: 1: current memory limit, 2: minimum required */
					__( 'Your PHP memory limit is %1$s MB, but WordPress needs at least %2$s MB to function (like trying to run modern software on an old computer with too little RAM). You\'ll likely see "memory exhausted" errors. Contact your hosting provider to increase this limit.', 'wpshadow' ),
					number_format_i18n( $memory_mb, 0 ),
					$min_required
				),
				'severity'     => 'critical',
				'threat_level' => 90,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/php-memory-limit',
				'context'      => array(
					'current_mb' => $memory_mb,
					'minimum'    => $min_required,
				),
			);
		}

		// Check against recommended.
		if ( $has_woocommerce && $memory_mb < $woocommerce_recommended ) {
			return array(
				'id'           => self::$slug . '-woocommerce',
				'title'        => __( 'PHP Memory Low for WooCommerce', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: 1: current memory limit, 2: recommended for WooCommerce */
					__( 'Your PHP memory limit is %1$s MB. WooCommerce works better with %2$s MB or more (like needing extra RAM when running multiple programs at once). You may experience slowness during checkout or when managing large product catalogs. Consider asking your hosting provider to increase the memory limit.', 'wpshadow' ),
					number_format_i18n( $memory_mb, 0 ),
					$woocommerce_recommended
				),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/php-memory-limit',
				'context'      => array(
					'current_mb'     => $memory_mb,
					'recommended'    => $woocommerce_recommended,
					'has_woocommerce' => true,
				),
			);
		}

		if ( $memory_mb < $recommended ) {
			return array(
				'id'           => self::$slug . '-low',
				'title'        => __( 'PHP Memory Below Recommended', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: 1: current memory limit, 2: recommended */
					__( 'Your PHP memory limit is %1$s MB. While WordPress runs, increasing to %2$s MB or more improves performance and stability (like adding more RAM to a computer). This is especially helpful when using image editing, large imports, or many plugins. Ask your hosting provider about increasing this limit.', 'wpshadow' ),
					number_format_i18n( $memory_mb, 0 ),
					$recommended
				),
				'severity'     => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/php-memory-limit',
				'context'      => array(
					'current_mb'  => $memory_mb,
					'recommended' => $recommended,
				),
			);
		}

		return null; // Memory limit is adequate.
	}
}
