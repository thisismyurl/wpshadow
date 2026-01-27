<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Diagnostic_Environment_Variable_Mapping extends Diagnostic_Base {
	protected static $slug = 'environment-variable-mapping';
	protected static $title = 'Missing Environment Variables';
	protected static $description = 'Detects missing APP_ENV or WP_ENV';
	protected static $family = 'monitoring';
	public static function check() {
		$app_env = getenv( 'APP_ENV' );
		$wp_env = getenv( 'WP_ENV' );
		if ( empty( $app_env ) && empty( $wp_env ) ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'Neither APP_ENV nor WP_ENV environment variables are set. Define at least one to indicate environment (development, staging, production).', 'wpshadow' ),
				'severity' => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/environment-variable-mapping',
				'meta' => array(),
			);
		}
		return null;
	}
}
