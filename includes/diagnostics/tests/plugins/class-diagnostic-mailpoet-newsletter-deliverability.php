<?php
/**
 * Mailpoet Newsletter Deliverability Diagnostic
 *
 * Mailpoet Newsletter Deliverability configuration issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.709.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mailpoet Newsletter Deliverability Diagnostic Class
 *
 * @since 1.709.0000
 */
class Diagnostic_MailpoetNewsletterDeliverability extends Diagnostic_Base {

	protected static $slug = 'mailpoet-newsletter-deliverability';
	protected static $title = 'Mailpoet Newsletter Deliverability';
	protected static $description = 'Mailpoet Newsletter Deliverability configuration issues';
	protected static $family = 'functionality';

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
				'severity'    => self::calculate_severity( 50 ),
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/mailpoet-newsletter-deliverability',
			);
		}
		
		return null;
	}
}
