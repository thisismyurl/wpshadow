<?php
/**
 * Exit Survey Builder
 *
 * Generates personalized survey questions based on user context and exit reason.
 * Creates dynamic surveys tailored to:
 * - Competitor analysis (if they mentioned switching to another plugin)
 * - Feature needs assessment (if they left due to missing features)
 * - General feedback (catch-all for other exit reasons)
 *
 * Philosophy Alignment:
 * - Commandment #1: Helpful Neighbor - Questions are genuine and conversational
 * - Commandment #4: Advice Not Sales - Focus on learning, not persuasion
 * - Commandment #11: Talk-About-Worthy - Insightful questions that show we care
 *
 * @since   1.6030.2148
 * @package WPShadow\Engagement
 */

declare(strict_types=1);

namespace WPShadow\Engagement;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Exit Survey Builder Class
 *
 * Builds personalized survey questions based on exit interview context.
 */
class Exit_Survey_Builder {

	/**
	 * Survey question types
	 */
	const TYPE_MULTIPLE_CHOICE = 'multiple_choice';
	const TYPE_TEXT            = 'text';
	const TYPE_RATING          = 'rating';
	const TYPE_YES_NO          = 'yes_no';

	/**
	 * Build survey questions based on survey type and context.
	 *
	 * @since  1.6030.2148
	 * @param  string $survey_type Survey type: 'competitor_analysis', 'feature_needs', or 'general_followup'.
	 * @param  array  $context     Exit interview context data.
	 * @return array Array of survey questions.
	 */
	public static function build_survey( $survey_type, $context ) {
		switch ( $survey_type ) {
			case 'competitor_analysis':
				return self::build_competitor_survey( $context );

			case 'feature_needs':
				return self::build_feature_needs_survey( $context );

			case 'general_followup':
				return self::build_general_followup_survey( $context );

			default:
				return self::build_general_followup_survey( $context );
		}
	}

	/**
	 * Build competitor analysis survey.
	 *
	 * Focuses on understanding why they chose a competitor and what we can learn.
	 *
	 * @since  1.6030.2148
	 * @param  array $context Exit interview context.
	 * @return array Survey questions.
	 */
	private static function build_competitor_survey( $context ) {
		$questions   = array();
		$competitor  = ! empty( $context['competitor_name'] ) ? $context['competitor_name'] : __( 'the other plugin', 'wpshadow' );
		$usage_days  = ! empty( $context['usage_duration_days'] ) ? $context['usage_duration_days'] : 0;
		$usage_label = self::get_usage_duration_label( $usage_days );

		// Question 1: How's the competitor working out?
		$questions[] = array(
			'id'       => 'competitor_satisfaction',
			'type'     => self::TYPE_RATING,
			'question' => sprintf(
				/* translators: 1: competitor name, 2: usage duration label */
				__( 'You mentioned switching to %1$s after using WPShadow for %2$s. How\'s it working out so far?', 'wpshadow' ),
				$competitor,
				$usage_label
			),
			'scale'    => 5,
			'labels'   => array(
				1 => __( 'Not well', 'wpshadow' ),
				5 => __( 'Much better', 'wpshadow' ),
			),
			'required' => true,
		);

		// Question 2: What made the difference?
		$questions[] = array(
			'id'          => 'key_differences',
			'type'        => self::TYPE_TEXT,
			'question'    => sprintf(
				/* translators: %s: competitor name */
				__( 'What does %s do better than WPShadow? (This genuinely helps us improve)', 'wpshadow' ),
				$competitor
			),
			'placeholder' => __( 'Be as specific as you\'d like...', 'wpshadow' ),
			'required'    => false,
		);

		// Question 3: Missing features
		$questions[] = array(
			'id'             => 'missing_features',
			'type'           => self::TYPE_MULTIPLE_CHOICE,
			'question'       => __( 'Which of these would have kept you with WPShadow?', 'wpshadow' ),
			'options'        => array(
				'better_performance' => __( 'Better performance/speed', 'wpshadow' ),
				'more_features'      => __( 'More features', 'wpshadow' ),
				'easier_ui'          => __( 'Easier to use interface', 'wpshadow' ),
				'better_support'     => __( 'Better support/documentation', 'wpshadow' ),
				'lower_price'        => __( 'Lower price', 'wpshadow' ),
				'better_integration' => __( 'Better integration with other tools', 'wpshadow' ),
				'other'              => __( 'Something else', 'wpshadow' ),
			),
			'allow_multiple' => true,
			'required'       => false,
		);

		// Question 4: Would you recommend us for specific use cases?
		$questions[] = array(
			'id'          => 'would_recommend',
			'type'        => self::TYPE_TEXT,
			'question'    => __( 'Is there a specific type of website or use case where you\'d still recommend WPShadow?', 'wpshadow' ),
			'placeholder' => __( 'e.g., "Great for small blogs" or "Good for beginners"', 'wpshadow' ),
			'required'    => false,
		);

		// Question 5: Can we reach out with updates?
		$questions[] = array(
			'id'       => 'stay_in_touch',
			'type'     => self::TYPE_YES_NO,
			'question' => __( 'If we address the issues you mentioned, would you like us to let you know?', 'wpshadow' ),
			'required' => false,
		);

		return $questions;
	}

