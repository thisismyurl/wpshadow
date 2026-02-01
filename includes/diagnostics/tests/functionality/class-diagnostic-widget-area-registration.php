<?php
/**
 * Widget Area Registration Diagnostic
 *
 * Validates that widget areas (sidebars) are properly registered
 * with appropriate settings and actually used in theme templates.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6032.1335
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Widget Area Registration Diagnostic Class
 *
 * Checks widget area configuration and usage.
 *
 * @since 1.6032.1335
 */
class Diagnostic_Widget_Area_Registration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'widget-area-registration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Widget Area Registration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates widget area registration and usage';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6032.1335
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Get all registered sidebars.
		global $wp_registered_sidebars;

		if ( empty( $wp_registered_sidebars ) ) {
			return null; // No sidebars registered - might be a block theme.
		}

		// Check for reasonable number of sidebars.
		if ( count( $wp_registered_sidebars ) > 20 ) {
			$issues[] = sprintf(
				/* translators: %d: number of widget areas */
				__( '%d widget areas registered (may be excessive)', 'wpshadow' ),
				count( $wp_registered_sidebars )
			);
		}

		// Check each sidebar for proper configuration.
		$empty_sidebars    = array();
		$unused_sidebars   = array();
		$unnamed_sidebars  = array();

		foreach ( $wp_registered_sidebars as $sidebar_id => $sidebar ) {
			// Check for proper name.
			if ( empty( $sidebar['name'] ) || 'Sidebar' === $sidebar['name'] ) {
				$unnamed_sidebars[] = $sidebar_id;
			}

			// Check if sidebar is empty (no widgets).
			$sidebar_widgets = wp_get_sidebars_widgets();
			if ( isset( $sidebar_widgets[ $sidebar_id ] ) && empty( $sidebar_widgets[ $sidebar_id ] ) ) {
				$empty_sidebars[] = array(
					'id'   => $sidebar_id,
					'name' => $sidebar['name'],
				);
			}

			// Check for before/after widget markup.
			if ( empty( $sidebar['before_widget'] ) || empty( $sidebar['after_widget'] ) ) {
				$issues[] = sprintf(
					/* translators: %s: sidebar name */
					__( 'Widget area "%s" lacks before/after widget markup', 'wpshadow' ),
					$sidebar['name']
				);
			}
		}

		if ( ! empty( $unnamed_sidebars ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of unnamed sidebars */
				__( '%d widget areas have generic/missing names', 'wpshadow' ),
				count( $unnamed_sidebars )
			);
		}

		if ( count( $empty_sidebars ) > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of empty sidebars */
				__( '%d widget areas are empty (consider removing unused areas)', 'wpshadow' ),
				count( $empty_sidebars )
			);
		}

		// Check theme templates for sidebar usage.
		$template_dir = get_template_directory();
		$templates    = array( 'sidebar.php', 'sidebar-footer.php', 'footer.php' );

		$templates_using_sidebars = 0;
		foreach ( $templates as $template ) {
			$file = $template_dir . '/' . $template;
			if ( file_exists( $file ) ) {
				$content = file_get_contents( $file );
				if ( false !== stripos( $content, 'dynamic_sidebar' ) || false !== stripos( $content, 'is_active_sidebar' ) ) {
					$templates_using_sidebars++;
				}
			}
		}

		if ( $templates_using_sidebars === 0 && count( $wp_registered_sidebars ) > 0 ) {
			$issues[] = __( 'Widget areas registered but no templates display them', 'wpshadow' );
		}

		// Check for orphaned widget instances.
		$sidebar_widgets = wp_get_sidebars_widgets();
		$orphaned_widgets = 0;

		if ( isset( $sidebar_widgets['wp_inactive_widgets'] ) ) {
			$orphaned_widgets = count( $sidebar_widgets['wp_inactive_widgets'] );
		}

		if ( $orphaned_widgets > 10 ) {
			$issues[] = sprintf(
				/* translators: %d: number of inactive widgets */
				__( '%d inactive widgets (clean up recommended)', 'wpshadow' ),
				$orphaned_widgets
			);
		}

		// Check for duplicate sidebar IDs (shouldn't happen but check anyway).
		$sidebar_ids = array_keys( $wp_registered_sidebars );
		$unique_ids  = array_unique( $sidebar_ids );
		if ( count( $sidebar_ids ) !== count( $unique_ids ) ) {
			$issues[] = __( 'Duplicate widget area IDs detected (registration conflict)', 'wpshadow' );
		}

		// Check theme functions.php for registration.
		$functions_file = $template_dir . '/functions.php';
		if ( file_exists( $functions_file ) ) {
			$content = file_get_contents( $functions_file );

			if ( false !== stripos( $content, 'register_sidebar' ) ) {
				// Check for proper sanitization.
				if ( false === stripos( $content, 'before_widget' ) ) {
					// Not critical, might be set elsewhere.
				}
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of widget area issues */
					__( 'Found %d widget area configuration issues.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'details'      => array(
					'issues'          => $issues,
					'total_sidebars'  => count( $wp_registered_sidebars ),
					'empty_sidebars'  => count( $empty_sidebars ),
					'orphaned_widgets' => $orphaned_widgets,
					'recommendation'  => __( 'Ensure widget areas have descriptive names, proper markup, and are actually used in templates.', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
