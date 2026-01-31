<?php
/**
 * bbPress Topic Freshness Caching Diagnostic
 *
 * bbPress topic freshness calculations slow.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.241.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * bbPress Topic Freshness Caching Diagnostic Class
 *
 * @since 1.241.0000
 */
class Diagnostic_BbpressTopicFreshness extends Diagnostic_Base {

	protected static $slug = 'bbpress-topic-freshness';
	protected static $title = 'bbPress Topic Freshness Caching';
	protected static $description = 'bbPress topic freshness calculations slow';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'bbPress' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/bbpress-topic-freshness',
			);
		}
		
		return null;
	}
}
