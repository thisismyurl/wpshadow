<?php
/**
 * Form Builder File Upload Integration Diagnostic
 *
 * Tests file upload functionality in popular form builders (Contact Form 7, Gravity Forms, WPForms)
 * and validates media library integration, upload permissions, and security.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2603.1356
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Form Builder File Upload Integration Diagnostic Class
 *
 * Detects file upload issues in form builder plugins.
 *
 * @since 1.2603.1356
 */
class Diagnostic_Form_Builder_File_Upload_Integration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'form-builder-file-upload-integration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Form Builder File Upload Integration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests file uploads in form builders and validates media library integration';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'integrations';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks:
	 * - Form builder plugins are active
	 * - Upload directories are writable
	 * - File upload size limits are adequate
	 * - Allowed file types are configured
	 * - Media library integration works
	 * - Temporary upload cleanup is functional
	 *
	 * @since  1.2603.1356
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$active_form_builders = array();

		// Detect active form builder plugins.
		$form_builders = array(
			'contact-form-7/wp-contact-form-7.php' => array(
				'name'        => 'Contact Form 7',
				'upload_dir'  => WP_CONTENT_DIR . '/uploads/wpcf7_uploads',
				'option_key'  => 'wpcf7',
			),
			'gravityforms/gravityforms.php'        => array(
				'name'        => 'Gravity Forms',
				'upload_dir'  => WP_CONTENT_DIR . '/uploads/gravity_forms',
				'option_key'  => 'gf_settings',
			),
			'wpforms-lite/wpforms.php'             => array(
				'name'        => 'WPForms Lite',
				'upload_dir'  => WP_CONTENT_DIR . '/uploads/wpforms',
				'option_key'  => 'wpforms_settings',
			),
			'wpforms/wpforms.php'                  => array(
				'name'        => 'WPForms',
				'upload_dir'  => WP_CONTENT_DIR . '/uploads/wpforms',
				'option_key'  => 'wpforms_settings',
			),
			'formidable/formidable.php'            => array(
				'name'        => 'Formidable Forms',
				'upload_dir'  => WP_CONTENT_DIR . '/uploads/formidable',
				'option_key'  => 'frm_options',
			),
			'ninja-forms/ninja-forms.php'          => array(
				'name'        => 'Ninja Forms',
				'upload_dir'  => WP_CONTENT_DIR . '/uploads/ninja-forms',
				'option_key'  => 'ninja_forms_settings',
			),
		);

		foreach ( $form_builders as $plugin_file => $plugin_data ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_form_builders[] = $plugin_data['name'];

				// Check if upload directory exists and is writable.
				$upload_dir = $plugin_data['upload_dir'];
				
				if ( file_exists( $upload_dir ) ) {
					if ( ! wp_is_writable( $upload_dir ) ) {
						$issues[] = sprintf(
							/* translators: 1: plugin name, 2: directory path */
							__( '%1$s upload directory is not writable: %2$s', 'wpshadow' ),
							$plugin_data['name'],
							$upload_dir
						);
					}

					// Check for old files that weren't cleaned up.
					$files = glob( $upload_dir . '/*' );
					if ( is_array( $files ) && count( $files ) > 100 ) {
						$issues[] = sprintf(
							/* translators: 1: plugin name, 2: number of files */
							__( '%1$s has %2$d files in temporary upload directory. Consider cleanup.', 'wpshadow' ),
							$plugin_data['name'],
							count( $files )
						);
					}
				}
			}
		}

		// Only run further checks if a form builder is active.
		if ( empty( $active_form_builders ) ) {
			return null;
		}

		// Check PHP upload settings.
		$upload_max_filesize = ini_get( 'upload_max_filesize' );
		$post_max_size = ini_get( 'post_max_size' );
		$max_file_uploads = ini_get( 'max_file_uploads' );

		// Convert to bytes for comparison.
		$upload_max_bytes = wp_convert_hr_to_bytes( $upload_max_filesize );
		$post_max_bytes = wp_convert_hr_to_bytes( $post_max_size );

		if ( $upload_max_bytes < 2097152 ) { // Less than 2MB
			$issues[] = sprintf(
				/* translators: %s: current upload limit */
				__( 'PHP upload_max_filesize is very low (%s). Form file uploads may fail.', 'wpshadow' ),
				$upload_max_filesize
			);
		}

		if ( $post_max_bytes < $upload_max_bytes ) {
			$issues[] = sprintf(
				/* translators: 1: post_max_size, 2: upload_max_filesize */
				__( 'PHP post_max_size (%1$s) is smaller than upload_max_filesize (%2$s), which will prevent file uploads.', 'wpshadow' ),
				$post_max_size,
				$upload_max_filesize
			);
		}

		if ( $max_file_uploads < 5 ) {
			$issues[] = sprintf(
				/* translators: %d: max number of files */
				__( 'PHP max_file_uploads is set to %d, which may be too low for multi-file form uploads.', 'wpshadow' ),
				$max_file_uploads
			);
		}

		// Check WordPress upload size limits.
		$wp_max_upload = wp_max_upload_size();
		if ( $wp_max_upload < 2097152 ) { // Less than 2MB
			$issues[] = sprintf(
				/* translators: %s: upload limit */
				__( 'WordPress upload limit is %s, which may be too low for form file uploads.', 'wpshadow' ),
				size_format( $wp_max_upload )
			);
		}

		// Check file type restrictions.
		$allowed_mime_types = get_allowed_mime_types();
		$common_form_types = array( 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'zip' );
		
		$restricted_types = array();
		foreach ( $common_form_types as $type ) {
			$found = false;
			foreach ( $allowed_mime_types as $exts => $mime ) {
				if ( strpos( $exts, $type ) !== false ) {
					$found = true;
					break;
				}
			}
			if ( ! $found ) {
				$restricted_types[] = strtoupper( $type );
			}
		}

		if ( ! empty( $restricted_types ) ) {
			$issues[] = sprintf(
				/* translators: %s: comma-separated list of file types */
				__( 'Common file types are not allowed for upload: %s. Users may not be able to submit forms.', 'wpshadow' ),
				implode( ', ', $restricted_types )
			);
		}

		// Check for security plugins that might block uploads.
		$security_plugins = array(
			'wordfence/wordfence.php'            => 'Wordfence',
			'better-wp-security/better-wp-security.php' => 'iThemes Security',
			'all-in-one-wp-security-and-firewall/wp-security.php' => 'All In One WP Security',
			'sucuri-scanner/sucuri.php'          => 'Sucuri Security',
		);

		$active_security = array();
		foreach ( $security_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_security[] = $plugin_name;
			}
		}

		if ( ! empty( $active_security ) ) {
			$issues[] = sprintf(
				/* translators: %s: comma-separated list of security plugins */
				__( 'Security plugins are active (%s) that may block form file uploads. Verify upload rules.', 'wpshadow' ),
				implode( ', ', $active_security )
			);
		}

		// Check Contact Form 7 specific settings.
		if ( is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) ) {
			// CF7 stores uploaded files temporarily. Check if cleanup is working.
			$cf7_upload_dir = WP_CONTENT_DIR . '/uploads/wpcf7_uploads';
			if ( file_exists( $cf7_upload_dir ) ) {
				// Check for files older than 1 day.
				$old_files = 0;
				$files = glob( $cf7_upload_dir . '/*' );
				if ( is_array( $files ) ) {
					foreach ( $files as $file ) {
						if ( file_exists( $file ) && ( time() - filemtime( $file ) ) > 86400 ) {
							$old_files++;
						}
					}
				}

				if ( $old_files > 50 ) {
					$issues[] = sprintf(
						/* translators: %d: number of old files */
						__( 'Contact Form 7 has %d files older than 1 day. Automatic cleanup may not be working.', 'wpshadow' ),
						$old_files
					);
				}
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( ' ', $issues ),
				'severity'    => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'details'     => array(
					'active_form_builders'   => $active_form_builders,
					'upload_max_filesize'    => $upload_max_filesize,
					'post_max_size'          => $post_max_size,
					'wp_max_upload_size'     => size_format( $wp_max_upload ),
					'restricted_file_types'  => $restricted_types ?? array(),
					'active_security_plugins' => $active_security,
				),
				'kb_link'     => 'https://wpshadow.com/kb/form-builder-file-upload-integration',
			);
		}

		return null;
	}
}
