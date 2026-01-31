<?php
/**
 * Newsletter Plugin Database Optimization Diagnostic
 *
 * Newsletter Plugin Database Optimization configuration issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.720.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Newsletter Plugin Database Optimization Diagnostic Class
 *
 * @since 1.720.0000
 */
class Diagnostic_NewsletterPluginDatabaseOptimization extends Diagnostic_Base {

	protected static $slug = 'newsletter-plugin-database-optimization';
	protected static $title = 'Newsletter Plugin Database Optimization';
	protected static $description = 'Newsletter Plugin Database Optimization configuration issues';
	protected static $family = 'functionality';

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
				'severity'    => self::calculate_severity( 50 ),
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/newsletter-plugin-database-optimization',
			);
		}
		
		return null;
	}
}
