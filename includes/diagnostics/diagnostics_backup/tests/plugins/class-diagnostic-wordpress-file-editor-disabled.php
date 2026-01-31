<?php
/**
 * Wordpress File Editor Disabled Diagnostic
 *
 * Wordpress File Editor Disabled issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1271.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wordpress File Editor Disabled Diagnostic Class
 *
 * @since 1.1271.0000
 */
class Diagnostic_WordpressFileEditorDisabled extends Diagnostic_Base {

	protected static $slug = 'wordpress-file-editor-disabled';
	protected static $title = 'Wordpress File Editor Disabled';
	protected static $description = 'Wordpress File Editor Disabled issue detected';
	protected static $family = 'functionality';

	public static function check() {
		$issues = array();

		// Check 1: File editor disabled
		if ( ! defined( 'DISALLOW_FILE_EDIT' ) || ! DISALLOW_FILE_EDIT ) {
			$issues[] = 'File editor is enabled';
		}

		// Check 2: File modifications disabled
		if ( ! defined( 'DISALLOW_FILE_MODS' ) || ! DISALLOW_FILE_MODS ) {
			$issues[] = 'File modifications are enabled';
		}

		// Check 3: Theme editor capability present
		if ( current_user_can( 'edit_themes' ) ) {
			$issues[] = 'Theme editing capability is available';
		}

		// Check 4: Plugin editor capability present
		if ( current_user_can( 'edit_plugins' ) ) {
			$issues[] = 'Plugin editing capability is available';
		}

		// Check 5: Multisite super admin still allowed
		if ( is_multisite() && is_super_admin() && ( ! defined( 'DISALLOW_FILE_EDIT' ) || ! DISALLOW_FILE_EDIT ) ) {
			$issues[] = 'Multisite super admins can edit files';
		}

		// Check 6: Debug logging with editor enabled
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && ( ! defined( 'DISALLOW_FILE_EDIT' ) || ! DISALLOW_FILE_EDIT ) ) {
			$issues[] = 'File editor enabled with WP_DEBUG active';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 45;
			$threat_multiplier = 6;
			$max_threat = 75;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d file editor security issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/wordpress-file-editor-disabled',
			);
		}

		return null;
	}
}
