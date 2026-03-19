<?php
/**
 * Page Template Availability Diagnostic
 *
 * Validates that custom page templates are properly registered and
 * available for selection in the page editor.
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
 * Page Template Availability Diagnostic Class
 *
 * Checks page template registration and availability.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Page_Template_Availability extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'page-template-availability';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Page Template Availability';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates page template registration';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues       = array();
		$template_dir = get_template_directory();

		// Get registered page templates.
		$templates = wp_get_theme()->get_page_templates();

		// Check for orphaned template files (exist but not registered).
		$template_files = glob( $template_dir . '/template-*.php' );
		$orphaned       = array();

		if ( ! empty( $template_files ) ) {
			foreach ( $template_files as $file ) {
				$file_name     = basename( $file );
				$file_contents = file_get_contents( $file );

				// Check if file has proper template name header.
				if ( preg_match( '/Template Name:\s*(.+)/i', $file_contents, $matches ) ) {
					$template_name = trim( $matches[1] );

					// Check if this template is registered.
					$is_registered = false;
					foreach ( $templates as $template_slug => $registered_name ) {
						if ( basename( $template_slug ) === $file_name || $registered_name === $template_name ) {
							$is_registered = true;
							break;
						}
					}

					if ( ! $is_registered ) {
						$orphaned[] = array(
							'file' => $file_name,
							'name' => $template_name,
						);
					}
				} else {
					// Template file without proper header.
					$orphaned[] = array(
						'file'   => $file_name,
						'reason' => __( 'Missing "Template Name" header', 'wpshadow' ),
					);
				}
			}
		}

		if ( ! empty( $orphaned ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of orphaned templates */
				__( '%d template files found but not properly registered', 'wpshadow' ),
				count( $orphaned )
			);
		}

		// Check for pages using templates that no longer exist.
		global $wpdb;
		$pages_with_templates = $wpdb->get_results(
			"SELECT p.ID, p.post_title, pm.meta_value as template
			FROM {$wpdb->posts} p
			INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
			WHERE p.post_type = 'page'
			AND p.post_status = 'publish'
			AND pm.meta_key = '_wp_page_template'
			AND pm.meta_value != 'default'"
		);

		$missing_templates = array();
		foreach ( $pages_with_templates as $page ) {
			$template_file = $template_dir . '/' . $page->template;

			if ( ! file_exists( $template_file ) ) {
				$missing_templates[] = array(
					'page_id'    => $page->ID,
					'page_title' => $page->post_title,
					'template'   => $page->template,
				);
			}
		}

		if ( ! empty( $missing_templates ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of pages */
				__( '%d pages use templates that no longer exist', 'wpshadow' ),
				count( $missing_templates )
			);
		}

		// Check if theme has any custom templates at all.
		if ( empty( $templates ) && empty( $template_files ) ) {
			// No custom templates - this is not necessarily an issue.
			// Some themes don't need custom templates.
		}

		// Check for duplicate template names.
		if ( ! empty( $templates ) ) {
			$template_names = array_values( $templates );
			$duplicates     = array();

			foreach ( array_count_values( $template_names ) as $name => $count ) {
				if ( $count > 1 ) {
					$duplicates[] = $name;
				}
			}

			if ( ! empty( $duplicates ) ) {
				$issues[] = sprintf(
					/* translators: %s: comma-separated list of template names */
					__( 'Duplicate template names found: %s', 'wpshadow' ),
					implode( ', ', $duplicates )
				);
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of page template issues */
					__( 'Found %d page template configuration issues.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 35,
				'auto_fixable' => false,
				'details'      => array(
					'issues'            => $issues,
					'orphaned_files'    => $orphaned,
					'missing_templates' => array_slice( $missing_templates, 0, 10 ),
					'registered_count'  => count( $templates ),
					'recommendation'    => __( 'Ensure all template files have proper headers and update pages using missing templates.', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
