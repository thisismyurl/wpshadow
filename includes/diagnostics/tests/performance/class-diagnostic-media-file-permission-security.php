<?php
/**
 * Media File Permission Security Diagnostic
 *
 * Validates file and directory permissions for uploads
 * to prevent overly permissive access (e.g., 777).
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Tests
 * @since      1.6033.1605
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Media_File_Permission_Security Class
 *
 * Checks for insecure permissions in the uploads directory
 * and recent media files.
 *
 * @since 1.6033.1605
 */
class Diagnostic_Media_File_Permission_Security extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-file-permission-security';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'File Permission Security';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates secure file permissions in uploads directory';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.1605
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$upload_dir = wp_upload_dir();
		$base_dir = $upload_dir['basedir'];

		if ( empty( $base_dir ) || ! is_dir( $base_dir ) ) {
			$issues[] = __( 'Uploads directory is missing or inaccessible; cannot verify file permissions', 'wpshadow' );
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/media-file-permission-security',
			);
		}

		$dir_perms = fileperms( $base_dir );
		if ( false !== $dir_perms ) {
			if ( $dir_perms & 0x0002 ) {
				$issues[] = __( 'Uploads directory is world-writable; set permissions to 755 or 750', 'wpshadow' );
			} elseif ( $dir_perms & 0x0010 ) {
				$issues[] = __( 'Uploads directory is group-writable; consider restricting to 755 or 750', 'wpshadow' );
			}
		}

		$recent_attachments = get_posts(
			array(
				'post_type'      => 'attachment',
				'posts_per_page' => 5,
				'post_status'    => 'inherit',
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		$insecure_files = 0;
		foreach ( $recent_attachments as $attachment ) {
			$file_path = get_attached_file( $attachment->ID );
			if ( empty( $file_path ) || ! file_exists( $file_path ) ) {
				continue;
			}

			$perms = fileperms( $file_path );
			if ( false === $perms ) {
				continue;
			}

			if ( $perms & 0x0002 ) {
				$insecure_files++;
			} elseif ( $perms & 0x0010 ) {
				$insecure_files++;
			}
		}

		if ( $insecure_files > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of files */
				_n(
					'%d recent media file has insecure permissions; tighten file permissions to 644',
					'%d recent media files have insecure permissions; tighten file permissions to 644',
					$insecure_files,
					'wpshadow'
				),
				$insecure_files
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/media-file-permission-security',
			);
		}

		return null;
	}
}
