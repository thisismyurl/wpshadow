<?php
/**
 * Exit Followup Manager
 *
 * Manages exit interview followup scheduling and personalized survey generation.
 * When users allow contact during deactivation, this system schedules appropriate
 * followup timing and generates customized questions based on their usage context.
 *
 * Philosophy Alignment:
 * - Commandment #1: Helpful Neighbor - Genuinely curious about user needs, not pushy
 * - Commandment #4: Advice Not Sales - Focus on learning what they needed
 * - Commandment #6: Drive to Free Training - Help them succeed even if they left
 * - Commandment #10: Beyond Pure Privacy - Explicit consent, easy opt-out, GDPR compliant
 *
 * @since   1.2601.2148
 * @package WPShadow\Engagement
 */

declare(strict_types=1);

namespace WPShadow\Engagement;

use WPShadow\Core\Activity_Logger;
use WPShadow\Core\Error_Handler;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Exit Followup Manager Class
 *
 * Handles scheduling and management of exit interview followups.
 */
class Exit_Followup_Manager {

	/**
	 * Followup timing constants (in days)
	 */
	const TIMING_IMMEDIATE  = 3;  // Quick wins, competitor intel
	const TIMING_SHORT_TERM = 14; // Feature needs assessment
	const TIMING_LONG_TERM  = 30; // Competitive analysis

	/**
	 * Followup types
	 */
	const TYPE_IMMEDIATE  = 'immediate';
	const TYPE_SHORT_TERM = 'short_term';
	const TYPE_LONG_TERM  = 'long_term';

	/**
	 * Followup status
	 */
	const STATUS_PENDING   = 'pending';
	const STATUS_SENT      = 'sent';
	const STATUS_COMPLETED = 'completed';
	const STATUS_CANCELLED = 'cancelled';

	/**
	 * Record exit interview with contact permission.
	 *
	 * @since  1.2601.2148
	 * @param  array $data {
	 *     Exit interview data.
	 *
	 *     @type int    $user_id            WordPress user ID.
	 *     @type string $exit_reason        Reason for leaving.
	 *     @type string $detailed_feedback  Detailed feedback text.
	 *     @type string $competitor_name    Competitor plugin name (if applicable).
	 *     @type string $features_needed    Features they needed but we lacked.
	 *     @type bool   $contact_allowed    Permission to follow up.
	 *     @type string $contact_email      Email for followup contact.
	 *     @type int    $usage_duration_days How long they used the plugin.
	 *     @type array  $features_used      List of features they used.
	 *     @type string $site_type          Type of website (blog, ecommerce, etc).
	 * }
	 * @return int|false Interview ID on success, false on failure.
	 */
	public static function record_exit_interview( $data ) {
		global $wpdb;

		// Validate required fields
		if ( empty( $data['user_id'] ) ) {
			Error_Handler::log_error( 'Exit interview missing user_id' );
			return false;
		}

		$table = $wpdb->prefix . 'wpshadow_exit_interviews';

		// Prepare data for insertion
		$insert_data = array(
			'user_id'             => absint( $data['user_id'] ),
			'site_url'            => esc_url_raw( get_site_url() ),
			'exit_date'           => current_time( 'mysql' ),
			'exit_reason'         => isset( $data['exit_reason'] ) ? sanitize_text_field( $data['exit_reason'] ) : '',
			'detailed_feedback'   => isset( $data['detailed_feedback'] ) ? sanitize_textarea_field( $data['detailed_feedback'] ) : '',
			'competitor_name'     => isset( $data['competitor_name'] ) ? sanitize_text_field( $data['competitor_name'] ) : '',
			'features_needed'     => isset( $data['features_needed'] ) ? sanitize_textarea_field( $data['features_needed'] ) : '',
			'contact_allowed'     => isset( $data['contact_allowed'] ) ? (int) $data['contact_allowed'] : 0,
			'contact_email'       => isset( $data['contact_email'] ) ? sanitize_email( $data['contact_email'] ) : '',
			'usage_duration_days' => isset( $data['usage_duration_days'] ) ? absint( $data['usage_duration_days'] ) : 0,
			'features_used'       => isset( $data['features_used'] ) ? wp_json_encode( $data['features_used'] ) : '',
			'site_type'           => isset( $data['site_type'] ) ? sanitize_text_field( $data['site_type'] ) : '',
		);

		// Insert into database
		$result = $wpdb->insert(
			$table,
			$insert_data,
			array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%d', '%s', '%s' )
		);

		if ( false === $result ) {
			Error_Handler::log_error(
				'Failed to insert exit interview',
				array(
					'error' => $wpdb->last_error,
				)
			);
			return false;
		}

		$interview_id = $wpdb->insert_id;

		// Log to activity
		Activity_Logger::log(
			'exit_interview_recorded',
			array(
				'interview_id'    => $interview_id,
				'user_id'         => $insert_data['user_id'],
				'contact_allowed' => $insert_data['contact_allowed'],
				'exit_reason'     => $insert_data['exit_reason'],
			)
		);

		// Schedule followups if contact allowed
		if ( ! empty( $insert_data['contact_allowed'] ) ) {
			self::schedule_followups( $interview_id, $insert_data );
		}

		return $interview_id;
	}

