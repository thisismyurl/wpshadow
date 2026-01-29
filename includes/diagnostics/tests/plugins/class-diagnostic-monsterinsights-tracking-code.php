<?php
/**
 * MonsterInsights Tracking Code Diagnostic
 *
 * MonsterInsights tracking code not properly installed.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.228.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * MonsterInsights Tracking Code Diagnostic Class
 *
 * @since 1.228.0000
 */
class Diagnostic_MonsterinsightsTrackingCode extends Diagnostic_Base {

	protected static $slug = 'monsterinsights-tracking-code';
	protected static $title = 'MonsterInsights Tracking Code';
	protected static $description = 'MonsterInsights tracking code not properly installed';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'MONSTERINSIGHTS_VERSION' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/monsterinsights-tracking-code',
			);
		}
		
		return null;
	}
}
