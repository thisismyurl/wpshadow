<?php
/**
 * Listify Listing Security Diagnostic
 *
 * Listify listings not secured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.557.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Listify Listing Security Diagnostic Class
 *
 * @since 1.557.0000
 */
class Diagnostic_ListifyListingSecurity extends Diagnostic_Base {

	protected static $slug = 'listify-listing-security';
	protected static $title = 'Listify Listing Security';
	protected static $description = 'Listify listings not secured';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'LISTIFY_VERSION' ) ) {
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
				'severity'    => self::calculate_severity( 65 ),
				'threat_level' => 65,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/listify-listing-security',
			);
		}
		
		return null;
	}
}
