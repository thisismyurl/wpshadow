<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Diagnostic_Admin_Username extends Diagnostic_Base {

	protected function get_id(): string {
		return 'admin-username';
	}

	protected function get_title(): string {
		return __( 'Default Admin Username', 'wpshadow' );
	}

	protected function get_description(): string {
		return __( 'Checks if the default "admin" username exists, which is a security vulnerability.', 'wpshadow' );
	}

	protected function get_category(): string {
		return 'security';
	}

	protected function get_severity(): string {
		return 'high';
	}

	protected function is_auto_fixable(): bool {
		return false;
	}

	public function check(): ?array {
		$admin_user = get_user_by( 'login', 'admin' );

		if ( ! $admin_user ) {
			return null;
		}

		return array(
			'finding_id'   => $this->get_id(),
			'title'        => $this->get_title(),
			'description'  => __( 'Default "admin" username exists. This is a major security vulnerability as it\'s a primary brute-force target. Create a new admin account with a unique username and delete this one.', 'wpshadow' ),
			'category'     => $this->get_category(),
			'severity'     => $this->get_severity(),
			'threat_level' => 85,
			'auto_fixable' => $this->is_auto_fixable(),
			'timestamp'    => current_time( 'mysql' ),
		);
	}
}
