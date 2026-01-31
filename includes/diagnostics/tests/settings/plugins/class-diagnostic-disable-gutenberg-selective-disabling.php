<?php
/**
 * Disable Gutenberg Selective Disabling Diagnostic
 *
 * Disable Gutenberg Selective Disabling issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1436.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Disable Gutenberg Selective Disabling Diagnostic Class
 *
 * @since 1.1436.0000
 */
class Diagnostic_DisableGutenbergSelectiveDisabling extends Diagnostic_Base {

	protected static $slug = 'disable-gutenberg-selective-disabling';
	protected static $title = 'Disable Gutenberg Selective Disabling';
	protected static $description = 'Disable Gutenberg Selective Disabling issue found';
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
				'kb_link'     => 'https://wpshadow.com/kb/disable-gutenberg-selective-disabling',
			);
		}
		
		return null;
	}
}
