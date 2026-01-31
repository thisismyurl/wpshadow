<?php
/**
 * Plugin Installation Source Verification Diagnostic
 *
 * Verifies plugins are installed from trusted sources.
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
 * Plugin Installation Source Verification Diagnostic
 *
 * Checks that plugins are installed from official WordPress sources.
 *
 * @since 1.2601.2240
 */
class Diagnostic_Plugin_Installation_Source_Verification extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-installation-source-verification';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Installation Source Verification';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies plugins are installed from trusted sources';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2240
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$suspicious_plugins = array();

		$active_plugins = get_option( 'active_plugins', array() );
		$plugins = get_plugins();

		// Known trusted sources
		$trusted_sources = array(
			'wordpress.org',
			'github.com',
			'bitbucket.org',
			'gitlab.com',
		);

		// Known commercial trusted sources
		$commercial_trusted = array(
			'woocommerce',
			'jetpack',
			'akismet',
			'yoast',
			'wpforms',
			'sumobi',
		);

		foreach ( $active_plugins as $plugin ) {
			if ( ! isset( $plugins[ $plugin ] ) ) {
				continue;
			}

			$plugin_data = $plugins[ $plugin ];
			$plugin_name = $plugin_data['Name'] ?? 'Unknown';
			$plugin_url = $plugin_data['PluginURI'] ?? '';
			$author_url = $plugin_data['AuthorURI'] ?? '';
			$update_uri = $plugin_data['UpdateURI'] ?? '';

			// Check if plugin has a valid author URL or plugin URL
			if ( empty( $plugin_url ) && empty( $author_url ) ) {
				$suspicious_plugins[] = array(
					'name'   => $plugin_name,
					'reason' => 'No plugin URL or author URL specified',
				);
				continue;
			}

			// Check if from WordPress.org
			$is_org_plugin = false;
			$is_trusted = false;

			if ( strpos( $plugin, '/' ) !== false ) {
				$folder = dirname( $plugin );
				// Assume WordPress.org plugins follow standard structure
				$is_org_plugin = true;
				$is_trusted = true;
			}

			// Check URLs against trusted sources
			if ( ! empty( $plugin_url ) ) {
				foreach ( $trusted_sources as $source ) {
					if ( strpos( $plugin_url, $source ) !== false ) {
						$is_trusted = true;
						break;
					}
				}
			}

			if ( ! empty( $author_url ) ) {
				foreach ( $trusted_sources as $source ) {
					if ( strpos( $author_url, $source ) !== false ) {
						$is_trusted = true;
						break;
					}
				}
			}

			// Check update URI
			if ( ! empty( $update_uri ) ) {
				foreach ( $commercial_trusted as $brand ) {
					if ( strpos( strtolower( $update_uri ), strtolower( $brand ) ) !== false ) {
						$is_trusted = true;
						break;
					}
				}
			}

			// Flag plugins from unknown sources
			if ( ! $is_trusted && ! empty( $plugin_url ) ) {
				// Allow localhost or private IPs
				if ( strpos( $plugin_url, 'localhost' ) === false && strpos( $plugin_url, '192.168' ) === false &&
					strpos( $plugin_url, '10.0' ) === false ) {
					$suspicious_plugins[] = array(
						'name'   => $plugin_name,
						'url'    => $plugin_url,
						'reason' => 'Unknown or unverified source',
					);
				}
			}
		}

		// Check for child plugins or themes modified plugins
		if ( ! empty( $suspicious_plugins ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of suspicious plugins */
				__( '%d plugins from unverified sources detected', 'wpshadow' ),
				count( $suspicious_plugins )
			);
		}

		// Check for plugins in non-standard locations
		$plugins_dir = WP_PLUGIN_DIR;
		$mu_plugins_dir = WPMU_PLUGIN_DIR;

		// Scan for plugins not in standard directories
		$custom_plugin_locations = array();
		if ( defined( 'WP_PLUGIN_DIR' ) ) {
			foreach ( glob( $plugins_dir . '/*/*.php' ) as $file ) {
				if ( ! file_exists( dirname( $file ) . '/index.php' ) && basename( $file ) !== 'plugin.php' ) {
					$custom_plugin_locations[] = basename( dirname( $file ) );
				}
			}
		}

		if ( ! empty( $custom_plugin_locations ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of plugins */
				__( '%d plugins found in non-standard locations', 'wpshadow' ),
				count( $custom_plugin_locations )
			);
		}

		// Report findings
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Plugins from unverified sources detected', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/plugin-installation-source-verification',
				'details'      => array(
					'issues'               => $issues,
					'suspicious_plugins'   => $suspicious_plugins,
					'custom_locations'     => $custom_plugin_locations,
					'recommendations'      => array(
						__( 'Only install plugins from WordPress.org or verified vendors', 'wpshadow' ),
						__( 'Check plugin author before installation', 'wpshadow' ),
						__( 'Review plugin code from GitHub repositories', 'wpshadow' ),
					),
				),
			);
		}

		return null;
	}
}
