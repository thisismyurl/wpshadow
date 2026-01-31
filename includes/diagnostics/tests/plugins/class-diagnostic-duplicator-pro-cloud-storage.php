<?php
/**
 * Duplicator Pro Cloud Storage Diagnostic
 *
 * Duplicator cloud credentials insecure.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.398.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Duplicator Pro Cloud Storage Diagnostic Class
 *
 * @since 1.398.0000
 */
class Diagnostic_DuplicatorProCloudStorage extends Diagnostic_Base {

	protected static $slug = 'duplicator-pro-cloud-storage';
	protected static $title = 'Duplicator Pro Cloud Storage';
	protected static $description = 'Duplicator cloud credentials insecure';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'DUP_PRO_Package' ) || class_exists( 'DUP_Package' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: SSL for cloud sync.
		if ( ! is_ssl() ) {
			$issues[] = 'cloud sync without HTTPS';
		}

		// Check 2: Credentials encryption.
		$encrypt = get_option( 'duplicator_pro_encrypt_credentials', '1' );
		if ( '0' === $encrypt ) {
			$issues[] = 'credentials not encrypted';
		}

		// Check 3: Cloud storage access.
		$storage = get_option( 'duplicator_pro_storage', array() );
		if ( ! empty( $storage ) && defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			$issues[] = 'cloud credentials visible with debug on';
		}

		// Check 4: Backup retention.
		$retention = get_option( 'duplicator_pro_retention_count', 0 );
		if ( 0 === $retention ) {
			$issues[] = 'no retention policy';
		}

		// Check 5: Transfer encryption.
		$transfer_encrypt = get_option( 'duplicator_pro_transfer_encryption', '1' );
		if ( '0' === $transfer_encrypt ) {
			$issues[] = 'transfer encryption disabled';
		}

		// Check 6: Storage test.
		$test_storage = get_option( 'duplicator_pro_test_storage', '1' );
		if ( '0' === $test_storage ) {
			$issues[] = 'storage testing disabled';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 85, 70 + ( count( $issues ) * 3 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Duplicator Pro security issues: ' . implode( ', ', $issues ),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/duplicator-pro-cloud-storage',
			);
		}

		return null;
	}
}
