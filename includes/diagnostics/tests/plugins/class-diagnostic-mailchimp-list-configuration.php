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

		// Check 1: API key
		$api_key = get_option( 'mc4wp_api_key', '' );
		if ( empty( $api_key ) ) {
			$issues[] = __( 'No API key (not connected)', 'wpshadow' );
		}

		// Check 2: Default list
		$default_list = get_option( 'mc4wp_default_list', '' );
		if ( empty( $default_list ) ) {
			$issues[] = __( 'No default list (forms may fail)', 'wpshadow' );
		}

		// Check 3: Double opt-in
		$double_optin = get_option( 'mc4wp_double_optin', 'yes' );
		if ( 'no' === $double_optin ) {
			$issues[] = __( 'Single opt-in (spam/GDPR concern)', 'wpshadow' );
		}

		// Check 4: List segmentation
		$segments = get_option( 'mc4wp_list_segments', array() );
		if ( empty( $segments ) ) {
			$issues[] = __( 'No list segmentation (poor targeting)', 'wpshadow' );
		}

		// Check 5: Welcome automation
		$welcome = get_option( 'mc4wp_welcome_email', 'no' );
		if ( 'no' === $welcome ) {
			$issues[] = __( 'No welcome automation (missed engagement)', 'wpshadow' );
		}

		// Check 6: Sync frequency
		$sync = get_option( 'mc4wp_sync_frequency', 'manual' );
		if ( 'manual' === $sync ) {
			$issues[] = __( 'Manual sync only (outdated lists)', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		$threat_level = 45;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 57;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 51;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				__( 'Mailchimp has %d list configuration issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/mailchimp-list-configuration',
		);
	}
}
