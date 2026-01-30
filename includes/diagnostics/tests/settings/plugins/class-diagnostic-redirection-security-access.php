<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_RedirectionSecurityAccess extends Diagnostic_Base {
	protected static $slug = 'redirection-security-access';
	protected static $title = 'Redirection Access Controls';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! class_exists( 'Red_Item' ) ) { return null; }
		$options = get_option( 'redirection_options', array() );
		if ( empty( $options['token'] ) ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'REST API token not configured', 'wpshadow' ),
				'severity' => 'high',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/redirection-security',
			);
		}
		return null;
	}
}
