<?php
/**
 * Contact Form 7 File Uploads Diagnostic
 *
 * Contact Form 7 File Uploads issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1202.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contact Form 7 File Uploads Diagnostic Class
 *
 * @since 1.1202.0000
 */
class Diagnostic_ContactForm7FileUploads extends Diagnostic_Base {

	protected static $slug = 'contact-form-7-file-uploads';
	protected static $title = 'Contact Form 7 File Uploads';
	protected static $description = 'Contact Form 7 File Uploads issue found';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'WPCF7_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Get all contact forms and check file upload configurations
		$forms = get_posts( array(
			'post_type'      => 'wpcf7_contact_form',
			'posts_per_page' => -1,
		) );
		
		if ( empty( $forms ) ) {
			return null; // No forms, no issues
		}
		
		foreach ( $forms as $form ) {
			$form_content = $form->post_content;
			
			// Check if form has file upload field
			if ( strpos( $form_content, '[file' ) === false && strpos( $form_content, '[file*' ) === false ) {
				continue; // No file upload in this form
			}
			
			// Check 2: Verify file size limits are set
			if ( ! preg_match( '/limit:[0-9]+/', $form_content ) ) {
				$issues[] = 'no_file_size_limit';
			}
			
			// Check 3: Verify file type restrictions are in place
			if ( ! preg_match( '/filetypes:/', $form_content ) ) {
				$issues[] = 'no_file_type_restriction';
			} else {
				// Check if dangerous file types are allowed
				preg_match( '/filetypes:([^\]]+)/', $form_content, $matches );
				if ( ! empty( $matches[1] ) ) {
					$allowed_types = strtolower( $matches[1] );
					$dangerous_types = array( 'php', 'exe', 'sh', 'bat', 'cmd', 'js', 'html' );
					
					foreach ( $dangerous_types as $type ) {
						if ( strpos( $allowed_types, $type ) !== false ) {
							$issues[] = 'dangerous_file_types_allowed';
							break;
						}
					}
				}
			}
		}
		
		// Check 4: Verify upload directory permissions
		$upload_dir = wp_upload_dir();
		$wpcf7_dir = $upload_dir['basedir'] . '/wpcf7_uploads';
		
		if ( file_exists( $wpcf7_dir ) ) {
			// Check if directory is directly accessible via URL
			$test_url = $upload_dir['baseurl'] . '/wpcf7_uploads/';
			$response = wp_remote_get( $test_url, array( 'timeout' => 5 ) );
			
			if ( ! is_wp_error( $response ) ) {
				$status_code = wp_remote_retrieve_response_code( $response );
				// Directory listing enabled (200) is a security risk
				if ( 200 === $status_code ) {
					$body = wp_remote_retrieve_body( $response );
					if ( strpos( $body, 'Index of' ) !== false ) {
						$issues[] = 'upload_directory_listing_enabled';
					}
				}
			}
			
			// Check if .htaccess protection exists
			$htaccess_file = $wpcf7_dir . '/.htaccess';
			if ( ! file_exists( $htaccess_file ) ) {
				$issues[] = 'no_htaccess_protection';
			}
		}
		
		// Check 5: Verify file upload size limit in WordPress
		$max_upload_size = wp_max_upload_size();
		if ( $max_upload_size > 10 * 1024 * 1024 ) { // 10MB
			$issues[] = 'high_upload_size_limit';
		}
		
		if ( ! empty( $issues ) ) {
			$issues = array_unique( $issues );
			$description = sprintf(
				/* translators: %s: list of file upload security issues */
				__( 'Contact Form 7 file upload security issues: %s. Unrestricted file uploads can lead to malware uploads, data breaches, or server compromise.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);
			
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => 70,
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/contact-form-7-file-uploads',
			);
		}
		
		return null;
	}
}
