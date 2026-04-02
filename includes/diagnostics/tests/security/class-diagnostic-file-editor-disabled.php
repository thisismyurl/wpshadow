<?php
/**
 * Theme and Plugin Editor Disabled Diagnostic (Stub)
 *
 * Generated diagnostic stub for post-install hardening checklist item 14.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Diagnostics\Helpers\Diagnostic_Server_Environment_Helper as Server_Env;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme and Plugin Editor Disabled Diagnostic Class (Stub)
 *
 * TODO: Implement robust, production-safe test logic.
 * TODO: Implement companion treatment after validation.
 * TODO: Add KB article and user-facing remediation guidance.
 *
 * @since 0.6093.1200
 */
class Diagnostic_File_Editor_Disabled extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'file-editor-disabled';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Theme and Plugin Editor Disabled';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Stub diagnostic for Theme and Plugin Editor Disabled. TODO: implement full test and remediation guidance.';

	/**
	 * Gauge family/category for dashboard placement.
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * TODO Test Plan:
	 * Use defined('DISALLOW_FILE_EDIT') and value checks.
	 *
	 * TODO Fix Plan:
	 * Fix by setting DISALLOW_FILE_EDIT true.
	 *
	 * Constraints:
	 * - Must be testable using built-in WordPress functions or PHP checks.
	 * - Must be fixable via hooks/filters/settings/DB/PHP/server setting.
	 * - Must not modify WordPress core files.
	 * - Must improve performance, security, or site success.
	 *
	 * @since  0.6093.1200
	 * @return array|null Return finding array when issue exists, null when healthy.
	 */
	public static function check() {
		if ( Server_Env::is_file_edit_disabled() ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'The built-in theme and plugin file editor is enabled in the WordPress admin. If an attacker compromises an admin account, they can execute arbitrary code directly through this editor without needing server access. Add DISALLOW_FILE_EDIT in wp-config.php to disable it.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 55,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/file-editor-disabled',
			'details'      => array(
				'disallow_file_edit' => false,
				'fix'                => __( 'Add define( \'DISALLOW_FILE_EDIT\', true ); to wp-config.php.', 'wpshadow' ),
			),
		);
	}
}
