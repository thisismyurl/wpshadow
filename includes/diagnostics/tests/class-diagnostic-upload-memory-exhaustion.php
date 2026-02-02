<?php
/**
 * Upload Memory Exhaustion Diagnostic
 *
 * Checks if uploads might cause memory exhaustion issues.
 *
 * @since   1.26033.0901
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Upload_Memory_Exhaustion Class
 *
 * Detects memory issues during uploads.
 *
 * @since 1.26033.0901
 */
class Diagnostic_Upload_Memory_Exhaustion extends Diagnostic_Base {

	protected static $slug = 'upload-memory-exhaustion';
	protected static $title = 'Upload Memory Exhaustion';
	protected static $description = 'Checks for memory exhaustion during uploads';
	protected static $family = 'uploads';

	public static function check() {
		$memory_limit = (int) wp_convert_hr_to_bytes( WP_MEMORY_LIMIT );
		$max_upload = wp_max_upload_size();

		if ( $memory_limit > 0 && $max_upload > ( $memory_limit * 0.5 ) ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'Max upload size is more than half your memory limit. Uploading large files may cause memory exhaustion.', 'wpshadow' ),
				'severity' => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/upload-memory-exhaustion',
			);
		}
		return null;
	}
}
