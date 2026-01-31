<?php
/**
 * Diagnostic: WebP Image Decoding
 *
 * Checks if WebP image decoding is supported by the server.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Performance
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Webp_Decoding
 *
 * Tests if PHP can decode WebP images.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Webp_Decoding extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'webp-decoding';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'WebP Image Decoding';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if server supports WebP image decoding';

	/**
	 * Check WebP support.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		// Check GD library for WebP support.
		if ( ! extension_loaded( 'gd' ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'GD image library is not loaded. WebP support cannot be verified. Install the gd PHP extension for image processing.', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/webp_decoding',
				'meta'        => array(
					'gd_loaded' => false,
				),
			);
		}

		// Check if GD was compiled with WebP support.
		$gd_info = gd_info();

		if ( ! isset( $gd_info['WebP Support'] ) || ! $gd_info['WebP Support'] ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'GD library is loaded but WebP support is not compiled in. Recompile PHP GD with --with-webp to enable WebP image handling.', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/webp_decoding',
				'meta'        => array(
					'gd_loaded'      => true,
					'webp_support'   => false,
				),
			);
		}

		return null;
	}
}
