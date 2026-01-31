<?php
/**
 * Lingotek Translation Workflow Diagnostic
 *
 * Lingotek Translation Workflow misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1180.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lingotek Translation Workflow Diagnostic Class
 *
 * @since 1.1180.0000
 */
class Diagnostic_LingotekTranslationWorkflow extends Diagnostic_Base {

	protected static $slug = 'lingotek-translation-workflow';
	protected static $title = 'Lingotek Translation Workflow';
	protected static $description = 'Lingotek Translation Workflow misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! true // Generic check ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/lingotek-translation-workflow',
			);
		}
		
		return null;
	}
}
