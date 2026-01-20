<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Diagnostic_CSS_Classes extends Diagnostic_Base {

	protected function get_id(): string {
		return 'css-classes';
	}

	protected function get_title(): string {
		return __( 'Excessive CSS Classes', 'wpshadow' );
	}

	protected function get_description(): string {
		return __( 'Checks for excessive CSS classes on body, post, and navigation elements that can be simplified.', 'wpshadow' );
	}

	protected function get_category(): string {
		return 'performance';
	}

	protected function get_severity(): string {
		return 'low';
	}

	protected function is_auto_fixable(): bool {
		return true;
	}

	public function check(): ?array {
		if ( get_option( 'wpshadow_css_class_cleanup_enabled', false ) ) {
			return null;
		}

		$body_classes = get_body_class();
		$class_count  = count( $body_classes );

		if ( $class_count < 10 ) {
			return null;
		}

		return array(
			'finding_id'   => $this->get_id(),
			'title'        => $this->get_title(),
			'description'  => sprintf(
				__( 'Found %d CSS classes on body element. Simplifying classes reduces HTML size and improves performance. WordPress often adds unnecessary classes for post types, templates, and browser detection.', 'wpshadow' ),
				$class_count
			),
			'category'     => $this->get_category(),
			'severity'     => $this->get_severity(),
			'threat_level' => 20,
			'auto_fixable' => $this->is_auto_fixable(),
			'timestamp'    => current_time( 'mysql' ),
		);
	}
}
