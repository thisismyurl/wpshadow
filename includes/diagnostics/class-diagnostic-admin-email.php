<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Diagnostic_Admin_Email extends Diagnostic_Base {

	protected function get_id(): string {
		return 'admin-email';
	}

	protected function get_title(): string {
		return __( 'Admin Email Configuration', 'wpshadow' );
	}

	protected function get_description(): string {
		return __( 'Checks if admin email is valid and configured.', 'wpshadow' );
	}

	protected function get_category(): string {
		return 'settings';
	}

	protected function get_severity(): string {
		return 'high';
	}

	protected function is_auto_fixable(): bool {
		return false;
	}

	public function check(): ?array {
		$admin_email = get_option( 'admin_email' );

		if ( empty( $admin_email ) ) {
			return array(
				'finding_id'   => $this->get_id(),
				'title'        => $this->get_title(),
				'description'  => __( 'Admin email is not configured. WordPress sends critical notifications to this address including security alerts, update notifications, and user registration confirmations.', 'wpshadow' ),
				'category'     => $this->get_category(),
				'severity'     => 'critical',
				'threat_level' => 90,
				'auto_fixable' => $this->is_auto_fixable(),
				'timestamp'    => current_time( 'mysql' ),
			);
		}

		if ( false === filter_var( $admin_email, FILTER_VALIDATE_EMAIL ) ) {
			return array(
				'finding_id'   => $this->get_id(),
				'title'        => $this->get_title(),
				'description'  => sprintf(
					__( 'Admin email "%s" is not a valid email address. You will not receive important notifications about your site.', 'wpshadow' ),
					esc_html( $admin_email )
				),
				'category'     => $this->get_category(),
				'severity'     => $this->get_severity(),
				'threat_level' => 85,
				'auto_fixable' => $this->is_auto_fixable(),
				'timestamp'    => current_time( 'mysql' ),
			);
		}

		if ( strpos( $admin_email, 'example.com' ) !== false || strpos( $admin_email, 'test.com' ) !== false ) {
			return array(
				'finding_id'   => $this->get_id(),
				'title'        => $this->get_title(),
				'description'  => sprintf(
					__( 'Admin email appears to be a placeholder (%s). Set a real, monitored email address.', 'wpshadow' ),
					esc_html( $admin_email )
				),
				'category'     => $this->get_category(),
				'severity'     => $this->get_severity(),
				'threat_level' => 70,
				'auto_fixable' => $this->is_auto_fixable(),
				'timestamp'    => current_time( 'mysql' ),
			);
		}

		return null;
	}
}
