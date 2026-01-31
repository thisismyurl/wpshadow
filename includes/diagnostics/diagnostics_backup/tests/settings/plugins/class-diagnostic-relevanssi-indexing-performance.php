<?php
/**
 * Relevanssi Indexing Performance Diagnostic
 *
 * Relevanssi indexing slowing database.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.399.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Relevanssi Indexing Performance Diagnostic Class
 *
 * @since 1.399.0000
 */
class Diagnostic_RelevanssiIndexingPerformance extends Diagnostic_Base {

	protected static $slug = 'relevanssi-indexing-performance';
	protected static $title = 'Relevanssi Indexing Performance';
	protected static $description = 'Relevanssi indexing slowing database';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'RELEVANSSI_PREMIUM_VERSION' ) || function_exists( 'relevanssi_search' ) ) {
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
				'severity'    => 55,
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/relevanssi-indexing-performance',
			);
		}
		
		return null;
	}
}
