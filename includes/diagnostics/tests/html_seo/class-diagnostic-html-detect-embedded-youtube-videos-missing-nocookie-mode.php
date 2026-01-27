<?php
/**
 * HTML Detect Embedded YouTube Videos Missing Nocookie Mode Diagnostic
 *
 * Detects embedded YouTube videos not using nocookie domain.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\HTML
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * HTML Detect Embedded YouTube Videos Missing Nocookie Mode Diagnostic Class
 *
 * Identifies YouTube embeds using youtube.com instead of youtube-nocookie.com,
 * which impacts user privacy by sending tracking cookies.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Html_Detect_Embedded_Youtube_Videos_Missing_Nocookie_Mode extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'html-detect-embedded-youtube-videos-missing-nocookie-mode';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'YouTube Embeds Missing No-Cookie Mode';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects YouTube embeds not using nocookie domain for privacy';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( is_admin() ) {
			return null;
		}

		$youtube_issues = array();

		// Check scripts for YouTube embeds.
		global $wp_scripts;

		if ( ! empty( $wp_scripts ) && isset( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script_obj ) {
				if ( isset( $script_obj->extra['data'] ) ) {
					$data = (string) $script_obj->extra['data'];

					// Look for YouTube iframes with standard domain.
					if ( preg_match_all( '/<iframe[^>]*src=["\']([^"\']*youtube\.com[^"\']*)["\'][^>]*>/', $data, $matches ) ) {
						foreach ( $matches[1] as $src ) {
							// Check if using youtube-nocookie.com instead.
							if ( strpos( $src, 'youtube-nocookie.com' ) === false ) {
								$video_id = '';

								if ( preg_match( '/(?:youtube\.com\/embed\/|v=)([a-zA-Z0-9_-]{11})/', $src, $m ) ) {
									$video_id = $m[1];
								}

								$youtube_issues[] = array(
									'handle'   => $handle,
									'src'      => $src,
									'video_id' => $video_id,
									'issue'    => __( 'YouTube embed using youtube.com instead of youtube-nocookie.com', 'wpshadow' ),
									'fix'      => __( 'Replace youtube.com with youtube-nocookie.com in iframe src', 'wpshadow' ),
								);
							}
						}
					}
				}
			}
		}

		// Check post content for YouTube embeds.
		global $post;

		if ( ! empty( $post ) && $post instanceof \WP_Post ) {
			$content = $post->post_content;

			if ( preg_match_all( '/<iframe[^>]*src=["\']([^"\']*youtube\.com[^"\']*)["\'][^>]*>/', $content, $matches ) ) {
				foreach ( $matches[1] as $src ) {
					// Check if using youtube-nocookie.com instead.
					if ( strpos( $src, 'youtube-nocookie.com' ) === false ) {
						$video_id = '';

						if ( preg_match( '/(?:youtube\.com\/embed\/|v=)([a-zA-Z0-9_-]{11})/', $src, $m ) ) {
							$video_id = $m[1];
						}

						$youtube_issues[] = array(
							'handle'   => 'post_content',
							'src'      => $src,
							'video_id' => $video_id,
							'issue'    => __( 'YouTube embed using youtube.com instead of youtube-nocookie.com', 'wpshadow' ),
							'fix'      => __( 'Replace youtube.com with youtube-nocookie.com in iframe src', 'wpshadow' ),
						);
					}
				}
			}
		}

		if ( empty( $youtube_issues ) ) {
			return null;
		}

		$items_list = '';
		$max_items  = 5;

		foreach ( array_slice( $youtube_issues, 0, $max_items ) as $issue ) {
			$items_list .= sprintf(
				"\n- Video ID %s: %s",
				esc_html( $issue['video_id'] ?: 'unknown' ),
				esc_html( $issue['issue'] )
			);
		}

		if ( count( $youtube_issues ) > $max_items ) {
			$items_list .= sprintf(
				/* translators: %d: count */
				__( "\n...and %d more YouTube embeds", 'wpshadow' ),
				count( $youtube_issues ) - $max_items
			);
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: count, 2: list */
				__( 'Found %1$d YouTube embed(s) using standard youtube.com domain. YouTube embeds should use youtube-nocookie.com to respect user privacy by not setting tracking cookies until user interacts with the video. Update iframes to use the no-cookie domain.%2$s', 'wpshadow' ),
				count( $youtube_issues ),
				$items_list
			),
			'severity'     => 'low',
			'threat_level' => 30,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/html-detect-embedded-youtube-videos-missing-nocookie-mode',
			'meta'         => array(
				'videos' => $youtube_issues,
			),
		);
	}
}
