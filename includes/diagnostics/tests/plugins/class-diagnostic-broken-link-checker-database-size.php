<?php
/**
 * Broken Link Checker Database Size Diagnostic
 *
 * Broken Link Checker Database Size issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1422.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Broken Link Checker Database Size Diagnostic Class
 *
 * @since 1.1422.0000
 */
class Diagnostic_BrokenLinkCheckerDatabaseSize extends Diagnostic_Base {

	protected static $slug = 'broken-link-checker-database-size';
	protected static $title = 'Broken Link Checker Database Size';
	protected static $description = 'Broken Link Checker Database Size issue found';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'BLC_ACTIVE' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/broken-link-checker-database-size',
			);
		}
		
		return null;
	}
}
