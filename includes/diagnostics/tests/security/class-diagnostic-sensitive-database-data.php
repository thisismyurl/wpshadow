<?php
/**
 * Sensitive Data in Database Diagnostic
 *
 * Checks for plaintext passwords, credit card data, or unencrypted API keys
 * stored in the WordPress database.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sensitive Data in Database Diagnostic Class
 *
 * Scans for insecure storage of sensitive information and provides
 * recommendations for proper encryption and data handling.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Sensitive_Database_Data extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'protects_sensitive_database_data';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Sensitive Data in Database';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Verifies sensitive data is not stored in plaintext in the database';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$stats    = array();
		$issues   = array();
		$warnings = array();

		$total_points  = 100;
		$earned_points = 0;

		// Check for encryption/security plugins (30 points).
		$encryption_plugins = array(
			'wp-encrypt/wp-encrypt.php'                     => 'WP Encrypt',
			'wordfence/wordfence.php'                       => 'Wordfence Security',
			'better-wp-security/better-wp-security.php'     => 'iThemes Security Pro',
			'all-in-one-wp-security-and-firewall/wp-security.php' => 'All In One WP Security',
			'sucuri-scanner/sucuri.php'                     => 'Sucuri Security',
		);

		$active_encryption = array();
		foreach ( $encryption_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_encryption[] = $plugin_name;
				$earned_points      += 10; // Up to 30 points.
			}
		}

		if ( count( $active_encryption ) > 0 ) {
			$stats['encryption_plugins'] = implode( ', ', $active_encryption );
		} else {
			$issues[] = 'No dedicated encryption or security plugins detected';
		}

		// Check for plaintext password patterns in usermeta (30 points penalty if found).
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$suspicious_meta_keys = $wpdb->get_results(
			"SELECT DISTINCT meta_key 
			FROM {$wpdb->usermeta} 
			WHERE meta_key LIKE '%password%' 
			OR meta_key LIKE '%pass%' 
			OR meta_key LIKE '%pwd%'
			LIMIT 20"
		);

		$suspicious_password_keys = array();
		if ( ! empty( $suspicious_meta_keys ) ) {
			foreach ( $suspicious_meta_keys as $row ) {
				// Exclude known safe WordPress keys.
				if ( ! in_array(
					$row->meta_key,
					array(
						'default_password_nag',
						'show_admin_bar_front',
						'use_ssl',
						'session_tokens',
					),
					true
				) ) {
					$suspicious_password_keys[] = $row->meta_key;
				}
			}
		}

		if ( count( $suspicious_password_keys ) > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: Number of suspicious keys */
				_n(
					'%d suspicious meta key found that may contain passwords',
					'%d suspicious meta keys found that may contain passwords',
					count( $suspicious_password_keys ),
					'wpshadow'
				),
				count( $suspicious_password_keys )
			);
			$stats['suspicious_password_keys'] = array_slice( $suspicious_password_keys, 0, 5 );
		} else {
			$earned_points += 30;
		}

		// Check for credit card patterns in options (25 points penalty if found).
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$cc_options = $wpdb->get_results(
			"SELECT option_name 
			FROM {$wpdb->options} 
			WHERE option_name LIKE '%card%' 
			OR option_name LIKE '%credit%' 
			OR option_name LIKE '%payment%'
			LIMIT 20"
		);

		$suspicious_cc_keys = array();
		if ( ! empty( $cc_options ) ) {
			foreach ( $cc_options as $row ) {
				// Exclude known safe keys.
				if ( ! in_array(
					$row->option_name,
					array(
						'woocommerce_credit_cards',
						'payment_gateway_settings',
					),
					true
				) && strpos( $row->option_name, '_transient' ) === false ) {
					$suspicious_cc_keys[] = $row->option_name;
				}
			}
		}

		if ( count( $suspicious_cc_keys ) > 0 ) {
			$warnings[] = sprintf(
				/* translators: %d: Number of suspicious keys */
				_n(
					'%d option key found that may store payment data',
					'%d option keys found that may store payment data',
					count( $suspicious_cc_keys ),
					'wpshadow'
				),
				count( $suspicious_cc_keys )
			);
			$stats['suspicious_payment_keys'] = array_slice( $suspicious_cc_keys, 0, 5 );
		} else {
			$earned_points += 25;
		}

		// Check for API key patterns in options (15 points).
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$api_options = $wpdb->get_results(
			"SELECT option_name, option_value 
			FROM {$wpdb->options} 
			WHERE option_name LIKE '%api_key%' 
			OR option_name LIKE '%apikey%' 
			OR option_name LIKE '%secret%'
			LIMIT 50"
		);

		$plaintext_api_keys = 0;
		if ( ! empty( $api_options ) ) {
			foreach ( $api_options as $row ) {
				$value = maybe_unserialize( $row->option_value );
				// Check if value looks like plaintext (no encryption markers).
				if ( is_string( $value ) && strlen( $value ) > 10 && strlen( $value ) < 200 ) {
					// If it doesn't look encrypted (no special chars pattern).
					if ( ! preg_match( '/^[A-Za-z0-9+\/=]+$/', $value ) || strlen( $value ) < 32 ) {
						$plaintext_api_keys++;
					}
				}
			}
		}

		if ( $plaintext_api_keys > 0 ) {
			$warnings[] = sprintf(
				/* translators: %d: Number of API keys */
				_n(
					'%d API key may be stored in plaintext',
					'%d API keys may be stored in plaintext',
					$plaintext_api_keys,
					'wpshadow'
				),
				$plaintext_api_keys
			);
			$stats['plaintext_api_keys'] = $plaintext_api_keys;
		} else {
			$earned_points += 15;
		}

		// Calculate score percentage.
		$score      = ( $earned_points / $total_points ) * 100;
		$score_text = round( $score ) . '%';

		$stats['total_points']  = $total_points;
		$stats['earned_points'] = $earned_points;
		$stats['score']         = $score_text;

		// Return finding if score is below 60% (this is critical security).
		if ( $score < 60 ) {
			$severity     = $score < 40 ? 'high' : 'medium';
			$threat_level = $score < 40 ? 90 : 70;

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: Score percentage */
					__( 'Your database security scored %s. Sensitive data like passwords, credit cards, or API keys may be stored without proper encryption. This puts your users and business at serious risk if the database is compromised.', 'wpshadow' ),
					$score_text
				),
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/sensitive-data-in-database',
				'context'      => array(
					'stats'    => $stats,
					'issues'   => $issues,
					'warnings' => $warnings,
				),
			);
		}

		return null;
	}
}
