<?php
/**
 * Privacy Request Management
 *
 * Handles user privacy data export and erasure requests.
 *
 * @package WPSHADOW_SUPPORT
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPSHADOW_Privacy_Requests class
 */
class WPSHADOW_Privacy_Requests {
	/**
	 * Table name for privacy requests.
	 */
	private const TABLE_NAME = 'wpshadow_privacy_requests';

	/**
	 * Initialize the privacy requests manager.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'init', array( __CLASS__, 'register_table' ) );
		add_action( 'wp_ajax_WPSHADOW_submit_privacy_request', array( __CLASS__, 'ajax_submit_request' ) );
		add_action( 'wp_ajax_WPSHADOW_process_privacy_request', array( __CLASS__, 'ajax_process_request' ) );
		add_action( 'wp_ajax_WPSHADOW_download_export', array( __CLASS__, 'ajax_download_export' ) );
	}

	/**
	 * Create the privacy requests table.
	 *
	 * @return void
	 */
	public static function register_table(): void {
		global $wpdb;

		$table_name      = $wpdb->prefix . self::TABLE_NAME;
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			user_id bigint(20) unsigned NOT NULL,
			request_type varchar(20) NOT NULL,
			status varchar(20) NOT NULL DEFAULT 'pending',
			request_data longtext,
			admin_notes text,
			processed_by bigint(20) unsigned,
			export_file varchar(255),
			created_at datetime NOT NULL,
			updated_at datetime NOT NULL,
			PRIMARY KEY (id),
			KEY user_id (user_id),
			KEY status (status),
			KEY request_type (request_type),
			KEY created_at (created_at)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Submit a new privacy request.
	 *
	 * @param int    $user_id      User ID making the request.
	 * @param string $request_type Type of request: 'export' or 'erase'.
	 * @param array  $request_data Additional request data.
	 * @return int|false Request ID on success, false on failure.
	 */
	public static function submit_request( int $user_id, string $request_type, array $request_data = array() ) {
		global $wpdb;

		$valid_types = array( 'export', 'erase' );
		if ( ! in_array( $request_type, $valid_types, true ) ) {
			return false;
		}

		$table = $wpdb->prefix . self::TABLE_NAME;
		$now   = current_time( 'mysql' );

		$result = $wpdb->insert(
			$table,
			array(
				'user_id'      => $user_id,
				'request_type' => $request_type,
				'status'       => 'pending',
				'request_data' => wp_json_encode( $request_data ),
				'created_at'   => $now,
				'updated_at'   => $now,
			),
			array( '%d', '%s', '%s', '%s', '%s', '%s' )
		);

		if ( ! $result ) {
			return false;
		}

		$request_id = (int) $wpdb->insert_id;

		// Log the request.
		if ( class_exists( '\\WPShadow\\WPSHADOW_Activity_Logger' ) ) {
			WPSHADOW_Activity_Logger::log(
				'info',
				sprintf(
					/* translators: 1: request type, 2: user ID */
					__( 'Privacy %1$s request submitted by user #%2$d', 'plugin-wpshadow' ),
					$request_type,
					$user_id
				),
				array(
					'request_id'   => $request_id,
					'request_type' => $request_type,
					'user_id'      => $user_id,
				)
			);
		}

		// Send notification email to user.
		self::send_request_notification( $request_id, 'submitted' );

		// Send admin notification.
		self::send_admin_notification( $request_id );

		return $request_id;
	}

	/**
	 * Get a privacy request by ID.
	 *
	 * @param int $request_id Request ID.
	 * @return object|null Request object or null if not found.
	 */
	public static function get_request( int $request_id ): ?object {
		global $wpdb;

		$table = $wpdb->prefix . self::TABLE_NAME;

		$request = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE id = %d",
				$request_id
			)
		);

