<?php
/**
 * Cloudflare Page Rules Limits Diagnostic
 *
 * Cloudflare Page Rules Limits needs attention.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.991.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cloudflare Page Rules Limits Diagnostic Class
 *
 * @since 1.991.0000
 */
class Diagnostic_CloudflarePageRulesLimits extends Diagnostic_Base {

	protected static $slug = 'cloudflare-page-rules-limits';
	protected static $title = 'Cloudflare Page Rules Limits';
	protected static $description = 'Cloudflare Page Rules Limits needs attention';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'CLOUDFLARE_VERSION' ) ) {
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
				'severity'    => self::calculate_severity( 50 ),
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/cloudflare-page-rules-limits',
			);
		}
		
		return null;
	}
}
