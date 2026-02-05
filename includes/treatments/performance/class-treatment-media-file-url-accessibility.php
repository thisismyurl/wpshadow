<?php
/**
 * Media File URL Accessibility Treatment
 *
 * Tests whether uploaded media files are accessible via
 * their public URLs and detects 404 errors.
 *
 * @package    WPShadow
 * @subpackage Treatments\Tests
 * @since      1.6033.1605
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Treatments\Helpers\Treatment_Request_Helper;
use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Media_File_URL_Accessibility Class
 *
 * Verifies that recent media attachments are accessible
 * via their URLs without returning errors.
 *
 * @since 1.6033.1605
 */
class Treatment_Media_File_URL_Accessibility extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-file-url-accessibility';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'File URL Accessibility';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests media URLs for accessibility and 404 errors';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
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

			$response = Treatment_Request_Helper::head_result(
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
