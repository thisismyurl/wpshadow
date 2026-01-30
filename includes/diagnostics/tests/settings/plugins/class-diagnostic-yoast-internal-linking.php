<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_YoastInternalLinking extends Diagnostic_Base {
	protected static $slug = 'yoast-internal-linking';
	protected static $title = 'Yoast Internal Linking';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! defined( 'WPSEO_PREMIUM_FILE' ) ) { return null; }
		$enabled = get_option( 'wpseo_internal_link_suggestions_enabled', true );
		if ( ! $enabled ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'Internal link suggestions disabled', 'wpshadow' ),
				'severity' => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/yoast-internal-linking',
			);
		}
		return null;
	}
}
