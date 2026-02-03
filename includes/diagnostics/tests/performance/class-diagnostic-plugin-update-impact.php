<?php
/**
 * Plugin Update Impact Diagnostic
 *
 * Checks for outdated plugins and their potential impact on performance
 * and security, recommending updates.
 *
 * @since   1.26033.2083
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Update Impact Diagnostic Class
 *
 * Monitors plugin status:
 * - Outdated plugins count
 * - Performance plugins available
 * - Security plugin status
 * - Plugin dependency resolution
 *
 * @since 1.26033.2083
 */
class Diagnostic_Plugin_Update_Impact extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-update-impact';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Update Status';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for outdated plugins and update availability';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.2083
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		// Get plugin updates
		$plugin_updates = get_site_transient( 'update_plugins' );
		$outdated_count = 0;
		$security_plugins = array();

		if ( ! empty( $plugin_updates ) && ! empty( $plugin_updates->response ) ) {
			$outdated_count = count( $plugin_updates->response );

			// Check for security plugins that need update
			$security_plugin_files = array(
				'wordfence/wordfence.php',
				'jetpack/jetpack.php',
				'sucuri-scanner/sucuri.php',
				'iThemes-Security-Pro/iThemes-Security-Pro.php',
			);

			foreach ( $security_plugin_files as $plugin_file ) {
				if ( is_plugin_active( $plugin_file ) && isset( $plugin_updates->response[ $plugin_file ] ) ) {
					$security_plugins[] = $plugin_file;
				}
			}
		}

		if ( $outdated_count >= 3 || ! empty( $security_plugins ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					/* translators: %d: number of outdated plugins, %d: number of security plugins with updates */
					__( 'You have %d outdated plugins. %s security plugins need updating.', 'wpshadow' ),
					$outdated_count,
					! empty( $security_plugins ) ? count( $security_plugins ) : 'No'
				),
				'severity'      => ! empty( $security_plugins ) ? 'high' : 'medium',
				'threat_level'  => ! empty( $security_plugins ) ? 70 : 45,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/plugin-updates',
				'meta'          => array(
					'outdated_count'       => $outdated_count,
					'security_updates'     => count( $security_plugins ),
					'recommendation'       => 'Update all plugins, especially security and performance plugins',
					'impact'               => 'Updates often include performance optimizations and security patches',
					'priority_plugins'     => array(
						'Wordfence (security)',
						'Jetpack (security & performance)',
						'WP Rocket (performance)',
						'Autoptimize (performance)',
					),
				),
			);
		}

		return null;
	}
}
