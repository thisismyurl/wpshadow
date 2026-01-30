<?php
/**
 * HTML Check If Page Includes Excessive Strong Or Em Usage Diagnostic
 *
 * Detects excessive use of semantic emphasis tags.
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
 * HTML Check If Page Includes Excessive Strong Or Em Usage Diagnostic Class
 *
 * Identifies pages with excessive <strong> and <em> tags, which dilutes
 * their semantic importance and may signal SEO manipulation.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Html_Check_If_Page_Includes_Excessive_Or_Usage extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'html-check-if-page-includes-excessive-or-usage';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Excessive Emphasis Tag Usage';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects excessive <strong> or <em> tag usage';

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

		// Check post content.
		global $post;

		if ( empty( $post ) || ! ( $post instanceof \WP_Post ) ) {
			return null;
		}

		$content = $post->post_content;

		// Count <strong> and <em> tags.
		$strong_count = substr_count( strtolower( $content ), '<strong>' );
		$em_count     = substr_count( strtolower( $content ), '<em>' );

		$total_emphasis = $strong_count + $em_count;

		// If content is short, emphasis percentage matters more than absolute count.
		$word_count   = str_word_count( wp_strip_all_tags( $content ) );
		$emphasis_ratio = $word_count > 0 ? $total_emphasis / $word_count : 0;

		// Flag if more than 5% of content is emphasized (excessive).
		if ( $emphasis_ratio > 0.05 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: strong count, 2: em count, 3: percentage */
					__( 'Found excessive emphasis tags: %1$d <strong> tags and %2$d <em> tags (~%3$d%% of content). Emphasis should be used sparingly to highlight key terms. Overuse dilutes their importance and may appear manipulative to search engines. Use emphasis only for truly important terms.', 'wpshadow' ),
					$strong_count,
					$em_count,
					intval( $emphasis_ratio * 100 )
				),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/html-check-if-page-includes-excessive-or-usage',
				'meta'         => array(
					'strong_count'     => $strong_count,
					'em_count'         => $em_count,
					'total_emphasis'   => $total_emphasis,
					'word_count'       => $word_count,
					'emphasis_ratio'   => round( $emphasis_ratio, 4 ),
				),
			);
		}

		return null;
	}
}
