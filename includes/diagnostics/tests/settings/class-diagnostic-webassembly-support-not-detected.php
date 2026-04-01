<?php
/**
 * WebAssembly Support Not Detected Diagnostic
 *
 * Checks if WebAssembly support is detected.
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
 * WebAssembly Support Not Detected Diagnostic Class
 *
 * Detects missing WebAssembly support.
 *
 * @since 0.6093.1200
 */
class Diagnostic_WebAssembly_Support_Not_Detected extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'webassembly-support-not-detected';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'WebAssembly Support Not Detected';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if WebAssembly support is detected';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for WebAssembly detection
		if ( ! has_filter( 'wp_head', 'detect_wasm_support' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'WebAssembly support is not detected. Implement WASM detection to enable advanced client-side processing for image compression, video transcoding, and heavy computations.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 5,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/webassembly-support-not-detected?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