	/**
	 * Schedule followup contacts based on exit interview context.
	 *
	 * Determines optimal timing and survey questions based on:
	 * - Exit reason
	 * - Competitor mentioned
	 * - Features needed
	 * - Usage duration
	 *
	 * @since  1.2601.2148
	 * @param  int   $interview_id Exit interview ID.
	 * @param  array $context      Exit interview context data.
	 * @return array Array of followup IDs created.
	 */
	public static function schedule_followups( $interview_id, $context ) {
		$followup_ids = array();

		// Determine which followups to schedule based on context
		$followup_plan = self::determine_followup_plan( $context );

		foreach ( $followup_plan as $followup ) {
			$followup_id = self::create_followup( $interview_id, $followup );
			if ( $followup_id ) {
				$followup_ids[] = $followup_id;
			}
		}

		// Log followup scheduling
		Activity_Logger::log(
			'exit_followups_scheduled',
			array(
				'interview_id' => $interview_id,
				'followup_ids' => $followup_ids,
				'plan'         => $followup_plan,
			)
		);

		return $followup_ids;
	}

	/**
	 * Determine optimal followup plan based on exit context.
	 *
	 * @since  1.2601.2148
	 * @param  array $context Exit interview context.
	 * @return array Followup plan with type, timing, and survey questions.
	 */
	private static function determine_followup_plan( $context ) {
		$plan = array();

		// Immediate followup (3-5 days) - for competitor intel or quick wins
		if ( ! empty( $context['competitor_name'] ) ) {
			$plan[] = array(
				'type'           => self::TYPE_IMMEDIATE,
				'days_offset'    => self::TIMING_IMMEDIATE,
				'survey_builder' => 'competitor_analysis',
				'priority'       => 'high',
			);
		}

		// Short-term followup (14 days) - feature needs assessment
		if ( ! empty( $context['features_needed'] ) || 'missing_features' === $context['exit_reason'] ) {
			$plan[] = array(
				'type'           => self::TYPE_SHORT_TERM,
				'days_offset'    => self::TIMING_SHORT_TERM,
				'survey_builder' => 'feature_needs',
				'priority'       => 'medium',
			);
		}

		// Long-term followup (30 days) - general competitive analysis
		$plan[] = array(
			'type'           => self::TYPE_LONG_TERM,
			'days_offset'    => self::TIMING_LONG_TERM,
			'survey_builder' => 'general_followup',
			'priority'       => 'low',
		);

		return $plan;
	}

