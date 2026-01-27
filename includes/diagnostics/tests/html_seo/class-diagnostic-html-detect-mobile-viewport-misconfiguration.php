<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Diagnostic_Html_Detect_Mobile_Viewport_Misconfiguration extends Diagnostic_Base {
	protected static $slug = 'html-detect-mobile-viewport-misconfiguration';
	protected static $title = 'Mobile Viewport Misconfigured';
	protected static $description = 'Detects mobile viewport misconfiguration';
	protected static $family = 'mobile';
	public static function check() {
		if ( is_admin() ) { return null; }
		$viewport_found = false;
		$viewport_config = '';
		global $wp_scripts;
		if ( ! empty( $wp_scripts ) && isset( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script_obj ) {
				if ( isset( $script_obj->extra['data'] ) ) {
					$data = (string) $script_obj->extra['data'];
					if ( preg_match( '/<meta[^>]*name=["\']viewport["\'][^>]*content=["\']([^"\']+)["\']/', $data, $m ) ) {
						$viewport_found = true;
						$viewport_config = $m[1];
						break;
					}
				}
			}
		}
		if ( ! $viewport_found ) { return null; }
		$issues = array();
		if ( ! preg_match( '/width\s*=\s*device-width/', $viewport_config ) ) {
			$issues[] = 'Missing width=device-width';
		}
		if ( ! preg_match( '/initial-scale\s*=\s*1/', $viewport_config ) ) {
			$issues[] = 'Missing or incorrect initial-scale';
		}
		if ( empty( $issues ) ) { return null; }
		return array(
			'id' => self::$slug,
			'title' => self::$title,
			'description' => sprintf( __( 'Viewport meta tag misconfigured: %s. Use: <meta name="viewport" content="width=device-width, initial-scale=1">%s', 'wpshadow' ), implode( ', ', $issues ), '' ),
			'severity' => 'medium',
			'threat_level' => 30,
			'auto_fixable' => true,
			'kb_link' => 'https://wpshadow.com/kb/html-detect-mobile-viewport-misconfiguration',
			'meta' => array( 'current' => $viewport_config, 'issues' => $issues ),
		);
	}
}
