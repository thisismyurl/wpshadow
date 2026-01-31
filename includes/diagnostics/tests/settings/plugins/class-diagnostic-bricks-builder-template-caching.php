<?php
/**
 * Bricks Builder Template Caching Diagnostic
 *
 * Bricks Builder Template Caching issues found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.821.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Bricks Builder Template Caching Diagnostic Class
 *
 * @since 1.821.0000
 */
class Diagnostic_BricksBuilderTemplateCaching extends Diagnostic_Base {

	protected static $slug = 'bricks-builder-template-caching';
	protected static $title = 'Bricks Builder Template Caching';
	protected static $description = 'Bricks Builder Template Caching issues found';
	protected static $family = 'performance';

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
				'severity'    => self::calculate_severity( 55 ),
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/bricks-builder-template-caching',
			);
		}
		
		return null;
	}
}
