<?php
/**
 * Theme Settings Backup Diagnostic
 *
 * Detects missing backup mechanisms for theme settings and customizer data.
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
 * Theme Settings Backup Diagnostic Class
 *
 * Checks if theme settings have backup/export functionality.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Theme_Settings_Backup extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-settings-backup';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Settings Backup';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if theme settings can be backed up';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$theme = wp_get_theme();
		$issues = array();

		// Check for theme mods (customizer data).
		$theme_mods = get_theme_mods();
		$has_theme_mods = ! empty( $theme_mods );

		// Check for theme options (separate from mods).
		$theme_slug = get_option( 'stylesheet' );
		$theme_options = get_option( 'theme_mods_' . $theme_slug );
		$has_options = ! empty( $theme_options );

		if ( $has_theme_mods || $has_options ) {
			// Check for export/import plugins.
			$backup_plugins = array(
				'customizer-export-import/customizer-export-import.php',
				'kirki/kirki.php',
				'coe-customizer-export/customizer-export.php',
			);

			$has_backup_mechanism = false;
			foreach ( $backup_plugins as $plugin ) {
				if ( is_plugin_active( $plugin ) ) {
					$has_backup_mechanism = true;
					break;
				}
			}

			// Check if theme has built-in export.
			if ( ! $has_backup_mechanism ) {
				// Check for export functions in theme.
				$theme_dir = get_stylesheet_directory();
				$functions_file = $theme_dir . '/functions.php';

				if ( file_exists( $functions_file ) ) {
					$functions_content = file_get_contents( $functions_file );
					if ( preg_match( '/export|backup|import.*settings/i', $functions_content ) ) {
						$has_backup_mechanism = true;
					}
				}
			}

			if ( ! $has_backup_mechanism ) {
				$issues[] = __( 'No backup mechanism found for theme settings', 'wpshadow' );
			}

			// Check if settings have been changed recently without backup.
			$last_modified = get_option( 'theme_mods_last_updated_' . $theme_slug, 0 );
			if ( ! $last_modified ) {
				// Try to detect from database.
				global $wpdb;
				$last_change = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT option_value FROM {$wpdb->options} WHERE option_name = %s",
						'theme_mods_' . $theme_slug
					)
				);
				if ( $last_change ) {
					$issues[] = __( 'Theme settings changed but no backup timestamp found', 'wpshadow' );
				}
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Theme settings lack backup or export functionality', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'details'     => array(
					'theme'           => $theme->get( 'Name' ),
					'has_theme_mods'  => $has_theme_mods,
					'has_options'     => $has_options,
					'issues'          => $issues,
				),
				'kb_link'     => 'https://wpshadow.com/kb/theme-settings-backup?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
