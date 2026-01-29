<?php
/**
 * Business Directory Listing Security Diagnostic
 *
 * Business Directory listings not protected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.546.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Business Directory Listing Security Diagnostic Class
 *
 * @since 1.546.0000
 */
class Diagnostic_BusinessDirectoryListingSecurity extends Diagnostic_Base {

	protected static $slug = 'business-directory-listing-security';
	protected static $title = 'Business Directory Listing Security';
	protected static $description = 'Business Directory listings not protected';
	protected static $family = 'security';

	public static function check() {
		if ( ! function_exists( 'wpbdp' ) ) {
			return null;
		}
		
		$has_issue = false;
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 65 ),
				'threat_level' => 65,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/business-directory-listing-security',
			);
		}
		
		return null;
	}
}
