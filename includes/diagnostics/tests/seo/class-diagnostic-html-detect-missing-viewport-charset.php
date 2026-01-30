<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Diagnostic_Html_Detect_Missing extends Diagnostic_Base {
	protected static $slug = 'html-detect-missing-viewport-charset';
	protected static $title = 'Missing Viewport & Charset Meta Tags';
	protected static $description = 'Detects missing critical meta tags';
	protected static $family = 'html';
	public static function check() {
		if ( is_admin() ) { return null; }
		$has_viewport = false;
		$has_charset = false;
		global $wp_scripts;
		if ( ! empty( $wp_scripts ) && isset( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script_obj ) {
				if ( isset( $script_obj->extra['data'] ) ) {
					$data = (string) $script_obj->extra['data'];
					if ( preg_match( '/<meta[^>]*name=["\']viewport["\']/', $data ) ) { $has_viewport = true; }
					if ( preg_match( '/<meta[^>]*charset=["\']?utf-8["\']?/', $data ) ) { $has_charset = true; }
				}
			}
		}
		$missing = array();
		if ( ! $has_viewport ) { $missing[] = 'viewport'; }
		if ( ! $has_charset ) { $missing[] = 'charset'; }
		if ( empty( $missing ) ) { return null; }
		return array(
			'id' => self::$slug,
			'title' => self::$title,
			'description' => sprintf( __( 'Missing critical meta tags: %s. Add to <head>: <meta charset="utf-8"> and <meta name="viewport" content="width=device-width, initial-scale=1">', 'wpshadow' ), implode( ', ', $missing ) ),
			'severity' => 'high',
			'threat_level' => 30,
			'auto_fixable' => true,
			'kb_link' => 'https://wpshadow.com/kb/html-detect-missing-meta-tags',
			'meta' => array( 'missing' => $missing ),
		);
	}
}
