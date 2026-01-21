<?php
/**
 * KB Article Formatter
 *
 * Converts markdown and template data to HTML for KB articles.
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\KnowledgeBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * KB Article Formatter
 */
class KB_Formatter {
	/**
	 * Convert markdown to HTML.
	 *
	 * @param string $markdown Markdown content.
	 * @return string HTML content.
	 */
	public static function markdown_to_html( $markdown ) {
		$html = wp_kses_post( $markdown );

		// Convert markdown headers
		$html = preg_replace( '/^### (.*?)$/m', '<h3>$1</h3>', $html );
		$html = preg_replace( '/^## (.*?)$/m', '<h2>$1</h2>', $html );
		$html = preg_replace( '/^# (.*?)$/m', '<h1>$1</h1>', $html );

		// Convert markdown bold
		$html = preg_replace( '/\*\*(.*?)\*\*/', '<strong>$1</strong>', $html );

		// Convert markdown italic
		$html = preg_replace( '/\*(.*?)\*/', '<em>$1</em>', $html );

		// Convert markdown links
		$html = preg_replace( '/\[(.*?)\]\((.*?)\)/', '<a href="$2">$1</a>', $html );

		// Convert markdown lists
		$html = preg_replace( '/^\- (.*?)$/m', '<li>$1</li>', $html );
		$html = preg_replace( '/(<li>.*?<\/li>)/s', '<ul>$1</ul>', $html );

		// Line breaks
		$html = nl2br( $html );

		return $html;
	}

	/**
	 * Format as HTML article.
	 *
	 * @param array $data Article data.
	 * @return string Complete HTML.
	 */
	public static function format_article( $data ) {
		$title       = isset( $data['title'] ) ? wp_kses_post( $data['title'] ) : '';
		$description = isset( $data['description'] ) ? wp_kses_post( $data['description'] ) : '';
		$content     = isset( $data['content'] ) ? wp_kses_post( $data['content'] ) : '';
		$category    = isset( $data['category'] ) ? wp_kses_post( $data['category'] ) : '';
		$difficulty  = isset( $data['difficulty'] ) ? wp_kses_post( $data['difficulty'] ) : 'Beginner';

		$html = '<article class="wpshadow-kb-article">';
		$html .= '<header class="wpshadow-kb-header">';
		$html .= '<h1>' . $title . '</h1>';
		$html .= '<div class="wpshadow-kb-meta">';
		$html .= '<span class="category">' . $category . '</span>';
		$html .= '<span class="difficulty">' . $difficulty . '</span>';
		$html .= '</div>';
		if ( $description ) {
			$html .= '<p class="description">' . $description . '</p>';
		}
		$html .= '</header>';
		$html .= '<div class="wpshadow-kb-content">';
		$html .= $content;
		$html .= '</div>';
		$html .= '</article>';

		return $html;
	}

	/**
	 * Create a table of contents.
	 *
	 * @param string $html HTML content.
	 * @return array Array of heading data.
	 */
	public static function generate_toc( $html ) {
		$toc = array();

		// Extract headings
		if ( preg_match_all( '/<h([2-3])>(.*?)<\/h[23]>/i', $html, $matches, PREG_OFFSET_CAPTURE ) ) {
			foreach ( $matches[0] as $idx => $match ) {
				$level   = (int) $matches[1][ $idx ][0];
				$title   = wp_strip_all_tags( $matches[2][ $idx ][0] );
				$anchor  = sanitize_title( $title );
				$toc[]   = array(
					'level'  => $level,
					'title'  => $title,
					'anchor' => $anchor,
				);
			}
		}

		return $toc;
	}

	/**
	 * Add anchors to headings for table of contents.
	 *
	 * @param string $html HTML content.
	 * @return string HTML with anchors.
	 */
	public static function add_heading_anchors( $html ) {
		return preg_replace_callback(
			'/<h([2-3])>(.*?)<\/h[23]>/i',
			function ( $matches ) {
				$level  = $matches[1];
				$title  = $matches[2];
				$anchor = sanitize_title( wp_strip_all_tags( $title ) );
				return '<h' . $level . ' id="' . esc_attr( $anchor ) . '">' . $title . '</h' . $level . '>';
			},
			$html
		);
	}
}
