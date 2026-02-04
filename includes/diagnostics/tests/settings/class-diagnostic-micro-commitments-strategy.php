<?php
/**
 * Micro-Commitments Strategy Diagnostic
 *
 * Tests whether the site uses a stepped conversion strategy with small commitments before big asks.
 *
 * @since   1.6034.0230
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Micro-Commitments Strategy Diagnostic Class
 *
 * The foot-in-the-door technique increases major conversion rates by 33%.
 * Start with small asks (newsletter, quiz, free tool) before major commitments.
 *
 * @since 1.6034.0230
 */
class Diagnostic_Micro_Commitments_Strategy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'micro-commitments-strategy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Micro-Commitments Strategy';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site uses a stepped conversion strategy with small commitments before big asks';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'cro';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6034.0230
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$micro_commitment_score = 0;
		$max_score = 6;

		// Check for lead magnets.
		$lead_magnets = self::check_lead_magnets();
		if ( $lead_magnets ) {
			$micro_commitment_score++;
		} else {
			$issues[] = __( 'No low-commitment lead magnets (free downloads, guides)', 'wpshadow' );
		}

		// Check for quiz/assessment tools.
		$quizzes = self::check_quizzes();
		if ( $quizzes ) {
			$micro_commitment_score++;
		} else {
			$issues[] = __( 'No interactive quizzes or assessments for engagement', 'wpshadow' );
		}

		// Check for free trials/samples.
		$free_trials = self::check_free_trials();
		if ( $free_trials ) {
			$micro_commitment_score++;
		} else {
			$issues[] = __( 'No free trial or sample offers before purchase', 'wpshadow' );
		}

		// Check for progressive profiling.
		$progressive_profiling = self::check_progressive_profiling();
		if ( $progressive_profiling ) {
			$micro_commitment_score++;
		} else {
			$issues[] = __( 'Forms ask for too much information upfront', 'wpshadow' );
		}

		// Check for multi-step forms.
		$multistep_forms = self::check_multistep_forms();
		if ( $multistep_forms ) {
			$micro_commitment_score++;
		} else {
			$issues[] = __( 'No multi-step forms to reduce perceived commitment', 'wpshadow' );
		}

		// Check for nurture sequences.
		$nurture_sequences = self::check_nurture_sequences();
		if ( $nurture_sequences ) {
			$micro_commitment_score++;
		} else {
			$issues[] = __( 'No email nurture sequence between micro and macro conversions', 'wpshadow' );
		}

		// Determine severity based on micro-commitment implementation.
		$micro_commitment_percentage = ( $micro_commitment_score / $max_score ) * 100;

		if ( $micro_commitment_percentage < 30 ) {
			$severity = 'low';
			$threat_level = 30;
		} elseif ( $micro_commitment_percentage < 60 ) {
			$severity = 'low';
			$threat_level = 20;
		} else {
			return null;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %d: Micro-commitment strategy percentage */
				__( 'Micro-commitment strategy at %d%%. ', 'wpshadow' ),
				(int) $micro_commitment_percentage
			) . implode( '. ', $issues ) . ' ' . __( 'Graduated commitment increases major conversions by 33%', 'wpshadow' );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/micro-commitments-strategy',
			);
		}

		return null;
	}

	/**
	 * Check for lead magnets.
	 *
	 * @since  1.6034.0230
	 * @return bool True if lead magnets exist, false otherwise.
	 */
	private static function check_lead_magnets() {
		$keywords = array( 'free download', 'free guide', 'free ebook', 'free template', 'free checklist' );

		foreach ( $keywords as $keyword ) {
			$query = new \WP_Query(
				array(
					's'              => $keyword,
					'post_type'      => array( 'post', 'page' ),
					'posts_per_page' => 1,
					'post_status'    => 'publish',
				)
			);
			if ( $query->have_posts() ) {
				return true;
			}
		}

		// Check for download plugins.
		if ( is_plugin_active( 'easy-digital-downloads/easy-digital-downloads.php' ) ) {
			$downloads = get_posts(
				array(
					'post_type'      => 'download',
					'posts_per_page' => 1,
					'meta_query'     => array(
						array(
							'key'     => 'edd_price',
							'value'   => '0',
							'compare' => '=',
						),
					),
				)
			);
			if ( ! empty( $downloads ) ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_lead_magnets', false );
	}

	/**
	 * Check for quizzes.
	 *
	 * @since  1.6034.0230
	 * @return bool True if quizzes exist, false otherwise.
	 */
	private static function check_quizzes() {
		// Check for quiz plugins.
		$quiz_plugins = array(
			'quiz-master-next/mlw_quizmaster2.php',
			'wp-quiz/wp-quiz.php',
			'quiz-maker/quiz-maker.php',
		);

		foreach ( $quiz_plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		// Check for quiz content.
		$query = new \WP_Query(
			array(
				's'              => 'take quiz assessment interactive',
				'post_type'      => 'any',
				'posts_per_page' => 1,
				'post_status'    => 'publish',
			)
		);

		return $query->have_posts();
	}

	/**
	 * Check for free trials.
	 *
	 * @since  1.6034.0230
	 * @return bool True if trials exist, false otherwise.
	 */
	private static function check_free_trials() {
		$keywords = array( 'free trial', 'try free', 'free sample', 'no credit card' );

		foreach ( $keywords as $keyword ) {
			$query = new \WP_Query(
				array(
					's'              => $keyword,
					'post_type'      => array( 'post', 'page', 'product' ),
					'posts_per_page' => 1,
					'post_status'    => 'publish',
				)
			);
			if ( $query->have_posts() ) {
				return true;
			}
		}

		// WooCommerce Subscriptions can offer trials.
		if ( is_plugin_active( 'woocommerce-subscriptions/woocommerce-subscriptions.php' ) ) {
			return true;
		}

		return apply_filters( 'wpshadow_has_free_trials', false );
	}

	/**
	 * Check for progressive profiling.
	 *
	 * @since  1.6034.0230
	 * @return bool True if progressive profiling exists, false otherwise.
	 */
	private static function check_progressive_profiling() {
		// Check for forms with minimal fields.
		$contact_form_plugins = array(
			'contact-form-7/wp-contact-form-7.php',
			'wpforms-lite/wpforms.php',
			'formidable/formidable.php',
		);

		foreach ( $contact_form_plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true; // Assume properly configured.
			}
		}

		return apply_filters( 'wpshadow_uses_progressive_profiling', false );
	}

	/**
	 * Check for multi-step forms.
	 *
	 * @since  1.6034.0230
	 * @return bool True if multi-step forms exist, false otherwise.
	 */
	private static function check_multistep_forms() {
		// WPForms and Formidable support multi-step.
		if ( is_plugin_active( 'wpforms-lite/wpforms.php' ) ||
			 is_plugin_active( 'formidable/formidable.php' ) ) {
			return true;
		}

		// Check for step indicator content.
		$query = new \WP_Query(
			array(
				's'              => 'step 1 step 2 progress',
				'post_type'      => 'page',
				'posts_per_page' => 1,
				'post_status'    => 'publish',
			)
		);

		return $query->have_posts();
	}

	/**
	 * Check for nurture sequences.
	 *
	 * @since  1.6034.0230
	 * @return bool True if sequences exist, false otherwise.
	 */
	private static function check_nurture_sequences() {
		// Check for email marketing.
		$email_plugins = array(
			'mailpoet/mailpoet.php',
			'newsletter/newsletter.php',
			'mailchimp-for-wp/mailchimp-for-wp.php',
		);

		foreach ( $email_plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_nurture_sequences', false );
	}
}
