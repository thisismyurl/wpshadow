<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_ContactForm7FileUploadSecurity extends Diagnostic_Base {
	protected static $slug = 'contact-form-7-file-upload-security';
	protected static $title = 'Contact Form 7 File Upload Security';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! defined( 'WPCF7_VERSION' ) ) { return null; }
		$upload_dir = wp_upload_dir();
		$cf7_uploads = $upload_dir['basedir'] . '/wpcf7_uploads';
		if ( is_dir( $cf7_uploads ) && ! file_exists( $cf7_uploads . '/.htaccess' ) ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'File upload directory not protected', 'wpshadow' ),
				'severity' => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/cf7-file-security',
			);
		}
		return null;
	}
}
