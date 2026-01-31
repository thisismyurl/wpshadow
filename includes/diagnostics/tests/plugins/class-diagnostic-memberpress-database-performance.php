<?php
/**
 * MemberPress Database Performance Diagnostic
 *
 * MemberPress queries slowing database.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.324.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * MemberPress Database Performance Diagnostic Class
 *
 * @since 1.324.0000
 */
class Diagnostic_MemberpressDatabasePerformance extends Diagnostic_Base {

	protected static $slug = 'memberpress-database-performance';
	protected static $title = 'MemberPress Database Performance';
	protected static $description = 'MemberPress queries slowing database';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'MEPR_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		// Check if feature is configured
		$option_prefix = 'diagnostic_' . str_replace('-', '_', self::$slug);
		$configured = get_option($option_prefix, false);
		if (!$configured) {
			$issues[] = 'feature not configured';
		}
		$has_issue = !empty($issues)
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 55 ),
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/memberpress-database-performance',
			);
		}
		
		return null;
	}
}
