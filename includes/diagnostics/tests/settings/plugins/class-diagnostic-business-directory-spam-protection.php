<?php
/**
 * Business Directory Spam Protection Diagnostic
 *
 * Business Directory spam not filtered.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.548.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Business Directory Spam Protection Diagnostic Class
 *
 * @since 1.548.0000
 */
class Diagnostic_BusinessDirectorySpamProtection extends Diagnostic_Base {

	protected static $slug = 'business-directory-spam-protection';
	protected static $title = 'Business Directory Spam Protection';
	protected static $description = 'Business Directory spam not filtered';
	protected static $family = 'security';

	public static function check() {
		if ( ! function_exists( 'wpbdp' ) ) {
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
				'severity'    => self::calculate_severity( 60 ),
				'threat_level' => 60,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/business-directory-spam-protection',
			);
		}
		
		return null;
	}
}
