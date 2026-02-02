<?php
/**
 * Media CORS Configuration Diagnostic
 *
 * Tests Cross-Origin Resource Sharing settings for media
 * and validates security headers.
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
 * Diagnostic_Media_CORS_Configuration Class
 *
 * Checks Access-Control-Allow-Origin headers on media URLs.
 *
 * @since 1.26033.1615
 */
class Diagnostic_Media_CORS_Configuration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-cors-configuration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Media CORS Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests CORS settings for media files';

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
		$site_host = wp_parse_url( home_url(), PHP_URL_HOST );

		$attachments = get_posts(
			array(
				'post_type'      => 'attachment',
				'posts_per_page' => 1,
				'post_status'    => 'inherit',
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		if ( empty( $attachments ) ) {
			return null;
		}

		$url = wp_get_attachment_url( $attachments[0]->ID );
		if ( empty( $url ) ) {
			return null;
		}

		$response = wp_remote_head(
			$url,
			array(
				'timeout' => 5,
			)
		);

		if ( is_wp_error( $response ) ) {
			$issues[] = __( 'Unable to retrieve media headers for CORS validation', 'wpshadow' );
		} else {
			$headers = wp_remote_retrieve_headers( $response );
			$aco = '';
			if ( isset( $headers['access-control-allow-origin'] ) ) {
				$aco = (string) $headers['access-control-allow-origin'];
			}

			$url_host = wp_parse_url( $url, PHP_URL_HOST );
			if ( ! empty( $url_host ) && $url_host !== $site_host ) {
				if ( empty( $aco ) ) {
					$issues[] = __( 'Media served from a different host without CORS headers; this can block cross-origin usage', 'wpshadow' );
				} elseif ( '*' !== $aco && false === strpos( $aco, $site_host ) ) {
					$issues[] = __( 'CORS header does not allow the site origin; media requests may be blocked in browsers', 'wpshadow' );
				}
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/media-cors-configuration',
			);
		}

		return null;
	}
}
