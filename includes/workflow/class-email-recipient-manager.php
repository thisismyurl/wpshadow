<?php
/**
 * Email Recipient Manager - Manages pre-approved email recipients for workflows
 *
 * Handles verification, approval, and management of email recipients that can be used in workflows.
 * Requires admin approval before emails can be added to the pre-approved list.
 *
 * @package WPShadow
 * @subpackage Workflow
 */

namespace WPShadow\Workflow;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manages pre-approved email recipients
 */
class Email_Recipient_Manager {

	const OPTION_KEY = 'wpshadow_approved_email_recipients';
	const VERIFICATION_OPTION_KEY = 'wpshadow_email_verification_tokens';
	const NONCE_ACTION = 'wpshadow_email_recipient';

	/**
	 * Initialize hooks
	 */
	public static function init() {
		add_action( 'wp_ajax_wpshadow_add_email_recipient', array( __CLASS__, 'handle_add_recipient' ) );
		add_action( 'wp_ajax_wpshadow_approve_recipient', array( __CLASS__, 'handle_approve_recipient' ) );
		add_action( 'wp_ajax_wpshadow_remove_recipient', array( __CLASS__, 'handle_remove_recipient' ) );
		add_action( 'wp_ajax_nopriv_wpshadow_verify_email_recipient', array( __CLASS__, 'handle_verify_email' ) );
	}

	/**
	 * Get all approved email recipients
	 *
	 * @return array List of approved recipients with metadata
	 */
	public static function get_approved_recipients() {
		$recipients = get_option( self::OPTION_KEY, array() );
		return is_array( $recipients ) ? $recipients : array();
	}

	/**
	 * Check if an email is pre-approved
	 *
	 * @param string $email Email address to check
	 * @return bool True if approved
	 */
	public static function is_approved( $email ) {
		$recipients = self::get_approved_recipients();
		return isset( $recipients[ $email ] ) && isset( $recipients[ $email ]['approved'] ) && $recipients[ $email ]['approved'];
	}

	/**
	 * Request to add a new email recipient
	 *
	 * Creates a verification token and either sends verification email or marks for admin approval
	 *
	 * @param string $email Email address to add
	 * @param bool   $send_verification Whether to send verification email
	 * @return array Result with success and message
	 */
	public static function request_recipient( $email, $send_verification = true ) {
		// Validate email
		if ( ! is_email( $email ) ) {
			return array(
				'success' => false,
				'message' => 'Invalid email address.',
			);
		}

		// Check if already exists
		$recipients = self::get_approved_recipients();
		if ( isset( $recipients[ $email ] ) ) {
			return array(
				'success' => false,
				'message' => 'This email is already in the system.',
			);
		}

		// Generate verification token
		$token = self::generate_verification_token( $email );

		if ( $send_verification ) {
			// Send verification email
			$result = self::send_verification_email( $email, $token );
			if ( ! $result['success'] ) {
				return $result;
			}

			return array(
				'success' => true,
				'message' => 'Verification email sent to ' . sanitize_email( $email ) . '. Please check your email to approve this recipient.',
			);
		} else {
			// Mark for admin approval
			$recipients[ $email ] = array(
				'approved'      => false,
				'pending_admin'  => true,
				'added_date'     => current_time( 'mysql' ),
				'added_by'       => get_current_user_id(),
			);

			update_option( self::OPTION_KEY, $recipients );

			return array(
				'success' => true,
				'message' => 'Email added. Awaiting admin approval.',
			);
		}
	}

	/**
	 * Generate a unique verification token
	 *
	 * @param string $email Email address
	 * @return string Verification token
	 */
	private static function generate_verification_token( $email ) {
		$token = bin2hex( random_bytes( 32 ) );
		$tokens = get_option( self::VERIFICATION_OPTION_KEY, array() );
		$tokens[ $token ] = array(
			'email'      => $email,
			'created'    => current_time( 'timestamp' ),
			'expires'    => current_time( 'timestamp' ) + ( 7 * DAY_IN_SECONDS ),
		);

		update_option( self::VERIFICATION_OPTION_KEY, $tokens );
		return $token;
	}

	/**
	 * Send verification email to recipient
	 *
	 * @param string $email Email address
	 * @param string $token Verification token
	 * @return array Result
	 */
	private static function send_verification_email( $email, $token ) {
		$verify_url = add_query_arg(
			array(
				'wpshadow_action' => 'verify_email_recipient',
				'token'           => $token,
			),
			home_url()
		);

		$subject = sprintf( 'Verify Email for %s Workflows', get_bloginfo( 'name' ) );
		$message = sprintf(
			"Hello,\n\n" .
			"An admin has requested to add your email (%s) to the approved recipient list for WPShadow workflows.\n\n" .
			"If you approve this, click the link below:\n" .
			"%s\n\n" .
			"This link expires in 7 days.\n\n" .
			"If you did not request this, you can ignore this email.\n\n" .
			"Thanks,\n" .
			"The %s Team",
			sanitize_email( $email ),
			esc_url( $verify_url ),
			get_bloginfo( 'name' )
		);

		$sent = wp_mail( $email, $subject, $message );

		return array(
			'success' => $sent,
			'message' => $sent ? 'Verification email sent.' : 'Failed to send verification email.',
		);
	}

