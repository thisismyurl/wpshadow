<?php
/**
 * WordPress Update Frequency Diagnostic
 *
 * Monitors how frequently WordPress core, plugins, and themes
 * are updated to ensure security patches are applied promptly.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_WordPress_Update_Frequency Class
 *
 * Verifies WordPress, plugins, and themes are updated regularly.
 *
 * @since 1.2601.2148
 */
class Diagnostic_WordPress_Update_Frequency extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'wordpress-update-frequency';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'WordPress Update Frequency';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Monitors how regularly WordPress core, plugins, and themes are updated';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if update issues found, null otherwise.
	 */
	public static function check() {
		$update_status = self::get_update_status();

		// Check for critical updates pending
		if ( $update_status['critical_updates_pending'] > 0 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of critical updates pending */
					__( '%d critical security updates are pending. Apply immediately to prevent exploitation.', 'wpshadow' ),
					$update_status['critical_updates_pending']
				),
				'severity'     => 'critical',
				'threat_level' => 92,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/wordpress-updates',
				'family'       => self::$family,
				'meta'         => array(
					'critical_updates'        => $update_status['critical_updates_pending'],
					'security_risk'           => __( 'Unpatched plugins are commonly exploited' ),
					'immediate_action_needed' => __( 'Update within 24 hours' ),
				),
				'details'      => array(
					'exploit_risk'         => array(
						__( '50% of WordPress breaches use outdated plugins' ),
						__( 'Vulnerability databases publicly list exploits' ),
						__( 'Automated attacks scan for vulnerable versions' ),
						__( 'Update delays = breach risk increases daily' ),
					),
					'update_procedures'    => array(
						'Safest Method (Recommended)' => array(
							'1. Backup database with UpdraftPlus',
							'2. Backup files to staging server',
							'3. Test update on staging first',
							'4. Schedule maintenance window',
							'5. Update 1 plugin at a time on live',
							'6. Test site after each update',
							'7. Rollback backup if issues occur',
						),
						'Quick Method (Higher Risk)' => array(
							'1. Go to Plugins dashboard',
							'2. Select all plugins with updates',
							'3. Bulk action: Update',
							'4. Test site immediately',
							'Risk: Changes break compatibility',
						),
					),
					'update_schedule'      => array(
						'WordPress Core'   => 'Security releases within 48 hours (automatic recommended)',
						'Security Plugins' => 'Within 24 hours (block all traffic until patched)',
						'Popular Plugins'  => 'Within 1 week',
						'Theme Updates'    => 'Within 2 weeks',
						'Custom Plugins'   => 'Quarterly minimum',
					),
					'update_automation'    => array(
						'Core Auto-Updates' => array(
							'Enable: define( \'WP_AUTO_UPDATE_CORE\', true ); in wp-config.php',
							'Allow theme/plugin auto-updates for trusted vendors',
							'Disable for custom/beta plugins',
						),
						'Scheduled Checks' => array(
							'Calendar reminder: Every Friday email update check',
							'Plugin: Easy Updates Manager for fine control',
							'Wordfence: Force updates for critical vulnerabilities',
						),
					),
				),
			);
		}

		// Check for high-severity updates
		if ( $update_status['high_updates_pending'] > 0 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of high-severity updates pending */
					__( '%d high-priority security updates pending. Schedule for this week.', 'wpshadow' ),
					$update_status['high_updates_pending']
				),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/wordpress-updates',
				'family'       => self::$family,
				'meta'         => array(
					'high_priority_updates' => $update_status['high_updates_pending'],
					'update_deadline'       => __( 'Apply within 7 days' ),
				),
				'details'      => array(
					__( 'High-severity updates address significant vulnerabilities' ),
					__( 'Testing recommended before applying to live site' ),
					__( 'Schedule during low-traffic periods' ),
				),
			);
		}

		// Check for general updates
		if ( $update_status['total_updates_pending'] > 0 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: total number of updates pending */
					__( '%d plugin/theme updates available. Schedule for next maintenance window.', 'wpshadow' ),
					$update_status['total_updates_pending']
				),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/wordpress-updates',
				'family'       => self::$family,
				'meta'         => array(
					'total_updates'   => $update_status['total_updates_pending'],
					'security_updates' => $update_status['security_updates_pending'],
					'routine_updates'  => $update_status['routine_updates_pending'],
				),
				'details'      => array(
					__( 'Mix of security and feature updates available' ),
					__( 'Security updates should be prioritized' ),
					__( 'Feature updates can wait for maintenance window' ),
				),
			);
		}

		return null; // Everything is up to date
	}

	/**
	 * Get update status.
	 *
	 * @since  1.2601.2148
	 * @return array Update status details.
	 */
	private static function get_update_status() {
		// Get available updates
		require_once ABSPATH . 'wp-admin/includes/update.php';
		wp_update_plugins();
		wp_update_themes();
		wp_version_check();

		$plugin_updates = get_site_transient( 'update_plugins' );
		$theme_updates  = get_site_transient( 'update_themes' );

		$critical_count = 0;
		$high_count     = 0;
		$security_count = 0;
		$routine_count  = 0;

		// Count plugin updates
		if ( $plugin_updates && isset( $plugin_updates->response ) ) {
			foreach ( $plugin_updates->response as $plugin => $details ) {
				// Check if this is a security update
				if ( isset( $details->upgrade_notice ) && stripos( $details->upgrade_notice, 'security' ) !== false ) {
					$critical_count++;
					$security_count++;
				} elseif ( isset( $details->upgrade_notice ) && stripos( $details->upgrade_notice, 'critical' ) !== false ) {
					$critical_count++;
				} else {
					$routine_count++;
				}
			}
		}

		// Count theme updates
		if ( $theme_updates && isset( $theme_updates->response ) ) {
			foreach ( $theme_updates->response as $theme => $details ) {
				if ( isset( $details['requires_wp'] ) ) {
					$routine_count++;
				}
			}
		}

		return array(
			'critical_updates_pending' => $critical_count,
			'high_updates_pending'     => $high_count,
			'security_updates_pending' => $security_count,
			'routine_updates_pending'  => $routine_count,
			'total_updates_pending'    => $critical_count + $high_count + $security_count + $routine_count,
		);
	}
}
