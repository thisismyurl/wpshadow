<?php
/**
 * Export Tool Plugin Warning Diagnostic
 *
 * Tests whether export process warns users about plugin-specific data
 * that won't export.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Export Tool Plugin Warning Diagnostic Class
 *
 * Ensures export tools warn users about data that may not be included
 * in the export (e.g., plugin-specific post meta, options).
 *
 * @since 1.6093.1200
 */
class Diagnostic_Export_Tool_Plugin_Warning extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'export-tool-plugin-warning';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Export Tool Plugin Data Warnings';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies export tool warns about non-exported plugin data';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'tools';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks:
	 * - Export handler detects plugin-specific data
	 * - Warnings are shown for WooCommerce, EDD, LMS, membership data
	 * - Export completeness is indicated
	 * - Documentation links provided
	 * - Plugin data detection is comprehensive
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if export handler exists.
		if ( ! class_exists( 'WPShadow\Core\Export_Handler' ) ) {
			$issues[] = __( 'Export handler class not found; plugin data warnings may not be implemented', 'wpshadow' );
		}

		// Check for common plugin-specific data that might not export.
		$plugins_to_check = array(
			'woocommerce/woocommerce.php'           => 'WooCommerce',
			'easy-digital-downloads/easy-digital-downloads.php' => 'Easy Digital Downloads',
			'learndash/learndash.php'               => 'LearnDash LMS',
			'memberpress/memberpress.php'           => 'MemberPress',
			'give/give.php'                         => 'GiveWP',
		);

		$active_plugins = get_option( 'active_plugins', array() );
		$detected_plugins = array();

		foreach ( $plugins_to_check as $plugin_path => $plugin_name ) {
			if ( in_array( $plugin_path, $active_plugins, true ) ) {
				$detected_plugins[] = $plugin_name;
			}
		}

		if ( ! empty( $detected_plugins ) ) {
			// Check if export warnings are configured for these plugins.
			$plugin_warning_filters = apply_filters( 'wpshadow_export_plugin_warnings', array() );

			if ( empty( $plugin_warning_filters ) ) {
				$issues[] = sprintf(
					/* translators: %s: comma-separated plugin names */
					__( 'Detected %d plugins with non-standard data (e.g., %s), but no export warnings are configured; users may not know about data limitations', 'wpshadow' ),
					count( $detected_plugins ),
					implode( ', ', array_slice( $detected_plugins, 0, 2 ) )
				);
			}

			// Check if each detected plugin has a warning defined.
			foreach ( $detected_plugins as $plugin_name ) {
				$warning_defined = false;
				foreach ( $plugin_warning_filters as $filter_data ) {
					if ( isset( $filter_data['plugin_name'] ) && $filter_data['plugin_name'] === $plugin_name ) {
						$warning_defined = true;
						break;
					}
				}

				if ( ! $warning_defined ) {
					$issues[] = sprintf(
						/* translators: %s: plugin name */
						__( 'No export warning defined for %s; users may not know data won\'t be exported', 'wpshadow' ),
						$plugin_name
					);
				}
			}
		}

		// Check if export preview/validation exists.
		if ( ! has_filter( 'wpshadow_export_data_validation' ) ) {
			$issues[] = __( 'Export data validation filter not found; export completeness cannot be verified before export', 'wpshadow' );
		}

		// Check if export process provides feedback about what will be exported.
		if ( ! has_filter( 'wpshadow_export_summary' ) ) {
			$issues[] = __( 'Export summary filter not found; users cannot see what data will be included in export', 'wpshadow' );
		}

		// Check for custom post type export support.
		$custom_post_types = get_post_types( array( '_builtin' => false, 'public' => true ), 'names' );
		if ( ! empty( $custom_post_types ) ) {
			$post_type_warnings = apply_filters( 'wpshadow_export_custom_post_type_warnings', array() );

			$uncovered_post_types = array_diff( $custom_post_types, array_keys( $post_type_warnings ) );
			if ( ! empty( $uncovered_post_types ) ) {
				$issues[] = sprintf(
					/* translators: %d: number of post types */
					__( '%d custom post types detected but no export warnings configured for them; users may lose data on migration', 'wpshadow' ),
					count( $uncovered_post_types )
				);
			}
		}

		// Check if help documentation exists for export limitations.
		$export_docs_link = get_option( 'wpshadow_export_help_documentation_url' );
		if ( empty( $export_docs_link ) ) {
			$issues[] = __( 'No help documentation link configured for export limitations; users cannot learn about data export scope', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/export-tool-plugin-warning',
			);
		}

		return null;
	}
}
