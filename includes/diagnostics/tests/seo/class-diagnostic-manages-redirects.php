<?php
/**
 * Redirects Properly Managed Diagnostic
 *
 * Tests if old URLs are properly redirected.
 *
 * @since   1.6050.0000
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Redirects Properly Managed Diagnostic Class
 *
 * Verifies that redirects are managed via plugin or server configuration.
 *
 * @since 1.6050.0000
 */
class Diagnostic_Manages_Redirects extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'manages-redirects';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Redirects Properly Managed';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if old URLs are properly redirected';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6050.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$redirect_plugins = array(
			'redirection/redirection.php',
			'safe-redirect-manager/safe-redirect-manager.php',
			'simple-301-redirects/wp-simple-301-redirects.php',
		);

		foreach ( $redirect_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return null;
			}
		}

		$htaccess_path = ABSPATH . '.htaccess';
		if ( file_exists( $htaccess_path ) ) {
			$contents = @file_get_contents( $htaccess_path );
			if ( $contents && ( strpos( $contents, 'Redirect' ) !== false || strpos( $contents, 'RewriteRule' ) !== false ) ) {
				return null;
			}
		}

		$manual_flag = get_option( 'wpshadow_redirects_managed' );
		if ( $manual_flag ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No redirect management detected. Use a redirect tool or server rules to avoid 404s and lost SEO value.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 35,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/redirects-properly-managed',
			'persona'      => 'publisher',
		);
	}
}
