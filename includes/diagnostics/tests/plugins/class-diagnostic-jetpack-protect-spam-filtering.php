<?php
/**
 * Jetpack Protect Spam Filtering Diagnostic
 *
 * Jetpack Protect Spam Filtering misconfiguration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.876.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Jetpack Protect Spam Filtering Diagnostic Class
 *
 * @since 1.876.0000
 */
class Diagnostic_JetpackProtectSpamFiltering extends Diagnostic_Base {

	protected static $slug = 'jetpack-protect-spam-filtering';
	protected static $title = 'Jetpack Protect Spam Filtering';
	protected static $description = 'Jetpack Protect Spam Filtering misconfiguration';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'Jetpack' ) ) {
			return null;
		}
		
		$issues = array();
		// Check if feature is configured
		$option_prefix = 'diagnostic_' . str_replace('-', '_', self::$slug);
		$configured = get_option($option_prefix, false);
		if (!$configured) {
			$issues[] = 'feature not configured';
		}
		$has_issue = !empty($issues)
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 70 ),
				'threat_level' => 70,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/jetpack-protect-spam-filtering',
			);
		}
		
		return null;
	}
}
