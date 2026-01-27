<?php
/**
 * HTML Detect Duplicate H1 Tags Diagnostic
 *
 * Detects multiple H1 tags on a single page.
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
 * HTML Detect Duplicate H1 Tags Diagnostic Class
 *
 * Identifies pages with multiple H1 tags, which can confuse search
 * engines and violates SEO best practices of one H1 per page.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Html_Detect_Duplicate_H1_Tags extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'html-detect-duplicate-h1-tags';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Multiple H1 Tags';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects multiple H1 tags on a single page';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

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

		$h1_tags = array();

		// Check scripts for H1 tags.
		global $wp_scripts;

		if ( ! empty( $wp_scripts ) && isset( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script_obj ) {
				if ( isset( $script_obj->extra['data'] ) ) {
					$data = (string) $script_obj->extra['data'];

					// Find all H1 tags.
					if ( preg_match_all( '/<h1[^>]*>(.+?)<\/h1>/is', $data, $matches ) ) {
						foreach ( $matches[1] as $h1_content ) {
							$h1_text = wp_strip_all_tags( $h1_content );

							$h1_tags[] = array(
								'handle' => $handle,
								'text'   => substr( $h1_text, 0, 100 ),
								'length' => strlen( $h1_text ),
							);
						}
					}
				}
			}
		}

		// Check post content if available.
		global $post;

		if ( ! empty( $post ) && $post instanceof \WP_Post ) {
			$content = $post->post_content;

			if ( preg_match_all( '/<h1[^>]*>(.+?)<\/h1>/is', $content, $matches ) ) {
				foreach ( $matches[1] as $h1_content ) {
					$h1_text = wp_strip_all_tags( $h1_content );

					$h1_tags[] = array(
						'handle' => 'post_content',
						'text'   => substr( $h1_text, 0, 100 ),
						'length' => strlen( $h1_text ),
					);
				}
			}
		}

		// Only report if multiple H1 tags found.
		if ( count( $h1_tags ) <= 1 ) {
			return null;
		}

		$items_list = '';
		$max_items  = 5;

		foreach ( array_slice( $h1_tags, 0, $max_items ) as $tag ) {
			$items_list .= sprintf(
				"\n- \"%s\"",
				esc_html( $tag['text'] )
			);
		}

		if ( count( $h1_tags ) > $max_items ) {
			$items_list .= sprintf(
				/* translators: %d: count */
				__( "\n...and %d more H1 tags", 'wpshadow' ),
				count( $h1_tags ) - $max_items
			);
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: count, 2: list */
				__( 'Found %1$d H1 tag(s) on this page. Best practice is to have exactly one H1 per page that represents the main heading/title. Multiple H1 tags can confuse search engines about what your page is about and dilute SEO impact.%2$s', 'wpshadow' ),
				count( $h1_tags ),
				$items_list
			),
			'severity'     => 'low',
			'threat_level' => 30,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/html-detect-duplicate-h1-tags',
			'meta'         => array(
				'h1_tags' => $h1_tags,
				'count'   => count( $h1_tags ),
			),
		);
	}
}