	/**
	 * Create a scheduled followup.
	 *
	 * @since  1.2601.2148
	 * @param  int   $interview_id Exit interview ID.
	 * @param  array $followup     Followup configuration.
	 * @return int|false Followup ID on success, false on failure.
	 */
	private static function create_followup( $interview_id, $followup ) {
		global $wpdb;

		$table = $wpdb->prefix . 'wpshadow_exit_followups';

		// Get interview data for survey generation
		$interview = self::get_exit_interview( $interview_id );
		if ( ! $interview ) {
			return false;
		}

		// Generate personalized survey questions
		$survey_questions = \WPShadow\Engagement\Exit_Survey_Builder::build_survey(
			$followup['survey_builder'],
			$interview
		);

		// Calculate scheduled date
		$scheduled_date = gmdate( 'Y-m-d H:i:s', strtotime( '+' . $followup['days_offset'] . ' days' ) );

		// Prepare data for insertion
		$insert_data = array(
			'interview_id'     => $interview_id,
			'followup_type'    => $followup['type'],
			'scheduled_date'   => $scheduled_date,
			'status'           => self::STATUS_PENDING,
			'survey_questions' => wp_json_encode( $survey_questions ),
			'contact_method'   => 'email',
			'notes'            => sprintf(
				/* translators: %s: followup type */
				__( 'Auto-scheduled %s followup', 'wpshadow' ),
				$followup['type']
			),
		);

		// Insert into database
		$result = $wpdb->insert(
			$table,
			$insert_data,
			array( '%d', '%s', '%s', '%s', '%s', '%s', '%s' )
		);

		if ( false === $result ) {
			Error_Handler::log_error(
				'Failed to create followup',
				array(
					'error'        => $wpdb->last_error,
					'interview_id' => $interview_id,
				)
			);
			return false;
		}

		return $wpdb->insert_id;
	}

