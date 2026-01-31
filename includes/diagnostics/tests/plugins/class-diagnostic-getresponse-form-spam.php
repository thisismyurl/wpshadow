<?php
/**
 * Getresponse Form Spam Diagnostic
 *
 * Getresponse Form Spam configuration issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.734.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Getresponse Form Spam Diagnostic Class
 *
 * @since 1.734.0000
 */
class Diagnostic_GetresponseFormSpam extends Diagnostic_Base {

	protected static $slug = 'getresponse-form-spam';
	protected static $title = 'Getresponse Form Spam';
	protected static $description = 'Getresponse Form Spam configuration issues';
	protected static $family = 'security';

	public static function check() {
		
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
				'severity'    => self::calculate_severity( 60 ),
				'threat_level' => 60,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/getresponse-form-spam',
			);
		}
		
		return null;
	}
}
