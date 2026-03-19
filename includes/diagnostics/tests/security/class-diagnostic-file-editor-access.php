<?php
/**
 * File Editor Access Diagnostic
 *
 * Issue #4913: File Editor Accessible in Admin (Code Injection Risk)
 * Pillar: 🛡️ Safe by Default
 *
 * Checks if theme/plugin file editor is disabled.
 * File editor allows code injection if admin account compromised.
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
 * Diagnostic_File_Editor_Access Class
 *
 * @since 1.6093.1200
 */
class Diagnostic_File_Editor_Access extends Diagnostic_Base {

	protected static $slug = 'file-editor-access';
	protected static $title = 'File Editor Accessible in Admin (Code Injection Risk)';
	protected static $description = 'Checks if theme and plugin file editors are disabled';
	protected static $family = 'security';

	public static function check() {
		// Check if file editing is enabled
		$file_editing_enabled = ! defined( 'DISALLOW_FILE_EDIT' ) || ! DISALLOW_FILE_EDIT;

		if ( $file_editing_enabled ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'The file editor lets admins modify PHP code directly. If an admin account is compromised, attackers can inject malicious code. Disable it.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 80,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/file-editor',
				'details'      => array(
					'attack_scenario'         => 'Compromised admin → Edit theme file → Inject backdoor',
					'disable_method'          => 'Add to wp-config.php: define("DISALLOW_FILE_EDIT", true);',
					'locations'               => 'Appearance → Theme Editor, Plugins → Plugin Editor',
					'alternative'             => 'Use SFTP/SSH for file changes (audit trail)',
				),
			);
		}

		return null;
	}
}
