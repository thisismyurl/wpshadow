<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_RankMathSecurityAccess extends Diagnostic_Base {
	protected static $slug = 'rank-math-security-access';
	protected static $title = 'Rank Math Security Access';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! class_exists( 'RankMath' ) ) { return null; }
		$general = get_option( 'rank-math-options-general', array() );
		if ( ! empty( $general['allow_for_editors'] ) || ! empty( $general['allow_for_authors'] ) ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'SEO settings accessible to non-admin users', 'wpshadow' ),
				'severity' => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/rank-math-security',
			);
		}
		return null;
	}
}
