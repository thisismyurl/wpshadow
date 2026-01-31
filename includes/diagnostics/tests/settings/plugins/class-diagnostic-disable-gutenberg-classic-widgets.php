<?php
/**
 * Disable Gutenberg Classic Widgets Diagnostic
 *
 * Disable Gutenberg Classic Widgets issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1437.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Disable Gutenberg Classic Widgets Diagnostic Class
 *
 * @since 1.1437.0000
 */
class Diagnostic_DisableGutenbergClassicWidgets extends Diagnostic_Base {

	protected static $slug = 'disable-gutenberg-classic-widgets';
	protected static $title = 'Disable Gutenberg Classic Widgets';
	protected static $description = 'Disable Gutenberg Classic Widgets issue found';
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
				'kb_link'     => 'https://wpshadow.com/kb/disable-gutenberg-classic-widgets',
			);
		}
		
		return null;
	}
}
