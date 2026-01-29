<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_JetpackPrivacyDataSharing extends Diagnostic_Base {
	protected static $slug = 'jetpack-privacy-data-sharing';
	protected static $title = 'Jetpack Privacy Data Sharing';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! class_exists( 'Jetpack' ) ) { return null; }
		$active = \Jetpack::get_active_modules();
		$privacy_modules = array( 'stats', 'subscriptions', 'publicize', 'related-posts' );
		$enabled = array_intersect( $privacy_modules, $active );
		if ( count( $enabled ) > 0 && ! has_action( 'wp_footer', 'jetpack_privacy_notice' ) ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'Data-sharing modules active without privacy disclosure', 'wpshadow' ),
				'severity' => 'high',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/jetpack-privacy',
			);
		}
		return null;
	}
}
