<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_LitespeedQuicCloud extends Diagnostic_Base {
	protected static $slug = 'litespeed-quic-cloud';
	protected static $title = 'LiteSpeed QUIC.cloud Integration';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! defined( 'LSCWP_V' ) ) { return null; }
		$apikey = get_option( 'litespeed.conf.apikey', '' );
		if ( empty( $apikey ) ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'QUIC.cloud not connected', 'wpshadow' ),
				'severity' => 'medium',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/litespeed-quic',
			);
		}
		return null;
	}
}
