<?php
/**
 * Wpforms Lite Upgrade Prompts Diagnostic
 *
 * Wpforms Lite Upgrade Prompts issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1197.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wpforms Lite Upgrade Prompts Diagnostic Class
 *
 * @since 1.1197.0000
 */
class Diagnostic_WpformsLiteUpgradePrompts extends Diagnostic_Base {

	protected static $slug = 'wpforms-lite-upgrade-prompts';
	protected static $title = 'Wpforms Lite Upgrade Prompts';
	protected static $description = 'Wpforms Lite Upgrade Prompts issue found';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! function_exists( 'wpforms' ) ) {
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
				'severity'    => self::calculate_severity( 50 ),
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/wpforms-lite-upgrade-prompts',
			);
		}
		
		return null;
	}
}
