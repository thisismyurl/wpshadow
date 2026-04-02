<?php
/**
 * Automated Update Checking Not Configured
 *
 * Checks if WordPress, plugins, and themes are configured for automated
 * update checking and vulnerability notifications.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Automated_Update_Checking_Not_Configured Class
 *
 * Detects when sites don't have proactive update monitoring configured,
 * leaving them vulnerable to known exploits for days or weeks.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Automated_Update_Checking_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'automated-update-checking-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Automated Update Checking Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies automated checking for WordPress, plugin, and theme updates with vulnerability alerts';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for pro security module first.
		if ( Upgrade_Path_Helper::has_pro_product( 'security' ) ) {
			return null;
		}

		// Check if WordPress auto-updates are disabled.
		$core_updates_disabled = defined( 'AUTOMATIC_UPDATER_DISABLED' ) && AUTOMATIC_UPDATER_DISABLED;
		$wp_auto_update_core   = get_option( 'auto_update_core_major', false );

		// Check for automated update monitoring plugins.
		$monitoring_plugins = array(
			'easy-updates-manager/easy-updates-manager.php', // Easy Updates Manager.
			'wp-updates-notifier/wp-updates-notifier.php',   // WP Updates Notifier.
			'wordfence/wordfence.php',                       // Wordfence (includes update alerts).
			'jetpack/jetpack.php',                           // Jetpack (includes update monitoring).
		);

		$monitoring_active = false;
		foreach ( $monitoring_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$monitoring_active = true;
				break;
			}
		}

		// Check if user has configured manual update monitoring.
		$manual_monitoring = get_option( 'wpshadow_update_monitoring_configured', false );

		// Check for email notifications configured.
		$admin_email    = get_option( 'admin_email' );
		$email_disabled = defined( 'WP_AUTO_UPDATE_CORE' ) && false === WP_AUTO_UPDATE_CORE;

		// If monitoring is active OR manual monitoring configured, return null.
		if ( $monitoring_active || $manual_monitoring ) {
			return null;
		}

		// Count outdated plugins/themes.
		$update_plugins = get_site_transient( 'update_plugins' );
		$update_themes  = get_site_transient( 'update_themes' );
		$outdated_count = 0;

		if ( is_object( $update_plugins ) && ! empty( $update_plugins->response ) ) {
			$outdated_count += count( $update_plugins->response );
		}

		if ( is_object( $update_themes ) && ! empty( $update_themes->response ) ) {
			$outdated_count += count( $update_themes->response );
		}

		// No monitoring configured.
		$finding = array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of outdated components */
				__(
					'Your site is not actively monitoring for available updates. You currently have %d outdated components. Attackers scan for known vulnerabilities within hours of disclosure (WPScan database has 25,000+ known WordPress vulnerabilities). Without automated alerts, you only discover updates when you manually check (weeks or months later). By then, automated bots have already exploited the vulnerabilities. 43%% of cyberattacks target outdated software (Verizon DBIR). Automated monitoring provides: immediate vulnerability alerts, update availability notifications, security patch prioritization, compatibility warnings before updating.',
					'wpshadow'
				),
				$outdated_count
			),
			'severity'     => 'high',
			'threat_level' => 75,
			'auto_fixable' => false,
			'kb_link'      => 'automated-update-monitoring-setup',
			'details'      => array(
				'outdated_count'         => $outdated_count,
				'core_updates_disabled'  => $core_updates_disabled,
				'email_notifications_on' => ! $email_disabled,
			),
		);

		// Add upgrade path for WPShadow Pro Security (when available).
		$finding = Upgrade_Path_Helper::add_upgrade_path(
			$finding,
			'security',
			'vulnerability-monitoring',
			'update-monitoring-manual-setup'
		);

		return $finding;
	}
}
