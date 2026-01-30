<?php
/**
 * Caldera Forms File Uploads Diagnostic
 *
 * Caldera Forms file uploads insecure.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.472.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Caldera Forms File Uploads Diagnostic Class
 *
 * @since 1.472.0000
 */
class Diagnostic_CalderaFormsFileUploads extends Diagnostic_Base {

	protected static $slug = 'caldera-forms-file-uploads';
	protected static $title = 'Caldera Forms File Uploads';
	protected static $description = 'Caldera Forms file uploads insecure';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'Caldera_Forms' ) ) {
			return null;
		}
		
		$issues = array();
		$threat_level = 0;

		// Get Caldera Forms upload directory
		$upload_dir = wp_upload_dir();
		$cf_upload_path = $upload_dir['basedir'] . '/caldera_forms';

		if ( is_dir( $cf_upload_path ) ) {
			// Check directory permissions
			$perms = fileperms( $cf_upload_path );
			if ( ( $perms & 0777 ) > 0755 ) {
				$issues[] = 'upload_directory_too_permissive';
				$threat_level += 20;
			}

			// Check for .htaccess protection
			$htaccess = $cf_upload_path . '/.htaccess';
			if ( ! file_exists( $htaccess ) ) {
				$issues[] = 'missing_htaccess_protection';
				$threat_level += 25;
			}

			// Check for index.php
			$index_file = $cf_upload_path . '/index.php';
			if ( ! file_exists( $index_file ) ) {
				$issues[] = 'directory_listing_possible';
				$threat_level += 15;
			}
		}

		// Check form file field configurations
		global $wpdb;
		$forms = $wpdb->get_results(
			"SELECT * FROM {$wpdb->prefix}cf_forms WHERE is_active = 1"
		);

		if ( $forms ) {
			foreach ( $forms as $form ) {
				$form_fields = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT * FROM {$wpdb->prefix}cf_form_fields WHERE form_id = %d AND type = %s",
						$form->ID,
						'file'
					)
				);
				if ( $form_fields ) {
					$issues[] = 'file_uploads_enabled';
					$threat_level += 15;
				}
			}
		}

		// Check Caldera Forms settings
		$cf_settings = get_option( 'caldera_forms_settings', array() );
		$max_upload_size = isset( $cf_settings['max_upload_size'] ) ? $cf_settings['max_upload_size'] : 0;
		if ( $max_upload_size > 10485760 ) { // 10MB.
			$issues[] = 'max_upload_size_too_large';
			$threat_level += 10;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of file upload security issues */
				__( 'Caldera Forms file uploads are insecure: %s. This exposes your site to malicious file uploads and data breaches.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => $description,
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/caldera-forms-file-uploads',
			);
		}
		
		return null;
	}
}
