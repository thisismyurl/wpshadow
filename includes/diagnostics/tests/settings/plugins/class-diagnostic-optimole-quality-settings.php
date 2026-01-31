<?php
/**
 * Optimole Quality Settings Diagnostic
 *
 * Optimole Quality Settings detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.767.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Optimole Quality Settings Diagnostic Class
 *
 * @since 1.767.0000
 */
class Diagnostic_OptimoleQualitySettings extends Diagnostic_Base {

	protected static $slug = 'optimole-quality-settings';
	protected static $title = 'Optimole Quality Settings';
	protected static $description = 'Optimole Quality Settings detected';
	protected static $family = 'functionality';

	public static function check() {
		
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
				'kb_link'     => 'https://wpshadow.com/kb/optimole-quality-settings',
			);
		}
		
		return null;
	}
}
