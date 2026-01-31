<?php
/**
 * Theme Custom Post Type Support Diagnostic
 *
 * Detects issues with theme's support for custom post types.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5049.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Custom Post Type Support Diagnostic Class
 *
 * Checks if theme has templates for registered custom post types.
 *
 * @since 1.5049.1200
 */
class Diagnostic_Theme_Custom_Post_Type_Support extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-custom-post-type-support';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Custom Post Type Support';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if theme supports custom post types';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.5049.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$theme = wp_get_theme();
		$issues = array();

		// Get all registered custom post types (exclude built-in).
		$post_types = get_post_types( array( '_builtin' => false ), 'objects' );

		if ( ! empty( $post_types ) ) {
			$missing_templates = array();

			foreach ( $post_types as $post_type ) {
				// Check for single-{post_type}.php template.
				$single_template = locate_template( "single-{$post_type->name}.php" );

				// Check for archive-{post_type}.php template.
				$archive_template = locate_template( "archive-{$post_type->name}.php" );

				// If post type is public and neither template exists.
				if ( $post_type->public && empty( $single_template ) && empty( $archive_template ) ) {
					$missing_templates[] = $post_type->label;
				}
			}

			if ( ! empty( $missing_templates ) ) {
				$issues[] = sprintf(
					/* translators: %s: comma-separated list of post type labels */
					__( 'Theme lacks templates for custom post types: %s', 'wpshadow' ),
					implode( ', ', $missing_templates )
				);
			}

			// Check for generic single.php.
			$single_template = locate_template( 'single.php' );
			if ( empty( $single_template ) && ! empty( $post_types ) ) {
				$issues[] = __( 'Theme missing single.php template (affects custom post types)', 'wpshadow' );
			}

			// Check for generic archive.php.
			$archive_template = locate_template( 'archive.php' );
			if ( empty( $archive_template ) && ! empty( $post_types ) ) {
				$issues[] = __( 'Theme missing archive.php template (affects custom post type archives)', 'wpshadow' );
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: comma-separated list of issues */
					__( 'Theme custom post type support issues: %s', 'wpshadow' ),
					implode( ', ', $issues )
				),
				'severity'    => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'details'     => array(
					'theme'       => $theme->get( 'Name' ),
					'issues'      => $issues,
					'post_types'  => ! empty( $post_types ) ? array_keys( $post_types ) : array(),
				),
				'kb_link'     => 'https://wpshadow.com/kb/theme-custom-post-type-support',
			);
		}

		return null;
	}
}
