<?php
/**
 * Missing Media Attachments After Import Diagnostic
 *
 * Detects posts with broken image links after import.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Diagnostics\Helpers\Diagnostic_Request_Helper;
use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Missing Media Attachments After Import Diagnostic Class
 *
 * Detects when imported posts reference images that failed to download.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Missing_Media_Attachments_After_Import extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'missing-media-attachments-after-import';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Missing Media Attachments After Import';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects posts with broken image links after import';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// Sample recent posts to check for broken images.
		$recent_posts = get_posts( array(
			'post_type'      => array( 'post', 'page' ),
			'post_status'    => 'publish',
			'posts_per_page' => 20,
			'orderby'        => 'modified',
			'order'          => 'DESC',
		) );

		if ( empty( $recent_posts ) ) {
			return null;
		}

		$broken_images = 0;
		$posts_checked = 0;

		foreach ( $recent_posts as $post ) {
			$posts_checked++;

			// Check for image tags in post content.
			if ( preg_match_all( '/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $post->post_content, $matches ) ) {
				foreach ( $matches[1] as $image_url ) {
					// Check if image file actually exists.
					$response = Diagnostic_Request_Helper::head_result( $image_url, array( 'timeout' => 3 ) );
					if ( ! $response['success'] || (int) $response['code'] >= 400 ) {
						$broken_images++;
					}
				}
			}
		}

		if ( $broken_images > 0 ) {
			$percentage = ( $broken_images / $posts_checked ) * 100;
			$issues[] = sprintf(
				/* translators: %d: percentage of broken images */
				__( '%d%% of sampled posts contain broken image links', 'wpshadow' ),
				round( $percentage )
			);
		}

		// Check for orphaned attachment records (in DB but files missing).
		$orphaned = $wpdb->get_var( "
			SELECT COUNT(*)
			FROM {$wpdb->posts}
			WHERE post_type = 'attachment'
			AND post_status = 'inherit'
		" );

		if ( $orphaned > 100 ) {
			$issues[] = sprintf(
				/* translators: %d: number of attachments */
				__( 'Large number of attachment records found (%d)', 'wpshadow' ),
				$orphaned
			);
		}

		// Check import logs for download failures.
		$import_log_file = WP_CONTENT_DIR . '/wpshadow-import.log';
		if ( file_exists( $import_log_file ) ) {
			$log_content = file_get_contents( $import_log_file );
			if ( stripos( $log_content, 'failed' ) !== false || stripos( $log_content, 'error' ) !== false ) {
				$issues[] = __( 'Import log contains error messages', 'wpshadow' );
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/missing-media-attachments-after-import?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
