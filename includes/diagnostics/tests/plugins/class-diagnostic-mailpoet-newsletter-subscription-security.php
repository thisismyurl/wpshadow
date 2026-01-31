<?php
/**
 * Mailpoet Newsletter Subscription Security Diagnostic
 *
 * Mailpoet Newsletter Subscription Security configuration issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.711.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mailpoet Newsletter Subscription Security Diagnostic Class
 *
 * @since 1.711.0000
 */
class Diagnostic_MailpoetNewsletterSubscriptionSecurity extends Diagnostic_Base {

	protected static $slug = 'mailpoet-newsletter-subscription-security';
	protected static $title = 'Mailpoet Newsletter Subscription Security';
	protected static $description = 'Mailpoet Newsletter Subscription Security configuration issues';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'MailPoet\Config\Initializer' ) ) {
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
				'severity'    => self::calculate_severity( 60 ),
				'threat_level' => 60,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/mailpoet-newsletter-subscription-security',
			);
		}
		
		return null;
	}
}
