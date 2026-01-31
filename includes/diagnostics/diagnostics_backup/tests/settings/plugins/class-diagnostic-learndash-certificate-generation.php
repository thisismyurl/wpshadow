<?php
/**
 * LearnDash Certificate Generation Diagnostic
 *
 * LearnDash certificates not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.361.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * LearnDash Certificate Generation Diagnostic Class
 *
 * @since 1.361.0000
 */
class Diagnostic_LearndashCertificateGeneration extends Diagnostic_Base {

	protected static $slug = 'learndash-certificate-generation';
	protected static $title = 'LearnDash Certificate Generation';
	protected static $description = 'LearnDash certificates not optimized';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'LEARNDASH_VERSION' ) ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		$threat_level = 0;

		// Check PDF library availability
		$pdf_builder = get_option( 'learndash_settings_certificates_pdf_engine', 'TCPDF' );
		if ( $pdf_builder === 'TCPDF' && ! class_exists( 'TCPDF' ) ) {
			$issues[] = 'pdf_library_missing';
			$threat_level += 15;
		}

		// Check certificate directory permissions
		$upload_dir = wp_upload_dir();
		$cert_dir = $upload_dir['basedir'] . '/learndash/certificates';
		if ( file_exists( $cert_dir ) && ! is_writable( $cert_dir ) ) {
			$issues[] = 'certificate_directory_not_writable';
			$threat_level += 10;
		}

		// Check for certificate templates
		$templates = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} 
				 WHERE post_type = %s AND post_status = %s",
				'sfwd-certificates',
				'publish'
			)
		);
		if ( $templates === 0 ) {
			$issues[] = 'no_certificate_templates';
			$threat_level += 10;
		}

		// Check certificate caching
		$cache_enabled = get_option( 'learndash_settings_certificates_cache_enabled', false );
		if ( ! $cache_enabled ) {
			$issues[] = 'certificate_caching_disabled';
			$threat_level += 10;
		}

		// Check for large certificate images
		$large_images = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->postmeta} pm
				 INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
				 WHERE p.post_type = %s 
				 AND pm.meta_key = %s
				 AND CAST(pm.meta_value AS UNSIGNED) > %d",
				'sfwd-certificates',
				'_thumbnail_file_size',
				500000
			)
		);
		if ( $large_images > 0 ) {
			$issues[] = 'large_certificate_images';
			$threat_level += 10;
		}

		// Check generation logging
		$logging_enabled = get_option( 'learndash_settings_certificates_logging', false );
		if ( ! $logging_enabled ) {
			$issues[] = 'certificate_logging_disabled';
			$threat_level += 5;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of certificate generation issues */
				__( 'LearnDash certificate generation has problems: %s. This can cause slow certificate downloads, failed generations, and poor user experience.', 'wpshadow' ),
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
				'kb_link'     => 'https://wpshadow.com/kb/learndash-certificate-generation',
			);
		}
		
		return null;
	}
}
