<?php
/**
 * Kanban Note Action - Creates notes in Kanban board from workflows
 *
 * Allows workflows to create custom notes/findings in the Kanban board
 * when specific events occur. Perfect for alerting admins to conditions
 * that don't fit standard diagnostic findings.
 *
 * @package WPShadow
 * @subpackage Workflow
 */

declare(strict_types=1);

namespace WPShadow\Workflow;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manages Kanban note creation from workflow actions
 */
class Kanban_Note_Action {

	const OPTION_KEY = 'wpshadow_kanban_workflow_notes';
	const STATUS_NEW = 'detected';

	/**
	 * Create a new note in the Kanban board from a workflow
	 *
	 * @param array $config Configuration for the note
	 * @return array Result with success status and message
	 *
	 * Configuration array expects:
	 * - title (string, required): Note title/heading
	 * - description (string, optional): Detailed note content
	 * - status (string, optional): Kanban status - detected|manual|automated|fixed (default: detected)
	 * - severity (string, optional): critical|high|medium|low (default: medium)
	 * - category (string, optional): seo|design|settings|performance (default: settings)
	 * - tags (array, optional): Additional tags for organization
	 * - action_url (string, optional): URL to link from the note
	 * - action_label (string, optional): Label for the action link
	 * - auto_dismiss (int, optional): Auto-dismiss after N seconds (0 = no dismiss)
	 */
	public static function create( array $config ): array {
		// Validate required fields
		if ( empty( $config['title'] ) ) {
			return array(
				'success' => false,
				'message' => __( 'Kanban note requires a title', 'wpshadow' ),
			);
		}

		// Generate unique ID
		$note_id = 'wf_note_' . wp_generate_uuid4();

		// Sanitize configuration
		$note = array(
			'id'           => $note_id,
			'title'        => sanitize_text_field( $config['title'] ),
			'description'  => isset( $config['description'] ) ? wp_kses_post( $config['description'] ) : '',
			'status'       => self::validate_status( $config['status'] ?? self::STATUS_NEW ),
			'severity'     => self::validate_severity( $config['severity'] ?? 'medium' ),
			'category'     => self::validate_category( $config['category'] ?? 'settings' ),
			'tags'         => self::validate_tags( $config['tags'] ?? array() ),
			'action_url'   => isset( $config['action_url'] ) ? esc_url( $config['action_url'] ) : '',
			'action_label' => isset( $config['action_label'] ) ? sanitize_text_field( $config['action_label'] ) : '',
			'workflow_id'  => isset( $config['workflow_id'] ) ? sanitize_key( $config['workflow_id'] ) : '',
			'trigger_at'   => isset( $config['trigger_at'] ) ? sanitize_text_field( $config['trigger_at'] ) : '',
			'created_at'   => current_time( 'mysql' ),
			'created_by'   => get_current_user_id(),
			'auto_dismiss' => isset( $config['auto_dismiss'] ) ? (int) $config['auto_dismiss'] : 0,
		);

		// Store the note
		$notes             = self::get_all_notes();
		$notes[ $note_id ] = $note;
		update_option( self::OPTION_KEY, $notes );

		// Add to Finding_Status_Manager for Kanban board display
		$status_manager = new \WPShadow\Core\Finding_Status_Manager();
		$status_manager->set_finding_status( $note_id, $note['status'] );

		return array(
			'success' => true,
			'message' => sprintf(
				__( 'Note "%s" added to Kanban board', 'wpshadow' ),
				$note['title']
			),
			'note_id' => $note_id,
		);
	}

	/**
	 * Get all Kanban workflow notes
	 *
	 * @return array All notes keyed by ID
	 */
	public static function get_all_notes(): array {
		$notes = get_option( self::OPTION_KEY, array() );
		return is_array( $notes ) ? $notes : array();
	}

	/**
	 * Get notes by status
	 *
	 * @param string $status Status filter
	 * @return array Notes with that status
	 */
	public static function get_by_status( string $status ): array {
		$notes = self::get_all_notes();
		return array_filter(
			$notes,
			function ( $note ) use ( $status ) {
				return $note['status'] === $status;
			}
		);
	}

