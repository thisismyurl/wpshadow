<?php
/**
 * Core Auto-Update Configuration Audit Diagnostic
 *
 * Validates auto-update configuration for security and stability.
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
 * Core Auto-Update Configuration Audit Class
 *
 * Tests auto-update configuration.
 *
 * @since 1.26028.1905
 */
class Diagnostic_Core_Auto_Update_Configuration_Audit extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'core-auto-update-configuration-audit';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Core Auto-Update Configuration Audit';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates auto-update configuration for security and stability';

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
		$audit = self::audit_auto_updates();
		
		if ( ! $audit['is_configured_safely'] ) {
			$issues = array();
			
			if ( $audit['all_updates_disabled'] ) {
				$issues[] = __( 'All auto-updates disabled (security risk)', 'wpshadow' );
			} elseif ( ! $audit['minor_updates_enabled'] ) {
				$issues[] = __( 'Minor security updates disabled (critical security risk)', 'wpshadow' );
			}

			if ( ! $audit['notification_enabled'] ) {
				$issues[] = __( 'Update notifications not configured for admins', 'wpshadow' );
			}

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $issues ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/core-auto-update-configuration-audit',
				'meta'         => array(
					'all_updates_disabled'  => $audit['all_updates_disabled'],
					'minor_updates_enabled' => $audit['minor_updates_enabled'],
					'major_updates_enabled' => $audit['major_updates_enabled'],
					'notification_enabled'  => $audit['notification_enabled'],
				),
			);
		}

		return null;
	}

	/**
	 * Audit auto-update configuration.
	 *
	 * @since  1.26028.1905
	 * @return array Audit results.
	 */
	private static function audit_auto_updates() {
		$audit = array(
			'is_configured_safely'  => true,
			'all_updates_disabled'  => false,
			'minor_updates_enabled' => false,
			'major_updates_enabled' => false,
			'notification_enabled'  => true,
		);

		// Check if all auto-updates are disabled.
		if ( defined( 'AUTOMATIC_UPDATER_DISABLED' ) && AUTOMATIC_UPDATER_DISABLED ) {
			$audit['all_updates_disabled'] = true;
			$audit['is_configured_safely'] = false;
			return $audit;
		}

		// Check minor version auto-updates (default: enabled).
		if ( defined( 'WP_AUTO_UPDATE_CORE' ) ) {
			if ( false === WP_AUTO_UPDATE_CORE ) {
				// Explicitly disabled.
				$audit['minor_updates_enabled'] = false;
				$audit['is_configured_safely'] = false;
			} elseif ( 'minor' === WP_AUTO_UPDATE_CORE ) {
				$audit['minor_updates_enabled'] = true;
			} elseif ( true === WP_AUTO_UPDATE_CORE ) {
				// All updates enabled.
				$audit['minor_updates_enabled'] = true;
				$audit['major_updates_enabled'] = true;
			}
		} else {
			// Default behavior: minor updates enabled.
			$audit['minor_updates_enabled'] = true;
		}

		// Check for update notification email.
		$admin_email = get_option( 'admin_email' );
		if ( empty( $admin_email ) ) {
			$audit['notification_enabled'] = false;
			$audit['is_configured_safely'] = false;
		}

		return $audit;
	}
}
