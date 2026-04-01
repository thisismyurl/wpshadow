<?php
/**
 * Nginx Rewrite Configuration Diagnostic
 *
 * Verifies Nginx server has proper rewrite rules configured for WordPress permalinks.
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
 * Nginx Rewrite Configuration Diagnostic Class
 *
 * Checks if Nginx server configuration supports WordPress permalinks.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Nginx_Rewrite_Configuration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'nginx-rewrite-configuration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Nginx Rewrite Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies Nginx permalink support';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'server';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Detect if running on Nginx.
		$server_software = isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : '';

		if ( empty( $server_software ) || false === stripos( $server_software, 'nginx' ) ) {
			return null; // Not Nginx, skip check.
		}

		$issues = array();

		// Check if using custom permalinks.
		$permalink_structure = get_option( 'permalink_structure' );
		if ( empty( $permalink_structure ) ) {
			return null; // Plain permalinks don't need rewrites.
		}

		// Check for common Nginx issues.
		// Nginx doesn't use .htaccess, so check if user is aware.
		$htaccess_file = get_home_path() . '.htaccess';
		if ( file_exists( $htaccess_file ) ) {
			$htaccess_content = file_get_contents( $htaccess_file );
			if ( false !== strpos( $htaccess_content, '# BEGIN WordPress' ) ) {
				$issues[] = __( 'Nginx server detected but .htaccess file with WordPress rules found (htaccess is ignored by Nginx)', 'wpshadow' );
			}
		}

		// Test if permalinks are working.
		// Create a test by checking if index.php is in REQUEST_URI for a non-admin page.
		if ( ! is_admin() && isset( $_SERVER['REQUEST_URI'] ) ) {
			$request_uri = sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) );
			if ( false !== strpos( $request_uri, 'index.php' ) ) {
				$issues[] = __( 'Permalinks may not be configured correctly in Nginx (index.php appears in URLs)', 'wpshadow' );
			}
		}

		// Check for common Nginx configuration markers.
		$nginx_conf_file = '/etc/nginx/nginx.conf';
		$has_access      = false;

		if ( file_exists( $nginx_conf_file ) && is_readable( $nginx_conf_file ) ) {
			$has_access    = true;
			$nginx_content = file_get_contents( $nginx_conf_file );

			// Check for WordPress try_files directive.
			if ( false === strpos( $nginx_content, 'try_files' ) ) {
				$issues[] = __( 'Nginx configuration may be missing try_files directive', 'wpshadow' );
			}

			// Check for proper index.php handling.
			if ( false === strpos( $nginx_content, 'index.php' ) ) {
				$issues[] = __( 'Nginx configuration may not be properly configured for PHP', 'wpshadow' );
			}
		}

		// Provide guidance if no access to Nginx config.
		if ( ! $has_access && ! empty( $issues ) ) {
			$issues[] = __( 'Cannot verify Nginx configuration file directly', 'wpshadow' );
		} elseif ( ! $has_access && empty( $issues ) ) {
			// If we can't check the config but permalinks seem to work, still warn.
			$issues[] = __( 'Running on Nginx - verify configuration includes WordPress rewrite rules', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/nginx-rewrite-configuration?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
