<?php
/**
 * wp-config.php Security Constants Audit Diagnostic
 *
 * Checks for security-enhancing constants in wp-config.php.
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
 * wp-config Security Constants Audit Class
 *
 * Tests security constants.
 *
 * @since 1.26028.1905
 */
class Diagnostic_Wp_Config_Php_Security_Constants_Audit extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'wp-config-php-security-constants-audit';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'wp-config.php Security Constants Audit';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for security-enhancing constants in wp-config.php';

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
		$constants_check = self::check_security_constants();
		
		if ( $constants_check['has_issues'] ) {
			$issues = array();
			
			if ( $constants_check['file_edit_not_disabled'] ) {
				$issues[] = __( 'DISALLOW_FILE_EDIT not set (theme/plugin editor accessible)', 'wpshadow' );
			}

			if ( $constants_check['ssl_admin_not_forced'] ) {
				$issues[] = __( 'FORCE_SSL_ADMIN not enabled (credentials at risk)', 'wpshadow' );
			}

			if ( $constants_check['debug_enabled'] ) {
				$issues[] = __( 'WP_DEBUG enabled on production (exposes sensitive data)', 'wpshadow' );
			}

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $issues ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/wp-config-php-security-constants-audit',
				'meta'         => array(
					'file_edit_not_disabled' => $constants_check['file_edit_not_disabled'],
					'ssl_admin_not_forced'   => $constants_check['ssl_admin_not_forced'],
					'debug_enabled'          => $constants_check['debug_enabled'],
				),
			);
		}

		return null;
	}

	/**
	 * Check security constants.
	 *
	 * @since  1.26028.1905
	 * @return array Check results.
	 */
	private static function check_security_constants() {
		$check = array(
			'has_issues'             => false,
			'file_edit_not_disabled' => false,
			'ssl_admin_not_forced'   => false,
			'debug_enabled'          => false,
		);

		// Check DISALLOW_FILE_EDIT.
		if ( ! defined( 'DISALLOW_FILE_EDIT' ) || ! DISALLOW_FILE_EDIT ) {
			$check['file_edit_not_disabled'] = true;
			$check['has_issues'] = true;
		}

		// Check FORCE_SSL_ADMIN (only if site uses HTTPS).
		if ( is_ssl() ) {
			if ( ! defined( 'FORCE_SSL_ADMIN' ) || ! FORCE_SSL_ADMIN ) {
				$check['ssl_admin_not_forced'] = true;
				$check['has_issues'] = true;
			}
		}

		// Check WP_DEBUG on production.
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			// Check if this is likely production.
			$env_type = wp_get_environment_type();
			
			if ( 'production' === $env_type || 'local' !== $env_type ) {
				$check['debug_enabled'] = true;
				$check['has_issues'] = true;
			}
		}

		return $check;
	}
}
