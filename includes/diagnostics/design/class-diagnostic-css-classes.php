<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Diagnostic_CSS_Classes extends Diagnostic_Base {

	protected static $slug = 'css-classes';
	protected static $title = 'Excessive CSS Classes';
	protected static $description = 'Checks for excessive CSS classes on body, post, and navigation elements that can be simplified.';

	public static function check(): ?array {
		if ( get_option( 'wpshadow_css_class_cleanup_enabled', false ) ) {
			return null;
		}

		$body_classes = get_body_class();
		$class_count  = count( $body_classes );

		if ( $class_count < 10 ) {
			return null;
		}

		return array(
			'finding_id'   => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				__( 'Found %d CSS classes on body element. Simplifying classes reduces HTML size and improves performance. WordPress often adds unnecessary classes for post types, templates, and browser detection.', 'wpshadow' ),
				$class_count
			),
			'category'     => 'performance',
			'severity'     => 'low',
			'threat_level' => 20,
			'auto_fixable' => true,
			'timestamp'    => current_time( 'mysql' ),
		);
	}

}