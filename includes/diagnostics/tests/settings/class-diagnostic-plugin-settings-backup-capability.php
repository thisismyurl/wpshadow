<?php
/**
 * Plugin Settings Backup Capability Diagnostic
 *
 * Checks whether plugins provide an export/backup path for settings.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Settings Backup Capability Diagnostic Class
 *
 * Detects plugins with stored options but no visible export/backup hints.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Plugin_Settings_Backup_Capability extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-settings-backup-capability';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Settings Backup Capability';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if plugins offer settings export or backup';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'plugins';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		global $wpdb;

		$active_plugins = get_option( 'active_plugins', array() );
		$all_plugins = get_plugins();
		$plugins_without_backup = array();

		foreach ( $active_plugins as $plugin_file ) {
			if ( ! isset( $all_plugins[ $plugin_file ] ) ) {
				continue;
			}

			$slug = sanitize_key( dirname( $plugin_file ) );
			if ( '.' === $slug || '' === $slug ) {
				$slug = sanitize_key( basename( $plugin_file, '.php' ) );
			}

			$option_count = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(1) FROM {$wpdb->options} WHERE option_name LIKE %s",
					$wpdb->esc_like( $slug ) . '%'
				)
			);

			if ( $option_count < 3 ) {
				continue;
			}

			$plugin_dir = WP_PLUGIN_DIR . '/' . dirname( $plugin_file );
			$export_hint = false;

			if ( is_dir( $plugin_dir ) ) {
				$files = glob( $plugin_dir . '/*.php' );
				if ( ! empty( $files ) ) {
					foreach ( array_slice( $files, 0, 10 ) as $file ) {
						$content = file_get_contents( $file, false, null, 0, 8000 );
						if ( false !== stripos( $content, 'export' ) || false !== stripos( $content, 'backup' ) || false !== stripos( $content, 'import' ) ) {
							$export_hint = true;
							break;
						}
					}
				}
			}

			if ( ! $export_hint ) {
				$plugins_without_backup[] = $all_plugins[ $plugin_file ]['Name'];
			}
		}

		if ( ! empty( $plugins_without_backup ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Some plugins store settings but do not appear to offer export or backup options.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'details'      => array(
					'plugins' => array_slice( $plugins_without_backup, 0, 10 ),
				),
				'kb_link'      => 'https://wpshadow.com/kb/plugin-settings-backup-capability',
			);
		}

		return null;
	}
}
