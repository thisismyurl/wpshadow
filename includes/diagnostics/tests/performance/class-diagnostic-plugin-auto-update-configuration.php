<?php
/**
 * Plugin Auto-Update Configuration Diagnostic
 *
 * Checks plugin auto-update configuration and enablement.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2240
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Auto-Update Configuration Diagnostic
 *
 * Validates plugin auto-update settings and recommendations.
 *
 * @since 1.2601.2240
 */
class Diagnostic_Plugin_Auto_Update_Configuration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-auto-update-configuration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Auto-Update Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks plugin auto-update configuration and enablement';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2240
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$details = array();

		// Get list of active plugins
		$active_plugins = get_option( 'active_plugins', array() );

		// Get auto-update settings for plugins
		$auto_updates_enabled = get_option( 'auto_update_plugins' );
		$single_auto_updates = get_option( 'auto_update_plugins_plugins', array() );

		// Check if core auto-updates are enabled
		if ( empty( $auto_updates_enabled ) ) {
			$details['core_auto_updates'] = 'disabled';
		} else {
			$details['core_auto_updates'] = 'enabled';
		}

		// Scan each active plugin for auto-update capability
		$plugins_without_auto_update = array();
		$plugins_with_auto_update = array();
		$premium_plugins = array();

		$plugin_data = get_plugins();

		foreach ( $active_plugins as $plugin ) {
			if ( ! isset( $plugin_data[ $plugin ] ) ) {
				continue;
			}

			$data = $plugin_data[ $plugin ];
			$plugin_name = $data['Name'] ?? $plugin;
			$update_uri = $data['UpdateURI'] ?? '';

			// Check if plugin supports auto-updates (has custom repository or is on .org)
			$is_org_plugin = false;
			if ( strpos( $plugin, '/' ) !== false ) {
				$folder = dirname( $plugin );
				// WordPress.org plugins use standard update mechanism
				$is_org_plugin = true;
			}

			// Check for premium/non-standard plugin
			if ( ! empty( $update_uri ) || strpos( $plugin, 'woo-' ) === 0 || strpos( $plugin, 'premium' ) !== false ) {
				$premium_plugins[ $plugin ] = $plugin_name;
			}

			// Check if individual plugin has auto-update enabled
			if ( ! empty( $single_auto_updates ) && in_array( $plugin, $single_auto_updates, true ) ) {
				$plugins_with_auto_update[] = $plugin_name;
			} else {
				$plugins_without_auto_update[] = $plugin_name;
			}
		}

		// Critical plugins that should have auto-updates
		$critical_plugins = array(
			'jetpack/jetpack.php'                     => 'Jetpack',
			'akismet/akismet.php'                     => 'Akismet',
			'wordfence/wordfence.php'                 => 'Wordfence',
			'wp-security-audit-log/wp-security-audit-log.php' => 'WP Security Audit Log',
		);

		$critical_without_auto_update = array();
		foreach ( $critical_plugins as $plugin => $name ) {
			if ( in_array( $plugin, $active_plugins, true ) ) {
				if ( empty( $single_auto_updates ) || ! in_array( $plugin, $single_auto_updates, true ) ) {
					$critical_without_auto_update[] = $name;
				}
			}
		}

		// Check if updates are being checked regularly
		$update_check_interval = wp_scheduled_hook_frequency( 'wp_version_check' );
		if ( empty( $update_check_interval ) ) {
			$issues[] = __( 'Plugin update checks are not scheduled', 'wpshadow' );
		}

		// Check if any plugins have pending updates
		$updates = get_transient( 'update_plugins' );
		$pending_updates = 0;

		if ( ! empty( $updates ) && ! empty( $updates->response ) ) {
			$pending_updates = count( $updates->response );

			if ( $pending_updates > 5 ) {
				$issues[] = sprintf(
					/* translators: %d: number of pending updates */
					__( '%d plugins have pending updates', 'wpshadow' ),
					$pending_updates
				);
			}

			// Check for security updates (if available)
			foreach ( $updates->response as $plugin_slug => $data ) {
				if ( ! empty( $data->upgrade_notice ) && strpos( strtolower( $data->upgrade_notice ), 'security' ) !== false ) {
					$issues[] = sprintf(
						/* translators: %s: plugin slug */
						__( 'Security update available for plugin %s', 'wpshadow' ),
						$plugin_slug
					);
				}
			}
		}

		// Check if critical plugins should have auto-update enabled
		if ( ! empty( $critical_without_auto_update ) ) {
			$issues[] = sprintf(
				/* translators: %s: plugin names */
				__( 'Critical security plugins lack auto-update: %s', 'wpshadow' ),
				implode( ', ', $critical_without_auto_update )
			);
		}

		// Recommendations
		$recommendations = array();
		if ( empty( $auto_updates_enabled ) ) {
			$recommendations[] = __( 'Consider enabling automatic plugin updates for security patches', 'wpshadow' );
		}

		if ( $pending_updates > 0 ) {
			$recommendations[] = sprintf(
				/* translators: %d: number of updates */
				__( 'Install %d pending plugin updates', 'wpshadow' ),
				$pending_updates
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Plugin auto-update configuration issues found', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/plugin-auto-update-configuration',
				'details'      => array(
					'issues'                       => $issues,
					'recommendations'              => $recommendations,
					'auto_updates_enabled'         => ! empty( $auto_updates_enabled ),
					'plugins_with_auto_update'     => $plugins_with_auto_update,
					'plugins_without_auto_update'  => $plugins_without_auto_update,
					'premium_plugins'              => array_values( $premium_plugins ),
					'pending_updates'              => $pending_updates,
				),
			);
		}

		return null;
	}
}

/**
 * Helper function to get scheduled hook frequency
 *
 * @param string $hook The hook name.
 * @return int|false Hook frequency in seconds or false if not scheduled.
 */
function wp_scheduled_hook_frequency( $hook ) {
	$crons = _get_cron_array();
	if ( empty( $crons ) ) {
		return false;
	}

	foreach ( $crons as $timestamp => $cron ) {
		if ( isset( $cron[ $hook ] ) ) {
			return $timestamp - time();
		}
	}

	return false;
}
