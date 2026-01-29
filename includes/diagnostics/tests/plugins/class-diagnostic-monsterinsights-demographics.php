<?php
/**
 * MonsterInsights Demographics Diagnostic
 *
 * MonsterInsights demographics tracking not enabled.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.230.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * MonsterInsights Demographics Diagnostic Class
 *
 * @since 1.230.0000
 */
class Diagnostic_MonsterinsightsDemographics extends Diagnostic_Base {

	protected static $slug = 'monsterinsights-demographics';
	protected static $title = 'MonsterInsights Demographics';
	protected static $description = 'MonsterInsights demographics tracking not enabled';
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
				'severity'    => self::calculate_severity( 30 ),
				'threat_level' => 30,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/monsterinsights-demographics',
			);
		}
		
		return null;
	}
}
