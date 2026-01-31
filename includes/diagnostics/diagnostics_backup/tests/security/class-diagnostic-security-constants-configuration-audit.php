<?php
/**
 * Security Constants Configuration Audit Diagnostic
 *
 * Validates critical security constants are properly configured.
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
 * Security Constants Configuration Audit Class
 *
 * Tests security constants configuration.
 *
 * @since 1.26029.0000
 */
class Diagnostic_Security_Constants_Configuration_Audit extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'security-constants-configuration-audit';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Security Constants Configuration Audit';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates critical security constants are properly configured';

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
		$constants_check = self::check_security_constants();
		
		if ( $constants_check['has_concerns'] ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $constants_check['concerns'] ),
				'severity'     => 'high',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/security-constants-configuration-audit',
				'meta'         => array(
					'file_edit_disabled'  => $constants_check['file_edit_disabled'],
					'ssl_forced'          => $constants_check['ssl_forced'],
					'auto_update_enabled' => $constants_check['auto_update_enabled'],
				),
			);
		}

		return null;
	}

	/**
	 * Check security constants configuration.
	 *
	 * @since  1.26029.0000
	 * @return array Check results.
	 */
	private static function check_security_constants() {
		$check = array(
			'has_concerns'        => false,
			'concerns'            => array(),
			'file_edit_disabled'  => false,
			'ssl_forced'          => false,
			'auto_update_enabled' => true,
		);

		// Check DISALLOW_FILE_EDIT.
		if ( ! defined( 'DISALLOW_FILE_EDIT' ) || ! DISALLOW_FILE_EDIT ) {
			$check['has_concerns'] = true;
			$check['concerns'][] = __( 'DISALLOW_FILE_EDIT not enabled (theme/plugin editor accessible to attackers)', 'wpshadow' );
		} else {
			$check['file_edit_disabled'] = true;
		}

		// Check FORCE_SSL_ADMIN.
		if ( ! defined( 'FORCE_SSL_ADMIN' ) || ! FORCE_SSL_ADMIN ) {
			if ( is_ssl() ) {
				$check['has_concerns'] = true;
				$check['concerns'][] = __( 'FORCE_SSL_ADMIN not set (admin login can occur over HTTP)', 'wpshadow' );
			}
		} else {
			$check['ssl_forced'] = true;
		}

		// Check AUTOMATIC_UPDATER_DISABLED.
		if ( defined( 'AUTOMATIC_UPDATER_DISABLED' ) && AUTOMATIC_UPDATER_DISABLED ) {
			$check['has_concerns'] = true;
			$check['auto_update_enabled'] = false;
			$check['concerns'][] = __( 'Auto-updater disabled (missing critical security updates)', 'wpshadow' );
		}

		// Check WP_HTTP_BLOCK_EXTERNAL.
		if ( ! defined( 'WP_HTTP_BLOCK_EXTERNAL' ) || ! WP_HTTP_BLOCK_EXTERNAL ) {
			$check['has_concerns'] = true;
			$check['concerns'][] = __( 'WP_HTTP_BLOCK_EXTERNAL not set (plugins can make unrestricted external requests)', 'wpshadow' );
		}

		// Check DISALLOW_FILE_MODS (should be false to allow updates).
		if ( defined( 'DISALLOW_FILE_MODS' ) && DISALLOW_FILE_MODS ) {
			$check['has_concerns'] = true;
			$check['concerns'][] = __( 'DISALLOW_FILE_MODS enabled (blocks plugin/theme updates, creates security risk)', 'wpshadow' );
		}

		return $check;
	}
}
