<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_GravityFormsWebhooks extends Diagnostic_Base {
	protected static $slug = 'gravity-forms-webhooks';
	protected static $title = 'Gravity Forms Webhooks';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! class_exists( 'GFForms' ) ) { return null; }
		global $wpdb;
		$webhooks = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}gf_webhook" );
		if ( $webhooks > 0 ) {
			$insecure = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}gf_webhook WHERE url LIKE 'http://%'" );
			if ( $insecure > 0 ) {
				return array(
					'id' => self::$slug,
					'title' => self::$title,
					'description' => sprintf( __( '%d webhooks using insecure HTTP', 'wpshadow' ), $insecure ),
					'severity' => 'high',
					'threat_level' => 70,
					'auto_fixable' => false,
					'kb_link' => 'https://wpshadow.com/kb/gravity-forms-webhooks',
				);
			}
		}
		return null;
	}
}
