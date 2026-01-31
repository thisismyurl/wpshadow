<?php
/**
 * Image Gallery Permissions Diagnostic
 *
 * Image gallery permissions too permissive.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.503.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Image Gallery Permissions Diagnostic Class
 *
 * @since 1.503.0000
 */
class Diagnostic_ImageGalleryPermissions extends Diagnostic_Base {

	protected static $slug = 'image-gallery-permissions';
	protected static $title = 'Image Gallery Permissions';
	protected static $description = 'Image gallery permissions too permissive';
	protected static $family = 'security';

	public static function check() {
			// Check if any gallery plugin is active
		$has_gallery = class_exists( 'Envira_Gallery' ) ||       // Envira Gallery
					   class_exists( 'C_NextGEN_Bootstrap' ) ||  // NextGEN Gallery
					   function_exists( 'foogallery_fs' ) ||      // FooGallery
					   class_exists( 'Modula' );                  // Modula

		if ( ! $has_gallery ) {
			return null;
		}

		$issues = array();
		$threat_level = 0;

		global $wpdb;

		// Check upload directory permissions
		$upload_dir = wp_upload_dir();
		$uploads_path = $upload_dir['basedir'];
		$perms = substr( sprintf( '%o', fileperms( $uploads_path ) ), -4 );
		
		if ( $perms === '0777' ) {
			$issues[] = 'upload_directory_too_permissive';
			$threat_level += 35;
		}

		// Check public gallery access
		$galleries_public = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} 
				 WHERE post_type IN (%s, %s, %s) 
				 AND post_status = %s",
				'envira',
				'ngg_gallery',
				'foogallery',
				'publish'
			)
		);
		
		if ( $galleries_public > 0 ) {
			// Check password protection
			$password_protected = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->posts} 
					 WHERE post_type IN (%s, %s, %s) 
					 AND post_status = %s
					 AND post_password != ''",
					'envira',
					'ngg_gallery',
					'foogallery',
					'publish'
				)
			);
			
			if ( $password_protected === 0 ) {
				$issues[] = 'no_galleries_password_protected';
				$threat_level += 25;
			}
		}

		// Check direct file access
		$htaccess_exists = file_exists( $uploads_path . '/.htaccess' );
		if ( ! $htaccess_exists ) {
			$issues[] = 'no_htaccess_protection';
			$threat_level += 30;
		}

		// Check hotlink protection
		$hotlink_protection = get_option( 'gallery_hotlink_protection', 0 ) ||
							  get_option( 'ngg_options' ) && isset( get_option( 'ngg_options' )['galleryprotect'] );
							  
		if ( ! $hotlink_protection ) {
			$issues[] = 'hotlink_protection_disabled';
			$threat_level += 20;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of permission issues */
				__( 'Image gallery permissions are too permissive: %s. This allows unauthorized access to private images.', 'wpshadow' ),
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
				'kb_link'     => 'https://wpshadow.com/kb/image-gallery-permissions',
			);
		}
		
		return null;
	}
}