	/**
	 * Verify email using token from email link
	 *
	 * @param string $token Verification token
	 * @return array Result
	 */
	public static function verify_token( $token ) {
		$tokens = get_option( self::VERIFICATION_OPTION_KEY, array() );

		if ( ! isset( $tokens[ $token ] ) ) {
			return array(
				'success' => false,
				'message' => 'Invalid or expired verification token.',
			);
		}

		$token_data = $tokens[ $token ];

		// Check if expired
		if ( $token_data['expires'] < current_time( 'timestamp' ) ) {
			unset( $tokens[ $token ] );
			update_option( self::VERIFICATION_OPTION_KEY, $tokens );

			return array(
				'success' => false,
				'message' => 'Verification token has expired. Please request a new one.',
			);
		}

		$email = $token_data['email'];

		// Add to approved recipients
		$recipients = self::get_approved_recipients();
		$recipients[ $email ] = array(
			'approved'      => true,
			'approved_date' => current_time( 'mysql' ),
			'verification'  => 'email',
		);

		update_option( self::OPTION_KEY, $recipients );

		// Remove the used token
		unset( $tokens[ $token ] );
		update_option( self::VERIFICATION_OPTION_KEY, $tokens );

		return array(
			'success' => true,
			'message' => 'Email verified and approved successfully!',
		);
	}

	/**
	 * Handle AJAX request to add email recipient
	 */
	public static function handle_add_recipient() {
		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], self::NONCE_ACTION ) ) {
			wp_send_json_error( array( 'message' => 'Security check failed.' ) );
		}

		// Verify capability
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'You do not have permission.' ) );
		}

		$email = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';
		$send_verification = isset( $_POST['send_verification'] ) && $_POST['send_verification'];

		if ( empty( $email ) ) {
			wp_send_json_error( array( 'message' => 'Email is required.' ) );
		}

		$result = self::request_recipient( $email, $send_verification );

		if ( $result['success'] ) {
			wp_send_json_success( $result );
		} else {
			wp_send_json_error( $result );
		}
	}

	/**
	 * Handle AJAX request to approve recipient (admin approval)
	 */
	public static function handle_approve_recipient() {
		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], self::NONCE_ACTION ) ) {
			wp_send_json_error( array( 'message' => 'Security check failed.' ) );
		}

		// Verify capability
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'You do not have permission.' ) );
		}

		$email = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';

		if ( empty( $email ) ) {
			wp_send_json_error( array( 'message' => 'Email is required.' ) );
		}

		$recipients = self::get_approved_recipients();

		if ( ! isset( $recipients[ $email ] ) ) {
			wp_send_json_error( array( 'message' => 'Email not found.' ) );
		}

		// Mark as approved
		$recipients[ $email ]['approved'] = true;
		$recipients[ $email ]['approved_date'] = current_time( 'mysql' );
		$recipients[ $email ]['verified_by'] = 'admin_approval';
		unset( $recipients[ $email ]['pending_admin'] );

		update_option( self::OPTION_KEY, $recipients );

		wp_send_json_success( array( 'message' => 'Email approved successfully.' ) );
	}

	/**
	 * Handle AJAX request to remove recipient
	 */
	public static function handle_remove_recipient() {
		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], self::NONCE_ACTION ) ) {
			wp_send_json_error( array( 'message' => 'Security check failed.' ) );
		}

		// Verify capability
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'You do not have permission.' ) );
		}

		$email = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';

		if ( empty( $email ) ) {
			wp_send_json_error( array( 'message' => 'Email is required.' ) );
		}

		$recipients = self::get_approved_recipients();

		if ( ! isset( $recipients[ $email ] ) ) {
			wp_send_json_error( array( 'message' => 'Email not found.' ) );
		}

		// Remove the recipient
		unset( $recipients[ $email ] );
		update_option( self::OPTION_KEY, $recipients );

		wp_send_json_success( array( 'message' => 'Email recipient removed.' ) );
	}

	/**
	 * Handle email verification from verification link
	 */
	public static function handle_verify_email() {
		$token = isset( $_GET['token'] ) ? sanitize_text_field( $_GET['token'] ) : '';

		if ( empty( $token ) ) {
			wp_die( 'No verification token provided.' );
		}

		$result = self::verify_token( $token );

		$message = $result['message'];
		$status = $result['success'] ? 'success' : 'error';

		echo '<div style="padding: 20px; text-align: center; font-family: Arial, sans-serif;">';
		echo '<h2>Email Verification</h2>';
		echo '<p style="color: ' . ( $result['success'] ? 'green' : 'red' ) . ';">' . esc_html( $message ) . '</p>';
		echo '<p><a href="' . esc_url( admin_url( 'admin.php?page=wpshadow-settings' ) ) . '">Back to WPShadow Settings</a></p>';
		echo '</div>';

		wp_die();
	}
}

Email_Recipient_Manager::init();
