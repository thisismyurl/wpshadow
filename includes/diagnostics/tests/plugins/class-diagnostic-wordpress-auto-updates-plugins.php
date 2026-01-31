<?php
/**
 * Wordpress Auto Updates Plugins Diagnostic
 *
 * Wordpress Auto Updates Plugins issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1254.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wordpress Auto Updates Plugins Diagnostic Class
 *
 * @since 1.1254.0000
 */
class Diagnostic_WordpressAutoUpdatesPlugins extends Diagnostic_Base {

	protected static $slug = 'wordpress-auto-updates-plugins';
	protected static $title = 'Wordpress Auto Updates Plugins';
	protected static $description = 'Wordpress Auto Updates Plugins issue detected';
	protected static $family = 'functionality';

	public static function check() {
		$issues = array();

		// Check 1: Auto-updates globally disabled
		if ( defined( 'AUTOMATIC_UPDATER_DISABLED' ) && AUTOMATIC_UPDATER_DISABLED ) {
			$issues[] = 'automatic updates globally disabled';
		}

		// Check 2: Plugin auto-updates specifically disabled
		if ( defined( 'WP_AUTO_UPDATE_CORE' ) && false === WP_AUTO_UPDATE_CORE ) {
			$issues[] = 'core auto-updates disabled';
		}

		// Check 3: Check enabled auto-update plugins
		$auto_updates = get_option( 'auto_update_plugins', array() );
		$all_plugins = get_plugins();
		if ( empty( $auto_updates ) && ! empty( $all_plugins ) ) {
			$issues[] = 'no plugins configured for auto-updates';
		} elseif ( ! empty( $all_plugins ) ) {
			$update_percent = ( count( $auto_updates ) / count( $all_plugins ) ) * 100;
			if ( $update_percent < 20 ) {
				$issues[] = 'only ' . round( $update_percent ) . '% of plugins auto-updating';
			}
		}

		// Check 4: Failed auto-update attempts
		$failed_updates = get_option( 'auto_plugin_theme_update_emails', array() );
		if ( ! empty( $failed_updates ) && is_array( $failed_updates ) ) {
			$recent_failures = array_filter( $failed_updates, function( $email ) {
				return isset( $email['time'] ) && $email['time'] > strtotime( '-7 days' );
			} );
			if ( count( $recent_failures ) > 3 ) {
				$issues[] = count( $recent_failures ) . ' auto-update failures in last 7 days';
			}
		}

		// Check 5: VCS detection (should disable auto-updates)
		if ( is_dir( ABSPATH . '.git' ) || is_dir( ABSPATH . '.svn' ) ) {
			if ( ! empty( $auto_updates ) ) {
				$issues[] = 'auto-updates enabled on version-controlled site';
			}
		}

		// Check 6: Notification email configured
		$admin_email = get_option( 'admin_email', '' );
		if ( ! empty( $auto_updates ) && ( empty( $admin_email ) || ! is_email( $admin_email ) ) ) {
			$issues[] = 'no valid admin email for update notifications';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 70, 40 + ( count( $issues ) * 6 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'WordPress plugin auto-update issues: ' . implode( ', ', $issues ),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/wordpress-auto-updates-plugins',
			);
		}

		return null;
	}
}
