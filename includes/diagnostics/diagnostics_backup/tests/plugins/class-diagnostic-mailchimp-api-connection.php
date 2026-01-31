<?php
/**
 * Mailchimp API Connection Diagnostic
 *
 * Mailchimp API key not configured or connection failing.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.223.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mailchimp API Connection Diagnostic Class
 *
 * @since 1.223.0000
 */
class Diagnostic_MailchimpApiConnection extends Diagnostic_Base {

	protected static $slug = 'mailchimp-api-connection';
	protected static $title = 'Mailchimp API Connection';
	protected static $description = 'Mailchimp API key not configured or connection failing';
	protected static $family = 'security';

	public static function check() {
		if ( ! function_exists( 'mc4wp' ) ) {
			return null;
		}
		
		$issues = array();
		$threat_level = 0;

		// Get Mailchimp API key
		$api_key = mc4wp()->get_api()->get_api_key();
		if ( empty( $api_key ) ) {
			$issues[] = 'api_key_not_configured';
			$threat_level += 25;
			return $this->build_finding( $issues, $threat_level );
		}

		// Validate API key format
		if ( ! preg_match( '/^[a-f0-9]{32}-us\d+$/', $api_key ) ) {
			$issues[] = 'invalid_api_key_format';
			$threat_level += 20;
		}

		// Test API connection
		$api = mc4wp()->get_api();
		if ( method_exists( $api, 'is_connected' ) && ! $api->is_connected() ) {
			$issues[] = 'api_connection_failed';
			$threat_level += 25;
		}

		// Check for configured lists
		$lists = get_option( 'mc4wp_mailchimp_list_ids', array() );
		if ( empty( $lists ) ) {
			$issues[] = 'no_lists_configured';
			$threat_level += 15;
		}

		// Check error log
		$error_log = get_option( 'mc4wp_debug_log', array() );
		if ( ! empty( $error_log ) && count( $error_log ) > 10 ) {
			$issues[] = 'frequent_api_errors';
			$threat_level += 15;
		}

		// Check last sync time
		$last_sync = get_option( 'mc4wp_last_sync', 0 );
		if ( $last_sync > 0 && ( time() - $last_sync ) > 604800 ) {
			$issues[] = 'list_not_syncing';
			$threat_level += 10;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of API connection issues */
				__( 'Mailchimp API connection has problems: %s. This prevents email list synchronization and subscriber management.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => $description,
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/mailchimp-api-connection',
			);
		}
		
		return null;
	}
}
