<?php
/**
 * Directory Listing Categories Diagnostic
 *
 * Directory category queries inefficient.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.559.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Directory Listing Categories Diagnostic Class
 *
 * @since 1.559.0000
 */
class Diagnostic_DirectoryListingCategories extends Diagnostic_Base {

	protected static $slug = 'directory-listing-categories';
	protected static $title = 'Directory Listing Categories';
	protected static $description = 'Directory category queries inefficient';
	protected static $family = 'performance';

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
				'severity'    => self::calculate_severity( 45 ),
				'threat_level' => 45,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/directory-listing-categories',
			);
		}
		
		return null;
	}
}
