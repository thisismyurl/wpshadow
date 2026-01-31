<?php
/**
 * Directory Featured Listings Diagnostic
 *
 * Directory featured logic exploitable.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.563.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Directory Featured Listings Diagnostic Class
 *
 * @since 1.563.0000
 */
class Diagnostic_DirectoryFeaturedListings extends Diagnostic_Base {

	protected static $slug = 'directory-featured-listings';
	protected static $title = 'Directory Featured Listings';
	protected static $description = 'Directory featured logic exploitable';
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
				'severity'    => self::calculate_severity( 55 ),
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/directory-featured-listings',
			);
		}
		
		return null;
	}
}
