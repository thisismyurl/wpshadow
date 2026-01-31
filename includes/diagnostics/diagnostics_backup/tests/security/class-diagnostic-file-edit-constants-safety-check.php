<?php
/**
 * File Edit Constants Safety Check Diagnostic
 *
 * Validates DISALLOW_FILE_EDIT and DISALLOW_FILE_MODS constants for security.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26028.1905
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * File Edit Constants Safety Check Class
 *
 * Tests file edit security constants.
 *
 * @since 1.26028.1905
 */
class Diagnostic_File_Edit_Constants_Safety_Check extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'file-edit-constants-safety-check';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'File Edit Constants Safety Check';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates DISALLOW_FILE_EDIT and DISALLOW_FILE_MODS constants for security';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26028.1905
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$safety_check = self::check_file_edit_constants();
		
		if ( ! $safety_check['is_safe'] ) {
			$issues = array();
			
			if ( ! $safety_check['disallow_file_edit'] ) {
				$issues[] = __( 'DISALLOW_FILE_EDIT not set (theme/plugin editor accessible)', 'wpshadow' );
			}

			if ( $safety_check['theme_editor_accessible'] ) {
				$issues[] = __( 'Theme Editor accessible to admins (code execution risk)', 'wpshadow' );
			}

			if ( $safety_check['plugin_editor_accessible'] ) {
				$issues[] = __( 'Plugin Editor accessible to admins (code execution risk)', 'wpshadow' );
			}

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $issues ),
				'severity'     => 'critical',
				'threat_level' => 85,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/file-edit-constants-safety-check',
				'meta'         => array(
					'disallow_file_edit'       => $safety_check['disallow_file_edit'],
					'disallow_file_mods'       => $safety_check['disallow_file_mods'],
					'theme_editor_accessible'  => $safety_check['theme_editor_accessible'],
					'plugin_editor_accessible' => $safety_check['plugin_editor_accessible'],
				),
			);
		}

		return null;
	}

	/**
	 * Check file edit constants.
	 *
	 * @since  1.26028.1905
	 * @return array Check results.
	 */
	private static function check_file_edit_constants() {
		$check = array(
			'is_safe'                  => true,
			'disallow_file_edit'       => false,
			'disallow_file_mods'       => false,
			'theme_editor_accessible'  => false,
			'plugin_editor_accessible' => false,
		);

		// Check DISALLOW_FILE_EDIT.
		if ( defined( 'DISALLOW_FILE_EDIT' ) && DISALLOW_FILE_EDIT ) {
			$check['disallow_file_edit'] = true;
		} else {
			$check['is_safe'] = false;
		}

		// Check DISALLOW_FILE_MODS (stricter - disables editing AND installation).
		if ( defined( 'DISALLOW_FILE_MODS' ) && DISALLOW_FILE_MODS ) {
			$check['disallow_file_mods'] = true;
			$check['disallow_file_edit'] = true; // Implies file edit disabled.
		}

		// Test if theme editor is actually accessible.
		if ( ! $check['disallow_file_edit'] ) {
			// Check if current user can edit themes.
			$check['theme_editor_accessible'] = current_user_can( 'edit_themes' );
			if ( $check['theme_editor_accessible'] ) {
				$check['is_safe'] = false;
			}
		}

		// Test if plugin editor is actually accessible.
		if ( ! $check['disallow_file_edit'] ) {
			// Check if current user can edit plugins.
			$check['plugin_editor_accessible'] = current_user_can( 'edit_plugins' );
			if ( $check['plugin_editor_accessible'] ) {
				$check['is_safe'] = false;
			}
		}

		return $check;
	}
}
