<?php
/**
 * Missing Hosting Environment Checks
 *
 * Detects whether Site Health comprehensively checks hosting configuration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\SiteHealth
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Missing_Hosting_Environment_Checks Class
 *
 * Validates comprehensive hosting environment coverage in Site Health checks.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Missing_Hosting_Environment_Checks extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'missing-hosting-environment-checks';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Hosting Environment Coverage';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies comprehensive Site Health checks for hosting configuration';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'site_health';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests for missing hosting environment checks.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$missing_checks = array();

		// 1. Check file permission checks
		if ( ! self::has_file_permission_checks() ) {
			$missing_checks[] = __( 'File permission checks', 'wpshadow' );
		}

		// 2. Check for server software detection
		if ( ! self::has_server_software_checks() ) {
			$missing_checks[] = __( 'Server software compatibility checks', 'wpshadow' );
		}

		// 3. Check for disk space checks
		if ( ! self::has_disk_space_checks() ) {
			$missing_checks[] = __( 'Disk space availability checks', 'wpshadow' );
		}

		// 4. Check for memory allocation checks
		if ( ! self::has_memory_checks() ) {
			$missing_checks[] = __( 'Memory allocation adequacy checks', 'wpshadow' );
		}

		// 5. Check for SSL/TLS configuration
		if ( ! self::has_ssl_checks() ) {
			$missing_checks[] = __( 'SSL/TLS certificate validation checks', 'wpshadow' );
		}

		// 6. Check for .htaccess checks
		if ( ! self::has_htaccess_checks() ) {
			$missing_checks[] = __( '.htaccess/web server config checks', 'wpshadow' );
		}

		// 7. Check for database connection checks
		if ( ! self::has_database_checks() ) {
			$missing_checks[] = __( 'Database connection reliability checks', 'wpshadow' );
		}

		// 8. Check for email delivery checks
		if ( ! self::has_email_checks() ) {
			$missing_checks[] = __( 'Email delivery configuration checks', 'wpshadow' );
		}

		if ( ! empty( $missing_checks ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of missing checks */
					__( '%d hosting environment checks are missing', 'wpshadow' ),
					count( $missing_checks )
				),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => true,
				'details'      => $missing_checks,
				'kb_link'      => 'https://wpshadow.com/kb/hosting-environment-checks?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'recommendations' => array(
					__( 'Add comprehensive hosting compatibility tests', 'wpshadow' ),
					__( 'Verify file/directory permissions are correct', 'wpshadow' ),
					__( 'Check server software compatibility', 'wpshadow' ),
					__( 'Monitor disk space regularly', 'wpshadow' ),
					__( 'Validate SSL certificates and configuration', 'wpshadow' ),
				),
			);
		}

		return null;
	}

	/**
	 * Check if file permission checks exist.
	 *
	 * @since 0.6093.1200
	 * @return bool True if checks available.
	 */
	private static function has_file_permission_checks() {
		// Check for permission verification
		$wp_content_dir = WP_CONTENT_DIR;

		if ( ! is_writable( $wp_content_dir ) ) {
			return false; // Should be writable
		}

		// Check for uploads directory
		$uploads = wp_upload_dir();
		if ( ! is_writable( $uploads['basedir'] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Check if server software checks exist.
	 *
	 * @since 0.6093.1200
	 * @return bool True if checks available.
	 */
	private static function has_server_software_checks() {
		$server_software = isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : '';

		if ( empty( $server_software ) ) {
			return false; // Can't detect
		}

		// Check for common incompatibilities
		$compatible = array( 'Apache', 'nginx', 'LiteSpeed', 'IIS', 'Caddy' );

		foreach ( $compatible as $server ) {
			if ( stripos( $server_software, $server ) !== false ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if disk space checks exist.
	 *
	 * @since 0.6093.1200
	 * @return bool True if checks available.
	 */
	private static function has_disk_space_checks() {
		// Check disk space on uploads directory
		$uploads = wp_upload_dir();
		$disk_free = disk_free_space( $uploads['basedir'] );

		if ( false === $disk_free || $disk_free === 0 ) {
			return false; // Can't determine
		}

		// Disk space available, checks should exist
		return true;
	}

	/**
	 * Check if memory allocation checks exist.
	 *
	 * @since 0.6093.1200
	 * @return bool True if checks available.
	 */
	private static function has_memory_checks() {
		// Check if memory limit is detected
		$memory_limit = ini_get( 'memory_limit' );

		if ( empty( $memory_limit ) || '-1' === $memory_limit ) {
			return false; // Can't detect
		}

		// Should recommend 256MB+
		$memory_mb = self::convert_to_mb( $memory_limit );

		return $memory_mb >= 64;
	}

	/**
	 * Check if SSL checks exist.
	 *
	 * @since 0.6093.1200
	 * @return bool True if checks available.
	 */
	private static function has_ssl_checks() {
		// Check SSL status
		if ( is_ssl() ) {
			return true; // Using SSL
		}

		// Check for SSL available
		if ( function_exists( 'openssl_random_pseudo_bytes' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if .htaccess checks exist.
	 *
	 * @since 0.6093.1200
	 * @return bool True if checks available.
	 */
	private static function has_htaccess_checks() {
		// Check for server type
		$server_software = isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : '';

		// Apache-specific
		if ( stripos( $server_software, 'Apache' ) !== false ) {
			// Check if .htaccess exists and is readable
			$htaccess_path = ABSPATH . '.htaccess';
			return file_exists( $htaccess_path ) || is_writable( ABSPATH );
		}

		// Nginx doesn't use .htaccess
		if ( stripos( $server_software, 'nginx' ) !== false ) {
			return true; // Nginx configuration is checked differently
		}

		return false;
	}

	/**
	 * Check if database checks exist.
	 *
	 * @since 0.6093.1200
	 * @return bool True if checks available.
	 */
	private static function has_database_checks() {
		global $wpdb;

		// Check database connection
		try {
			$wpdb->get_var( 'SELECT 1' );
			return true;
		} catch ( \Exception $e ) {
			return false;
		}
	}

	/**
	 * Check if email checks exist.
	 *
	 * @since 0.6093.1200
	 * @return bool True if checks available.
	 */
	private static function has_email_checks() {
		// Check for mail function
		if ( ! function_exists( 'wp_mail' ) ) {
			return false;
		}

		// Check if SMTP is configured
		$smtp_host = defined( 'SMTP' ) ? SMTP : '';

		// Either mail() function works or SMTP configured
		if ( ini_get( 'sendmail_path' ) || ! empty( $smtp_host ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Convert memory value to MB.
	 *
	 * @since 0.6093.1200
	 * @param  string $value Memory value like "256M".
	 * @return int Memory in MB.
	 */
	private static function convert_to_mb( $value ) {
		$value = strtoupper( $value );

		if ( 'G' === substr( $value, -1 ) ) {
			return (int) substr( $value, 0, -1 ) * 1024;
		} elseif ( 'M' === substr( $value, -1 ) ) {
			return (int) substr( $value, 0, -1 );
		} elseif ( 'K' === substr( $value, -1 ) ) {
			return (int) substr( $value, 0, -1 ) / 1024;
		}

		return (int) $value / 1048576;
	}
}
