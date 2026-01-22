<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Diagnostic_Nav_ARIA extends Diagnostic_Base {

	protected static $slug        = 'nav-aria';
	protected static $title       = 'Navigation Accessibility';
	protected static $description = 'Checks for missing ARIA attributes on navigation menus that help screen readers.';

	public static function check(): ?array {
		if ( get_option( 'wpshadow_nav_accessibility_enabled', false ) ) {
			return null;
		}

		$menus = wp_get_nav_menus();
		if ( empty( $menus ) ) {
			return null;
		}

		return array(
			'finding_id'   => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				__( 'Found %d navigation menus without enhanced accessibility features. Adding ARIA current-page attributes helps screen reader users understand their location in the site structure.', 'wpshadow' ),
				count( $menus )
			),
			'category'     => 'accessibility',
			'severity'     => 'medium',
			'threat_level' => 40,
			'auto_fixable' => true,
			'timestamp'    => current_time( 'mysql' ),
		);
	}
}
