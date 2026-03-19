<?php
/**
 * Alert Configuration Diagnostic
 *
 * Analyzes alert and notification configuration for critical events.
 *
 * @since 1.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Alert Configuration Diagnostic
 *
 * Evaluates notification system for critical events and alerts.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Alert_Configuration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'alert-configuration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Alert Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes alert and notification configuration for critical events';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'monitoring';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check admin email
		$admin_email = get_option( 'admin_email' );
		$has_valid_email = ! empty( $admin_email ) && is_email( $admin_email );

		// Check for notification plugins
		$notification_plugins = array(
			'wp-mail-smtp/wp_mail_smtp.php'             => 'WP Mail SMTP',
			'better-wp-security/better-wp-security.php' => 'iThemes Security',
			'wordfence/wordfence.php'                   => 'Wordfence',
			'jetpack/jetpack.php'                       => 'Jetpack',
		);

		$active_plugin = null;
		foreach ( $notification_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_plugin = $name;
				break;
			}
		}

		// Basic email capability is inferred from valid admin email and plugin setup.

		// Check for critical event monitoring
		$monitors_core_updates = has_filter( 'auto_core_update_email' );
		$monitors_plugin_updates = has_filter( 'auto_plugin_update_email' );

		// Generate findings if no alert system configured
		if ( ! $has_valid_email ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No valid admin email configured. WordPress cannot send critical alerts for security issues or updates.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/alert-configuration',
				'meta'         => array(
					'admin_email'      => $admin_email,
					'has_valid_email'  => $has_valid_email,
					'recommendation'   => 'Set valid admin email in Settings > General',
					'critical_alerts'  => array(
						'Core updates available',
						'Plugin security updates',
						'Failed login attempts',
						'Site downtime',
						'Database errors',
					),
				),
			);
		}

		if ( ! $active_plugin ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No dedicated notification system configured. Consider WP Mail SMTP or security plugin for reliable alerts.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/alert-configuration',
				'meta'         => array(
					'active_plugin'   => $active_plugin,
					'admin_email'     => $admin_email,
					'recommendation'  => 'Install WP Mail SMTP for reliable email delivery',
					'alert_services'  => array(
						'WP Mail SMTP (email reliability)',
						'Wordfence (security alerts)',
						'iThemes Security (breach alerts)',
						'ManageWP (centralized notifications)',
					),
				),
			);
		}

		return null;
	}
}
