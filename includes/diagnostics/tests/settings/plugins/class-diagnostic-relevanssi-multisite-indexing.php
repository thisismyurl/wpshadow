<?php
/**
 * Relevanssi Multisite Indexing Diagnostic
 *
 * Relevanssi multisite indexing slow.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.405.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Relevanssi Multisite Indexing Diagnostic Class
 *
 * @since 1.405.0000
 */
class Diagnostic_RelevanssiMultisiteIndexing extends Diagnostic_Base {

	protected static $slug = 'relevanssi-multisite-indexing';
	protected static $title = 'Relevanssi Multisite Indexing';
	protected static $description = 'Relevanssi multisite indexing slow';
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
				'severity'    => 50,
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/relevanssi-multisite-indexing',
			);
		}
		
		return null;
	}
}
