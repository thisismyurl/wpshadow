<?php
/**
 * Legacy JavaScript Library Usage Not Detected Diagnostic
 *
 * Checks if legacy JavaScript libraries are being used.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2348
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Legacy JavaScript Library Usage Not Detected Diagnostic Class
 *
 * Detects legacy JavaScript libraries.
 *
 * @since 1.2601.2348
 */
class Diagnostic_Legacy_JavaScript_Library_Usage_Not_Detected extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'legacy-javascript-library-usage-not-detected';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Legacy JavaScript Library Usage Not Detected';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if legacy JavaScript libraries are used';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2348
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wp_scripts;

		// Check for jQuery library (legacy indicator)
		$legacy_libs = array( 'jquery', 'jquery-ui-core', 'jquery-ui-widget' );
		$found_legacy = false;

		foreach ( $legacy_libs as $lib ) {
			if ( $wp_scripts->query( $lib, 'registered' ) ) {
				$found_legacy = true;
				break;
			}
		}

		if ( $found_legacy ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Legacy JavaScript libraries are enqueued. Consider using modern alternatives like vanilla JavaScript or Webpack.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/legacy-javascript-library-usage-not-detected',
			);
		}

		return null;
	}
}
