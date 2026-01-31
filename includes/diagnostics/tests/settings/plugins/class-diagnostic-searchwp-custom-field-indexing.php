<?php
/**
 * SearchWP Custom Field Indexing Diagnostic
 *
 * SearchWP custom fields slowing index.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.408.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SearchWP Custom Field Indexing Diagnostic Class
 *
 * @since 1.408.0000
 */
class Diagnostic_SearchwpCustomFieldIndexing extends Diagnostic_Base {

	protected static $slug = 'searchwp-custom-field-indexing';
	protected static $title = 'SearchWP Custom Field Indexing';
	protected static $description = 'SearchWP custom fields slowing index';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'SearchWP' ) ) {
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
				'severity'    => self::calculate_severity( 45 ),
				'threat_level' => 45,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/searchwp-custom-field-indexing',
			);
		}
		
		return null;
	}
}
