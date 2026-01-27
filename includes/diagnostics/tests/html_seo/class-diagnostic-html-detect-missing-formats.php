<?php
/**
 * HTML Detect Missing Link Icon Formats Diagnostic
 *
 * Detects missing favicon link formats.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\HTML
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * HTML Detect Missing Link Icon Formats Diagnostic Class
 *
 * Identifies pages missing proper favicon link formats, which ensures
 * consistent icon display across browsers and devices.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Html_Detect_Missing_Formats extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'html-detect-missing-formats';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Missing Favicon Link Formats';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects missing favicon <link> tag formats';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'html';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( is_admin() ) {
			return null;
		}

		$favicon_formats = array();
		$missing_formats = array();

		// Check scripts for favicon link tags.
		global $wp_scripts;

		if ( ! empty( $wp_scripts ) && isset( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script_obj ) {
				if ( isset( $script_obj->extra['data'] ) ) {
					$data = (string) $script_obj->extra['data'];

					// Check for icon link tags.
					if ( preg_match_all( '/<link[^>]*rel=["\'](?:icon|shortcut icon)["\'][^>]*>/i', $data, $matches ) ) {
						foreach ( $matches[0] as $link_tag ) {
							// Extract type/format.
							$type = 'unknown';

							if ( preg_match( '/type=["\']([^"\']+)["\']/', $link_tag, $m ) ) {
								$type = $m[1];
							}

							$favicon_formats[] = $type;
						}
					}
				}
			}
		}

		// Recommended favicon formats: ico, png, svg.
		$recommended = array( 'image/x-icon', 'image/png', 'image/svg+xml' );

		foreach ( $recommended as $format ) {
			if ( ! in_array( $format, $favicon_formats, true ) ) {
				$missing_formats[] = $format;
			}
		}

		if ( ! empty( $missing_formats ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: format list */
					__( 'Missing favicon link formats. Browsers expect multiple favicon formats: .ico (legacy), .png (modern), and .svg (scalable). Add these to your <head>: <link rel="icon" type="image/x-icon" href="/favicon.ico"> <link rel="icon" type="image/png" href="/favicon.png"> <link rel="icon" type="image/svg+xml" href="/favicon.svg">%s', 'wpshadow' )
				),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/html-detect-missing-formats',
				'meta'         => array(
					'current'       => $favicon_formats,
					'missing'       => $missing_formats,
					'recommended'   => $recommended,
				),
			);
		}

		return null;
	}
}
