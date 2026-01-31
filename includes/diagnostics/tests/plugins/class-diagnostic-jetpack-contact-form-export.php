<?php
/**
 * Jetpack Contact Form Export Diagnostic
 *
 * Jetpack Contact Form Export issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1219.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Jetpack Contact Form Export Diagnostic Class
 *
 * @since 1.1219.0000
 */
class Diagnostic_JetpackContactFormExport extends Diagnostic_Base {

	protected static $slug = 'jetpack-contact-form-export';
	protected static $title = 'Jetpack Contact Form Export';
	protected static $description = 'Jetpack Contact Form Export issue found';
	protected static $family = 'functionality';

	public static function check() {
		// Check for Jetpack
		if ( ! class_exists( 'Jetpack' ) ) {
			return null;
		}
		
		// Check if contact forms module active
		if ( ! Jetpack::is_module_active( 'contact-form' ) ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Submission storage
		$feedback_table = $wpdb->prefix . 'jetpack_contact_form_submissions';
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $feedback_table ) ) !== $feedback_table ) {
			$issues[] = __( 'Submission storage not configured (data loss)', 'wpshadow' );
		} else {
			// Check submission count
			$submission_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$feedback_table}" );
			
			if ( $submission_count > 10000 ) {
				$issues[] = sprintf( __( '%s submissions stored (database bloat)', 'wpshadow' ), number_format_i18n( $submission_count ) );
			}
		}
		
		// Check 2: Export functionality
		$export_enabled = get_option( 'jetpack_contact_form_export', 'yes' );
		if ( 'no' === $export_enabled ) {
			$issues[] = __( 'Export disabled (GDPR compliance issue)', 'wpshadow' );
		}
		
		// Check 3: Retention policy
		$retention_days = get_option( 'jetpack_contact_form_retention', 0 );
		if ( $retention_days === 0 ) {
			$issues[] = __( 'No retention policy (indefinite storage)', 'wpshadow' );
		}
		
		// Check 4: Attachment storage
		$store_attachments = get_option( 'jetpack_contact_form_store_attachments', 'yes' );
		if ( 'yes' === $store_attachments ) {
			$upload_dir = wp_upload_dir();
			$attachment_dir = $upload_dir['basedir'] . '/jetpack-contact-form/';
			
			if ( is_dir( $attachment_dir ) ) {
				$size = 0;
				$files = new \RecursiveIteratorIterator(
					new \RecursiveDirectoryIterator( $attachment_dir, \RecursiveDirectoryIterator::SKIP_DOTS )
				);
				
				foreach ( $files as $file ) {
					if ( $file->isFile() ) {
						$size += $file->getSize();
					}
				}
				
				if ( $size > ( 500 * 1024 * 1024 ) ) { // 500MB
					$issues[] = sprintf( __( 'Form attachments: %s (storage cost)', 'wpshadow' ), size_format( $size ) );
				}
			}
		}
		
		// Check 5: Spam protection
		$akismet_enabled = get_option( 'jetpack_contact_form_akismet', 'no' );
		if ( 'no' === $akismet_enabled && class_exists( 'Akismet' ) ) {
			$issues[] = __( 'Akismet available but not enabled for forms', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 50;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 62;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 56;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of contact form export issues */
				__( 'Jetpack contact forms have %d export issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/jetpack-contact-form-export',
		);
	}
}
