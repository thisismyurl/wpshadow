<?php
/**
 * wp-config.php Location Security Diagnostic
 *
 * Verifies wp-config.php is protected and optimally located.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26028.1905
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * wp-config.php Location Security Class
 *
 * Tests wp-config.php security.
 *
 * @since 1.26028.1905
 */
class Diagnostic_Wp_Config_Php_Location_Security extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'wp-config-php-location-security';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'wp-config.php Location Security';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies wp-config.php is protected and optimally located';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26028.1905
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$security_check = self::check_wp_config_security();
		
		if ( ! $security_check['is_secure'] ) {
			$issues = array();
			
			if ( $security_check['permissions_too_open'] ) {
				$issues[] = sprintf(
					/* translators: %s: file permissions */
					__( 'wp-config.php permissions too open (%s - should be 600 or 640)', 'wpshadow' ),
					$security_check['current_permissions']
				);
			}

			if ( $security_check['web_accessible'] ) {
				$issues[] = __( 'wp-config.php may be web-accessible (should return 403)', 'wpshadow' );
			}

			if ( $security_check['sample_exists'] ) {
				$issues[] = __( 'wp-config-sample.php exists (should be deleted)', 'wpshadow' );
			}

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $issues ),
				'severity'     => 'critical',
				'threat_level' => 90,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/wp-config-php-location-security',
				'meta'         => array(
					'permissions_too_open'  => $security_check['permissions_too_open'],
					'current_permissions'   => $security_check['current_permissions'],
					'web_accessible'        => $security_check['web_accessible'],
					'sample_exists'         => $security_check['sample_exists'],
					'location'              => $security_check['location'],
				),
			);
		}

		return null;
	}

	/**
	 * Check wp-config.php security.
	 *
	 * @since  1.26028.1905
	 * @return array Check results.
	 */
	private static function check_wp_config_security() {
		$check = array(
			'is_secure'            => true,
			'permissions_too_open' => false,
			'current_permissions'  => '',
			'web_accessible'       => false,
			'sample_exists'        => false,
			'location'             => 'root',
		);

		$config_path = ABSPATH . 'wp-config.php';
		
		// Check if config is one level up (more secure).
		if ( ! file_exists( $config_path ) && file_exists( dirname( ABSPATH ) . '/wp-config.php' ) ) {
			$config_path = dirname( ABSPATH ) . '/wp-config.php';
			$check['location'] = 'parent';
		}

		if ( file_exists( $config_path ) ) {
			// Check file permissions.
			$perms = fileperms( $config_path );
			$perms_octal = substr( sprintf( '%o', $perms ), -3 );
			$check['current_permissions'] = $perms_octal;

			// Permissions should be 600 (owner only) or 640 (owner + group read).
			if ( ! in_array( $perms_octal, array( '600', '640', '400' ), true ) ) {
				$check['permissions_too_open'] = true;
				$check['is_secure'] = false;
			}
		}

		// Check if wp-config.php is web-accessible.
		$config_url = get_site_url() . '/wp-config.php';
		$response = wp_remote_head( $config_url, array( 'timeout' => 5 ) );
		
		if ( ! is_wp_error( $response ) ) {
			$status_code = wp_remote_retrieve_response_code( $response );
			
			// Should be 403 Forbidden or 404.
			if ( 200 === $status_code ) {
				$check['web_accessible'] = true;
				$check['is_secure'] = false;
			}
		}

		// Check if sample file exists.
		$sample_path = ABSPATH . 'wp-config-sample.php';
		if ( file_exists( $sample_path ) ) {
			$check['sample_exists'] = true;
			$check['is_secure'] = false;
		}

		return $check;
	}
}
