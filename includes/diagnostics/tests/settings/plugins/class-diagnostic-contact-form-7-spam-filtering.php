<?php
/**
 * Contact Form 7 Spam Filtering Diagnostic
 *
 * Contact Form 7 Spam Filtering issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1201.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contact Form 7 Spam Filtering Diagnostic Class
 *
 * @since 1.1201.0000
 */
class Diagnostic_ContactForm7SpamFiltering extends Diagnostic_Base {

	protected static $slug = 'contact-form-7-spam-filtering';
	protected static $title = 'Contact Form 7 Spam Filtering';
	protected static $description = 'Contact Form 7 Spam Filtering issue found';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'WPCF7_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		// Check if feature is configured
		$option_prefix = 'diagnostic_' . str_replace('-', '_', self::$slug);
		$configured = get_option($option_prefix, false);
		if (!$configured) {
			$issues[] = 'feature not configured';
		}
		$has_issue = !empty($issues);
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 70 ),
				'threat_level' => 70,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/contact-form-7-spam-filtering',
			);
		}
		
		return null;
	}
}
