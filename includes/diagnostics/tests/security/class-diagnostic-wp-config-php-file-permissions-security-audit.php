<?php
/**
 * wp-config.php File Permissions Security Audit Diagnostic
 *
 * Validates wp-config.php has restrictive permissions preventing unauthorized access.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26029.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * wp-config.php File Permissions Security Audit Class
 *
 * Tests wp-config.php permissions.
 *
 * @since 1.26029.0000
 */
class Diagnostic_Wp_Config_Php_File_Permissions_Security_Audit extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'wp-config-php-file-permissions-security-audit';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'wp-config.php File Permissions Security Audit';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates wp-config.php has restrictive permissions preventing unauthorized access';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26029.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$config_check = self::check_wp_config_permissions();
		
		if ( $config_check['has_vulnerabilities'] ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $config_check['vulnerabilities'] ),
				'severity'     => 'critical',
				'threat_level' => 95,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/wp-config-php-file-permissions-security-audit',
				'meta'         => array(
					'file_permissions' => $config_check['file_permissions'],
					'file_owner'       => $config_check['file_owner'],
				),
			);
		}

		return null;
	}

	/**
	 * Check wp-config.php permissions and security.
	 *
	 * @since  1.26029.0000
	 * @return array Check results.
	 */
	private static function check_wp_config_permissions() {
		$check = array(
			'has_vulnerabilities' => false,
			'vulnerabilities'     => array(),
			'file_permissions'    => null,
			'file_owner'          => null,
		);

		// Locate wp-config.php.
		$config_file = ABSPATH . 'wp-config.php';
		
		if ( ! file_exists( $config_file ) ) {
			// Check one level up.
			$config_file = dirname( ABSPATH ) . '/wp-config.php';
			
			if ( ! file_exists( $config_file ) ) {
				$check['has_vulnerabilities'] = true;
				$check['vulnerabilities'][] = __( 'wp-config.php not found', 'wpshadow' );
				return $check;
			}
		}

		// Get file permissions.
		$perms = fileperms( $config_file );
		$perms_octal = substr( sprintf( '%o', $perms ), -3 );
		$check['file_permissions'] = $perms_octal;

		// Check for insecure permissions.
		if ( '777' === $perms_octal ) {
			$check['has_vulnerabilities'] = true;
			$check['vulnerabilities'][] = __( 'wp-config.php has 777 permissions (world-writable, CRITICAL vulnerability)', 'wpshadow' );
		} elseif ( '666' === $perms_octal || '644' === $perms_octal ) {
			$check['has_vulnerabilities'] = true;
			$check['vulnerabilities'][] = sprintf(
				/* translators: %s: permission mode */
				__( 'wp-config.php has %s permissions (world-readable, exposes database credentials)', 'wpshadow' ),
				$perms_octal
			);
		} elseif ( '664' === $perms_octal ) {
			$check['has_vulnerabilities'] = true;
			$check['vulnerabilities'][] = __( 'wp-config.php has 664 permissions (group-writable, security risk)', 'wpshadow' );
		}

		// Get file owner.
		$owner = posix_getpwuid( fileowner( $config_file ) );
		$check['file_owner'] = $owner ? $owner['name'] : null;

		// Check if directly accessible via HTTP.
		$config_url = home_url( 'wp-config.php' );
		$response = wp_remote_get( $config_url, array(
			'timeout'     => 5,
			'redirection' => 0,
		) );

		if ( ! is_wp_error( $response ) ) {
			$status = wp_remote_retrieve_response_code( $response );
			
			if ( 200 === $status ) {
				$check['has_vulnerabilities'] = true;
				$check['vulnerabilities'][] = __( 'wp-config.php is directly accessible via HTTP (exposes configuration)', 'wpshadow' );
			}
		}

		return $check;
	}
}