	/**
	 * Get notes by category
	 *
	 * @param string $category Category filter
	 * @return array Notes in that category
	 */
	public static function get_by_category( string $category ): array {
		$notes = self::get_all_notes();
		return array_filter(
			$notes,
			function ( $note ) use ( $category ) {
				return $note['category'] === $category;
			}
		);
	}

	/**
	 * Get note by ID
	 *
	 * @param string $note_id Note ID
	 * @return array|null Note data or null
	 */
	public static function get( string $note_id ): ?array {
		$notes = self::get_all_notes();
		return $notes[ $note_id ] ?? null;
	}

	/**
	 * Update note status
	 *
	 * @param string $note_id New status
	 * @param string $status Status value
	 * @return bool Success
	 */
	public static function update_status( string $note_id, string $status ): bool {
		$note = self::get( $note_id );
		if ( ! $note ) {
			return false;
		}

		$status                      = self::validate_status( $status );
		$notes                       = self::get_all_notes();
		$notes[ $note_id ]['status'] = $status;
		update_option( self::OPTION_KEY, $notes );

		// Update in status manager
		$status_manager = new \WPShadow\Core\Finding_Status_Manager();
		$status_manager->set_finding_status( $note_id, $status );

		return true;
	}

	/**
	 * Delete a note
	 *
	 * @param string $note_id Note ID
	 * @return bool Success
	 */
	public static function delete( string $note_id ): bool {
		$notes = self::get_all_notes();
		if ( ! isset( $notes[ $note_id ] ) ) {
			return false;
		}

		unset( $notes[ $note_id ] );
		update_option( self::OPTION_KEY, $notes );

		return true;
	}

	/**
	 * Validate note status
	 *
	 * @param string $status Status to validate
	 * @return string Validated status or default
	 */
	private static function validate_status( string $status ): string {
		$valid_statuses = array( 'detected', 'manual', 'automated', 'fixed' );
		return in_array( $status, $valid_statuses, true ) ? $status : self::STATUS_NEW;
	}

	/**
	 * Validate note severity
	 *
	 * @param string $severity Severity level
	 * @return string Validated severity
	 */
	private static function validate_severity( string $severity ): string {
		$valid_severities = array( 'critical', 'high', 'medium', 'low', 'info' );
		return in_array( $severity, $valid_severities, true ) ? $severity : 'medium';
	}

	/**
	 * Validate note category
	 *
	 * @param string $category Category name
	 * @return string Validated category
	 */
	private static function validate_category( string $category ): string {
		$valid_categories = array( 'seo', 'design', 'settings', 'performance', 'security', 'admin-ux' );
		return in_array( $category, $valid_categories, true ) ? $category : 'settings';
	}

	/**
	 * Validate tags array
	 *
	 * @param mixed $tags Tags to validate
	 * @return array Validated tags
	 */
	private static function validate_tags( $tags ): array {
		if ( ! is_array( $tags ) ) {
			return array();
		}

		return array_map(
			function ( $tag ) {
				return sanitize_text_field( $tag );
			},
			$tags
		);
	}

	/**
	 * Clean up old notes (older than 30 days)
	 *
	 * @param int $days_old Number of days to keep (default: 30)
	 * @return int Number of notes deleted
	 */
	public static function cleanup_old_notes( int $days_old = 30 ): int {
		$notes   = self::get_all_notes();
		$cutoff  = time() - ( $days_old * DAY_IN_SECONDS );
		$deleted = 0;

		foreach ( $notes as $note_id => $note ) {
			$created_time = strtotime( $note['created_at'] );
			if ( $created_time < $cutoff ) {
				self::delete( $note_id );
				++$deleted;
			}
		}

		return $deleted;
	}

	/**
	 * Get notes created by specific workflow
	 *
	 * @param string $workflow_id Workflow ID
	 * @return array Notes from this workflow
	 */
	public static function get_by_workflow( string $workflow_id ): array {
		$notes = self::get_all_notes();
		return array_filter(
			$notes,
			function ( $note ) use ( $workflow_id ) {
				return $note['workflow_id'] === $workflow_id;
			}
		);
	}
}
