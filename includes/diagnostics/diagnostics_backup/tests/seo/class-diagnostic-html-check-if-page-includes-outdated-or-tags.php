<?php
/**
 * HTML Check If Page Includes Outdated B Or I Tags Diagnostic
 *
 * Detects outdated <b> and <i> tags in HTML.
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
 * HTML Check If Page Includes Outdated B Or I Tags Diagnostic Class
 *
 * Identifies pages using outdated <b> and <i> tags instead of semantic
 * alternatives <strong> and <em>.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Html_Check_If_Page_Includes_Outdated_Or_Tags extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'html-check-if-page-includes-outdated-or-tags';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Outdated B and I Tags Detected';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects outdated <b> and <i> tags instead of semantic alternatives';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'html';

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

		$outdated_tags = array();

		// Check post content.
		global $post;

		if ( ! empty( $post ) && $post instanceof \WP_Post ) {
			$content = $post->post_content;

			// Count <b> tags (should use <strong> instead).
			if ( preg_match_all( '/<b[^>]*>([^<]*)<\/b>/i', $content, $matches ) ) {
				$outdated_tags['b'] = count( $matches[0] );
			}

			// Count <i> tags (should use <em> instead).
			if ( preg_match_all( '/<i[^>]*>([^<]*)<\/i>/i', $content, $matches ) ) {
				$outdated_tags['i'] = count( $matches[0] );
			}
		}

		if ( empty( $outdated_tags ) ) {
			return null;
		}

		$tag_list = '';

		if ( ! empty( $outdated_tags['b'] ) ) {
			$tag_list .= sprintf(
				/* translators: %d: count */
				__( "\n- %d <b> tag(s) found (replace with <strong>)", 'wpshadow' ),
				$outdated_tags['b']
			);
		}

		if ( ! empty( $outdated_tags['i'] ) ) {
			$tag_list .= sprintf(
				/* translators: %d: count */
				__( "\n- %d <i> tag(s) found (replace with <em>)", 'wpshadow' ),
				$outdated_tags['i']
			);
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: tag list */
				__( 'Found outdated HTML formatting tags. The <b> and <i> tags are deprecated in favor of semantic alternatives: use <strong> for bold/important text (implies importance to screen readers) and <em> for italics/emphasis. Semantic tags improve accessibility and SEO.%s', 'wpshadow' ),
				$tag_list
			),
			'severity'     => 'low',
			'threat_level' => 30,
			'auto_fixable' => true,
			'kb_link'      => 'https://wpshadow.com/kb/html-check-if-page-includes-outdated-or-tags',
			'meta'         => array(
				'tags_found'  => $outdated_tags,
				'replacements' => array(
					'<b>' => '<strong>',
					'<i>' => '<em>',
				),
			),
		);
	}
}
