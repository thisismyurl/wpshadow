<?php
/**
 * Emergency SOS Support System - MVP Implementation
 *
 * Handles emergency support incidents without payment processing (MVP).
 * Provides incident form, ticket creation, email notifications, and admin queue.
 *
 * @package WPS_SUPPORT
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SOS Support Manager
 */
class WPS_SOS_Support {

	/**
	 * Incidents option key.
	 */
	private const INCIDENTS_KEY = 'wps_sos_incidents';

	/**
	 * Incident counter key.
	 */
	private const COUNTER_KEY = 'wps_sos_incident_counter';

	/**
	 * Initialize SOS Support system.
	 *
	 * @return void
	 */
	public static function init(): void {
		// Register admin menu.
		add_action( 'admin_menu', array( __CLASS__, 'register_admin_menu' ) );

		// Handle form submissions.
		add_action( 'admin_post_wps_submit_sos', array( __CLASS__, 'handle_sos_submission' ) );
		add_action( 'admin_post_nopriv_wps_submit_sos', array( __CLASS__, 'handle_sos_submission' ) );

		// AJAX handlers for incident management.
		add_action( 'wp_ajax_wps_update_incident_status', array( __CLASS__, 'ajax_update_incident_status' ) );
		add_action( 'wp_ajax_wps_add_incident_note', array( __CLASS__, 'ajax_add_incident_note' ) );

		// Dashboard widget.
		add_action( 'wp_dashboard_setup', array( __CLASS__, 'register_dashboard_widget' ) );

		// Email notifications on incident creation.
		add_action( 'wps_sos_incident_created', array( __CLASS__, 'send_incident_notifications' ), 10, 1 );
	}

	/**
	 * Register admin menu.
	 *
	 * @return void
	 */
	public static function register_admin_menu(): void {
		add_submenu_page(
			'wp-support',
			__( '🚨 Emergency SOS', 'plugin-wp-support-thisismyurl' ),
			__( '🚨 Emergency SOS', 'plugin-wp-support-thisismyurl' ),
			'manage_options',
			'wps-sos-support',
			array( __CLASS__, 'render_sos_page' )
		);
	}

	/**
	 * Register dashboard widget for pending incidents.
	 *
	 * @return void
	 */
	public static function register_dashboard_widget(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$pending = self::get_pending_incidents_count();

		if ( $pending === 0 ) {
			return;
		}

		wp_add_dashboard_widget(
			'wps_sos_pending',
			sprintf( __( '🚨 Emergency SOS: %d Pending', 'plugin-wp-support-thisismyurl' ), $pending ),
			array( __CLASS__, 'render_dashboard_widget' )
		);
	}

	/**
	 * Render dashboard widget.
	 *
	 * @return void
	 */
	public static function render_dashboard_widget(): void {
		$incidents = self::get_incidents( array( 'status' => 'pending' ), 5 );

		if ( empty( $incidents ) ) {
			echo '<p>' . esc_html__( 'No pending incidents.', 'plugin-wp-support-thisismyurl' ) . '</p>';
			return;
		}

		echo '<div style="margin-bottom: 10px;">';
		echo '<strong>' . esc_html( sprintf( __( '%d urgent incident(s) need attention:', 'plugin-wp-support-thisismyurl' ), count( $incidents ) ) ) . '</strong>';
		echo '</div>';

		foreach ( $incidents as $incident ) {
			$severity_color = self::get_severity_color( $incident['severity'] ?? 'high' );
			$time_ago       = human_time_diff( $incident['created'] ?? time() );

			echo '<div style="border-left: 4px solid ' . esc_attr( $severity_color ) . '; padding: 10px; margin: 10px 0; background: #f9f9f9;">';
			echo '<p style="margin: 0; font-size: 13px;"><strong>#' . esc_html( $incident['id'] ?? '' ) . '</strong> - ' . esc_html( $incident['subject'] ?? __( 'No subject', 'plugin-wp-support-thisismyurl' ) ) . '</p>';
			echo '<p style="margin: 5px 0 0 0; font-size: 11px; color: #666;">';
			echo esc_html( sprintf( __( 'Severity: %1$s | %2$s ago', 'plugin-wp-support-thisismyurl' ), ucfirst( $incident['severity'] ?? 'high' ), $time_ago ) );
			echo '</p>';
			echo '</div>';
		}

		echo '<div style="margin-top: 15px;">';
		echo '<a href="' . esc_url( admin_url( 'admin.php?page=wps-sos-support' ) ) . '" class="button button-primary">';
		echo esc_html__( 'View All Incidents', 'plugin-wp-support-thisismyurl' );
		echo '</a>';
		echo '</div>';
	}

