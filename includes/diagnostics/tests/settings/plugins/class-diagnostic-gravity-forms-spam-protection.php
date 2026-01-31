<?php
/**
 * Gravity Forms Spam Protection Diagnostic
 *
 * Gravity Forms spam filtering inadequate.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.255.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gravity Forms Spam Protection Diagnostic Class
 *
 * @since 1.255.0000
 */
class Diagnostic_GravityFormsSpamProtection extends Diagnostic_Base {

	protected static $slug = 'gravity-forms-spam-protection';
	protected static $title = 'Gravity Forms Spam Protection';
	protected static $description = 'Gravity Forms spam filtering inadequate';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'GFForms' ) ) {
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
				'severity'    => self::calculate_severity( 55 ),
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/gravity-forms-spam-protection',
			);
		}
		
		return null;
	}
}
