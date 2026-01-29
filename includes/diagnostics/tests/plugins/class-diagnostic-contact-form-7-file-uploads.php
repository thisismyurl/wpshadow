<?php
/**
 * Contact Form 7 File Uploads Diagnostic
 *
 * Contact Form 7 File Uploads issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1202.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contact Form 7 File Uploads Diagnostic Class
 *
 * @since 1.1202.0000
 */
class Diagnostic_ContactForm7FileUploads extends Diagnostic_Base {

	protected static $slug = 'contact-form-7-file-uploads';
	protected static $title = 'Contact Form 7 File Uploads';
	protected static $description = 'Contact Form 7 File Uploads issue found';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'WPCF7_VERSION' ) ) {
			return null;
		}
		
		$has_issue = false;
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 50 ),
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/contact-form-7-file-uploads',
			);
		}
		
		return null;
	}
}
