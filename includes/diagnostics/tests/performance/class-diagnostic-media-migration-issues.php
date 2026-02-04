<?php
/**
 * Media Migration Issues Diagnostic
 *
 * Detects broken media links after site migrations by
 * checking for hardcoded or mismatched domains.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Tests
 * @since      1.6033.1615
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Media_Migration_Issues Class
 *
 * Identifies media URLs that reference old domains or
 * cause mixed content after migrations.
 *
 * @since 1.6033.1615
 */
class Diagnostic_Media_Migration_Issues extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-migration-issues';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Media Migration Issues';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects broken media links after migrations';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.1615
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$site_host = wp_parse_url( home_url(), PHP_URL_HOST );
		$site_scheme = wp_parse_url( home_url(), PHP_URL_SCHEME );

		$attachments = get_posts(
			array(
				'post_type'      => 'attachment',
				'posts_per_page' => 10,
				'post_status'    => 'inherit',
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		$foreign_host_count = 0;
		$mixed_content_count = 0;
		foreach ( $attachments as $attachment ) {
			$url = wp_get_attachment_url( $attachment->ID );
			if ( empty( $url ) ) {
				continue;
			}
			$url_host = wp_parse_url( $url, PHP_URL_HOST );
			$url_scheme = wp_parse_url( $url, PHP_URL_SCHEME );

			if ( ! empty( $url_host ) && $url_host !== $site_host ) {
				$foreign_host_count++;
			}
			if ( 'https' === $site_scheme && 'http' === $url_scheme ) {
				$mixed_content_count++;
			}
		}

		if ( $foreign_host_count > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of attachments */
				_n(
					'%d recent media URL points to a different domain; verify migration rewrite rules',
					'%d recent media URLs point to a different domain; verify migration rewrite rules',
					$foreign_host_count,
					'wpshadow'
				),
				$foreign_host_count
			);
		}

		if ( $mixed_content_count > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of URLs */
				_n(
					'%d recent media URL is using HTTP on an HTTPS site; update URLs to prevent mixed content',
					'%d recent media URLs are using HTTP on an HTTPS site; update URLs to prevent mixed content',
					$mixed_content_count,
					'wpshadow'
				),
				$mixed_content_count
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
				'kb_link'      => 'https://wpshadow.com/kb/media-migration-issues',
			);
		}

		return null;
	}
}
