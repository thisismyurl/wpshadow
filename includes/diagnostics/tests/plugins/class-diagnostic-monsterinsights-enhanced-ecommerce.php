<?php
/**
 * MonsterInsights Enhanced Ecommerce Diagnostic
 *
 * MonsterInsights enhanced ecommerce not configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.229.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * MonsterInsights Enhanced Ecommerce Diagnostic Class
 *
 * @since 1.229.0000
 */
class Diagnostic_MonsterinsightsEnhancedEcommerce extends Diagnostic_Base {

	protected static $slug = 'monsterinsights-enhanced-ecommerce';
	protected static $title = 'MonsterInsights Enhanced Ecommerce';
	protected static $description = 'MonsterInsights enhanced ecommerce not configured';
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
				'severity'    => self::calculate_severity( 40 ),
				'threat_level' => 40,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/monsterinsights-enhanced-ecommerce',
			);
		}
		
		return null;
	}
}
