<?php
/**
 * Mailchimp List Configuration Diagnostic
 *
 * Mailchimp lists not properly configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.224.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mailchimp List Configuration Diagnostic Class
 *
 * @since 1.224.0000
 */
class Diagnostic_MailchimpListConfiguration extends Diagnostic_Base {

	protected static $slug = 'mailchimp-list-configuration';
	protected static $title = 'Mailchimp List Configuration';
	protected static $description = 'Mailchimp lists not properly configured';
	protected static $family = 'functionality';

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
		$has_issue = !empty($issues);
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 45 ),
				'threat_level' => 45,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/mailchimp-list-configuration',
			);
		}
		

		// Feature availability checks
		if ( ! function_exists( 'add_action' ) ) {
			$issues[] = __( 'WordPress hooks unavailable', 'wpshadow' );
		}
		if ( empty( $GLOBALS['wpdb'] ) ) {
			$issues[] = __( 'Database not initialized', 'wpshadow' );
		}
		// Verify core functionality
		if ( ! function_exists( 'get_post' ) ) {
			$issues[] = __( 'Post functionality not available', 'wpshadow' );
		}
		return null;
	}
}