	/**
	 * Build feature needs assessment survey.
	 *
	 * Focuses on understanding what features they needed but couldn't find.
	 *
	 * @since  1.6030.2148
	 * @param  array $context Exit interview context.
	 * @return array Survey questions.
	 */
	private static function build_feature_needs_survey( $context ) {
		$questions       = array();
		$features_needed = ! empty( $context['features_needed'] ) ? $context['features_needed'] : '';

		// Question 1: Did you find the feature elsewhere?
		$questions[] = array(
			'id'       => 'found_alternative',
			'type'     => self::TYPE_YES_NO,
			'question' => __( 'Did you find another solution that has the features you needed?', 'wpshadow' ),
			'required' => true,
		);

		// Question 2: What was the feature?
		if ( empty( $features_needed ) ) {
			$questions[] = array(
				'id'          => 'specific_features',
				'type'        => self::TYPE_TEXT,
				'question'    => __( 'Can you describe the specific features you were looking for?', 'wpshadow' ),
				'placeholder' => __( 'The more detail, the better...', 'wpshadow' ),
				'required'    => false,
			);
		} else {
			$questions[] = array(
				'id'       => 'feature_importance',
				'type'     => self::TYPE_RATING,
				'question' => __( 'How critical was this feature to your workflow?', 'wpshadow' ),
				'scale'    => 5,
				'labels'   => array(
					1 => __( 'Nice to have', 'wpshadow' ),
					5 => __( 'Absolutely essential', 'wpshadow' ),
				),
				'required' => true,
			);
		}

		// Question 3: Feature priority
		$questions[] = array(
			'id'             => 'feature_priorities',
			'type'           => self::TYPE_MULTIPLE_CHOICE,
			'question'       => __( 'Which types of features matter most to your workflow?', 'wpshadow' ),
			'options'        => array(
				'security'    => __( 'Security features', 'wpshadow' ),
				'performance' => __( 'Performance optimization', 'wpshadow' ),
				'analytics'   => __( 'Analytics and reporting', 'wpshadow' ),
				'automation'  => __( 'Automation and workflows', 'wpshadow' ),
				'backup'      => __( 'WPShadow Vault Light', 'wpshadow' ),
				'seo'         => __( 'SEO tools', 'wpshadow' ),
				'integration' => __( 'Third-party integrations', 'wpshadow' ),
				'other'       => __( 'Other', 'wpshadow' ),
			),
			'allow_multiple' => true,
			'required'       => false,
		);

		// Question 4: Workaround attempts
		$questions[] = array(
			'id'          => 'workaround_tried',
			'type'        => self::TYPE_TEXT,
			'question'    => __( 'Did you try any workarounds before deciding to leave? What did you try?', 'wpshadow' ),
			'placeholder' => __( 'This helps us understand pain points...', 'wpshadow' ),
			'required'    => false,
		);

		// Question 5: Would feature implementation bring you back?
		$questions[] = array(
			'id'       => 'would_return',
			'type'     => self::TYPE_YES_NO,
			'question' => __( 'If we implemented the features you need, would you consider coming back?', 'wpshadow' ),
			'required' => false,
		);

		return $questions;
	}

