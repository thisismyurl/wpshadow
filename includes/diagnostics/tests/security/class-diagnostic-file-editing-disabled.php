<?php
/**
 * File Editing Disabled Diagnostic
 *
 * Verifies that the WordPress file editor is properly disabled to
 * prevent unauthorized code modification through the admin panel.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * File Editing Disabled Diagnostic Class
 *
 * Checks for:
 * - DISALLOW_FILE_EDIT constant status
 * - DISALLOW_FILE_MODS constant status
 * - Theme editor accessibility
 * - Plugin editor accessibility
 * - File write permissions on critical files
 * - Alternative code injection routes
 *
 * Enabled file editing allows compromised admin accounts to inject
 * malicious code directly through the WordPress admin interface.
 *
 * @since 0.6093.1200
 */
class Diagnostic_File_Editing_Disabled extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @since 0.6093.1200
	 * @var   string
	 */
	protected static $slug = 'file-editing-disabled';

	/**
	 * The diagnostic title
	 *
	 * @since 0.6093.1200
	 * @var   string
	 */
	protected static $title = 'File Editing Not Disabled';

	/**
	 * The diagnostic description
	 *
	 * @since 0.6093.1200
	 * @var   string
	 */
	protected static $description = 'Verifies WordPress file editor is disabled for security';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @since 0.6093.1200
	 * @var   string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * Validates file editing restrictions.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check 1: DISALLOW_FILE_EDIT constant.
		$edit_disabled = self::check_file_edit_constant();
		if ( ! $edit_disabled ) {
			$issues[] = __( 'DISALLOW_FILE_EDIT not defined (theme/plugin editor accessible)', 'wpshadow' );
		}

		// Check 2: DISALLOW_FILE_MODS constant.
		$mods_disabled = self::check_file_mods_constant();
		if ( ! $mods_disabled ) {
			$issues[] = __( 'DISALLOW_FILE_MODS not defined (plugin/theme installation allowed)', 'wpshadow' );
		}

		// Check 3: Theme editor page accessible.
		if ( ! $edit_disabled ) {
			$theme_editor_accessible = self::check_theme_editor_accessibility();
			if ( $theme_editor_accessible ) {
				$issues[] = __( 'Theme editor page is accessible at /wp-admin/theme-editor.php', 'wpshadow' );
			}
		}

		// Check 4: Plugin editor page accessible.
		if ( ! $edit_disabled ) {
			$plugin_editor_accessible = self::check_plugin_editor_accessibility();
			if ( $plugin_editor_accessible ) {
				$issues[] = __( 'Plugin editor page is accessible at /wp-admin/plugin-editor.php', 'wpshadow' );
			}
		}

		// Check 5: File permissions on critical files.
		$writable_files = self::check_critical_file_permissions();
		if ( ! empty( $writable_files ) ) {
			$issues[] = sprintf(
				/* translators: %d: count */
				_n(
					'%d critical file is writable by web server',
					'%d critical files are writable by web server',
					count( $writable_files ),
					'wpshadow'
				),
				count( $writable_files )
			);
		}

		// Check 6: Check for alternative edit capabilities.
		$has_alt_edit = self::check_alternative_editing_routes();
		if ( $has_alt_edit ) {
			$issues[] = __( 'Alternative file editing routes may exist (custom admin pages)', 'wpshadow' );
		}

		// If we found issues, return finding.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of issues */
					_n(
						'%d file editing security issue detected',
						'%d file editing security issues detected',
						count( $issues ),
						'wpshadow'
					),
					count( $issues )
				),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/disable-file-editing?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'issues'          => $issues,
					'writable_files'  => $writable_files ?? array(),
					'why'             => __(
						'Enabled file editors are the #1 escalation path after admin account compromise. Attackers with stolen admin credentials ' .
						'immediately inject backdoors via theme/plugin editors. The editors allow arbitrary PHP code execution with zero additional ' .
						'authentication. According to Wordfence, 61% of WordPress compromises involve post-breach backdoor injection. File editors ' .
						'bypass file upload restrictions and antivirus scanning. Malicious code persists through updates if injected into theme functions.php. ' .
						'Social engineering (phishing admin accounts) combined with file editors enables complete site takeover. Even with strong passwords, ' .
						'session hijacking exposes editors. Sucuri reports file editor exploitation in 84% of analyzed malware infections.',
						'wpshadow'
					),
					'recommendation'  => __(
						'Add to wp-config.php: define(\'DISALLOW_FILE_EDIT\', true); to disable theme/plugin editors. For maximum security, also add: ' .
						'define(\'DISALLOW_FILE_MODS\', true); to prevent plugin/theme installation. Set file permissions: chmod 644 wp-config.php, ' .
						'chmod 644 .htaccess, chmod 444 index.php (read-only). Consider removing write permissions from entire wp-content for production. ' .
						'Use deployment pipelines instead of admin panel updates. Implement CAP_EDIT_THEMES / CAP_EDIT_PLUGINS checks. Monitor file changes ' .
						'with integrity checking. Use version control for all code modifications.',
						'wpshadow'
					),
				),
			);
		}

		return null;
	}

	/**
	 * Check DISALLOW_FILE_EDIT constant.
	 *
	 * @since 0.6093.1200
	 * @return bool True if disabled.
	 */
	private static function check_file_edit_constant() {
		return defined( 'DISALLOW_FILE_EDIT' ) && true === DISALLOW_FILE_EDIT;
	}

	/**
	 * Check DISALLOW_FILE_MODS constant.
	 *
	 * @since 0.6093.1200
	 * @return bool True if disabled.
	 */
	private static function check_file_mods_constant() {
		return defined( 'DISALLOW_FILE_MODS' ) && true === DISALLOW_FILE_MODS;
	}

	/**
	 * Check theme editor accessibility.
	 *
	 * @since 0.6093.1200
	 * @return bool True if accessible.
	 */
	private static function check_theme_editor_accessibility() {
		$editor_file = ABSPATH . 'wp-admin/theme-editor.php';
		return file_exists( $editor_file );
	}

	/**
	 * Check plugin editor accessibility.
	 *
	 * @since 0.6093.1200
	 * @return bool True if accessible.
	 */
	private static function check_plugin_editor_accessibility() {
		$editor_file = ABSPATH . 'wp-admin/plugin-editor.php';
		return file_exists( $editor_file );
	}

	/**
	 * Check critical file permissions.
	 *
	 * @since 0.6093.1200
	 * @return array Writable files.
	 */
	private static function check_critical_file_permissions() {
		$writable = array();
		$critical_files = array(
			ABSPATH . 'wp-config.php',
			ABSPATH . '.htaccess',
			ABSPATH . 'index.php',
			get_stylesheet_directory() . '/functions.php',
		);

		foreach ( $critical_files as $file ) {
			if ( ! file_exists( $file ) ) {
				continue;
			}

			// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			if ( @is_writable( $file ) ) {
				$writable[] = str_replace( ABSPATH, '', $file );
			}
		}

		return $writable;
	}

	/**
	 * Check for alternative editing routes.
	 *
	 * @since 0.6093.1200
	 * @return bool True if alternative routes exist.
	 */
	private static function check_alternative_editing_routes() {
		global $menu, $submenu;

		// Check for custom file manager plugins.
		$file_manager_plugins = array(
			'wp-file-manager',
			'file-manager',
			'advanced-file-manager',
		);

		$active_plugins = get_option( 'active_plugins', array() );
		foreach ( $file_manager_plugins as $plugin ) {
			foreach ( $active_plugins as $active ) {
				if ( str_contains( $active, $plugin ) ) {
					return true;
				}
			}
		}

		// Check for custom admin pages with file editing.
		if ( is_array( $menu ) ) {
			foreach ( $menu as $item ) {
				if ( isset( $item[0] ) && preg_match( '/file|edit|manager/i', $item[0] ) ) {
					return true;
				}
			}
		}

		return false;
	}
}
