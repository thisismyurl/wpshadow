<?php
/**
 * Plugin Version Consistency Diagnostic
 *
 * Checks for plugin version consistency and update issues.
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
 * Plugin Version Consistency Diagnostic
 *
 * Validates plugin versions and update consistency.
 *
 * @since 1.2601.2240
 */
class Diagnostic_Plugin_Version_Consistency extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-version-consistency';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Version Consistency';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for plugin version consistency and update issues';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'plugins';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2240
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$details = array();

		// Get plugin data
		$plugins = get_plugins();
		$active_plugins = get_option( 'active_plugins', array() );

		// Check for version mismatches
		$version_mismatches = array();
		$very_old_plugins = array();
		$unversioned_plugins = array();

		foreach ( $active_plugins as $plugin_file ) {
			if ( ! isset( $plugins[ $plugin_file ] ) ) {
				$issues[] = sprintf(
					/* translators: %s: plugin file */
					__( 'Plugin file does not exist: %s', 'wpshadow' ),
					$plugin_file
				);
				continue;
			}

			$plugin_data = $plugins[ $plugin_file ];
			$version = $plugin_data['Version'] ?? 'unknown';

			// Check for missing version
			if ( empty( $version ) || 'unknown' === $version ) {
				$unversioned_plugins[] = $plugin_data['Name'] ?? $plugin_file;
				continue;
			}

			// Check version format
			if ( ! preg_match( '/^\d+(\.\d+)*/', $version ) ) {
				$version_mismatches[] = array(
					'plugin' => $plugin_data['Name'] ?? $plugin_file,
					'reason' => 'Invalid version format: ' . $version,
				);
			}

			// Check for very old versions (>3 years)
			$last_update = $plugin_data['_LastUpdateTime'] ?? 0;

			// Estimate version age by parsing version number
			if ( preg_match( '/^(\d+)\.(\d+)/', $version, $matches ) ) {
				$major_version = (int) $matches[1];
				$minor_version = (int) $matches[2];

				// Very rough estimate - if major version is < 1 and hasn't been updated
				if ( $major_version < 2 && time() - $last_update > YEAR_IN_SECONDS * 3 ) {
					$very_old_plugins[] = array(
						'name'    => $plugin_data['Name'] ?? $plugin_file,
						'version' => $version,
					);
				}
			}
		}

		// Check for duplicate plugins
		$plugin_names = array();
		$duplicate_plugins = array();

		foreach ( $plugins as $plugin_file => $plugin_data ) {
			$name = $plugin_data['Name'] ?? '';
			if ( ! empty( $name ) ) {
				if ( isset( $plugin_names[ $name ] ) ) {
					$duplicate_plugins[] = $name;
				}
				$plugin_names[ $name ] = $plugin_file;
			}
		}

		// Check for version inconsistencies between main file and subdirectories
		foreach ( $active_plugins as $plugin_file ) {
			if ( ! isset( $plugins[ $plugin_file ] ) ) {
				continue;
			}

			$plugin_dir = WP_PLUGIN_DIR . '/' . dirname( $plugin_file );
			$main_file = WP_PLUGIN_DIR . '/' . $plugin_file;

			if ( is_dir( $plugin_dir ) && file_exists( $main_file ) ) {
				// Check if there are version strings in subdirectories
				$subdir_files = glob( $plugin_dir . '/*/version.txt' );
				if ( ! empty( $subdir_files ) ) {
					foreach ( $subdir_files as $version_file ) {
						$subdir_version = trim( file_get_contents( $version_file ) );
						$main_version = $plugins[ $plugin_file ]['Version'] ?? '';

						if ( $subdir_version !== $main_version ) {
							$issues[] = sprintf(
								/* translators: %1$s: plugin name, %2$s: main version, %3$s: subdir version */
								__( 'Plugin %1$s has version mismatch: main %2$s vs subdir %3$s', 'wpshadow' ),
								$plugins[ $plugin_file ]['Name'] ?? $plugin_file,
								$main_version,
								$subdir_version
							);
						}
					}
				}
			}
		}

		// Check available updates
		$updates = get_transient( 'update_plugins' );
		$outdated_count = 0;

		if ( ! empty( $updates ) && ! empty( $updates->response ) ) {
			$outdated_count = count( $updates->response );

			foreach ( $updates->response as $plugin_slug => $data ) {
				if ( isset( $plugins[ $plugin_slug ] ) ) {
					$current = $plugins[ $plugin_slug ]['Version'] ?? 'unknown';
					$new_version = $data->new_version ?? 'unknown';

					$version_mismatches[] = array(
						'plugin'  => $plugins[ $plugin_slug ]['Name'] ?? $plugin_slug,
						'current' => $current,
						'new'     => $new_version,
					);
				}
			}
		}

		// Report findings
		if ( ! empty( $issues ) || ! empty( $version_mismatches ) || ! empty( $very_old_plugins ) ||
			! empty( $unversioned_plugins ) || ! empty( $duplicate_plugins ) ) {

			$all_issues = array();

			if ( ! empty( $issues ) ) {
				$all_issues = array_merge( $all_issues, $issues );
			}

			if ( ! empty( $very_old_plugins ) ) {
				$all_issues[] = sprintf(
					/* translators: %d: number of old plugins */
					__( '%d plugins have not been updated in over 3 years', 'wpshadow' ),
					count( $very_old_plugins )
				);
			}

			if ( ! empty( $unversioned_plugins ) ) {
				$all_issues[] = sprintf(
					/* translators: %d: number of plugins without version */
					__( '%d plugins do not have version information', 'wpshadow' ),
					count( $unversioned_plugins )
				);
			}

			if ( ! empty( $duplicate_plugins ) ) {
				$all_issues[] = sprintf(
					/* translators: %d: number of duplicate plugins */
					__( '%d duplicate plugin names detected', 'wpshadow' ),
					count( $duplicate_plugins )
				);
			}

			if ( $outdated_count > 0 ) {
				$all_issues[] = sprintf(
					/* translators: %d: number of outdated plugins */
					__( '%d plugins have available updates', 'wpshadow' ),
					$outdated_count
				);
			}

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Plugin version inconsistencies detected', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/plugin-version-consistency',
				'details'      => array(
					'issues'               => $all_issues,
					'version_mismatches'   => $version_mismatches,
					'very_old_plugins'     => $very_old_plugins,
					'unversioned_plugins'  => $unversioned_plugins,
					'duplicate_plugins'    => $duplicate_plugins,
					'outdated_count'       => $outdated_count,
				),
			);
		}

		return null;
	}
}
