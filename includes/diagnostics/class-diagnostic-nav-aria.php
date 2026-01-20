<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Diagnostic_Nav_ARIA extends Diagnostic_Base {

	protected function get_id(): string {
		return 'nav-aria';
	}

	protected function get_title(): string {
		return __( 'Navigation Accessibility', 'wpshadow' );
	}

	protected function get_description(): string {
		return __( 'Checks for missing ARIA attributes on navigation menus that help screen readers.', 'wpshadow' );
	}

	protected function get_category(): string {
		return 'accessibility';
	}

	protected function get_severity(): string {
		return 'medium';
	}

	protected function is_auto_fixable(): bool {
		return true;
	}

	public function check(): ?array {
		if ( get_option( 'wpshadow_nav_accessibility_enabled', false ) ) {
			return null;
		}

		$menus = wp_get_nav_menus();
		if ( empty( $menus ) ) {
			return null;
		}

		return array(
			'finding_id'   => $this->get_id(),
			'title'        => $this->get_title(),
			'description'  => sprintf(
				__( 'Found %d navigation menus without enhanced accessibility features. Adding ARIA current-page attributes helps screen reader users understand their location in the site structure.', 'wpshadow' ),
				count( $menus )
			),
			'category'     => $this->get_category(),
			'severity'     => $this->get_severity(),
			'threat_level' => 40,
			'auto_fixable' => $this->is_auto_fixable(),
			'timestamp'    => current_time( 'mysql' ),
		);
	}
}
