<?php
/**
 * Cloudflare Ssl Mode Diagnostic
 *
 * Cloudflare Ssl Mode needs attention.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.990.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cloudflare Ssl Mode Diagnostic Class
 *
 * @since 1.990.0000
 */
class Diagnostic_CloudflareSslMode extends Diagnostic_Base {

	protected static $slug = 'cloudflare-ssl-mode';
	protected static $title = 'Cloudflare Ssl Mode';
	protected static $description = 'Cloudflare Ssl Mode needs attention';
	protected static $family = 'security';

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
		$has_issue = !empty($issues)
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 70 ),
				'threat_level' => 70,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/cloudflare-ssl-mode',
			);
		}
		
		return null;
	}
}
