<?php
/**
 * Must-Use (MU) Plugins Audit Diagnostic
 *
 * Audits must-use plugins for conflicts and issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Must-Use (MU) Plugins Audit Diagnostic
 *
 * Checks must-use plugins for functionality and conflicts.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Must_Use_Plugins_Audit extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'must-use-plugins-audit';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Must-Use (MU) Plugins Audit';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Audits must-use plugins for conflicts and issues';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'plugins';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$mu_plugins = array();
		$details = array();

		// Get MU plugin directory
		$mu_plugins_dir = WPMU_PLUGIN_DIR;

		if ( ! is_dir( $mu_plugins_dir ) ) {
			// No MU plugins directory
			return null;
		}

		// Scan MU plugins directory
		$files = scandir( $mu_plugins_dir );

		if ( false === $files ) {
			$issues[] = __( 'Cannot read MU plugins directory', 'wpshadow' );
		}

		if ( ! empty( $files ) ) {
			foreach ( $files as $file ) {
				if ( '.' === $file || '..' === $file ) {
					continue;
				}

				$file_path = $mu_plugins_dir . '/' . $file;

				if ( is_file( $file_path ) && '.php' === substr( $file, -4 ) ) {
					$mu_plugins[] = $file;

					// Check for common MU plugin issues
					$content = file_get_contents( $file_path );

					// Check if it contains header comment
					if ( strpos( $content, '<?php' ) === false ) {
						$issues[] = sprintf(
							/* translators: %s: filename */
							__( 'MU plugin %s does not start with PHP tag', 'wpshadow' ),
							$file
						);
					}

					// Check for common errors
					if ( strpos( $content, 'fatal error' ) !== false || strpos( $content, 'syntax error' ) !== false ) {
						$issues[] = sprintf(
							/* translators: %s: filename */
							__( 'MU plugin %s contains error messages', 'wpshadow' ),
							$file
						);
					}

					// Check file permissions
					$perms = substr( sprintf( '%o', fileperms( $file_path ) ), -4 );
					if ( (int) $perms > 755 ) {
						$issues[] = sprintf(
							/* translators: %1$s: filename, %2$s: permissions */
							__( 'MU plugin %1$s has overly permissive permissions (%2$s)', 'wpshadow' ),
							$file,
							$perms
						);
					}
				} elseif ( is_dir( $file_path ) && '.' !== $file[0] ) {
					// Check for plugin loaders
					$loader_file = $file_path . '/index.php';
					if ( file_exists( $loader_file ) ) {
						$mu_plugins[] = $file . '/index.php (directory-based)';
					}
				}
			}
		}

		// Check for MU plugin conflicts with regular plugins
		if ( ! empty( $mu_plugins ) ) {
			$active_plugins = get_option( 'active_plugins', array() );

			// Map of MU plugin patterns to regular plugin patterns
			$conflict_patterns = array(
				'object-cache'      => array( 'wp-redis', 'memcached' ),
				'db-error'          => array( 'custom-db', 'db-wrapper' ),
				'advanced-cache'    => array( 'wp-rocket', 'w3-total-cache' ),
				'site-config'       => array( 'multisite-language', 'domain-mapping' ),
			);

			foreach ( $conflict_patterns as $mu_pattern => $plugin_patterns ) {
				$mu_found = false;
				foreach ( $mu_plugins as $mu_plugin ) {
					if ( strpos( $mu_plugin, $mu_pattern ) !== false ) {
						$mu_found = true;
						break;
					}
				}

				if ( $mu_found ) {
					foreach ( $plugin_patterns as $plugin_pattern ) {
						foreach ( $active_plugins as $plugin ) {
							if ( strpos( $plugin, $plugin_pattern ) !== false ) {
								$issues[] = sprintf(
									/* translators: %1$s: MU plugin pattern, %2$s: plugin */
									__( 'MU plugin %1$s may conflict with active plugin %2$s', 'wpshadow' ),
									$mu_pattern,
									$plugin
								);
							}
						}
					}
				}
			}
		}

		// Check MU plugin execution
		$mu_plugin_count = count( $mu_plugins );

		if ( $mu_plugin_count > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of MU plugins */
				__( '%d MU plugins found - high number may impact performance', 'wpshadow' ),
				$mu_plugin_count
			);
		}

		// Report findings
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Must-use plugin audit found potential issues', 'wpshadow' ),
				'severity'     => count( $issues ) > 3 ? 'high' : 'medium',
				'threat_level' => min( 60 + ( count( $issues ) * 5 ), 90 ),
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/must-use-plugins-audit?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'mu_plugins'   => $mu_plugins,
					'mu_count'     => $mu_plugin_count,
					'issues'       => $issues,
				),
			);
		}

		return null;
	}
}
