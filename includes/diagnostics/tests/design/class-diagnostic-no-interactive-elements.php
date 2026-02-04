<?php
/**
 * Diagnostic: No Interactive Elements
 *
 * Detects lack of interactive elements (polls, quizzes, calculators) which
 * provide only passive reading experience. Interactive content increases
 * engagement by 70% and time on page by 200%.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.7030.1451
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * No Interactive Elements Diagnostic Class
 *
 * Checks for interactive content via plugins and content analysis.
 *
 * Detection methods:
 * - Interactive plugin detection (polls, quizzes, calculators)
 * - Interactive shortcodes in content
 * - Interactive elements in posts
 *
 * @since 1.7030.1451
 */
class Diagnostic_No_Interactive_Elements extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-interactive-elements';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'No Interactive Elements';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'No polls/quizzes/calculators = passive experience only';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'user-engagement';

	/**
	 * Run the diagnostic check.
	 *
	 * Scoring system (4 points):
	 * - 2 points: Interactive plugin active
	 * - 1 point: Interactive elements found in content
	 * - 1 point: Multiple types of interactive content
	 *
	 * @since  1.7030.1451
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$score              = 0;
		$max_score          = 4;
		$has_plugin         = false;
		$has_interactive    = false;
		$interactive_types  = array();
		$active_plugins     = array();

		// Check for interactive plugins.
		$interactive_plugins = array(
			'quiz-master-next/mlw_quizmaster2.php'      => 'Quiz Maker',
			'wp-polls/wp-polls.php'                     => 'WP-Polls',
			'formidable/formidable.php'                 => 'Formidable Forms (calculators)',
			'interactive-geo-maps/interactive-geo-maps.php' => 'Interactive Geo Maps',
			'h5p/h5p.php'                               => 'H5P (interactive content)',
			'thrive-quiz-builder/thrive-quiz-builder.php' => 'Thrive Quiz Builder',
		);

		foreach ( $interactive_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$score         += 2;
				$has_plugin     = true;
				$active_plugins[] = $name;

				// Determine type.
				if ( stripos( $name, 'quiz' ) !== false ) {
					$interactive_types[] = 'quiz';
				} elseif ( stripos( $name, 'poll' ) !== false ) {
					$interactive_types[] = 'poll';
				} elseif ( stripos( $name, 'calculator' ) !== false || stripos( $name, 'formidable' ) !== false ) {
					$interactive_types[] = 'calculator';
				} else {
					$interactive_types[] = 'other';
				}
				break;
			}
		}

		// Check for interactive shortcodes in content.
		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'posts_per_page' => 50,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		$posts_with_interactive = 0;
		$interactive_patterns = array(
			'[poll'        => 'poll',
			'[quiz'        => 'quiz',
			'[survey'      => 'survey',
			'[calculator'  => 'calculator',
			'[h5p'         => 'interactive',
			'[formidable'  => 'form/calculator',
		);

		foreach ( $posts as $post ) {
			$content = $post->post_content;
			foreach ( $interactive_patterns as $pattern => $type ) {
				if ( stripos( $content, $pattern ) !== false ) {
					$posts_with_interactive++;
					$has_interactive = true;
					if ( ! in_array( $type, $interactive_types, true ) ) {
						$interactive_types[] = $type;
					}
					break;
				}
			}
		}

		if ( $has_interactive ) {
			$score++;
		}

		if ( count( $interactive_types ) > 1 ) {
			$score++;
		}

		// Pass if score is high.
		if ( $score >= ( $max_score * 0.7 ) ) {
			return null;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: 1: number of posts checked, 2: number with interactive elements */
				__( 'Found %2$d posts with interactive elements out of %1$d checked. Interactive content (polls, quizzes, calculators, surveys) increases engagement by 70%%, time on page by 200%%, and social shares by 90%%. Benefits: Higher engagement signals (ranking factor), more shares (backlinks), lead generation (quiz results for email), memorability. Types: Quizzes (personality, knowledge), Polls (opinions, predictions), Calculators (ROI, savings, cost), Assessments (skill level), Interactive infographics. Best for: Educational content, tools/resources pages, cornerstone content.', 'wpshadow' ),
				count( $posts ),
				$posts_with_interactive
			),
			'severity'    => 'low',
			'threat_level' => 20,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/no-interactive-elements',
			'stats'       => array(
				'has_plugin'         => $has_plugin,
				'posts_checked'      => count( $posts ),
				'posts_interactive'  => $posts_with_interactive,
				'interactive_types'  => $interactive_types,
				'active_plugins'     => $active_plugins,
			),
			'recommendation' => __( 'Install Quiz Maker or WP-Polls. Add interactive elements to cornerstone content. Start with simple polls (opinions), progress to quizzes (lead generation). Use quiz results as email opt-in incentive. Test engagement metrics before/after.', 'wpshadow' ),
		);
	}
}