	/**
	 * Build general followup survey.
	 *
	 * Generic survey for users who left for other reasons or long-term followup.
	 *
	 * @since  1.6030.2148
	 * @param  array $context Exit interview context.
	 * @return array Survey questions.
	 */
	private static function build_general_followup_survey( $context ) {
		$questions  = array();
		$usage_days = ! empty( $context['usage_duration_days'] ) ? $context['usage_duration_days'] : 0;

		// Question 1: Overall experience
		$questions[] = array(
			'id'       => 'overall_experience',
			'type'     => self::TYPE_RATING,
			'question' => __( 'Looking back, how would you rate your overall experience with WPShadow?', 'wpshadow' ),
			'scale'    => 5,
			'labels'   => array(
				1 => __( 'Poor', 'wpshadow' ),
				5 => __( 'Excellent', 'wpshadow' ),
			),
			'required' => true,
		);

		// Question 2: What worked well?
		$questions[] = array(
			'id'          => 'what_worked_well',
			'type'        => self::TYPE_TEXT,
			'question'    => __( 'What did WPShadow do well? (We\'d love to hear what we should keep doing)', 'wpshadow' ),
			'placeholder' => __( 'Any features or aspects you appreciated...', 'wpshadow' ),
			'required'    => false,
		);

		// Question 3: What could be better?
		$questions[] = array(
			'id'          => 'what_could_improve',
			'type'        => self::TYPE_TEXT,
			'question'    => __( 'What could we have done better?', 'wpshadow' ),
			'placeholder' => __( 'Honest feedback helps us improve...', 'wpshadow' ),
			'required'    => false,
		);

		// Question 4: Current solution
		$questions[] = array(
			'id'          => 'current_solution',
			'type'        => self::TYPE_TEXT,
			'question'    => __( 'What are you using now instead of WPShadow (if anything)?', 'wpshadow' ),
			'placeholder' => __( 'Plugin name, manual process, nothing, etc.', 'wpshadow' ),
			'required'    => false,
		);

		// Question 5: Would you recommend to others?
		$questions[] = array(
			'id'       => 'would_recommend_to_others',
			'type'     => self::TYPE_RATING,
			'question' => __( 'How likely would you be to recommend WPShadow to a friend with a similar website?', 'wpshadow' ),
			'scale'    => 10,
			'labels'   => array(
				0  => __( 'Not at all likely', 'wpshadow' ),
				10 => __( 'Extremely likely', 'wpshadow' ),
			),
			'required' => false,
		);

		// Question 6: Any resources we could share?
		$questions[] = array(
			'id'          => 'helpful_resources',
			'type'        => self::TYPE_TEXT,
			'question'    => __( 'Is there anything we could help you with, even if you\'re not using WPShadow? (tutorials, recommendations, etc.)', 'wpshadow' ),
			'placeholder' => __( 'We genuinely want to help you succeed...', 'wpshadow' ),
			'required'    => false,
		);

		return $questions;
	}

	/**
	 * Get human-readable usage duration label.
	 *
	 * @since  1.6030.2148
	 * @param  int $days Number of days.
	 * @return string Human-readable label.
	 */
	private static function get_usage_duration_label( $days ) {
		if ( $days < 7 ) {
			return __( 'a few days', 'wpshadow' );
		} elseif ( $days < 30 ) {
			return sprintf(
				/* translators: %d: number of weeks */
				_n( '%d week', '%d weeks', ceil( $days / 7 ), 'wpshadow' ),
				ceil( $days / 7 )
			);
		} elseif ( $days < 365 ) {
			return sprintf(
				/* translators: %d: number of months */
				_n( '%d month', '%d months', ceil( $days / 30 ), 'wpshadow' ),
				ceil( $days / 30 )
			);
		} else {
			return sprintf(
				/* translators: %d: number of years */
				_n( '%d year', '%d years', ceil( $days / 365 ), 'wpshadow' ),
				ceil( $days / 365 )
			);
		}
	}

	/**
	 * Validate survey responses.
	 *
	 * @since  1.6030.2148
	 * @param  array $questions Survey questions.
	 * @param  array $responses User responses.
	 * @return array {
	 *     Validation result.
	 *
	 *     @type bool   $valid   Whether responses are valid.
	 *     @type array  $errors  Array of validation errors.
	 * }
	 */
	public static function validate_responses( $questions, $responses ) {
		$errors = array();

		foreach ( $questions as $question ) {
			$question_id = $question['id'];
			$response    = isset( $responses[ $question_id ] ) ? $responses[ $question_id ] : null;

			// Check required fields
			if ( ! empty( $question['required'] ) && empty( $response ) ) {
				$errors[ $question_id ] = sprintf(
					/* translators: %s: question text */
					__( 'Please answer: %s', 'wpshadow' ),
					$question['question']
				);
				continue;
			}

			// Skip validation for optional unanswered questions
			if ( empty( $response ) ) {
				continue;
			}

			// Type-specific validation
			switch ( $question['type'] ) {
				case self::TYPE_RATING:
					$scale = isset( $question['scale'] ) ? $question['scale'] : 5;
					if ( ! is_numeric( $response ) || $response < 1 || $response > $scale ) {
						$errors[ $question_id ] = sprintf(
							/* translators: 1: minimum value, 2: maximum value */
							__( 'Rating must be between %1$d and %2$d', 'wpshadow' ),
							1,
							$scale
						);
					}
					break;

				case self::TYPE_MULTIPLE_CHOICE:
					if ( ! is_array( $response ) ) {
						$response = array( $response );
					}
					$valid_options = array_keys( $question['options'] );
					foreach ( $response as $value ) {
						if ( ! in_array( $value, $valid_options, true ) ) {
							$errors[ $question_id ] = __( 'Invalid option selected', 'wpshadow' );
							break;
						}
					}
					break;

				case self::TYPE_YES_NO:
					if ( ! in_array( $response, array( 'yes', 'no' ), true ) ) {
						$errors[ $question_id ] = __( 'Please answer yes or no', 'wpshadow' );
					}
					break;
			}
		}

		return array(
			'valid'  => empty( $errors ),
			'errors' => $errors,
		);
	}
}
