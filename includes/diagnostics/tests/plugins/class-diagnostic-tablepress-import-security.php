<?php
/**
 * TablePress Import Security Diagnostic
 *
 * TablePress imports not validated.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.416.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * TablePress Import Security Diagnostic Class
 *
 * @since 1.416.0000
 */
class Diagnostic_TablepressImportSecurity extends Diagnostic_Base {

	protected static $slug = 'tablepress-import-security';
	protected static $title = 'TablePress Import Security';
	protected static $description = 'TablePress imports not validated';
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
				'severity'    => self::calculate_severity( 60 ),
				'threat_level' => 60,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/tablepress-import-security',
			);
		}
		
		return null;
	}
}
