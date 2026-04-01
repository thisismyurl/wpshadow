<?php
/**
 * Diagnostic: No Code Blocks in Technical Content
 *
 * Detects code displayed as plain text instead of properly formatted blocks.
 * Proper code formatting improves UX by 80% for technical content.
 *
 * @package    WPShadow
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
 * Inline Code Not Formatted Diagnostic Class
 *
 * Checks for code formatting in technical posts.
 *
 * Detection methods:
 * - <code> and <pre> tag detection
 * - Code-related keywords
 * - Syntax highlighter plugins
 *
 * @since 0.6093.1200
 */
class Diagnostic_Inline_Code_Not_Formatted extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'inline-code-not-formatted';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'No Code Blocks in Technical Content';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Code in plain text unreadable - Proper blocks = 80% better UX';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'structure';

	/**
	 * Run the diagnostic check.
	 *
	 * Scoring system (4 points):
	 * - 2 points: Syntax highlighter plugin installed
	 * - 1 point: Code blocks found in technical posts
	 * - 1 point: <20% technical posts lack code blocks
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$score           = 0;
		$max_score       = 4;
		$has_highlighter = false;

		// Check for syntax highlighter plugins.
		$highlighter_plugins = array(
			'syntaxhighlighter/syntaxhighlighter.php' => 'SyntaxHighlighter Evolved',
			'crayon-syntax-highlighter/crayon_wp.php' => 'Crayon Syntax Highlighter',
			'prismatic/prismatic.php'                 => 'Prismatic',
			'wp-code-highlight/wp-code-highlight.php' => 'WP Code Highlight',
		);

		foreach ( $highlighter_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$score          += 2;
				$has_highlighter = true;
				break;
			}
		}

		// Identify technical posts.
		$technical_keywords = array(
			'code',
			'function',
			'script',
			'css',
			'javascript',
			'php',
			'python',
			'tutorial',
			'developer',
			'programming',
		);

		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'posts_per_page' => 50,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		if ( empty( $posts ) ) {
			return null;
		}

		$technical_posts        = 0;
		$technical_without_code = 0;
		$problem_posts          = array();

		foreach ( $posts as $post ) {
			$content = strtolower( $post->post_content . ' ' . $post->post_title );

			// Check if post is technical.
			$is_technical = false;
			foreach ( $technical_keywords as $keyword ) {
				if ( strpos( $content, $keyword ) !== false ) {
					$is_technical = true;
					break;
				}
			}

			if ( ! $is_technical ) {
				continue;
			}

			++$technical_posts;

			// Check for code formatting.
			$has_code_blocks = false;

			// Check for <pre>, <code>, or code shortcodes.
			if (
				strpos( $content, '<pre' ) !== false ||
				strpos( $content, '<code' ) !== false ||
				strpos( $content, '[code' ) !== false ||
				strpos( $content, '```' ) !== false
			) {
				$has_code_blocks = true;
			}

			if ( ! $has_code_blocks ) {
				++$technical_without_code;
				if ( count( $problem_posts ) < 10 ) {
					$problem_posts[] = array(
						'post_id' => $post->ID,
						'title'   => $post->post_title,
						'url'     => get_permalink( $post->ID ),
					);
				}
			}
		}

		if ( 0 === $technical_posts ) {
			// No technical posts to check.
			return null;
		}

		$missing_code_percentage = ( $technical_without_code / $technical_posts ) * 100;

		// Scoring.
		if ( $missing_code_percentage < 20 ) {
			$score += 2;
		} elseif ( $missing_code_percentage < 40 ) {
			++$score;
		}

		// Pass if score is high.
		if ( $score >= ( $max_score * 0.7 ) ) {
			return null;
		}

		return array(
			'id'             => self::$slug,
			'title'          => self::$title,
			'description'    => sprintf(
				/* translators: 1: percentage, 2: number without code blocks, 3: total technical posts */
				__( '%1$d%% of technical posts (%2$d/%3$d) lack proper code formatting. Unformatted code causes: 80%% reduced readability (plain text = visual chaos), Copy-paste errors (smart quotes break code), Professional appearance loss (looks amateur), Syntax confusion (no color-coding = hard to parse), Mobile horror (code wraps badly). Proper formatting provides: Syntax highlighting (language-specific colors), Line numbers (reference specific lines), Copy button (one-click copy), Line wrapping control (horizontal scroll for long lines), Theme options (dark/light modes). Use <pre><code> tags or syntax highlighter plugins. Popular: Prism.js, SyntaxHighlighter, Crayon. Format inline code with <code> tags, blocks with <pre><code>.', 'wpshadow' ),
				round( $missing_code_percentage ),
				$technical_without_code,
				$technical_posts
			),
			'severity'       => 'medium',
			'threat_level'   => 30,
			'auto_fixable'   => false,
			'kb_link'        => 'https://wpshadow.com/kb/inline-code-not-formatted?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'problem_posts'  => $problem_posts,
			'stats'          => array(
				'technical_posts'     => $technical_posts,
				'without_code_blocks' => $technical_without_code,
				'percentage'          => round( $missing_code_percentage, 1 ),
				'has_highlighter'     => $has_highlighter,
			),
			'recommendation' => __( 'Install syntax highlighter plugin (Prismatic, SyntaxHighlighter). Wrap inline code with <code> tags. Use <pre><code> for code blocks. Enable copy button. Choose readable color scheme. Test on mobile.', 'wpshadow' ),
		);
	}
}
