<?php
/**
 * Gravity Forms File Upload Diagnostic
 *
 * Gravity Forms file upload vulnerable.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.256.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gravity Forms File Upload Diagnostic Class
 *
 * @since 1.256.0000
 */
class Diagnostic_GravityFormsFileUpload extends Diagnostic_Base {

	protected static $slug = 'gravity-forms-file-upload';
	protected static $title = 'Gravity Forms File Upload';
	protected static $description = 'Gravity Forms file upload vulnerable';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'GFForms' ) ) {
			return null;
		}
		
		$issues = array();
		$threat_level = 0;

		// Get Gravity Forms settings
		$settings = get_option( 'rg_gforms_enable_html5', false );

		// Check upload directory
		$upload_dir = wp_upload_dir();
		$gf_upload_path = $upload_dir['basedir'] . '/gravity_forms';
		if ( is_dir( $gf_upload_path ) ) {
			// Check directory permissions
			$perms = fileperms( $gf_upload_path );
			if ( ( $perms & 0777 ) > 0755 ) {
				$issues[] = 'upload_directory_too_permissive';
				$threat_level += 20;
			}

			// Check for .htaccess protection
			$htaccess_file = $gf_upload_path . '/.htaccess';
			if ( ! file_exists( $htaccess_file ) ) {
				$issues[] = 'missing_htaccess_protection';
				$threat_level += 25;
			}
		}

		// Check file upload settings in forms
		global $wpdb;
		$forms = $wpdb->get_results( "SELECT id, meta_value FROM {$wpdb->prefix}gf_form_meta" );
		if ( $forms ) {
			foreach ( $forms as $form ) {
				$form_meta = maybe_unserialize( $form->meta_value );
				if ( isset( $form_meta['fields'] ) ) {
					foreach ( $form_meta['fields'] as $field ) {
						if ( isset( $field['type'] ) && $field['type'] === 'fileupload' ) {
							// Check file type restrictions
							if ( empty( $field['allowedExtensions'] ) ) {
								$issues[] = 'no_file_type_restrictions';
								$threat_level += 30;
							}
							// Check max file size
							if ( ! isset( $field['maxFileSize'] ) || $field['maxFileSize'] > 10 ) {
								$issues[] = 'max_file_size_too_large';
								$threat_level += 15;
							}
						}
					}
				}
			}
		}

		// Check for public file access
		$test_file = $gf_upload_path . '/index.php';
		if ( ! file_exists( $test_file ) ) {
			$issues[] = 'directory_listing_possible';
			$threat_level += 15;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of file upload security issues */
				__( 'Gravity Forms file uploads have security issues: %s. This can allow malicious file uploads and unauthorized access to submitted files.', 'wpshadow' ),
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
				'kb_link'     => 'https://wpshadow.com/kb/gravity-forms-file-upload',
			);
		}
		
		return null;
	}
}
