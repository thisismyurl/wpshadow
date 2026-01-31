<?php
/**
 * Redirection Plugin Redirect Performance Diagnostic
 *
 * Redirection Plugin Redirect Performance issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1419.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Redirection Plugin Redirect Performance Diagnostic Class
 *
 * @since 1.1419.0000
 */
class Diagnostic_RedirectionPluginRedirectPerformance extends Diagnostic_Base {

	protected static $slug = 'redirection-plugin-redirect-performance';
	protected static $title = 'Redirection Plugin Redirect Performance';
	protected static $description = 'Redirection Plugin Redirect Performance issue found';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'REDIRECTION_VERSION' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/redirection-plugin-redirect-performance',
			);
		}
		
		return null;
	}
}
