<?php
/**
 * Polylang Pro Menu Sync Diagnostic
 *
 * Polylang Pro Menu Sync misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1149.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Polylang Pro Menu Sync Diagnostic Class
 *
 * @since 1.1149.0000
 */
class Diagnostic_PolylangProMenuSync extends Diagnostic_Base {

	protected static $slug = 'polylang-pro-menu-sync';
	protected static $title = 'Polylang Pro Menu Sync';
	protected static $description = 'Polylang Pro Menu Sync misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'POLYLANG_VERSION' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/polylang-pro-menu-sync',
			);
		}
		
		return null;
	}
}
