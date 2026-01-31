<?php
/**
 * Wp Offload Media S3 Sync Diagnostic
 *
 * Wp Offload Media S3 Sync detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.778.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wp Offload Media S3 Sync Diagnostic Class
 *
 * @since 1.778.0000
 */
class Diagnostic_WpOffloadMediaS3Sync extends Diagnostic_Base {

	protected static $slug = 'wp-offload-media-s3-sync';
	protected static $title = 'Wp Offload Media S3 Sync';
	protected static $description = 'Wp Offload Media S3 Sync detected';
	protected static $family = 'functionality';

	public static function check() {
		
		$issues = array();
		// Check if feature is configured
		$option_prefix = 'diagnostic_' . str_replace('-', '_', self::$slug);
		$configured = get_option($option_prefix, false);
		if (!$configured) {
			$issues[] = 'feature not configured';
		}
		$has_issue = !empty($issues);
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 50 ),
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/wp-offload-media-s3-sync',
			);
		}
		
		return null;
	}
}
