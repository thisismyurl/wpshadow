<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Diagnostic_Wp_Rest_DateTime_Handling extends Diagnostic_Base {
	protected static $slug = 'wp-rest-datetime-handling';
	protected static $title = 'REST API DateTime Handling';
	protected static $description = 'Validates REST API date/time conversions';
	protected static $family = 'rest_api';
	public static function check() {
		if ( ! function_exists( 'rest_api_init' ) ) { return null; }
		$current_offset = get_option( 'gmt_offset' );
		if ( empty( $current_offset ) || ! is_numeric( $current_offset ) ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'REST API timezone handling may be inconsistent. Set a proper timezone in WordPress Settings > General.', 'wpshadow' ),
				'severity' => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/wp-rest-datetime-handling',
				'meta' => array( 'gmt_offset' => $current_offset ),
			);
		}
		return null;
	}
}
