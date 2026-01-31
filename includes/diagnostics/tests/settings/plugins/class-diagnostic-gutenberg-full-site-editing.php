<?php
/**
 * Gutenberg Full Site Editing Diagnostic
 *
 * Gutenberg Full Site Editing issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1240.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gutenberg Full Site Editing Diagnostic Class
 *
 * @since 1.1240.0000
 */
class Diagnostic_GutenbergFullSiteEditing extends Diagnostic_Base {

	protected static $slug = 'gutenberg-full-site-editing';
	protected static $title = 'Gutenberg Full Site Editing';
	protected static $description = 'Gutenberg Full Site Editing issue detected';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! true // WordPress core feature ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/gutenberg-full-site-editing',
			);
		}
		
		return null;
	}
}
