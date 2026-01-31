<?php
/**
 * Rank Math 404 Monitoring Diagnostic
 *
 * Rank Math 404 Monitoring configuration issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.698.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Rank Math 404 Monitoring Diagnostic Class
 *
 * @since 1.698.0000
 */
class Diagnostic_RankMath404Monitoring extends Diagnostic_Base {

	protected static $slug = 'rank-math-404-monitoring';
	protected static $title = 'Rank Math 404 Monitoring';
	protected static $description = 'Rank Math 404 Monitoring configuration issues';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'RANK_MATH_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		// Check if feature is configured
		$option_prefix = 'diagnostic_' . str_replace('-', '_', self::$slug);
		$configured = get_option($option_prefix, false);
		if (!$configured) {
			$issues[] = 'feature not configured';
		}
		$has_issue = !empty($issues);
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 50 ),
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/rank-math-404-monitoring',
			);
		}
		
		return null;
	}
}
