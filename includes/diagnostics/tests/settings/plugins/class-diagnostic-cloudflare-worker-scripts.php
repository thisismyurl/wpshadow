<?php
/**
 * Cloudflare Worker Scripts Diagnostic
 *
 * Cloudflare Worker Scripts needs attention.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.993.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cloudflare Worker Scripts Diagnostic Class
 *
 * @since 1.993.0000
 */
class Diagnostic_CloudflareWorkerScripts extends Diagnostic_Base {

	protected static $slug = 'cloudflare-worker-scripts';
	protected static $title = 'Cloudflare Worker Scripts';
	protected static $description = 'Cloudflare Worker Scripts needs attention';
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
				'kb_link'     => 'https://wpshadow.com/kb/cloudflare-worker-scripts',
			);
		}
		
		return null;
	}
}
