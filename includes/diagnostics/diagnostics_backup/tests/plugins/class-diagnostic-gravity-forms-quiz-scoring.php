<?php
/**
 * Gravity Forms Quiz Scoring Diagnostic
 *
 * Gravity Forms Quiz Scoring issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1193.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gravity Forms Quiz Scoring Diagnostic Class
 *
 * @since 1.1193.0000
 */
class Diagnostic_GravityFormsQuizScoring extends Diagnostic_Base {

	protected static $slug = 'gravity-forms-quiz-scoring';
	protected static $title = 'Gravity Forms Quiz Scoring';
	protected static $description = 'Gravity Forms Quiz Scoring issue found';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'GFForms' ) ) {
			return null;
		}

		$issues = array();

		// Check for quiz forms
		global $wpdb;
		$quiz_forms = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->prefix}gf_form_meta WHERE display_meta LIKE '%gquiz%'"
		);

		if ( $quiz_forms > 0 ) {
			// Check if quiz addon is active
			if ( ! class_exists( 'GFQuiz' ) ) {
				$issues[] = 'quiz forms configured but quiz addon not active';
			}

			// Check for instant feedback setting
			$instant_feedback = get_option( 'gf_quiz_instant_feedback', '1' );
			if ( '0' === $instant_feedback ) {
				$issues[] = 'instant feedback disabled (users wait for results)';
			}

			// Check for grade display configuration
			$show_grades = get_option( 'gf_quiz_show_grades', '1' );
			if ( '0' === $show_grades && $quiz_forms > 0 ) {
				$issues[] = 'grades hidden from users in quiz results';
			}

			// Check for quiz entry storage
			$quiz_entries = $wpdb->get_var(
				"SELECT COUNT(*) FROM {$wpdb->prefix}gf_entry_meta WHERE meta_key LIKE 'gquiz%'"
			);

			if ( $quiz_entries > 5000 ) {
				$issues[] = "large quiz entry database ({$quiz_entries} entries, consider archiving)";
			}

			// Check for answer randomization
			$randomize = get_option( 'gf_quiz_randomize_answers', '0' );
			if ( '0' === $randomize && $quiz_forms > 3 ) {
				$issues[] = 'answer randomization disabled (questions predictable)';
			}

			// Check for passing grade configuration
			$has_passing_grade = $wpdb->get_var(
				"SELECT COUNT(*) FROM {$wpdb->prefix}gf_form_meta
				 WHERE display_meta LIKE '%gquiz%' AND display_meta LIKE '%passPercent%'"
			);

			if ( $has_passing_grade < 1 && $quiz_forms > 0 ) {
				$issues[] = 'no passing grade thresholds configured for quizzes';
			}
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 70, 40 + ( count( $issues ) * 6 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Gravity Forms quiz configuration issues: ' . implode( ', ', $issues ),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/gravity-forms-quiz-scoring',
			);
		}

		return null;
	}
}
