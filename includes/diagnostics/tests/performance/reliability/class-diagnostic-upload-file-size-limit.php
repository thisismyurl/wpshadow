<?php
/**
 * Upload File Size Limit Diagnostic
 *
 * Issue #4941: Upload File Size Limit Too Small
 * Pillar: ⚙️ Murphy's Law
 *
 * Checks if upload_max_filesize is adequate.
 * Small limits prevent uploading images and media.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Upload_File_Size_Limit Class
 *
 * @since 1.6093.1200
 */
class Diagnostic_Upload_File_Size_Limit extends Diagnostic_Base {

	protected static $slug = 'upload-file-size-limit';
	protected static $title = 'Upload File Size Limit Too Small';
	protected static $description = 'Checks if file upload size limits are adequate';
	protected static $family = 'reliability';

	public static function check() {
		$upload_max = wp_convert_hr_to_bytes( ini_get( 'upload_max_filesize' ) );
		$post_max = wp_convert_hr_to_bytes( ini_get( 'post_max_size' ) );
		$recommended = 64 * 1024 * 1024; // 64MB

		if ( $upload_max < $recommended || $post_max < $recommended ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: current upload limit, 2: recommended limit */
					__( 'Current upload limit is %1$s. Modern images and videos need at least %2$s.', 'wpshadow' ),
					size_format( $upload_max ),
					size_format( $recommended )
				),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/upload-limits',
				'details'      => array(
					'upload_max_filesize'     => size_format( $upload_max ),
					'post_max_size'           => size_format( $post_max ),
					'recommended'             => size_format( $recommended ),
					'php_ini_settings'        => 'upload_max_filesize = 64M, post_max_size = 64M',
					'affected_content'        => 'High-res images, videos, plugin/theme uploads',
				),
			);
		}

		return null;
	}
}
