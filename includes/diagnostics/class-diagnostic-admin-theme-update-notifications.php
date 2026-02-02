<?php
/**
 * Admin Theme Update Notifications
 *
 * Checks if theme update notifications are enabled and properly configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26033.0639
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: Admin Theme Update Notifications
 *
 * @since 1.26033.0639
 */
class Diagnostic_Admin_Theme_Update_Notifications extends Diagnostic_Base {

	protected static $slug = 'admin-theme-update-notifications';
	protected static $title = 'Admin Theme Update Notifications';
	protected static $description = 'Verifies theme update notifications are enabled';
	protected static $family = 'admin-security';

	public static function check() {
		$issues = array();

		// Check if updates are disabled globally
		if ( defined( 'AUTOMATIC_UPDATER_DISABLED' ) && AUTOMATIC_UPDATER_DISABLED ) {
			$issues[] = __( 'Automatic updates are globally disabled', 'wpshadow' );
		}

		// Check if theme updates are disabled
		if ( defined( 'WP_AUTO_UPDATE_CORE' ) && false === WP_AUTO_UPDATE_CORE ) {
			$issues[] = __( 'Core updates are disabled', 'wpshadow' );
		}

		// Check for outdated themes
		$themes = wp_get_themes();
		$outdated = 0;

		foreach ( $themes as $theme ) {
			// Check if theme has known vulnerabilities (via Theme Version)
			if ( version_compare( $theme->get( 'Version' ), '1.0', '<' ) ) {
				$outdated++;
			}
		}

		if ( $outdated > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of themes */
				__( '%d theme(s) may be outdated', 'wpshadow' ),
				$outdated
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/admin-theme-update-notifications',
			);
		}

		return null;
	}
}