		return $request ?: null;
	}

	/**
	 * Get all privacy requests for a user.
	 *
	 * @param int $user_id User ID.
	 * @return array List of requests.
	 */
	public static function get_user_requests( int $user_id ): array {
		global $wpdb;

		$table = $wpdb->prefix . self::TABLE_NAME;

		$requests = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE user_id = %d ORDER BY created_at DESC",
				$user_id
			)
		);

		return $requests ?: array();
	}

	/**
	 * Get all pending privacy requests (admin view).
	 *
	 * @return array List of pending requests.
	 */
	public static function get_pending_requests(): array {
		global $wpdb;

		$table = $wpdb->prefix . self::TABLE_NAME;

		$requests = $wpdb->get_results(
			"SELECT * FROM {$table} WHERE status = 'pending' ORDER BY created_at ASC"
		);

		return $requests ?: array();
	}

	/**
	 * Update request status.
	 *
	 * @param int    $request_id   Request ID.
	 * @param string $status       New status.
	 * @param int    $processed_by Admin user ID processing the request.
	 * @param string $admin_notes  Admin notes.
	 * @return bool Success status.
	 */
	public static function update_request_status( int $request_id, string $status, int $processed_by = 0, string $admin_notes = '' ): bool {
		global $wpdb;

		$valid_statuses = array( 'pending', 'approved', 'processing', 'completed', 'denied' );
		if ( ! in_array( $status, $valid_statuses, true ) ) {
			return false;
		}

		$table = $wpdb->prefix . self::TABLE_NAME;
		$now   = current_time( 'mysql' );

		$update_data = array(
			'status'     => $status,
			'updated_at' => $now,
		);

		if ( $processed_by > 0 ) {
			$update_data['processed_by'] = $processed_by;
		}

		if ( ! empty( $admin_notes ) ) {
			$update_data['admin_notes'] = $admin_notes;
		}

		$result = $wpdb->update(
			$table,
			$update_data,
			array( 'id' => $request_id ),
			array( '%s', '%s', '%d', '%s' ),
			array( '%d' )
		);

		if ( $result !== false ) {
			$request = self::get_request( $request_id );

			// Log status change.
			if ( class_exists( '\\WPShadow\\WPSHADOW_Activity_Logger' ) && $request ) {
				WPSHADOW_Activity_Logger::log(
					'info',
					sprintf(
						/* translators: 1: request type, 2: new status */
						__( 'Privacy %1$s request status changed to %2$s', 'plugin-wpshadow' ),
						$request->request_type,
						$status
					),
					array(
						'request_id'   => $request_id,
						'request_type' => $request->request_type,
						'status'       => $status,
						'processed_by' => $processed_by,
					)
				);
			}

			// Send notification to user.
			self::send_request_notification( $request_id, $status );

			return true;
		}

		return false;
	}

	/**
	 * Process an export request.
	 *
	 * @param int $request_id Request ID.
	 * @return bool Success status.
	 */
	public static function process_export_request( int $request_id ): bool {
		$request = self::get_request( $request_id );

		if ( ! $request || 'export' !== $request->request_type ) {
			return false;
		}

		// Update status to processing.
		self::update_request_status( $request_id, 'processing' );

		// Generate export data.
		$user_data = self::collect_user_data( $request->user_id );

		// Determine export format.
		$format = get_option( 'wpshadow_privacy_export_format', 'json' );

		// Create export file.
		$upload_dir = wp_upload_dir();
		$export_dir = $upload_dir['basedir'] . '/wps-privacy-exports';

		if ( ! file_exists( $export_dir ) ) {
			wp_mkdir_p( $export_dir );
			// Add index.php to prevent directory listing.
			file_put_contents( $export_dir . '/index.php', '<?php // Silence is golden' );
		}

		$filename = 'export-user-' . $request->user_id . '-' . time() . '.' . $format;
		$filepath = $export_dir . '/' . $filename;

		switch ( $format ) {
			case 'csv':
				$content = self::format_export_csv( $user_data );
				break;
			case 'zip':
				$content = self::format_export_zip( $user_data );
				break;
			case 'json':
			default:
				$content = wp_json_encode( $user_data, JSON_PRETTY_PRINT );
				break;
		}

		file_put_contents( $filepath, $content );

		// Update request with export file.
		global $wpdb;
		$table = $wpdb->prefix . self::TABLE_NAME;
		$wpdb->update(
			$table,
			array(
				'export_file' => $filename,
				'status'      => 'completed',
				'updated_at'  => current_time( 'mysql' ),
			),
			array( 'id' => $request_id ),
			array( '%s', '%s', '%s' ),
			array( '%d' )
		);

		// Send completion notification.
		self::send_request_notification( $request_id, 'completed' );

		return true;
	}

	/**
	 * Process an erase request.
	 *
	 * @param int $request_id Request ID.
	 * @return bool Success status.
	 */
	public static function process_erase_request( int $request_id ): bool {
		$request = self::get_request( $request_id );

		if ( ! $request || 'erase' !== $request->request_type ) {
			return false;
		}

		// Update status to processing.
		self::update_request_status( $request_id, 'processing' );

		// Erase user data (keep WordPress core user account intact).
		$erased = self::erase_user_data( $request->user_id );

		// Update status to completed.
		self::update_request_status( $request_id, 'completed' );

		// Send completion notification.
		self::send_request_notification( $request_id, 'completed' );

		return true;
	}

	/**
	 * Collect user personal data for export.
	 *
	 * @param int $user_id User ID.
	 * @return array User data.
	 */
	private static function collect_user_data( int $user_id ): array {
		$user = get_userdata( $user_id );

		if ( ! $user ) {
			return array();
		}

		$data = array(
			'user_info'     => array(
				'username'     => $user->user_login,
				'email'        => $user->user_email,
				'display_name' => $user->display_name,
				'registered'   => $user->user_registered,
				'roles'        => $user->roles,
			),
			'activity_logs' => array(),
			'requests'      => array(),
		);

		// Collect activity logs.
		global $wpdb;
		$activity_table = $wpdb->prefix . 'wpshadow_activity_log';
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $activity_table ) ) === $activity_table ) {
			$logs                  = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM {$activity_table} WHERE user_id = %d ORDER BY logged_at DESC",
					$user_id
				),
				ARRAY_A
			);
			$data['activity_logs'] = $logs ?: array();
		}

		// Collect privacy requests.
		$data['requests'] = self::get_user_requests( $user_id );

		/**
		 * Filter user data before export.
		 *
		 * @param array $data    User data.
		 * @param int   $user_id User ID.
		 */
		return apply_filters( 'wpshadow_privacy_export_data', $data, $user_id );
	}

	/**
	 * Erase user personal data.
	 *
	 * @param int $user_id User ID.
	 * @return bool Success status.
	 */
	private static function erase_user_data( int $user_id ): bool {
		global $wpdb;

		// Erase activity logs.
		$activity_table = $wpdb->prefix . 'wpshadow_activity_log';
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $activity_table ) ) === $activity_table ) {
			$wpdb->delete( $activity_table, array( 'user_id' => $user_id ), array( '%d' ) );
		}

		/**
		 * Action hook to allow modules to erase their user data.
		 *
		 * @param int $user_id User ID.
		 */
		do_action( 'wpshadow_privacy_erase_user_data', $user_id );

		return true;
	}

	/**
	 * Format export data as CSV.
	 *
	 * @param array $data User data.
	 * @return string CSV content.
	 */
	private static function format_export_csv( array $data ): string {
		$csv = '';

		// User info section.
		$csv .= "User Information\n";
		foreach ( $data['user_info'] as $key => $value ) {
			if ( is_array( $value ) ) {
				$value = implode( ', ', $value );
			}
			$csv .= sprintf( '"%s","%s"', $key, $value ) . "\n";
		}

		$csv .= "\n";

		// Activity logs section.
		$csv .= "Activity Logs\n";
		$csv .= "\"Date\",\"Message\",\"Context\"\n";
		foreach ( $data['activity_logs'] as $log ) {
			$csv .= sprintf(
				'"%s","%s","%s"',
				$log['logged_at'] ?? '',
				$log['message'] ?? '',
				$log['context'] ?? ''
			) . "\n";
		}

		return $csv;
	}

	/**
	 * Format export data as ZIP archive.
	 *
	 * @param array $data User data.
	 * @return string ZIP file content.
	 */
	private static function format_export_zip( array $data ): string {
		// For now, just return JSON in a string format.
		// Full ZIP implementation would require ZipArchive.
		return wp_json_encode( $data, JSON_PRETTY_PRINT );
	}

	/**
	 * Send notification email to user about their request.
	 *
	 * @param int    $request_id Request ID.
	 * @param string $event      Event type: submitted, approved, completed, denied.
	 * @return bool Success status.
	 */
	private static function send_request_notification( int $request_id, string $event ): bool {
		$request = self::get_request( $request_id );

		if ( ! $request ) {
			return false;
		}

		$user = get_userdata( $request->user_id );

		if ( ! $user ) {
			return false;
		}

		$subject = '';
		$message = '';

		switch ( $event ) {
			case 'submitted':
				$subject = __( 'Privacy Request Submitted', 'plugin-wpshadow' );
				$message = sprintf(
					/* translators: 1: request type */
					__( 'Your privacy %1$s request has been submitted and is pending review.', 'plugin-wpshadow' ),
					$request->request_type
				);
				break;

			case 'approved':
				$subject = __( 'Privacy Request Approved', 'plugin-wpshadow' );
				$message = sprintf(
					/* translators: 1: request type */
					__( 'Your privacy %1$s request has been approved and is being processed.', 'plugin-wpshadow' ),
					$request->request_type
				);
				break;

			case 'completed':
				$subject = __( 'Privacy Request Completed', 'plugin-wpshadow' );
				if ( 'export' === $request->request_type ) {
					$download_url = admin_url( 'admin-ajax.php?action=WPSHADOW_download_export&request_id=' . $request_id );
					$message      = sprintf(
						/* translators: 1: download URL */
						__( 'Your data export is ready. Download it here: %1$s', 'plugin-wpshadow' ),
						$download_url
					);
				} else {
					$message = __( 'Your data erasure request has been completed.', 'plugin-wpshadow' );
				}
				break;

			case 'denied':
				$subject = __( 'Privacy Request Denied', 'plugin-wpshadow' );
				$message = sprintf(
					/* translators: 1: request type, 2: admin notes */
					__( 'Your privacy %1$s request has been denied. Reason: %2$s', 'plugin-wpshadow' ),
					$request->request_type,
					$request->admin_notes ?: __( 'No reason provided', 'plugin-wpshadow' )
				);
				break;
		}

		if ( empty( $subject ) || empty( $message ) ) {
			return false;
		}

		return wp_mail( $user->user_email, $subject, $message );
	}

	/**
	 * Send notification to admin about new request.
	 *
	 * @param int $request_id Request ID.
	 * @return bool Success status.
	 */
	private static function send_admin_notification( int $request_id ): bool {
		$request = self::get_request( $request_id );

		if ( ! $request ) {
			return false;
		}

		$admin_email = get_option( 'admin_email' );
		$subject     = __( 'New Privacy Request', 'plugin-wpshadow' );
		$message     = sprintf(
			/* translators: 1: request type, 2: user ID, 3: admin URL */
			__( 'A new privacy %1$s request has been submitted by user #%2$d. Review it here: %3$s', 'plugin-wpshadow' ),
			$request->request_type,
			$request->user_id,
			admin_url( 'admin.php?page=wps-privacy-requests' )
		);

		return wp_mail( $admin_email, $subject, $message );
	}

	/**
	 * AJAX handler for submitting privacy request.
	 *
	 * @return void
	 */
	public static function ajax_submit_request(): void {
		check_ajax_referer( 'wpshadow_privacy_request', 'nonce' );

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => __( 'You must be logged in to submit a request.', 'plugin-wpshadow' ) ) );
		}

		$user_id      = get_current_user_id();
		$request_type = \WPShadow\WPSHADOW_get_post_key( 'request_type' );

		if ( ! in_array( $request_type, array( 'export', 'erase' ), true ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid request type.', 'plugin-wpshadow' ) ) );
		}

		$request_id = self::submit_request( $user_id, $request_type );

		if ( ! $request_id ) {
			wp_send_json_error( array( 'message' => __( 'Failed to submit request.', 'plugin-wpshadow' ) ) );
		}

		wp_send_json_success(
			array(
				'message'    => __( 'Request submitted successfully.', 'plugin-wpshadow' ),
				'request_id' => $request_id,
			)
		);
	}

	/**
	 * AJAX handler for processing privacy request (admin only).
	 *
	 * @return void
	 */
	public static function ajax_process_request(): void {
		check_ajax_referer( 'wpshadow_privacy_admin', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'plugin-wpshadow' ) ) );
		}

		$request_id = \WPShadow\WPSHADOW_get_post_int( 'request_id' );
		$action     = \WPShadow\WPSHADOW_get_post_key( 'request_action' );

		$request = self::get_request( $request_id );

		if ( ! $request ) {
			wp_send_json_error( array( 'message' => __( 'Request not found.', 'plugin-wpshadow' ) ) );
		}

		switch ( $action ) {
			case 'approve':
				self::update_request_status( $request_id, 'approved', get_current_user_id() );

				// Automatically process approved requests.
				if ( 'export' === $request->request_type ) {
					self::process_export_request( $request_id );
				} elseif ( 'erase' === $request->request_type ) {
					self::process_erase_request( $request_id );
				}

				wp_send_json_success( array( 'message' => __( 'Request approved and processed.', 'plugin-wpshadow' ) ) );
				break;

			case 'deny':
				$notes = \WPShadow\WPSHADOW_get_post_textarea( 'admin_notes' );
				self::update_request_status( $request_id, 'denied', get_current_user_id(), $notes );
				wp_send_json_success( array( 'message' => __( 'Request denied.', 'plugin-wpshadow' ) ) );
				break;

			default:
				wp_send_json_error( array( 'message' => __( 'Invalid action.', 'plugin-wpshadow' ) ) );
		}
	}

	/**
	 * AJAX handler for downloading export file.
	 *
	 * @return void
	 */
	public static function ajax_download_export(): void {
		if ( ! is_user_logged_in() ) {
			wp_die( esc_html__( 'Access denied.', 'plugin-wpshadow' ) );
		}

		$request_id = isset( $_GET['request_id'] ) ? absint( $_GET['request_id'] ) : 0;
		$request    = self::get_request( $request_id );

		if ( ! $request || $request->user_id !== get_current_user_id() ) {
			wp_die( esc_html__( 'Invalid request or access denied.', 'plugin-wpshadow' ) );
		}

		if ( empty( $request->export_file ) ) {
			wp_die( esc_html__( 'Export file not available.', 'plugin-wpshadow' ) );
		}

		$upload_dir = wp_upload_dir();
		$export_dir = $upload_dir['basedir'] . '/wps-privacy-exports';
		$filepath   = $export_dir . '/' . $request->export_file;

		if ( ! file_exists( $filepath ) ) {
			wp_die( esc_html__( 'Export file not found.', 'plugin-wpshadow' ) );
		}

		header( 'Content-Type: application/octet-stream' );
		header( 'Content-Disposition: attachment; filename="' . basename( $request->export_file ) . '"' );
		header( 'Content-Length: ' . filesize( $filepath ) );
		readfile( $filepath );
		exit;
	}
}
