<?php declare(strict_types=1);
/**
 * Feature: Magic Link Support Access
 *
 * Secure time-limited login URLs for developers.
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.75001
 */

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


final class WPSHADOW_Feature_Magic_Link_Support extends WPSHADOW_Abstract_Feature {

	public function __construct() {
		parent::__construct( array(
			'id'          => 'magic-link-support',
			'name'        => __( 'Temporary Support Login', 'wpshadow' ),
			'description' => __( 'Create secure, temporary login links so developers can help fix your site without needing your password.', 'wpshadow' ),
			'aliases'     => array( 'magic link', 'support access', 'temporary login', 'developer access', 'passwordless login', 'emergency access', 'support link', 'secure access', 'time-limited login', 'temporary access', 'support token', 'developer login' ),
			'sub_features' => array(
				'log_sessions'        => array(
					'name'               => __( 'Log Sessions', 'wpshadow' ),
					'description_short'  => __( 'Record who logs in with magic links', 'wpshadow' ),
					'description_long'   => __( 'Keeps detailed logs of every developer who uses a magic link to access your site, including when they logged in, how long they stayed, and what actions they took. This creates an audit trail for security purposes and helps you monitor who has access to your site. Essential for compliance and security tracking.', 'wpshadow' ),
					'description_wizard' => __( 'Track who accessed your site via magic link. Essential for security audits and compliance. Highly recommended to keep enabled.', 'wpshadow' ),
					'default_enabled'    => true,
				),
				'email_notifications' => array(
					'name'               => __( 'Email Notifications', 'wpshadow' ),
					'description_short'  => __( 'Email when support logs in', 'wpshadow' ),
					'description_long'   => __( 'Sends an email notification to your admin email address whenever a developer logs in with a magic link. This alerts you immediately when someone accesses your site and lets you verify it was authorized. Disabled by default but recommended for security-conscious sites.', 'wpshadow' ),
					'description_wizard' => __( 'Get notified immediately when support accesses your site. Recommended for security - you can verify the access was authorized.', 'wpshadow' ),
					'default_enabled'    => false,
				),
				'role_restriction'    => array(
					'name'               => __( 'Role Restriction', 'wpshadow' ),
					'description_short'  => __( 'Limit support access permissions', 'wpshadow' ),
					'description_long'   => __( 'Restricts support developers to Editor role instead of Administrator. Editors have broad capabilities but cannot manage users, plugins, or core settings. This limits potential damage if the account is compromised or abused. Disabled by default for convenience - enable if you want developers to have limited permissions for security.', 'wpshadow' ),
					'description_wizard' => __( 'Restrict developers to Editor role for limited access. More secure but less convenient. Enable for sensitive sites that need to minimize access.', 'wpshadow' ),
					'default_enabled'    => false,
				),
			),
		) );

		$this->register_default_settings( array(
			'log_sessions'        => true,
			'email_notifications' => false,
			'role_restriction'    => false,
		) );
	}

