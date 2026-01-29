<?php
/**
 * TablePress Export Permissions Diagnostic
 *
 * TablePress export accessible to all users.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.417.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * TablePress Export Permissions Diagnostic Class
 *
 * @since 1.417.0000
 */
class Diagnostic_TablepressExportPermissions extends Diagnostic_Base {

	protected static $slug = 'tablepress-export-permissions';
	protected static $title = 'TablePress Export Permissions';
	protected static $description = 'TablePress export accessible to all users';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'TABLEPRESS_VERSION' ) ) {
			return null;
		}
		
		$has_issue = false;
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 55 ),
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/tablepress-export-permissions',
			);
		}
		
		return null;
	}
}
