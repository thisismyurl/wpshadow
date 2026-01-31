<?php
/**
 * SearchWP Minimum Word Length Diagnostic
 *
 * SearchWP word length settings wrong.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.410.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SearchWP Minimum Word Length Diagnostic Class
 *
 * @since 1.410.0000
 */
class Diagnostic_SearchwpMinimumWordLength extends Diagnostic_Base {

	protected static $slug = 'searchwp-minimum-word-length';
	protected static $title = 'SearchWP Minimum Word Length';
	protected static $description = 'SearchWP word length settings wrong';
	protected static $family = 'functionality';

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
				'severity'    => self::calculate_severity( 35 ),
				'threat_level' => 35,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/searchwp-minimum-word-length',
			);
		}
		
		return null;
	}
}
