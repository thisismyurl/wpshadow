<?php
/**
 * Media External Media URL Handling Treatment
 *
 * Tests handling of external/remote media URLs and
 * validates that remote files are reachable.
 *
 * @package    WPShadow
 * @subpackage Treatments\Tests
 * @since      1.6033.1615
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Treatments\Helpers\Treatment_Request_Helper;
use WPShadow\Treatments\Helpers\Treatment_URL_And_Pattern_Helper;
use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Media_External_Media_URL_Handling Class
 *
 * Checks for external media URLs and validates access.
 *
 * @since 1.6033.1615
 */
class Treatment_Media_External_Media_URL_Handling extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-external-media-url-handling';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'External Media URL Handling';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests handling of external or remote media URLs';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.1615
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$site_host = Treatment_URL_And_Pattern_Helper::get_domain( home_url() );

		$attachments = get_posts(
			array(
				'post_type'      => 'attachment',
				'posts_per_page' => 5,
				'post_status'    => 'inherit',
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		$external_count = 0;
		$unreachable_count = 0;
		foreach ( $attachments as $attachment ) {
			$url = wp_get_attachment_url( $attachment->ID );
			if ( empty( $url ) ) {
				continue;
			}

			$url_host = Treatment_URL_And_Pattern_Helper::get_domain( $url );
			if ( ! empty( $url_host ) && $url_host !== $site_host ) {
				$external_count++;
				$response = Treatment_Request_Helper::head_result(
					$url,
					array(
						'timeout' => 5,
					)
				);
				if ( ! $response['success'] ) {
					$unreachable_count++;
					continue;
				}
				$code = (int) $response['code'];
				if ( $code >= 400 ) {
					$unreachable_count++;
				}
			}
		}

		if ( $external_count > 0 && '1' !== (string) ini_get( 'allow_url_fopen' ) ) {
			$issues[] = __( 'External media URLs detected, but remote file access is disabled; some embeds may fail', 'wpshadow' );
		}

		if ( $unreachable_count > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of URLs */
				_n(
					'%d external media URL is unreachable; consider downloading it locally',
					'%d external media URLs are unreachable; consider downloading them locally',
					$unreachable_count,
					'wpshadow'
				),
				$unreachable_count
			);
		}

		if ( $external_count > 0 && $unreachable_count === 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of external items */
				_n(
					'%d external media item detected; ensure hotlink policies allow access',
					'%d external media items detected; ensure hotlink policies allow access',
					$external_count,
					'wpshadow'
				),
				$external_count
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/media-external-media-url-handling',
			);
		}

		return null;
	}
}
