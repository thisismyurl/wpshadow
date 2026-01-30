<?php
/**
 * HTML Verify Favicon Is Defined Diagnostic
 *
 * Verifies favicon is properly defined in the page.
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
 * HTML Verify Favicon Is Defined Diagnostic Class
 *
 * Identifies pages without a favicon, which impacts branding and
 * user experience in browser tabs.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Html_Verify_Favicon_Is_Defined extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'html-verify-favicon-is-defined';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Favicon Not Defined';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects missing favicon definition';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'branding';

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

		$favicon_found = false;

		// Check scripts for favicon links.
		global $wp_scripts;

		if ( ! empty( $wp_scripts ) && isset( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script_obj ) {
				if ( isset( $script_obj->extra['data'] ) ) {
					$data = (string) $script_obj->extra['data'];

					// Check for icon link tags.
					if ( preg_match( '/<link[^>]*rel=["\'](?:icon|shortcut icon|apple-touch-icon)["\'][^>]*>/i', $data ) ) {
						$favicon_found = true;
						break;
					}
				}
			}
		}

		if ( ! $favicon_found ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: */
					__( 'No favicon defined. A favicon is the small icon that appears in browser tabs. It improves brand recognition and user experience. Add a favicon to your site by creating favicon.ico and adding a link in your <head>: <link rel="icon" type="image/x-icon" href="/favicon.ico">', 'wpshadow' )
				),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/html-verify-favicon-is-defined',
				'meta'         => array(
					'improves_branding'  => true,
					'improves_ux'        => true,
				),
			);
		}

		return null;
	}
}
