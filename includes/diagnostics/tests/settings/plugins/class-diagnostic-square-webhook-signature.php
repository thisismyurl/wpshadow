<?php
/**
 * Square Webhook Signature Diagnostic
 *
 * Square Webhook Signature vulnerability detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1404.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Square Webhook Signature Diagnostic Class
 *
 * @since 1.1404.0000
 */
class Diagnostic_SquareWebhookSignature extends Diagnostic_Base {

	protected static $slug = 'square-webhook-signature';
	protected static $title = 'Square Webhook Signature';
	protected static $description = 'Square Webhook Signature vulnerability detected';
	protected static $family = 'security';

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
				'severity'    => self::calculate_severity( 75 ),
				'threat_level' => 75,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/square-webhook-signature',
			);
		}
		
		return null;
	}
}