	public function register(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		add_action( 'init', array( $this, 'handle_magic_link_login' ) );
		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );
	}

	/**
	 * Create magic link for developer access.
	 */
	public function create_magic_link( string $developer_name, string $developer_email ): array {
		if ( ! current_user_can( 'manage_options' ) ) {
			return array(
				'success' => false,
				'error'   => __( 'Insufficient permissions.', 'wpshadow' ),
			);
		}

		$token = wp_generate_uuid4();
		$link = add_query_arg( 'magic_link_token', $token, home_url() );

		// Store token
		set_transient(
			'wpshadow_magic_link_' . $token,
			array(
				'developer_name' => $developer_name,
				'developer_email' => $developer_email,
				'created_at' => time(),
				'expires_at' => time() + ( 24 * HOUR_IN_SECONDS ),
			),
			24 * HOUR_IN_SECONDS
		);

		if ( $this->is_sub_feature_enabled( 'log_sessions', true ) ) {
			$this->log_activity(
				'Magic Link Created',
				sprintf( 'Magic link created for: %s (%s)', $developer_name, $developer_email ),
				'security'
			);
		}

		return array(
			'success' => true,
			'link'    => $link,
			'token'   => $token,
		);
	}

	/**
	 * Handle magic link authentication.
	 */
	public function handle_magic_link_login(): void {
		if ( ! isset( $_GET['magic_link_token'] ) ) {
			return;
		}

		$token = sanitize_text_field( wp_unslash( $_GET['magic_link_token'] ) );
		$verification = $this->verify_magic_link( $token );

		if ( ! $verification['valid'] ) {
			wp_die(
				esc_html( $verification['message'] ),
				esc_html__( 'Invalid Magic Link', 'wpshadow' ),
				array( 'response' => 403 )
			);
		}

		$data = $verification['data'];

		// Create temporary support user
		$username = 'wpshadow_support_' . substr( $token, 0, 8 );
		$user_id = username_exists( $username );

		if ( ! $user_id ) {
			$user_id = wp_create_user(
				$username,
				wp_generate_password( 32, true, true ),
				$data['developer_email']
			);

			if ( is_wp_error( $user_id ) ) {
				wp_die(
					esc_html__( 'Unable to create support user.', 'wpshadow' ),
					esc_html__( 'Login Failed', 'wpshadow' ),
					array( 'response' => 500 )
				);
			}

			// Set role based on restriction setting
			if ( $this->is_sub_feature_enabled( 'role_restriction', false ) ) {
				$user = new \WP_User( $user_id );
				$user->set_role( 'editor' );
			} else {
				$user = new \WP_User( $user_id );
				$user->set_role( 'administrator' );
			}
		}

		// Log the access
		if ( $this->is_sub_feature_enabled( 'log_sessions', true ) ) {
			$this->log_activity(
				'Magic Link Used',
				sprintf(
					'Developer logged in: %s (%s)',
					$data['developer_name'],
					$data['developer_email']
				),
				'security'
			);
		}

		// Send email notification
		if ( $this->is_sub_feature_enabled( 'email_notifications', false ) ) {
			$admin_email = get_option( 'admin_email' );
			wp_mail(
				$admin_email,
				__( 'Developer Support Access Granted', 'wpshadow' ),
				sprintf(
					__( "A developer has accessed your site using a magic link.\n\nDeveloper: %s\nEmail: %s\nTime: %s\n\nThis session will expire in 24 hours.", 'wpshadow' ),
					$data['developer_name'],
					$data['developer_email'],
					wp_date( 'Y-m-d H:i:s' )
				)
			);
		}

		// Delete the one-time token
		delete_transient( 'wpshadow_magic_link_' . $token );

		// Log the user in
		wp_set_current_user( $user_id );
		wp_set_auth_cookie( $user_id, true );

		// Redirect to admin
		wp_safe_redirect( admin_url() );
		exit;
	}

	/**
	 * Verify and validate magic link token.
	 */
	public function verify_magic_link( string $token ): array {
		$data = get_transient( 'wpshadow_magic_link_' . $token );

		if ( ! $data ) {
			return array(
				'valid'   => false,
				'message' => __( 'Magic link expired or invalid.', 'wpshadow' ),
			);
		}

		if ( $data['expires_at'] < time() ) {
			delete_transient( 'wpshadow_magic_link_' . $token );
			return array(
				'valid'   => false,
				'message' => __( 'Magic link expired.', 'wpshadow' ),
			);
		}

		return array(
			'valid' => true,
			'data'  => $data,
		);
	}

	/**
	 * Revoke magic link.
	 */
	public function revoke_magic_link( string $token ): array {
		if ( ! current_user_can( 'manage_options' ) ) {
			return array(
				'success' => false,
				'error'   => __( 'Insufficient permissions.', 'wpshadow' ),
			);
		}

		delete_transient( 'wpshadow_magic_link_' . $token );

		if ( $this->is_sub_feature_enabled( 'log_sessions', true ) ) {
			$this->log_activity(
				'Magic Link Revoked',
				'Magic link revoked: ' . $token,
				'security'
			);
		}

		return array( 'success' => true );
	}

	public function register_site_health_test( array $tests ): array {
		$tests['direct']['magic_link'] = array(
			'label'  => __( 'Magic Link Support', 'wpshadow' ),
			'test'   => array( $this, 'test_magic_link' ),
		);

		return $tests;
	}

	public function test_magic_link(): array {
		if ( ! $this->is_enabled() ) {
			return array(
				'label'       => __( 'Magic Link Support', 'wpshadow' ),
				'status'      => 'recommended',
				'badge'       => array( 'label' => __( 'WPShadow', 'wpshadow' ) ),
				'description' => __( 'Enable magic link support for secure developer access.', 'wpshadow' ),
				'actions'     => '',
				'test'        => 'magic_link',
			);
		}

		$enabled_count = 0;
		$subs = array( 'log_sessions', 'email_notifications', 'role_restriction' );
		foreach ( $subs as $sub ) {
			if ( $this->is_sub_feature_enabled( $sub, false ) ) {
				$enabled_count++;
			}
		}

		return array(
			'label'       => __( 'Magic Link Support', 'wpshadow' ),
			'status'      => 'good',
			'badge'       => array( 'label' => __( 'WPShadow', 'wpshadow' ) ),
			'description' => sprintf(
				__( '%d of 3 sub-features enabled.', 'wpshadow' ),
				$enabled_count
			),
			'actions'     => '',
			'test'        => 'magic_link',
		);
	}
}
