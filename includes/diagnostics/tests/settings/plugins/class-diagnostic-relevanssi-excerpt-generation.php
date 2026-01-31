<?php
/**
 * Relevanssi Excerpt Generation Diagnostic
 *
 * Relevanssi excerpt generation slow.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.401.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Relevanssi Excerpt Generation Diagnostic Class
 *
 * @since 1.401.0000
 */
class Diagnostic_RelevanssiExcerptGeneration extends Diagnostic_Base {

	protected static $slug = 'relevanssi-excerpt-generation';
	protected static $title = 'Relevanssi Excerpt Generation';
	protected static $description = 'Relevanssi excerpt generation slow';
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
		$has_issue = !empty($issues)
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 40 ),
				'threat_level' => 40,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/relevanssi-excerpt-generation',
			);
		}
		
		return null;
	}
}
