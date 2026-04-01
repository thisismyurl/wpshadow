<?php
/**
 * False Positive Security Warnings
 *
 * Tests whether Site Health flags non-issues as critical security problems.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Export
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_False_Positive_Security_Warnings Class
 *
 * Validates Site Health recommendations and detects false positives.
 * Checks for incorrect security warnings that cause alarm fatigue.
 *
 * @since 0.6093.1200
 */
class Diagnostic_False_Positive_Security_Warnings extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'false-positive-security-warnings';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'False Positive Security Warnings';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects inaccurate Site Health security warnings';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'export';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests for false positive security warnings from Site Health.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$false_positives = array();

		// 1. Check HTTPS false positive
		$https_issue = self::check_https_false_positive();
		if ( $https_issue ) {
			$false_positives[] = $https_issue;
		}

		// 2. Check file permission false positive
		$perms_issue = self::check_file_permission_false_positive();
		if ( $perms_issue ) {
			$false_positives[] = $perms_issue;
		}

		// 3. Check REST API authentication false positive
		$rest_issue = self::check_rest_api_false_positive();
		if ( $rest_issue ) {
			$false_positives[] = $rest_issue;
		}

		// 4. Check plugin vulnerability false positive
		$vuln_issue = self::check_plugin_vulnerability_false_positive();
		if ( $vuln_issue ) {
			$false_positives[] = $vuln_issue;
		}

		// 5. Check PHP version false positive
		$php_issue = self::check_php_version_false_positive();
		if ( $php_issue ) {
			$false_positives[] = $php_issue;
		}

		// 6. Check outdated recommendations
		$outdated_issue = self::check_outdated_recommendations();
		if ( $outdated_issue ) {
			$false_positives[] = $outdated_issue;
		}

		if ( ! empty( $false_positives ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of false positives */
					__( '%d false positive security warnings detected', 'wpshadow' ),
					count( $false_positives )
				),
				'severity'     => 'medium',
				'threat_level' => 65,
				'auto_fixable' => true,
				'details'      => $false_positives,
				'kb_link'      => 'https://wpshadow.com/kb/false-positive-security-warnings?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'recommendations' => array(
					__( 'Review actual security status vs Site Health warnings', 'wpshadow' ),
					__( 'Disable false-positive prone checks', 'wpshadow' ),
					__( 'Use custom Site Health checks with accurate detection', 'wpshadow' ),
					__( 'Educate team on warning accuracy', 'wpshadow' ),
				),
			);
		}

		return null;
	}

	/**
	 * Check for HTTPS false positive.
	 *
	 * @since 0.6093.1200
	 * @return string|null Issue description or null if no issue.
	 */
	private static function check_https_false_positive() {
		// Check if actually using HTTPS
		if ( is_ssl() ) {
			return null; // Using HTTPS correctly
		}

		// If not using HTTPS but might be behind proxy
		$forwarded_proto = isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) ) : '';

		if ( 'https' === $forwarded_proto ) {
			return __( 'Site Health warns about HTTPS but site uses reverse proxy with SSL (false positive)', 'wpshadow' );
		}

		return null;
	}

	/**
	 * Check for file permission false positive.
	 *
	 * @since 0.6093.1200
	 * @return string|null Issue description or null if no issue.
	 */
	private static function check_file_permission_false_positive() {
		// Check wp-content permissions
		$wp_content_dir = WP_CONTENT_DIR;

		if ( ! is_writable( $wp_content_dir ) ) {
			// Check if it's intentionally read-only for security
			$perms = substr( sprintf( '%o', fileperms( $wp_content_dir ) ), -4 );

			// If 555 or 755 with non-world-writable, might be intentional
			if ( '555' === $perms || '755' === $perms ) {
				return sprintf(
					/* translators: %s: permission bits */
					__( 'Site Health warns about wp-content permissions (%s) but may be intentionally hardened', 'wpshadow' ),
					$perms
				);
			}
		}

		return null;
	}

	/**
	 * Check for REST API authentication false positive.
	 *
	 * @since 0.6093.1200
	 * @return string|null Issue description or null if no issue.
	 */
	private static function check_rest_api_false_positive() {
		// Check if REST API is properly secured
		$rest_enabled = get_option( 'rest_api_enabled', true );

		if ( ! $rest_enabled ) {
			return null; // REST API properly disabled
		}

		// Check for authentication plugin
		if ( is_plugin_active( 'rest-api-authentication/rest-api-authentication.php' ) ||
			 is_plugin_active( 'jwt-authentication-for-wp-rest-api/jwt-authentication-for-wp-rest-api.php' ) ) {
			return null; // Authentication plugin active
		}

		// Check if site is behind security proxy
		if ( isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) ) {
			return __( 'Site Health may warn about REST API security but site is behind proxy with authentication', 'wpshadow' );
		}

		return null;
	}

	/**
	 * Check for plugin vulnerability false positive.
	 *
	 * @since 0.6093.1200
	 * @return string|null Issue description or null if no issue.
	 */
	private static function check_plugin_vulnerability_false_positive() {
		// Check plugin update status
		$plugins = get_plugins();
		$available_updates = get_plugin_updates();

		if ( empty( $available_updates ) ) {
			return null; // All plugins up to date
		}

		// Check if updates are beta/RC versions (might not want to update)
		foreach ( $available_updates as $plugin_file => $plugin_data ) {
			$version = $plugin_data->update->new_version;

			// Check for beta/RC versions
			if ( preg_match( '/(beta|rc|alpha)/i', $version ) ) {
				return sprintf(
					/* translators: %s: plugin name, %s: version */
					__( 'Site Health may flag %s as vulnerable but latest version (%s) is beta/RC', 'wpshadow' ),
					esc_html( $plugin_data['Name'] ),
					esc_html( $version )
				);
			}
		}

		return null;
	}

	/**
	 * Check for PHP version false positive.
	 *
	 * @since 0.6093.1200
	 * @return string|null Issue description or null if no issue.
	 */
	private static function check_php_version_false_positive() {
		global $wp_version;

		$php_version = phpversion();
		$wp_major    = (int) $wp_version; // Major version

		// Check if PHP is actually compatible
		if ( version_compare( $php_version, '7.4', '<' ) ) {
			// Legitimately old PHP
			return null;
		}

		// If PHP is 8.0+ but WordPress is 5.9+, should be compatible
		if ( version_compare( $php_version, '8.0', '>=' ) && $wp_major >= 5 ) {
			// Check if Site Health incorrectly flags as incompatible
			$incompatible_plugins = array();
			$plugins              = get_plugins();

			foreach ( $plugins as $plugin_file => $plugin_data ) {
				// Plugins might have incorrect "requires_php" in headers
				$requires_php = $plugin_data['RequiresPHP'] ?? '';

				if ( $requires_php && version_compare( $php_version, $requires_php, '<' ) ) {
					$incompatible_plugins[] = $plugin_data['Name'];
				}
			}

			if ( count( $incompatible_plugins ) === 1 ) {
				// Single plugin with false-positive requirement
				return sprintf(
					/* translators: %s: plugin name */
					__( 'Site Health may flag %s as incompatible but it works correctly on PHP 8+', 'wpshadow' ),
					esc_html( $incompatible_plugins[0] )
				);
			}
		}

		return null;
	}

	/**
	 * Check for outdated recommendations.
	 *
	 * @since 0.6093.1200
	 * @return string|null Issue description or null if no issue.
	 */
	private static function check_outdated_recommendations() {
		global $wp_version;

		// Check if WordPress version is recent
		if ( version_compare( $wp_version, '6.0', '<' ) ) {
			return sprintf(
				/* translators: %s: WordPress version */
				__( 'WordPress %s has outdated security recommendations', 'wpshadow' ),
				$wp_version
			);
		}

		// Check for deprecated REST API warnings
		$rest_warning = get_option( '_site_health_rest_api_warning_dismissed', false );
		if ( $rest_warning && is_ssl() ) {
			return __( 'Site Health may show outdated REST API security warnings despite HTTPS being enabled', 'wpshadow' );
		}

		return null;
	}
}
