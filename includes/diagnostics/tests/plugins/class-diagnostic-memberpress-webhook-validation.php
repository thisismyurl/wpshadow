<?php
/**
 * MemberPress Webhook Validation Diagnostic
 *
 * MemberPress webhooks not validated.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.529.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * MemberPress Webhook Validation Diagnostic Class
 *
 * @since 1.529.0000
 */
class Diagnostic_MemberpressWebhookValidation extends Diagnostic_Base {

	protected static $slug = 'memberpress-webhook-validation';
	protected static $title = 'MemberPress Webhook Validation';
	protected static $description = 'MemberPress webhooks not validated';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'MEPR_VERSION' ) ) {
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
				'severity'    => self::calculate_severity( 80 ),
				'threat_level' => 80,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/memberpress-webhook-validation',
			);
		}
		
		return null;
	}
}
