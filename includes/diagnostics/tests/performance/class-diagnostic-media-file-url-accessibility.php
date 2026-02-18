<?php
/**
 * Media File URL Accessibility Diagnostic
 *
 * Tests whether uploaded media files are accessible via
 * their public URLs and detects 404 errors.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Tests
 * @since      1.6033.1605
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Diagnostics\Helpers\Diagnostic_Request_Helper;
use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Media_File_URL_Accessibility Class
 *
 * Verifies that recent media attachments are accessible
 * via their URLs without returning errors.
 *
 * @since 1.6033.1605
 */
class Diagnostic_Media_File_URL_Accessibility extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-file-url-accessibility';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'File URL Accessibility';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests media URLs for accessibility and 404 errors';

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

		$recent_attachments = get_posts(
			array(
				'post_type'      => 'attachment',
				'posts_per_page' => 3,
				'post_status'    => 'inherit',
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		if ( empty( $recent_attachments ) ) {
			return null;
		}

		$failed_count = 0;
		foreach ( $recent_attachments as $attachment ) {
			$url = wp_get_attachment_url( $attachment->ID );
			if ( empty( $url ) ) {
				$failed_count++;
				continue;
			}

			$response = Diagnostic_Request_Helper::head_result(
				$url,
				array(
					'timeout' => 5,
				)
			);

			if ( ! $response['success'] ) {
				$failed_count++;
				continue;
			}

			$code = (int) $response['code'];
			if ( $code >= 400 ) {
				$failed_count++;
			}
		}

		if ( $failed_count > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of failed URLs */
				_n(
					'%d recent media URL failed to load; check file permissions or storage configuration',
					'%d recent media URLs failed to load; check file permissions or storage configuration',
					$failed_count,
					'wpshadow'
				),
				$failed_count
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/media-file-url-accessibility',
			);
		}

		return null;
	}
}
