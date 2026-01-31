<?php
/**
 * Beaver Builder Pro Templates Diagnostic
 *
 * Beaver Builder Pro Templates issues found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.800.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Beaver Builder Pro Templates Diagnostic Class
 *
 * @since 1.800.0000
 */
class Diagnostic_BeaverBuilderProTemplates extends Diagnostic_Base {

	protected static $slug = 'beaver-builder-pro-templates';
	protected static $title = 'Beaver Builder Pro Templates';
	protected static $description = 'Beaver Builder Pro Templates issues found';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'FLBuilder' ) ) {
			return null;
		}
		
		$issues = array();

		// Check 1: Verify template library enabled
		$library_enabled = get_option( 'fl_builder_template_library_enabled', false );
		if ( ! $library_enabled ) {
			$issues[] = __( 'Beaver Builder template library not enabled', 'wpshadow' );
		}

		// Check 2: Check cloud templates access
		$cloud_access = get_option( 'fl_builder_cloud_templates', false );
		if ( ! $cloud_access ) {
			$issues[] = __( 'Cloud template access not configured', 'wpshadow' );
		}

		// Check 3: Verify template caching
		$template_cache = get_transient( 'fl_builder_template_cache' );
		if ( false === $template_cache ) {
			$issues[] = __( 'Template caching not active', 'wpshadow' );
		}

		// Check 4: Check responsive templates
		$responsive = get_option( 'fl_builder_responsive_templates', false );
		if ( ! $responsive ) {
			$issues[] = __( 'Responsive template support not enabled', 'wpshadow' );
		}

		// Check 5: Verify template export
		$export_enabled = get_option( 'fl_builder_template_export', false );
		if ( ! $export_enabled ) {
			$issues[] = __( 'Template export functionality not enabled', 'wpshadow' );
		}

		// Check 6: Check template versioning
		$versioning = get_option( 'fl_builder_template_versioning', false );
		if ( ! $versioning ) {
			$issues[] = __( 'Template versioning not enabled', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 75, 50 + ( count( $issues ) * 5 ) );
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: Comma-separated list of issues */
					__( 'Beaver Builder Pro template issues detected: %s', 'wpshadow' ),
					implode( ', ', $issues )
				),
				'severity'     => 'medium',
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/beaver-builder-pro-templates',
			);
		}

		return null;
	}
}

	}
}
