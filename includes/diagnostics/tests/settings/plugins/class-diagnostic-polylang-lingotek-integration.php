<?php
/**
 * Polylang Lingotek Integration Diagnostic
 *
 * Polylang Lingotek not configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.311.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Polylang Lingotek Integration Diagnostic Class
 *
 * @since 1.311.0000
 */
class Diagnostic_PolylangLingotekIntegration extends Diagnostic_Base {

	protected static $slug = 'polylang-lingotek-integration';
	protected static $title = 'Polylang Lingotek Integration';
	protected static $description = 'Polylang Lingotek not configured';
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
		$has_issue = !empty($issues)
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 30 ),
				'threat_level' => 30,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/polylang-lingotek-integration',
			);
		}
		
		return null;
	}
}
