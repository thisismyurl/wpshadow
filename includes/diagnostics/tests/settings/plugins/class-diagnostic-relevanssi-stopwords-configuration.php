<?php
/**
 * Relevanssi Stopwords Configuration Diagnostic
 *
 * Relevanssi stopwords not configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.402.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Relevanssi Stopwords Configuration Diagnostic Class
 *
 * @since 1.402.0000
 */
class Diagnostic_RelevanssiStopwordsConfiguration extends Diagnostic_Base {

	protected static $slug = 'relevanssi-stopwords-configuration';
	protected static $title = 'Relevanssi Stopwords Configuration';
	protected static $description = 'Relevanssi stopwords not configured';
	protected static $family = 'functionality';

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
				'severity'    => 35,
				'threat_level' => 35,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/relevanssi-stopwords-configuration',
			);
		}
		
		return null;
	}
}
