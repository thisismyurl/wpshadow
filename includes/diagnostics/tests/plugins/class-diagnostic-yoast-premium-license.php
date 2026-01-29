<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_YoastPremiumLicense extends Diagnostic_Base {
	protected static $slug = 'yoast-premium-license';
	protected static $title = 'Yoast SEO Premium License';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! defined( 'WPSEO_VERSION' ) ) { return null; }
		$license = get_option( 'wpseo_license_key', '' );
		if ( empty( $license ) && defined( 'WPSEO_PREMIUM_FILE' ) ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'Yoast Premium license not active', 'wpshadow' ),
				'severity' => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/yoast-license',
			);
		}
		return null;
	}
}
