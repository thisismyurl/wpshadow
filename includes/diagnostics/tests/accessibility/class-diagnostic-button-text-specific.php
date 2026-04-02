<?php
/**
 * Button Text Specific Diagnostic
 *
 * Scans published posts and pages for Gutenberg button blocks and classic-editor
 * anchor tags that use vague, non-descriptive text (e.g. "Click Here", "Learn
 * More", "Submit") which fail WCAG 2.4.6 and SC 1.3.1.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Button_Text_Specific Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Button_Text_Specific extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'button-text-specific';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Button Text Specific';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Scans published posts and pages for button blocks and links that use vague non-descriptive text (e.g. "Click Here", "Learn More") which break screen-reader context.';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Vague phrases that provide no meaningful context for assistive technologies.
	 * Checked case-insensitively against button/link text in post content.
	 *
	 * @var string[]
	 */
	private const VAGUE_PHRASES = array(
		'click here',
		'click this',
		'learn more',
		'read more',
		'more info',
		'more information',
		'find out more',
		'here',
		'this link',
		'submit',
		'go',
		'continue',
		'details',
	);

	/**
	 * Run the diagnostic check.
	 *
	 * Fetches up to 200 published posts/pages and scans their content for
	 * Gutenberg button blocks and HTML anchor tags whose visible text matches
	 * a known list of vague/non-descriptive phrases. Reports up to 10
	 * affected items so the finding stays actionable.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		$posts = get_posts(
			array(
				'post_type'      => array( 'post', 'page' ),
				'post_status'    => 'publish',
				'posts_per_page' => 200,
				'fields'         => 'ids',
			)
		);

		if ( empty( $posts ) ) {
			return null;
		}

		// Build a single alternation regex from the vague phrase list so we
		// only iterate the post list once.
		$escaped   = array_map( 'preg_quote', self::VAGUE_PHRASES, array_fill( 0, count( self::VAGUE_PHRASES ), '/' ) );
		$pattern   = '/(?:<a\b[^>]*>|wp:button[^>]*>)(?:\s*<[^>]+>\s*)?' .
		             '(?:' . implode( '|', $escaped ) . ')' .
		             '(?:\s*<\/[^>]+>\s*)?(?:<\/a>|<\/div>)/i';

		$affected = array();

		foreach ( $posts as $post_id ) {
			if ( count( $affected ) >= 10 ) {
				break;
			}

			$post    = get_post( $post_id );
			$content = (string) $post->post_content;

			if ( '' === $content ) {
				continue;
			}

			if ( ! preg_match( $pattern, $content, $match ) ) {
				continue;
			}

			// Extract just the matched text for the report.
			$matched_text = wp_strip_all_tags( $match[0] );

			$affected[] = array(
				'post_id'      => $post_id,
				'post_title'   => $post->post_title,
				'post_type'    => $post->post_type,
				'matched_text' => trim( $matched_text ),
				'edit_url'     => get_edit_post_link( $post_id, 'raw' ),
			);
		}

		if ( empty( $affected ) ) {
			return null;
		}

		$count = count( $affected );

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of posts/pages affected */
				_n(
					'%d published post or page contains a button or link with vague non-descriptive text like "Click Here" or "Learn More". Screen-reader users navigating by link list will not know where the control leads.',
					'%d published posts and pages contain buttons or links with vague non-descriptive text like "Click Here" or "Learn More". Screen-reader users navigating by link list cannot determine where these controls lead.',
					$count,
					'wpshadow'
				),
				$count
			),
			'severity'     => 'medium',
			'threat_level' => 40,
			'kb_link'      => 'https://wpshadow.com/kb/accessible-button-link-text?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => array(
				'affected_count' => $count,
				'affected_items' => $affected,
				'vague_phrases'  => self::VAGUE_PHRASES,
				'fix'            => __( 'Edit each flagged post and replace the generic button or link label with a phrase that describes the destination or action, e.g. "Download the 2025 Report" instead of "Click Here".', 'wpshadow' ),
			),
		);
	}
}
