<?php
/**
 * Media SSL/HTTPS Enforcement Diagnostic
 *
 * Checks whether media files are served over HTTPS
 * and detects mixed content issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Tests
 * @since      1.26033.1615
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Media_SSL_HTTPS_Enforcement Class
 *
 * Verifies media URLs are HTTPS on secure sites.
 *
 * @since 1.26033.1615
 */
class Diagnostic_Media_SSL_HTTPS_Enforcement extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-ssl-https-enforcement';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Media SSL/HTTPS Enforcement';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if media files are served over HTTPS';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.1615
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$site_scheme = wp_parse_url( home_url(), PHP_URL_SCHEME );

		if ( 'https' !== $site_scheme ) {
			return null;
		}

		$attachments = get_posts(
			array(
				'post_type'      => 'attachment',
				'posts_per_page' => 5,
				'post_status'    => 'inherit',
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		$http_count = 0;
		foreach ( $attachments as $attachment ) {
			$url = wp_get_attachment_url( $attachment->ID );
			if ( empty( $url ) ) {
				continue;
			}
			if ( 0 === strpos( $url, 'http://' ) ) {
				$http_count++;
			}
		}

		if ( $http_count > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of media URLs */
				_n(
					'%d recent media URL is using HTTP on an HTTPS site; update media URLs to avoid mixed content',
					'%d recent media URLs are using HTTP on an HTTPS site; update media URLs to avoid mixed content',
					$http_count,
					'wpshadow'
				),
				$http_count
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/media-ssl-https-enforcement',
			);
		}

		return null;
	}
}
