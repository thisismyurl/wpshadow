<?php
/**
 * Mixed Content Detection Diagnostic
 *
 * Scans site pages for HTTP resources when HTTPS is enabled.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Diagnostics\Helpers\Diagnostic_HTML_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mixed Content Detection Diagnostic Class
 *
 * Detects http:// resources on HTTPS pages.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Mixed_Content_Detection extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'mixed-content-detection';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Mixed Content Detection';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects insecure HTTP resources on HTTPS pages';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! is_ssl() ) {
			return null;
		}

		$urls = array( home_url( '/' ) );
		$posts = get_posts(
			array(
				'post_type'      => array( 'post', 'page' ),
				'post_status'    => 'publish',
				'posts_per_page' => 3,
				'orderby'        => 'modified',
				'order'          => 'DESC',
			)
		);

		foreach ( $posts as $post ) {
			$urls[] = get_permalink( $post );
		}

		$mixed_resources = array();
		foreach ( array_unique( $urls ) as $url ) {
			$html = Diagnostic_HTML_Helper::fetch_html( $url );
			if ( empty( $html ) ) {
				continue;
			}

			if ( preg_match_all( '/(?:src|href|data)=["\']http:\/\/[^"\']+["\']/', $html, $matches ) ) {
				foreach ( $matches[0] as $match ) {
					$mixed_resources[] = $match;
				}
			}
		}

		$mixed_resources = array_unique( $mixed_resources );
		if ( empty( $mixed_resources ) ) {
			return null;
		}

		$sample = array_slice( $mixed_resources, 0, 5 );

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Mixed content detected. HTTP resources are loaded on HTTPS pages, which can trigger browser warnings and block assets.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 60,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/mixed-content-detection',
			'meta'         => array(
				'mixed_count' => count( $mixed_resources ),
				'sample'      => $sample,
			),
		);
	}
}
