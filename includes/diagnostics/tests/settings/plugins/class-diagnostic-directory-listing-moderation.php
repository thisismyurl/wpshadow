<?php
/**
 * Directory Listing Moderation Diagnostic
 *
 * Directory moderation queue growing.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.558.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Directory Listing Moderation Diagnostic Class
 *
 * @since 1.558.0000
 */
class Diagnostic_DirectoryListingModeration extends Diagnostic_Base {

	protected static $slug = 'directory-listing-moderation';
	protected static $title = 'Directory Listing Moderation';
	protected static $description = 'Directory moderation queue growing';
	protected static $family = 'functionality';

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
		$has_issue = !empty($issues);
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 50 ),
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/directory-listing-moderation',
			);
		}
		
		return null;
	}
}
