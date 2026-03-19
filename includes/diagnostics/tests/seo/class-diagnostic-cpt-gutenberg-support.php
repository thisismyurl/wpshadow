<?php
/**
 * CPT Gutenberg Support Diagnostic
 *
 * Verifies custom post types support Gutenberg editor properly.
 * Tests show_in_rest and editor compatibility settings.
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
 * CPT Gutenberg Support Class
 *
 * Ensures custom post types have proper Gutenberg editor support
 * and detects configuration issues preventing block editor use.
 *
 * @since 1.6093.1200
 */
class Diagnostic_CPT_Gutenberg_Support extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'cpt-gutenberg-support';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'CPT Gutenberg Support';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies CPTs support Gutenberg editor';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Run the diagnostic check.
	 *
	 * Validates Gutenberg support for custom post types and detects
	 * configurations that break the block editor.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if Gutenberg issues found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$problematic_cpts = array();

		$post_types = get_post_types(
			array(
				'public'   => true,
				'_builtin' => false,
			),
			'objects'
		);

		if ( empty( $post_types ) ) {
			return null;
		}

		foreach ( $post_types as $post_type => $post_type_obj ) {
			$cpt_issues = array();

			// Check if editor is disabled.
			if ( ! post_type_supports( $post_type, 'editor' ) ) {
				$post_count = wp_count_posts( $post_type );
				$total = isset( $post_count->publish ) ? $post_count->publish : 0;

				if ( $total > 0 ) {
					$cpt_issues[] = sprintf(
						/* translators: %d: number of posts */
						_n(
							'Has %d post but editor support is disabled',
							'Has %d posts but editor support is disabled',
							$total,
							'wpshadow'
						),
						number_format_i18n( $total )
					);
				}
			} else {
				// Editor is enabled - check Gutenberg requirements.
				if ( ! $post_type_obj->show_in_rest ) {
					$cpt_issues[] = __( 'Editor enabled but show_in_rest is false (blocks Gutenberg)', 'wpshadow' );
				}

				// Check for custom-fields support (breaks Gutenberg meta).
				if ( post_type_supports( $post_type, 'custom-fields' ) ) {
					$cpt_issues[] = __( 'Custom fields support may conflict with Gutenberg meta boxes', 'wpshadow' );
				}

				// Check if template is set but show_in_rest is false.
				if ( ! empty( $post_type_obj->template ) && ! $post_type_obj->show_in_rest ) {
					$cpt_issues[] = __( 'Block template defined but REST API disabled', 'wpshadow' );
				}
			}

			// Check for conflicting plugins.
			if ( is_plugin_active( 'classic-editor/classic-editor.php' ) ) {
				$classic_option = get_option( 'classic-editor-replace', 'classic' );

				if ( $classic_option === 'classic' ) {
					$cpt_issues[] = __( 'Classic Editor plugin forces classic editor sitewide', 'wpshadow' );
				}
			}

			if ( ! empty( $cpt_issues ) ) {
				$problematic_cpts[ $post_type ] = array(
					'label'         => $post_type_obj->label,
					'show_in_rest'  => $post_type_obj->show_in_rest,
					'editor_support' => post_type_supports( $post_type, 'editor' ),
					'issues'        => $cpt_issues,
				);

				$issues[] = sprintf(
					/* translators: 1: post type label, 2: list of issues */
					__( '%1$s: %2$s', 'wpshadow' ),
					$post_type_obj->label,
					implode( ', ', $cpt_issues )
				);
			}
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %d: number of CPTs with issues */
				_n(
					'Found Gutenberg issues in %d custom post type: ',
					'Found Gutenberg issues in %d custom post types: ',
					count( $problematic_cpts ),
					'wpshadow'
				) . implode( ' ', $issues ),
				number_format_i18n( count( $problematic_cpts ) )
			),
			'severity'    => 'medium',
			'threat_level' => 50,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/cpt-gutenberg-support',
			'details'     => array(
				'problematic_cpts' => $problematic_cpts,
			),
		);
	}
}
