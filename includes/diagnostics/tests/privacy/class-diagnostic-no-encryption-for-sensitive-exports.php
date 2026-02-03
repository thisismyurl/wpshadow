<?php
/**
 * No Encryption for Sensitive Exports Diagnostic
 *
 * Tests whether exports containing sensitive data (personal info, user data) are encrypted.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Privacy
 * @since      1.2034.1500
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_No_Encryption_For_Sensitive_Exports Class
 *
 * Verifies that sensitive exports are encrypted.
 *
 * @since 1.2034.1500
 */
class Diagnostic_No_Encryption_For_Sensitive_Exports extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-encryption-for-sensitive-exports';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Export Data Encryption';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if sensitive export files are encrypted to protect personal data';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2034.1500
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// 1. Check if personal data exports are encrypted.
		$upload_dir = wp_upload_dir();
		$export_dir = $upload_dir['basedir'] . '/wp-personal-data-exports/';
		
		if ( file_exists( $export_dir ) && is_dir( $export_dir ) ) {
			$export_files = glob( $export_dir . '*.zip' );
			
			if ( ! empty( $export_files ) ) {
				// Sample a file to check encryption.
				$sample_file = $export_files[0];
				
				// WordPress exports are ZIP files - check if password-protected.
				$zip = new \ZipArchive();
				if ( $zip->open( $sample_file ) === true ) {
					// If encryption is set, zip->getStatusString() would indicate it.
					// Basic check: WordPress doesn't encrypt exports by default.
					$issues[] = __( 'Personal data exports are not encrypted - data exposed if files are accessed', 'wpshadow' );
					$zip->close();
				}
			}
		}

		// 2. Check SSL/TLS for download.
		if ( ! is_ssl() ) {
			$issues[] = __( 'Site not using HTTPS - export downloads transmitted in plain text', 'wpshadow' );
		}

		// 3. Check for encryption libraries.
		$has_encryption = false;
		
		if ( extension_loaded( 'openssl' ) ) {
			$has_encryption = true;
		} elseif ( extension_loaded( 'mcrypt' ) ) {
			$has_encryption = true;
		}

		if ( ! $has_encryption ) {
			$issues[] = __( 'No encryption extension available (OpenSSL, mcrypt) - cannot encrypt exports', 'wpshadow' );
		}

		// 4. Check for encryption filter hooks.
		$encryption_filters = array(
			'wp_privacy_personal_data_export_file',
			'wp_privacy_personal_data_export_page',
		);

		$has_custom_encryption = false;
		foreach ( $encryption_filters as $filter ) {
			if ( has_filter( $filter ) ) {
				$has_custom_encryption = true;
				break;
			}
		}

		if ( ! $has_custom_encryption ) {
			$issues[] = __( 'No custom encryption filters detected - exports use default unencrypted format', 'wpshadow' );
		}

		// 5. Check WooCommerce exports if active.
		if ( class_exists( 'WooCommerce' ) ) {
			$wc_export_dir = $upload_dir['basedir'] . '/woocommerce_uploads/';
			
			if ( file_exists( $wc_export_dir ) && is_dir( $wc_export_dir ) ) {
				$wc_files = glob( $wc_export_dir . '*.csv' );
				
				if ( ! empty( $wc_files ) ) {
					$issues[] = __( 'WooCommerce exports are CSV (plain text) - sensitive order data not encrypted', 'wpshadow' );
				}
			}
		}

		// 6. Check for password protection options.
		$export_password = get_option( 'wp_privacy_export_password', false );
		
		if ( false === $export_password ) {
			$issues[] = __( 'No password protection configured for exports', 'wpshadow' );
		}

		// 7. Check GDPR export plugin features.
		$gdpr_plugins = array(
			'gdpr-data-request-form/gdpr-data-request-form.php'     => 'GDPR Data Request Form',
			'wp-gdpr-compliance/wp-gdpr-compliance.php'             => 'WP GDPR Compliance',
			'cookie-law-info/cookie-law-info.php'                   => 'GDPR Cookie Consent',
		);

		$active_gdpr_plugins = array();
		foreach ( $gdpr_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_gdpr_plugins[] = $plugin_name;
			}
		}

		if ( ! empty( $active_gdpr_plugins ) ) {
			// Verify they provide encryption.
			$issues[] = sprintf(
				/* translators: %s: comma-separated list of plugins */
				__( 'GDPR plugins active (%s) - verify they provide export encryption', 'wpshadow' ),
				implode( ', ', $active_gdpr_plugins )
			);
		}

		// 8. Check for encryption key management.
		if ( ! defined( 'AUTH_KEY' ) || empty( AUTH_KEY ) ) {
			$issues[] = __( 'AUTH_KEY not defined - cannot use WordPress encryption functions', 'wpshadow' );
		}

		// 9. Test encryption availability.
		if ( $has_encryption ) {
			$test_data = 'sensitive information';
			$encrypted = false;
			
			try {
				$iv     = openssl_random_pseudo_bytes( 16 );
				$key    = defined( 'AUTH_KEY' ) ? AUTH_KEY : 'test-key';
				$result = openssl_encrypt( $test_data, 'AES-256-CBC', $key, 0, $iv );
				
				if ( false !== $result ) {
					$encrypted = true;
				}
			} catch ( \Exception $e ) {
				$issues[] = __( 'OpenSSL encryption test failed - check server configuration', 'wpshadow' );
			}

			if ( ! $encrypted && $has_encryption ) {
				$issues[] = __( 'Encryption library present but test failed - configuration issue', 'wpshadow' );
			}
		}

		// 10. Check data minimization.
		global $wpdb;
		$request_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s",
				'user_request'
			)
		);

		if ( (int) $request_count > 0 && ! empty( $issues ) ) {
			$issues[] = __( 'Export requests exist but encryption not configured - GDPR data security requirement not met', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( 'Export encryption issues: %s', 'wpshadow' ),
				implode( '; ', $issues )
			),
			'severity'     => 'high',
			'threat_level' => 85,
			'auto_fixable' => true,
			'kb_link'      => 'https://wpshadow.com/kb/export-encryption',
			'details'      => array(
				'issues'          => $issues,
				'has_encryption'  => $has_encryption,
				'is_ssl'          => is_ssl(),
			),
		);
	}
}
