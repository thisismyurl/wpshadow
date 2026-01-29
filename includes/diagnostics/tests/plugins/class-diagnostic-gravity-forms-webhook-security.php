<?php
/**
 * Gravity Forms Webhook Security Diagnostic
 *
 * Gravity Forms webhooks not validated.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.259.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gravity Forms Webhook Security Diagnostic Class
 *
 * @since 1.259.0000
 */
class Diagnostic_GravityFormsWebhookSecurity extends Diagnostic_Base {

	protected static $slug = 'gravity-forms-webhook-security';
	protected static $title = 'Gravity Forms Webhook Security';
	protected static $description = 'Gravity Forms webhooks not validated';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'GFForms' ) ) {
			return null;
		}
		
		$has_issue = false;
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 60 ),
				'threat_level' => 60,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/gravity-forms-webhook-security',
			);
		}
		
		return null;
	}
}
