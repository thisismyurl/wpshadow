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

final class WPSHADOW_Feature_Magic_Link_Support extends WPSHADOW_Abstract_Feature {

	public function __construct() {
		parent::__construct( array(
			'id'          => 'magic-link-support',
			'name'        => __( 'Magic Link Support Access', 'wpshadow' ),
			'description' => __( 'Generate secure, time-limited login URLs for developer support.', 'wpshadow' ),
			'sub_features' => array(
				'log_sessions'        => __( 'Log Sessions', 'wpshadow' ),
				'email_notifications' => __( 'Email Notifications', 'wpshadow' ),
				'role_restriction'    => __( 'Role Restriction', 'wpshadow' ),
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