	/**
	 * Render SOS support page.
	 *
	 * @return void
	 */
	public static function render_sos_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'plugin-wp-support-thisismyurl' ) );
		}

		// Check if viewing a specific incident.
		$incident_id = isset( $_GET['incident'] ) ? sanitize_text_field( wp_unslash( $_GET['incident'] ) ) : '';

		if ( ! empty( $incident_id ) ) {
			self::render_incident_details( $incident_id );
			return;
		}

		// Check if showing form or admin queue.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$action = isset( $_GET['action'] ) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : 'queue';

		if ( 'form' === $action ) {
			self::render_sos_form();
		} else {
			self::render_admin_queue();
		}
	}

	/**
	 * Render SOS incident form.
	 *
	 * @return void
	 */
	private static function render_sos_form(): void {
		require_once plugin_dir_path( __FILE__ ) . 'views/sos-form.php';
	}

	/**
	 * Render admin incident queue.
	 *
	 * @return void
	 */
	private static function render_admin_queue(): void {
		require_once plugin_dir_path( __FILE__ ) . 'views/sos-admin.php';
	}

	/**
	 * Render individual incident details.
	 *
	 * @param string $incident_id Incident ID.
	 * @return void
	 */
	private static function render_incident_details( string $incident_id ): void {
		$incident = self::get_incident( $incident_id );

		if ( ! $incident ) {
			wp_die( esc_html__( 'Incident not found.', 'plugin-wp-support-thisismyurl' ) );
		}

		require_once plugin_dir_path( __FILE__ ) . 'views/sos-incident-details.php';
	}

	/**
	 * Handle SOS form submission.
	 *
	 * @return void
	 */
	public static function handle_sos_submission(): void {
		// Verify nonce.
		if ( empty( $_POST['wps_sos_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wps_sos_nonce'] ) ), 'wps_sos_submit' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'plugin-wp-support-thisismyurl' ) );
		}

		// Collect form data.
		$data = array(
			'name'        => \WPS\CoreSupport\wps_get_post_text( 'name' ),
			'email'       => \WPS\CoreSupport\wps_get_post_email( 'email' ),
			'phone'       => \WPS\CoreSupport\wps_get_post_text( 'phone' ),
			'subject'     => \WPS\CoreSupport\wps_get_post_text( 'subject' ),
			'description' => \WPS\CoreSupport\wps_get_post_textarea( 'description' ),
			'severity'    => \WPS\CoreSupport\wps_get_post_key( 'severity', 'high' ),
			'category'    => \WPS\CoreSupport\wps_get_post_key( 'category', 'general' ),
			'site_url'    => home_url(),
		);

		// Validate required fields.
		if ( empty( $data['email'] ) || empty( $data['subject'] ) || empty( $data['description'] ) ) {
			wp_die( esc_html__( 'Please fill in all required fields.', 'plugin-wp-support-thisismyurl' ) );
		}

		// Create incident.
		$incident_id = self::create_incident( $data );

		if ( ! $incident_id ) {
			wp_die( esc_html__( 'Failed to create incident. Please try again.', 'plugin-wp-support-thisismyurl' ) );
		}

		// Trigger notifications.
		do_action( 'wps_sos_incident_created', $incident_id );

		// Redirect to success page.
		wp_safe_redirect(
			add_query_arg(
				array(
					'page'    => 'wps-sos-support',
					'success' => '1',
					'id'      => $incident_id,
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

	/**
	 * Create a new incident.
	 *
	 * @param array $data Incident data.
	 * @return string|false Incident ID or false on failure.
	 */
	private static function create_incident( array $data ) {
		// Get next incident ID.
		$counter = (int) get_option( self::COUNTER_KEY, 0 );
		++$counter;
		$incident_id = 'SOS-' . str_pad( (string) $counter, 6, '0', STR_PAD_LEFT );

		// Build incident record.
		$incident = array(
			'id'          => $incident_id,
			'name'        => $data['name'] ?? '',
			'email'       => $data['email'] ?? '',
			'phone'       => $data['phone'] ?? '',
			'subject'     => $data['subject'] ?? '',
			'description' => $data['description'] ?? '',
			'severity'    => $data['severity'] ?? 'high',
			'category'    => $data['category'] ?? 'general',
			'site_url'    => $data['site_url'] ?? home_url(),
			'status'      => 'pending',
			'created'     => time(),
			'updated'     => time(),
			'notes'       => array(),
			'triage'      => self::auto_triage( $data ),
		);

		// Get existing incidents.
		$incidents = get_option( self::INCIDENTS_KEY, array() );

		// Add new incident.
		$incidents[ $incident_id ] = $incident;

		// Save.
		$saved = update_option( self::INCIDENTS_KEY, $incidents );

		if ( $saved ) {
			update_option( self::COUNTER_KEY, $counter );
			return $incident_id;
		}

		return false;
	}

	/**
	 * Auto-triage incident based on keywords and severity.
	 *
	 * @param array $data Incident data.
	 * @return array Triage information.
	 */
	private static function auto_triage( array $data ): array {
		$description = strtolower( $data['description'] ?? '' );
		$subject     = strtolower( $data['subject'] ?? '' );
		$combined    = $description . ' ' . $subject;

		// Detect issue type.
		$category         = 'general';
		$estimated_cause  = 'Unknown';
		$immediate_action = array();
		$estimated_time   = '2-4 hours';

		// Site down patterns.
		if ( preg_match( '/\b(down|offline|not loading|white screen|500 error|fatal error)\b/i', $combined ) ) {
			$category         = 'site_down';
			$estimated_cause  = 'Server error, plugin conflict, or hosting issue';
			$immediate_action = array(
				__( 'Check if site loads in incognito/private browsing', 'plugin-wp-support-thisismyurl' ),
				__( 'Disable plugins via FTP/cPanel', 'plugin-wp-support-thisismyurl' ),
				__( 'Check server error logs', 'plugin-wp-support-thisismyurl' ),
			);
			$estimated_time   = '1-2 hours';
		}

		// Database issues.
		if ( preg_match( '/\b(database|mysql|connection|error establishing)\b/i', $combined ) ) {
			$category         = 'database';
			$estimated_cause  = 'Database connection or corruption';
			$immediate_action = array(
				__( 'Check database credentials in wp-config.php', 'plugin-wp-support-thisismyurl' ),
				__( 'Verify database server is running', 'plugin-wp-support-thisismyurl' ),
				__( 'Repair database tables', 'plugin-wp-support-thisismyurl' ),
			);
			$estimated_time   = '1-3 hours';
		}

		// Security breach.
		if ( preg_match( '/\b(hacked|breach|malware|virus|injected|compromised)\b/i', $combined ) ) {
			$category         = 'security';
			$estimated_cause  = 'Security breach or malware';
			$immediate_action = array(
				__( 'Change all passwords immediately', 'plugin-wp-support-thisismyurl' ),
				__( 'Scan for malware', 'plugin-wp-support-thisismyurl' ),
				__( 'Check for unauthorized admin users', 'plugin-wp-support-thisismyurl' ),
			);
			$estimated_time   = '3-6 hours';
		}

		// Performance issues.
		if ( preg_match( '/\b(slow|performance|timeout|memory|cpu)\b/i', $combined ) ) {
			$category         = 'performance';
			$estimated_cause  = 'Plugin conflict, hosting limits, or database optimization needed';
			$immediate_action = array(
				__( 'Enable caching plugin', 'plugin-wp-support-thisismyurl' ),
				__( 'Disable resource-heavy plugins', 'plugin-wp-support-thisismyurl' ),
				__( 'Contact hosting provider about limits', 'plugin-wp-support-thisismyurl' ),
			);
			$estimated_time   = '2-4 hours';
		}

		// Plugin/theme issues.
		if ( preg_match( '/\b(plugin|theme|conflict|compatibility)\b/i', $combined ) ) {
			$category         = 'plugin';
			$estimated_cause  = 'Plugin or theme conflict';
			$immediate_action = array(
				__( 'Deactivate recently installed plugins', 'plugin-wp-support-thisismyurl' ),
				__( 'Switch to default theme temporarily', 'plugin-wp-support-thisismyurl' ),
				__( 'Enable WordPress debug mode', 'plugin-wp-support-thisismyurl' ),
			);
			$estimated_time   = '1-2 hours';
		}

		return array(
			'category'         => $category,
			'estimated_cause'  => $estimated_cause,
			'immediate_action' => $immediate_action,
			'estimated_time'   => $estimated_time,
			'severity_level'   => $data['severity'] ?? 'high',
		);
	}

	/**
	 * Send incident notifications.
	 *
	 * @param string $incident_id Incident ID.
	 * @return void
	 */
	public static function send_incident_notifications( string $incident_id ): void {
		$incident = self::get_incident( $incident_id );

		if ( ! $incident ) {
			return;
		}

		// Send to user.
		self::send_user_confirmation( $incident );

		// Send to admin.
		self::send_admin_notification( $incident );
	}

	/**
	 * Send confirmation email to user.
	 *
	 * @param array $incident Incident data.
	 * @return void
	 */
	private static function send_user_confirmation( array $incident ): void {
		$to      = $incident['email'] ?? '';
		$subject = sprintf( __( 'Emergency SOS Incident #%s Received', 'plugin-wp-support-thisismyurl' ), $incident['id'] ?? '' );

		$message = sprintf(
			"Hello %s,\n\n" .
			"We've received your emergency support request.\n\n" .
			"Incident ID: #%s\n" .
			"Subject: %s\n" .
			"Severity: %s\n" .
			"Status: %s\n\n" .
			"Auto-Triage Results:\n" .
			"- Issue Type: %s\n" .
			"- Estimated Cause: %s\n" .
			"- Estimated Resolution Time: %s\n\n" .
			"Immediate Actions You Can Take:\n%s\n\n" .
			"Our team has been notified and will respond according to your severity level.\n\n" .
			"For critical issues (site down, data loss, security breach):\n" .
			"- Expected response: Within 2 hours\n" .
			"- We'll contact you at: %s\n\n" .
			"Track your incident: %s\n\n" .
			"Thank you,\n" .
			'WordPress Support Team',
			$incident['name'] ?? 'there',
			$incident['id'] ?? '',
			$incident['subject'] ?? '',
			ucfirst( $incident['severity'] ?? 'high' ),
			ucfirst( $incident['status'] ?? 'pending' ),
			ucfirst( str_replace( '_', ' ', $incident['triage']['category'] ?? 'general' ) ),
			$incident['triage']['estimated_cause'] ?? 'Unknown',
			$incident['triage']['estimated_time'] ?? '2-4 hours',
			! empty( $incident['triage']['immediate_action'] ) ? '- ' . implode( "\n- ", $incident['triage']['immediate_action'] ) : 'None',
			$incident['phone'] ?? $incident['email'] ?? '',
			admin_url( 'admin.php?page=wps-sos-support&incident=' . $incident['id'] )
		);

		wp_mail( $to, $subject, $message );
	}

	/**
	 * Send notification email to admin.
	 *
	 * @param array $incident Incident data.
	 * @return void
	 */
	private static function send_admin_notification( array $incident ): void {
		$admin_email = get_option( 'admin_email' );

		$subject = sprintf( __( '🚨 New Emergency SOS: #%1$s (%2$s)', 'plugin-wp-support-thisismyurl' ), $incident['id'] ?? '', ucfirst( $incident['severity'] ?? 'high' ) );

		$message = sprintf(
			"New Emergency SOS Incident Received\n\n" .
			"Incident ID: #%s\n" .
			"Severity: %s\n" .
			"Category: %s\n" .
			"Status: %s\n\n" .
			"Contact Information:\n" .
			"Name: %s\n" .
			"Email: %s\n" .
			"Phone: %s\n" .
			"Site: %s\n\n" .
			"Subject: %s\n\n" .
			"Description:\n%s\n\n" .
			"Auto-Triage:\n" .
			"- Issue Type: %s\n" .
			"- Estimated Cause: %s\n" .
			"- Estimated Time: %s\n\n" .
			"View Incident: %s\n\n" .
			'Response Required: %s',
			$incident['id'] ?? '',
			ucfirst( $incident['severity'] ?? 'high' ),
			ucfirst( str_replace( '_', ' ', $incident['triage']['category'] ?? 'general' ) ),
			ucfirst( $incident['status'] ?? 'pending' ),
			$incident['name'] ?? 'Not provided',
			$incident['email'] ?? 'Not provided',
			$incident['phone'] ?? 'Not provided',
			$incident['site_url'] ?? home_url(),
			$incident['subject'] ?? '',
			$incident['description'] ?? '',
			ucfirst( str_replace( '_', ' ', $incident['triage']['category'] ?? 'general' ) ),
			$incident['triage']['estimated_cause'] ?? 'Unknown',
			$incident['triage']['estimated_time'] ?? '2-4 hours',
			admin_url( 'admin.php?page=wps-sos-support&incident=' . $incident['id'] ),
			'critical' === $incident['severity'] ? 'Within 2 hours' : 'Within 4 hours'
		);

		wp_mail( $admin_email, $subject, $message );
	}

	/**
	 * Get incident by ID.
	 *
	 * @param string $incident_id Incident ID.
	 * @return array|null Incident data or null if not found.
	 */
	public static function get_incident( string $incident_id ): ?array {
		$incidents = get_option( self::INCIDENTS_KEY, array() );
		return $incidents[ $incident_id ] ?? null;
	}

	/**
	 * Get incidents with filters.
	 *
	 * @param array $filters Filter criteria.
	 * @param int   $limit   Maximum number of incidents to return.
	 * @return array Filtered incidents.
	 */
	public static function get_incidents( array $filters = array(), int $limit = 0 ): array {
		$incidents = get_option( self::INCIDENTS_KEY, array() );

		// Apply filters.
		if ( ! empty( $filters['status'] ) ) {
			$incidents = array_filter(
				$incidents,
				static function ( $incident ) use ( $filters ) {
					return ( $incident['status'] ?? '' ) === $filters['status'];
				}
			);
		}

		if ( ! empty( $filters['severity'] ) ) {
			$incidents = array_filter(
				$incidents,
				static function ( $incident ) use ( $filters ) {
					return ( $incident['severity'] ?? '' ) === $filters['severity'];
				}
			);
		}

		// Sort by created date (newest first).
		usort(
			$incidents,
			static function ( $a, $b ) {
				return ( $b['created'] ?? 0 ) - ( $a['created'] ?? 0 );
			}
		);

		// Apply limit.
		if ( $limit > 0 ) {
			$incidents = array_slice( $incidents, 0, $limit );
		}

		return $incidents;
	}

	/**
	 * Get count of pending incidents.
	 *
	 * @return int Count of pending incidents.
	 */
	public static function get_pending_incidents_count(): int {
		$incidents = self::get_incidents( array( 'status' => 'pending' ) );
		return count( $incidents );
	}

	/**
	 * Update incident status.
	 *
	 * @param string $incident_id Incident ID.
	 * @param string $status      New status.
	 * @return bool Success.
	 */
	public static function update_incident_status( string $incident_id, string $status ): bool {
		$incidents = get_option( self::INCIDENTS_KEY, array() );

		if ( ! isset( $incidents[ $incident_id ] ) ) {
			return false;
		}

		$incidents[ $incident_id ]['status']  = $status;
		$incidents[ $incident_id ]['updated'] = time();

		return update_option( self::INCIDENTS_KEY, $incidents );
	}

	/**
	 * Add note to incident.
	 *
	 * @param string $incident_id Incident ID.
	 * @param string $note        Note text.
	 * @param int    $user_id     User ID adding the note.
	 * @return bool Success.
	 */
	public static function add_incident_note( string $incident_id, string $note, int $user_id = 0 ): bool {
		$incidents = get_option( self::INCIDENTS_KEY, array() );

		if ( ! isset( $incidents[ $incident_id ] ) ) {
			return false;
		}

		if ( 0 === $user_id ) {
			$user_id = get_current_user_id();
		}

		$user = get_userdata( $user_id );

		$incidents[ $incident_id ]['notes'][] = array(
			'note'      => $note,
			'user'      => $user ? $user->display_name : __( 'System', 'plugin-wp-support-thisismyurl' ),
			'timestamp' => time(),
		);

		$incidents[ $incident_id ]['updated'] = time();

		return update_option( self::INCIDENTS_KEY, $incidents );
	}

	/**
	 * AJAX handler for updating incident status.
	 *
	 * @return void
	 */
	public static function ajax_update_incident_status(): void {
		check_ajax_referer( 'wps_sos_actions', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'plugin-wp-support-thisismyurl' ) ) );
		}

		$incident_id = \WPS\CoreSupport\wps_get_post_text( 'incident_id' );
		$status      = \WPS\CoreSupport\wps_get_post_key( 'status' );

		if ( empty( $incident_id ) || empty( $status ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid parameters', 'plugin-wp-support-thisismyurl' ) ) );
		}

		$updated = self::update_incident_status( $incident_id, $status );

		if ( $updated ) {
			wp_send_json_success( array( 'message' => __( 'Status updated', 'plugin-wp-support-thisismyurl' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to update status', 'plugin-wp-support-thisismyurl' ) ) );
		}
	}

	/**
	 * AJAX handler for adding incident note.
	 *
	 * @return void
	 */
	public static function ajax_add_incident_note(): void {
		check_ajax_referer( 'wps_sos_actions', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'plugin-wp-support-thisismyurl' ) ) );
		}

		$incident_id = \WPS\CoreSupport\wps_get_post_text( 'incident_id' );
		$note        = \WPS\CoreSupport\wps_get_post_textarea( 'note' );

		if ( empty( $incident_id ) || empty( $note ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid parameters', 'plugin-wp-support-thisismyurl' ) ) );
		}

		$added = self::add_incident_note( $incident_id, $note );

		if ( $added ) {
			wp_send_json_success( array( 'message' => __( 'Note added', 'plugin-wp-support-thisismyurl' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to add note', 'plugin-wp-support-thisismyurl' ) ) );
		}
	}

	/**
	 * Get severity color.
	 *
	 * @param string $severity Severity level.
	 * @return string Color code.
	 */
	private static function get_severity_color( string $severity ): string {
		$colors = array(
			'critical' => '#c00',
			'urgent'   => '#f90',
			'high'     => '#fa0',
			'medium'   => '#0073aa',
			'low'      => '#46b450',
		);

		return $colors[ $severity ] ?? '#999';
	}
}
