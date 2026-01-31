<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_GravityFormsFileUploadSecurity extends Diagnostic_Base {
	protected static $slug = 'gravity-forms-file-upload-security';
	protected static $title = 'Gravity Forms File Upload Security';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! class_exists( 'GFForms' ) ) { return null; }
		$allowed_extensions = get_option( 'gform_upload_allowed_extensions', array() );
		$dangerous = array_intersect( $allowed_extensions, array( 'php', 'exe', 'sh', 'bat' ) );
		if ( ! empty( $dangerous ) ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'Dangerous file types allowed', 'wpshadow' ),
				'severity' => 'critical',
				'threat_level' => 90,
				'auto_fixable' => true,
				'kb_link' => 'https://wpshadow.com/kb/gravity-forms-file-security',
			);
		}

		// Plugin integration checks
		if ( ! function_exists( 'get_plugins' ) ) {
			$issues[] = __( 'Plugin listing not available', 'wpshadow' );
		}
		if ( ! function_exists( 'is_plugin_active' ) ) {
			$issues[] = __( 'Plugin status check unavailable', 'wpshadow' );
		}
		// Verify integration point
		if ( ! function_exists( 'do_action' ) ) {
			$issues[] = __( 'Action hooks unavailable', 'wpshadow' );
		}
		return null;
	}
}
