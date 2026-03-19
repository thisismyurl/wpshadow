<?php
/**
 * Page Template Assignment Diagnostic
 *
 * Verifies page templates are assigned and loading correctly. Tests template file
 * availability and detects orphaned template assignments.
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
 * Page Template Assignment Diagnostic Class
 *
 * Checks for page template assignment and availability issues.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Page_Template_Assignment extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'page-template-assignment';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Page Template Assignment';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies page templates are assigned correctly and template files exist';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'posts';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// Get all available page templates.
		$available_templates = wp_get_theme()->get_page_templates();

		// Get pages with template assignments.
		$assigned_templates = $wpdb->get_results(
			"SELECT post_id, meta_value as template
			FROM {$wpdb->postmeta}
			WHERE meta_key = '_wp_page_template'
			AND meta_value != 'default'
			AND meta_value != ''",
			ARRAY_A
		);

		if ( empty( $assigned_templates ) ) {
			return null; // No custom templates assigned.
		}

		// Check for templates assigned but files don't exist.
		$missing_templates = array();
		$orphaned_count = 0;

		foreach ( $assigned_templates as $assignment ) {
			$template = $assignment['template'];

			// Skip if template exists in theme.
			if ( isset( $available_templates[ $template ] ) ) {
				continue;
			}

			// Check if file exists directly.
			$template_file = get_stylesheet_directory() . '/' . $template;
			$parent_template_file = get_template_directory() . '/' . $template;

			if ( ! file_exists( $template_file ) && ! file_exists( $parent_template_file ) ) {
				$missing_templates[] = $template;
				++$orphaned_count;
			}
		}

		if ( $orphaned_count > 0 ) {
			$unique_missing = array_unique( $missing_templates );
			$issues[] = sprintf(
				/* translators: 1: number of pages, 2: example template names */
				__( '%1$d pages assigned to missing templates (e.g., %2$s)', 'wpshadow' ),
				$orphaned_count,
				implode( ', ', array_slice( $unique_missing, 0, 3 ) )
			);
		}

		// Check for pages with empty template meta.
		$empty_template_meta = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->postmeta} pm
			INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
			WHERE pm.meta_key = '_wp_page_template'
			AND pm.meta_value = ''
			AND p.post_status = 'publish'
			AND p.post_type = 'page'"
		);

		if ( $empty_template_meta > 10 ) {
			$issues[] = sprintf(
				/* translators: %d: number of pages */
				__( '%d pages have empty template meta (may not render correctly)', 'wpshadow' ),
				$empty_template_meta
			);
		}

		// Check for pages missing template meta entirely.
		$missing_template_meta = $wpdb->get_var(
			"SELECT COUNT(p.ID)
			FROM {$wpdb->posts} p
			LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_wp_page_template'
			WHERE p.post_type = 'page'
			AND p.post_status = 'publish'
			AND pm.meta_id IS NULL"
		);

		if ( $missing_template_meta > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of pages */
				__( '%d published pages missing template metadata (will use default)', 'wpshadow' ),
				$missing_template_meta
			);
		}

		// Check for template file syntax errors (basic check).
		$template_files = array_keys( $available_templates );
		$syntax_errors = 0;

		foreach ( array_slice( $template_files, 0, 20 ) as $template_file ) {
			$full_path = get_stylesheet_directory() . '/' . $template_file;
			if ( ! file_exists( $full_path ) ) {
				$full_path = get_template_directory() . '/' . $template_file;
			}

			if ( file_exists( $full_path ) ) {
				$content = file_get_contents( $full_path );
				// Check for unclosed PHP tags.
				if ( substr_count( $content, '<?php' ) > substr_count( $content, '?>' ) + 1 ) {
					++$syntax_errors;
				}
			}
		}

		if ( $syntax_errors > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of templates with errors */
				__( '%d template files have potential syntax errors (unclosed PHP tags)', 'wpshadow' ),
				$syntax_errors
			);
		}

		// Check for duplicate template names.
		$template_names = array_values( $available_templates );
		$duplicate_names = array_diff_assoc( $template_names, array_unique( $template_names ) );

		if ( ! empty( $duplicate_names ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of duplicate template names */
				__( '%d duplicate template names detected (confusing for users)', 'wpshadow' ),
				count( $duplicate_names )
			);
		}

		// Check if theme supports page templates.
		if ( empty( $available_templates ) && count( $assigned_templates ) > 0 ) {
			$issues[] = __( 'Pages have template assignments but theme has no templates (theme switched?)', 'wpshadow' );
		}

		// Check for child theme templates overriding parent.
		if ( is_child_theme() ) {
			$parent_templates = wp_get_theme( get_template() )->get_page_templates();
			$child_templates = wp_get_theme()->get_page_templates();

			$overridden = array_intersect_key( $child_templates, $parent_templates );
			if ( count( $overridden ) > 5 ) {
				$issues[] = sprintf(
					/* translators: %d: number of overridden templates */
					__( '%d parent theme templates overridden by child theme (verify behavior)', 'wpshadow' ),
					count( $overridden )
				);
			}
		}

		// Check for templates with non-standard naming.
		foreach ( $template_files as $template_file ) {
			if ( ! preg_match( '/^[a-z0-9_-]+\.php$/i', basename( $template_file ) ) ) {
				$issues[] = sprintf(
					/* translators: %s: template filename */
					__( 'Template "%s" has non-standard filename (may cause issues)', 'wpshadow' ),
					esc_html( basename( $template_file ) )
				);
				break; // Only report once.
			}
		}

		// Check for template assignments on non-page post types.
		$wrong_type_templates = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->postmeta} pm
			INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
			WHERE pm.meta_key = '_wp_page_template'
			AND pm.meta_value != 'default'
			AND p.post_type != 'page'"
		);

		if ( $wrong_type_templates > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of non-page posts with templates */
				__( '%d non-page posts have page template assignments (incorrect configuration)', 'wpshadow' ),
				$wrong_type_templates
			);
		}

		// Check for templates requiring specific plugins.
		foreach ( array_slice( $template_files, 0, 10 ) as $template_file ) {
			$full_path = get_stylesheet_directory() . '/' . $template_file;
			if ( ! file_exists( $full_path ) ) {
				$full_path = get_template_directory() . '/' . $template_file;
			}

			if ( file_exists( $full_path ) ) {
				$content = file_get_contents( $full_path );
				// Check for plugin function calls.
				if ( preg_match( '/(acf_|get_field|the_field|do_shortcode)/i', $content ) ) {
					$required_plugins = array();
					if ( preg_match( '/(acf_|get_field|the_field)/i', $content ) ) {
						$required_plugins[] = 'ACF';
					}

					if ( ! empty( $required_plugins ) && ! class_exists( 'ACF' ) ) {
						$issues[] = sprintf(
							/* translators: 1: template name, 2: plugin name */
							__( 'Template "%1$s" requires %2$s plugin but it\'s not active', 'wpshadow' ),
							esc_html( basename( $template_file ) ),
							implode( ', ', $required_plugins )
						);
						break; // Only report once.
					}
				}
			}
		}

		// Check for excessive template count.
		if ( count( $available_templates ) > 30 ) {
			$issues[] = sprintf(
				/* translators: %d: number of templates */
				__( '%d page templates available (excessive, may confuse editors)', 'wpshadow' ),
				count( $available_templates )
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/page-template-assignment',
			);
		}

		return null;
	}
}
