<?php
/**
 * Dropin Plugin Functionality Diagnostic
 *
 * Verifies drop-in plugin replacements are functioning correctly.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.2240
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Dropin Plugin Functionality Diagnostic
 *
 * Checks WordPress drop-in file functionality and configuration.
 *
 * @since 1.6030.2240
 */
class Diagnostic_Dropin_Plugin_Functionality extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'dropin-plugin-functionality';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Dropin Plugin Functionality';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies drop-in plugin replacements are functioning correctly';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'plugins';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2240
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Known WordPress drop-in files
		$known_dropins = array(
			'advanced-cache.php'   => 'Advanced Cache Handler',
			'object-cache.php'     => 'Object Cache Handler',
			'db.php'               => 'Database Handler',
			'db-error.php'         => 'Database Error Handler',
			'install.php'          => 'Installation Handler',
			'maintenance.php'      => 'Maintenance Mode Handler',
		);

		$wp_content = WP_CONTENT_DIR;
		$found_dropins = array();
		$inactive_dropins = array();

		foreach ( $known_dropins as $file => $name ) {
			$path = $wp_content . '/' . $file;

			if ( file_exists( $path ) ) {
				$found_dropins[] = $name;

				// Check if it's actually being used
				switch ( $file ) {
					case 'object-cache.php':
						// Check if wp_cache_get is actually using it
						if ( ! wp_cache_get( 'wpshadow_test', 'wpshadow' ) ) {
							wp_cache_set( 'wpshadow_test', 'success', 'wpshadow', 60 );
							$result = wp_cache_get( 'wpshadow_test', 'wpshadow' );
							if ( ! $result ) {
								$inactive_dropins[] = sprintf(
									/* translators: %s: dropin name */
									__( '%s installed but not functioning', 'wpshadow' ),
									$name
								);
							}
							wp_cache_delete( 'wpshadow_test', 'wpshadow' );
						}
						break;

					case 'advanced-cache.php':
						if ( ! defined( 'WP_CACHE' ) || ! WP_CACHE ) {
							$inactive_dropins[] = sprintf(
								/* translators: %s: dropin name */
								__( '%s installed but WP_CACHE is not enabled', 'wpshadow' ),
								$name
							);
						}
						break;

					case 'db.php':
						// Custom DB handler - check for potential issues
						$db_content = file_get_contents( $path, false, null, 0, 500 );
						if ( strpos( $db_content, '<?php' ) === false ) {
							$issues[] = sprintf(
								/* translators: %s: dropin file */
								__( '%s is not a valid PHP file', 'wpshadow' ),
								$file
							);
						}
						break;
				}
			}
		}

		// Check for conflicting cache plugins
		if ( in_array( 'advanced-cache.php', array_keys( $known_dropins ), true ) ) {
			$active_plugins = get_option( 'active_plugins', array() );
			$cache_plugins  = array(
				'wp-rocket/wp-rocket.php',
				'w3-total-cache/w3-total-cache.php',
				'wp-super-cache/wp-super-cache.php',
			);

			$conflict_count = 0;
			foreach ( $cache_plugins as $plugin ) {
				if ( in_array( $plugin, $active_plugins, true ) && file_exists( $wp_content . '/advanced-cache.php' ) ) {
					++$conflict_count;
				}
			}

			if ( $conflict_count > 0 ) {
				$issues[] = sprintf(
					/* translators: %d: number of conflicting plugins */
					__( '%d cache plugins conflict with advanced-cache.php dropin', 'wpshadow' ),
					$conflict_count
				);
			}
		}

		// Check dropin file permissions
		foreach ( $found_dropins as $idx => $name ) {
			$file_map = array_flip( $known_dropins );
			$filename = $file_map[ $name ];
			$path = $wp_content . '/' . $filename;

			if ( ! is_readable( $path ) ) {
				$issues[] = sprintf(
					/* translators: %s: filename */
					__( '%s dropin is not readable', 'wpshadow' ),
					$filename
				);
			}
		}

		// Report findings
		if ( ! empty( $found_dropins ) && ( ! empty( $issues ) || ! empty( $inactive_dropins ) ) ) {
			$severity     = 'medium';
			$threat_level = 50;

			if ( ! empty( $issues ) ) {
				$severity     = 'high';
				$threat_level = 70;
			}

			$description = __( 'Dropin plugin configuration or functionality issues detected', 'wpshadow' );

			$details = array(
				'active_dropins'   => $found_dropins,
				'inactive_dropins' => $inactive_dropins,
			);

			if ( ! empty( $issues ) ) {
				$details['issues'] = $issues;
			}

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/dropin-plugin-functionality',
				'details'      => $details,
			);
		}

		return null;
	}
}
