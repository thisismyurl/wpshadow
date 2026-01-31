<?php
/**
 * Theme Nonce Verification Diagnostic
 *
 * Checks for nonce verification in theme-admin actions.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2240
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Nonce Verification Diagnostic
 *
 * Ensures admin actions registered by themes verify nonces.
 *
 * @since 1.2601.2240
 */
class Diagnostic_Theme_Nonce_Verification extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-nonce-verification';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Nonce Verification';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for nonce verification in theme-admin actions';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2240
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$theme_dir = wp_get_theme()->get_stylesheet_directory();
		$functions_file = $theme_dir . '/functions.php';

		if ( ! file_exists( $functions_file ) ) {
			return null;
		}

		$content = file_get_contents( $functions_file, false, null, 0, 60000 );
		if ( false === $content ) {
			return null;
		}

		if ( false !== strpos( $content, 'admin_post' ) || false !== strpos( $content, 'admin_init' ) ) {
			if ( false === strpos( $content, 'check_admin_referer' ) && false === strpos( $content, 'wp_verify_nonce' ) ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'Theme admin actions may be missing nonce verification', 'wpshadow' ),
					'severity'     => 'high',
					'threat_level' => 80,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/theme-nonce-verification',
					'details'      => array(
						'issues' => array(
							__( 'Admin hooks detected without nonce verification', 'wpshadow' ),
						),
					),
				);
			}
		}

		return null;
	}
}