	/**
	 * Get exit interview by ID.
	 *
	 * @since  1.2601.2148
	 * @param  int $interview_id Interview ID.
	 * @return array|null Interview data or null if not found.
	 */
	public static function get_exit_interview( $interview_id ) {
		global $wpdb;

		$table = $wpdb->prefix . 'wpshadow_exit_interviews';

		$interview = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE id = %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$interview_id
			),
			ARRAY_A
		);

		if ( ! $interview ) {
			return null;
		}

		// Decode JSON fields
		if ( ! empty( $interview['features_used'] ) ) {
			$interview['features_used'] = json_decode( $interview['features_used'], true );
		}

		return $interview;
	}

	/**
	 * Get scheduled followups for an interview.
	 *
	 * @since  1.2601.2148
	 * @param  int    $interview_id Interview ID.
	 * @param  string $status       Optional. Filter by status.
	 * @return array Array of followup records.
	 */
	public static function get_followups( $interview_id, $status = '' ) {
		global $wpdb;

		$table = $wpdb->prefix . 'wpshadow_exit_followups';

		$query = $wpdb->prepare(
			"SELECT * FROM {$table} WHERE interview_id = %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$interview_id
		);

		if ( ! empty( $status ) ) {
			$query .= $wpdb->prepare( ' AND status = %s', $status );
		}

		$query .= ' ORDER BY scheduled_date ASC';

		$followups = $wpdb->get_results( $query, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		// Decode JSON fields
		foreach ( $followups as &$followup ) {
			if ( ! empty( $followup['survey_questions'] ) ) {
				$followup['survey_questions'] = json_decode( $followup['survey_questions'], true );
			}
			if ( ! empty( $followup['survey_responses'] ) ) {
				$followup['survey_responses'] = json_decode( $followup['survey_responses'], true );
			}
		}

		return $followups;
	}

	/**
	 * Get pending followups that are due.
	 *
	 * @since  1.2601.2148
	 * @return array Array of due followup records.
	 */
	public static function get_due_followups() {
		global $wpdb;

		$table = $wpdb->prefix . 'wpshadow_exit_followups';

		$followups = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT f.*, i.contact_email, i.user_id 
				FROM {$table} f -- phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				INNER JOIN {$wpdb->prefix}wpshadow_exit_interviews i ON f.interview_id = i.id
				WHERE f.status = %s 
				AND f.scheduled_date <= %s
				ORDER BY f.scheduled_date ASC",
				self::STATUS_PENDING,
				current_time( 'mysql' )
			),
			ARRAY_A
		);

		// Decode JSON fields
		foreach ( $followups as &$followup ) {
			if ( ! empty( $followup['survey_questions'] ) ) {
				$followup['survey_questions'] = json_decode( $followup['survey_questions'], true );
			}
		}

		return $followups;
	}

	/**
	 * Update followup status.
	 *
	 * @since  1.2601.2148
	 * @param  int    $followup_id Followup ID.
	 * @param  string $status      New status.
	 * @param  array  $data        Optional additional data to update.
	 * @return bool True on success, false on failure.
	 */
	public static function update_followup_status( $followup_id, $status, $data = array() ) {
		global $wpdb;

		$table = $wpdb->prefix . 'wpshadow_exit_followups';

		$update_data = array(
			'status' => $status,
		);

		$update_format = array( '%s' );

		// Add completion date if status is completed
		if ( self::STATUS_COMPLETED === $status ) {
			$update_data['completed_date'] = current_time( 'mysql' );
			$update_format[]               = '%s';
		}

		// Add survey responses if provided
		if ( ! empty( $data['survey_responses'] ) ) {
			$update_data['survey_responses'] = wp_json_encode( $data['survey_responses'] );
			$update_format[]                 = '%s';
		}

		// Add notes if provided
		if ( ! empty( $data['notes'] ) ) {
			$update_data['notes'] = sanitize_textarea_field( $data['notes'] );
			$update_format[]      = '%s';
		}

		$result = $wpdb->update(
			$table,
			$update_data,
			array( 'id' => $followup_id ),
			$update_format,
			array( '%d' )
		);

		if ( false === $result ) {
			Error_Handler::log_error(
				'Failed to update followup status',
				array(
					'followup_id' => $followup_id,
					'status'      => $status,
					'error'       => $wpdb->last_error,
				)
			);
			return false;
		}

		// Log status change
		Activity_Logger::log(
			'exit_followup_status_changed',
			array(
				'followup_id' => $followup_id,
				'new_status'  => $status,
			)
		);

		return true;
	}

	/**
	 * Get followup statistics.
	 *
	 * @since  1.2601.2148
	 * @return array Statistics about exit followups.
	 */
	public static function get_statistics() {
		global $wpdb;

		$table_interviews = $wpdb->prefix . 'wpshadow_exit_interviews';
		$table_followups  = $wpdb->prefix . 'wpshadow_exit_followups';

		// Count total interviews with contact allowed
		$total_with_contact = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table_interviews} WHERE contact_allowed = %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				1
			)
		);

		// Count followups by status
		$followup_counts = $wpdb->get_results(
			"SELECT status, COUNT(*) as count FROM {$table_followups} GROUP BY status", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			ARRAY_A
		);

		$stats = array(
			'total_interviews_with_contact' => $total_with_contact,
			'followups_by_status'           => wp_list_pluck( $followup_counts, 'count', 'status' ),
			'pending_due_count'             => count( self::get_due_followups() ),
		);

		return $stats;
	}

	/**
	 * Cancel all pending followups for an interview.
	 *
	 * @since  1.2601.2148
	 * @param  int $interview_id Interview ID.
	 * @return bool True on success, false on failure.
	 */
	public static function cancel_followups( $interview_id ) {
		global $wpdb;

		$table = $wpdb->prefix . 'wpshadow_exit_followups';

		$result = $wpdb->update(
			$table,
			array( 'status' => self::STATUS_CANCELLED ),
			array(
				'interview_id' => $interview_id,
				'status'       => self::STATUS_PENDING,
			),
			array( '%s' ),
			array( '%d', '%s' )
		);

		if ( false !== $result ) {
			Activity_Logger::log(
				'exit_followups_cancelled',
				array(
					'interview_id' => $interview_id,
					'count'        => $result,
				)
			);
		}

		return false !== $result;
	}
}
