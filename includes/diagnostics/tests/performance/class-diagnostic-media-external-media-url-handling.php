<?php
/**
 * Media External Media URL Handling Diagnostic
 *
 * Tests handling of external/remote media URLs and
 * validates that remote files are reachable.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Tests
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Diagnostics\Helpers\Diagnostic_Request_Helper;
use WPShadow\Diagnostics\Helpers\Diagnostic_URL_And_Pattern_Helper;
use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Media_External_Media_URL_Handling Class
 *
 * Checks for external media URLs and validates access.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Media_External_Media_URL_Handling extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-external-media-url-handling';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'External Media URL Handling';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests handling of external or remote media URLs';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$site_host = Diagnostic_URL_And_Pattern_Helper::get_domain( home_url() );

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

			$url_host = Diagnostic_URL_And_Pattern_Helper::get_domain( $url );
			if ( ! empty( $url_host ) && $url_host !== $site_host ) {
				$external_count++;
				$response = Diagnostic_Request_Helper::head_result(
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
