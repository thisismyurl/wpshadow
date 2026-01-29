<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_NinjaFormsFileUploadSecurity extends Diagnostic_Base {
	protected static $slug = 'ninja-forms-file-upload-security';
	protected static $title = 'Ninja Forms File Upload Security';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! class_exists( 'Ninja_Forms' ) ) { return null; }
		$settings = get_option( 'ninja_forms_settings' );
		if ( empty( $settings['upload_file_types'] ) ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'File upload restrictions not configured', 'wpshadow' ),
				'severity' => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/ninja-forms-uploads',
			);
		}
		return null;
	}
}
