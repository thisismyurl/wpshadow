<?php
/**
 * WCAG 2.4.4 Link Purpose Treatment
 *
 * Validates that link text is descriptive and meaningful.
 *
 * @since   1.6035.1200
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WCAG Link Purpose Treatment Class
 *
 * Checks for generic or unclear link text (WCAG 2.4.4 Level A).
 *
 * @since 1.6035.1200
 */
class Treatment_WCAG_Link_Purpose extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'wcag-link-purpose';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Link Purpose (WCAG 2.4.4)';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates that link text is descriptive and meaningful';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6035.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Get recent posts to check for generic link text.
		$posts = get_posts(
			array(
				'numberposts' => 20,
				'post_status' => 'publish',
				'post_type'   => 'any',
			)
		);

		$generic_patterns = array(
			'click here',
			'read more',
			'more',
			'here',
			'link',
			'this',
			'learn more',
		);

		$generic_count       = 0;
		$posts_with_generics = array();

		foreach ( $posts as $post ) {
			$content        = $post->post_content;
			$has_generic    = false;
			$found_patterns = array();

			// Check for generic link text patterns.
			foreach ( $generic_patterns as $pattern ) {
				$regex = '/<a[^>]*>[\s]*' . preg_quote( $pattern, '/' ) . '[\s]*<\/a>/i';
				if ( preg_match( $regex, $content ) ) {
					$has_generic      = true;
					$found_patterns[] = $pattern;
					$generic_count++;
				}
			}

			if ( $has_generic ) {
				$posts_with_generics[] = array(
					'title'    => $post->post_title,
					'patterns' => $found_patterns,
				);
			}
		}

		if ( $generic_count > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of generic links found */
				__( 'Found %d instances of generic link text like "click here" or "read more". Use descriptive text instead', 'wpshadow' ),
				$generic_count
			);
		}

		// Check for images used as links without alt text.
		$image_link_count = 0;
		foreach ( $posts as $post ) {
			$content = $post->post_content;

			// Check for <a><img> without alt.
			if ( preg_match_all( '/<a[^>]*>[\s]*<img[^>]*>[\s]*<\/a>/i', $content, $matches ) ) {
				foreach ( $matches[0] as $match ) {
					if ( ! preg_match( '/alt=["\'][^"\']*["\']/', $match ) || preg_match( '/alt=["\'][\s]*["\']/', $match ) ) {
						$image_link_count++;
					}
				}
			}
		}

		if ( $image_link_count > 3 ) {
			$issues[] = sprintf(
				/* translators: %d: number of image links without alt text */
				__( 'Found %d image links without descriptive alt text. Screen readers will just say "link, image"', 'wpshadow' ),
				$image_link_count
			);
		}

		// Check for URLs as link text (anti-pattern).
		$url_link_count = 0;
		foreach ( $posts as $post ) {
			$content = $post->post_content;

			// Check for links where the text is a URL.
			if ( preg_match_all( '/<a[^>]*href=["\']([^"\']*)["\'][^>]*>[\s]*(https?:\/\/[^<]+)[\s]*<\/a>/i', $content, $matches ) ) {
				$url_link_count += count( $matches[0] );
			}
		}

		if ( $url_link_count > 3 ) {
			$issues[] = sprintf(
				/* translators: %d: number of URL links */
				__( 'Found %d links where URL is the link text. Use descriptive text (e.g., "Visit our homepage" not "https://example.com")', 'wpshadow' ),
				$url_link_count
			);
		}

		// Check for multiple links with identical text pointing to different places.
		$link_texts = array();
		foreach ( $posts as $post ) {
			$content = $post->post_content;

			if ( preg_match_all( '/<a[^>]*href=["\']([^"\']*)["\'][^>]*>([^<]+)<\/a>/i', $content, $matches ) ) {
				for ( $i = 0; $i < count( $matches[0] ); $i++ ) {
					$href = $matches[1][ $i ];
					$text = trim( strip_tags( $matches[2][ $i ] ) );

					if ( ! isset( $link_texts[ $text ] ) ) {
						$link_texts[ $text ] = array();
					}
					$link_texts[ $text ][] = $href;
				}
			}
		}

		$ambiguous_count = 0;
		foreach ( $link_texts as $text => $hrefs ) {
			$unique_hrefs = array_unique( $hrefs );
			if ( count( $unique_hrefs ) > 1 && strlen( $text ) < 20 ) {
				$ambiguous_count++;
			}
		}

		if ( $ambiguous_count > 3 ) {
			$issues[] = sprintf(
				/* translators: %d: number of ambiguous link texts */
				__( 'Found %d cases where identical link text points to different destinations. Each link should be unique', 'wpshadow' ),
				$ambiguous_count
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Link text should be like street signs: clear and specific. Screen reader users often navigate by jumping from link to link, hearing only the link text out of context. "Click here" repeated 20 times is like having 20 identical street signs—you can\'t tell where any of them go. Instead of "Click here for pricing," use "View our pricing plans." This helps everyone, especially the 2% of users who rely on screen readers.', 'wpshadow' ) . ' ' . implode( ' ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/wcag-link-purpose',
			);
		}

		return null;
	}
}
