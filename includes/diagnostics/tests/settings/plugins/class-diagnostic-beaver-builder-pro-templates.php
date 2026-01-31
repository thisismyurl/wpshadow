<?php
/**
 * Beaver Builder Pro Templates Diagnostic
 *
 * Beaver Builder Pro Templates issues found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.800.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Beaver Builder Pro Templates Diagnostic Class
 *
 * @since 1.800.0000
 */
class Diagnostic_BeaverBuilderProTemplates extends Diagnostic_Base {

	protected static $slug = 'beaver-builder-pro-templates';
	protected static $title = 'Beaver Builder Pro Templates';
	protected static $description = 'Beaver Builder Pro Templates issues found';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'FLBuilder' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/beaver-builder-pro-templates',
			);
		}
		
		return null;
	}
}
