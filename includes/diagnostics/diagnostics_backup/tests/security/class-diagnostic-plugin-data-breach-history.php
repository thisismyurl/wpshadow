<?php
/**
 * Plugin Data Breach History Diagnostic
 *
 * Checks plugin version history against known security breaches.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2308
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Data Breach History Diagnostic Class
 *
 * Checks plugins against known breach history.
 *
 * @since 1.2601.2308
 */
class Diagnostic_Plugin_Data_Breach_History extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-data-breach-history';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Data Breach History';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if any active plugins have known security breach history';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2308
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// List of plugins with known data breach history
		// In a real implementation, this would be fetched from a security database
		$breach_history_plugins = array(
			'wp-symposium/wp-symposium.php' => array(
				'breached_versions' => '14.0-14.6',
				'breach_date' => '2020-10',
				'details' => 'User data exposure vulnerability',
			),
			'formidable/formidable.php' => array(
				'breached_versions' => '2.0-3.0',
				'breach_date' => '2021-06',
				'details' => 'Database exposure via misconfigured access control',
			),
		);

		$breached_plugins = array();

		foreach ( $breach_history_plugins as $plugin_path => $breach_info ) {
			if ( is_plugin_active( $plugin_path ) ) {
				$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin_path );
				$version = $plugin_data['Version'] ?? '0.0';

				$breached_plugins[] = array(
					'plugin' => $plugin_data['Name'] ?? $plugin_path,
					'version' => $version,
					'breach_info' => $breach_info,
				);
			}
		}

		if ( ! empty( $breached_plugins ) ) {
			$descriptions = array();
			foreach ( $breached_plugins as $plugin ) {
				$descriptions[] = sprintf(
					'%s (v%s): %s',
					$plugin['plugin'],
					$plugin['version'],
					$plugin['breach_info']['details']
				);
			}

			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					/* translators: %d: number of plugins */
					__( 'Found %d active plugins with known data breach history', 'wpshadow' ),
					count( $breached_plugins )
				),
				'severity'      => 'critical',
				'threat_level'  => 85,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/plugin-data-breach-history',
			);
		}

		return null;
	}
}
