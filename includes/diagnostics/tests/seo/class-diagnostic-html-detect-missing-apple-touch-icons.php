<?php
/**
 * HTML Detect Missing Apple Touch Icons Diagnostic
 *
 * Detects missing Apple Touch Icons for iOS.
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
 * HTML Detect Missing Apple Touch Icons Diagnostic Class
 *
 * Identifies pages missing Apple Touch Icon links for iOS devices,
 * which enables better bookmark and home screen appearance.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Html_Detect_Missing_Apple_Touch_Icons extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'html-detect-missing-apple-touch-icons';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Missing Apple Touch Icons';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects missing Apple Touch Icon links for iOS';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'mobile';

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

		$apple_icons_found = false;
		$icon_types       = array();

		// Check scripts for apple touch icon links.
		global $wp_scripts;

		if ( ! empty( $wp_scripts ) && isset( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script_obj ) {
				if ( isset( $script_obj->extra['data'] ) ) {
					$data = (string) $script_obj->extra['data'];

					// Check for apple-touch-icon link tags.
					if ( preg_match_all( '/<link[^>]*rel=["\']apple-touch-icon["\'][^>]*>/i', $data, $matches ) ) {
						$apple_icons_found = true;

						// Check sizes.
						foreach ( $matches[0] as $link_tag ) {
							if ( preg_match( '/sizes=["\']([^"\']+)["\']/', $link_tag, $m ) ) {
								$icon_types[] = $m[1];
							}
						}
					}
				}
			}
		}

		if ( ! $apple_icons_found ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: */
					__( 'No Apple Touch Icons detected. When users save your site as a bookmark or add to home screen on iOS, they see a placeholder icon unless you provide one. Add these to your <head>: <link rel="apple-touch-icon" href="/apple-touch-icon-180x180.png"> and other sizes (120x120, 152x152, 167x167, 180x180).', 'wpshadow' )
				),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/html-detect-missing-apple-touch-icons',
				'meta'         => array(
					'optional'              => true,
					'improves_home_screen'  => true,
					'recommended_sizes'     => array( '120x120', '152x152', '167x167', '180x180' ),
				),
			);
		}

		return null;
	}
}
