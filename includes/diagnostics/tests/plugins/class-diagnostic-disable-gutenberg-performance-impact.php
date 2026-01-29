<?php
/**
 * Disable Gutenberg Performance Impact Diagnostic
 *
 * Disable Gutenberg Performance Impact issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1438.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Disable Gutenberg Performance Impact Diagnostic Class
 *
 * @since 1.1438.0000
 */
class Diagnostic_DisableGutenbergPerformanceImpact extends Diagnostic_Base {

	protected static $slug = 'disable-gutenberg-performance-impact';
	protected static $title = 'Disable Gutenberg Performance Impact';
	protected static $description = 'Disable Gutenberg Performance Impact issue found';
	protected static $family = 'performance';

	public static function check() {
		if ( ! true // WordPress core feature ) {
			return null;
		}
		
		$has_issue = false;
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 55 ),
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/disable-gutenberg-performance-impact',
			);
		}
		
		return null;
	}
}
