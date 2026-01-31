<?php
/**
 * Mailchimp Double Opt-in Diagnostic
 *
 * Mailchimp double opt-in not enabled for GDPR.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.226.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mailchimp Double Opt-in Diagnostic Class
 *
 * @since 1.226.0000
 */
class Diagnostic_MailchimpDoubleOptin extends Diagnostic_Base {

	protected static $slug = 'mailchimp-double-optin';
	protected static $title = 'Mailchimp Double Opt-in';
	protected static $description = 'Mailchimp double opt-in not enabled for GDPR';
	protected static $family = 'security';

	public static function check() {
		if ( ! function_exists( 'mc4wp' ) ) {
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
				'severity'    => self::calculate_severity( 50 ),
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/mailchimp-double-optin',
			);
		}
		
		return null;
	}
}
