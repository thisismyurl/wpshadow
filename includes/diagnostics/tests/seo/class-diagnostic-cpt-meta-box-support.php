<?php
/**
 * CPT Meta Box Support Diagnostic
 *
 * Checks if custom post types support meta boxes correctly and validates
 * add_meta_box functionality for custom fields.
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
 * CPT Meta Box Support Class
 *
 * Verifies custom post types properly support meta boxes and that
 * registered meta boxes are accessible in the editor.
 *
 * @since 1.6093.1200
 */
class Diagnostic_CPT_Meta_Box_Support extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'cpt-meta-box-support';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'CPT Meta Box Support';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if CPTs support meta boxes correctly';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Run the diagnostic check.
	 *
	 * Validates CPT meta box support and checks for common
	 * configuration issues preventing meta boxes from displaying.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if meta box issues found, null otherwise.
	 */
	public static function check() {
		global $wp_meta_boxes;

		$issues = array();
		$problematic_cpts = array();

		// Get all custom post types.
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

			// Check if CPT supports custom-fields.
			if ( ! post_type_supports( $post_type, 'custom-fields' ) ) {
				// Check if posts have meta data.
				global $wpdb;

				$has_meta = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT COUNT(DISTINCT pm.post_id)
						FROM {$wpdb->postmeta} pm
						INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
						WHERE p.post_type = %s
						LIMIT 1",
						$post_type
					)
				);

				if ( $has_meta && (int) $has_meta > 0 ) {
					$cpt_issues[] = __( 'Has post meta but custom-fields support is disabled', 'wpshadow' );
				}
			}

			// Check if CPT supports title (required for most meta boxes).
			if ( ! post_type_supports( $post_type, 'title' ) ) {
				$cpt_issues[] = __( 'Does not support titles - may break meta box layouts', 'wpshadow' );
			}

			// Check if CPT supports editor.
			if ( ! post_type_supports( $post_type, 'editor' ) ) {
				$cpt_issues[] = __( 'Does not support editor - content meta boxes hidden', 'wpshadow' );
			}

			// Check if CPT is using Gutenberg but has disabled features.
			if ( $post_type_obj->show_in_rest ) {
				$supports = get_all_post_type_supports( $post_type );

				if ( empty( $supports ) ) {
					$cpt_issues[] = __( 'Gutenberg enabled but no editor features supported', 'wpshadow' );
				}
			}

			// Check for meta boxes registered for this CPT.
			$meta_box_count = 0;

			if ( isset( $wp_meta_boxes[ $post_type ] ) ) {
				foreach ( $wp_meta_boxes[ $post_type ] as $context => $priority ) {
					foreach ( $priority as $boxes ) {
						$meta_box_count += count( $boxes );
					}
				}
			}

			// If meta boxes exist but custom-fields support missing.
			if ( $meta_box_count > 0 && ! post_type_supports( $post_type, 'custom-fields' ) ) {
				$cpt_issues[] = sprintf(
					/* translators: %d: number of meta boxes */
					_n(
						'Has %d meta box but custom-fields support disabled',
						'Has %d meta boxes but custom-fields support disabled',
						$meta_box_count,
						'wpshadow'
					),
					number_format_i18n( $meta_box_count )
				);
			}

			// Check if register_meta is used without proper support.
			$registered_meta = get_registered_meta_keys( 'post', $post_type );

			if ( ! empty( $registered_meta ) ) {
				// Check if show_in_rest is true for meta but CPT doesn't support REST.
				if ( ! $post_type_obj->show_in_rest ) {
					$rest_meta_count = 0;

					foreach ( $registered_meta as $meta_key => $meta_args ) {
						if ( isset( $meta_args['show_in_rest'] ) && $meta_args['show_in_rest'] ) {
							$rest_meta_count++;
						}
					}

					if ( $rest_meta_count > 0 ) {
						$cpt_issues[] = sprintf(
							/* translators: %d: number of REST-enabled meta fields */
							_n(
								'Has %d REST-enabled meta field but CPT not in REST API',
								'Has %d REST-enabled meta fields but CPT not in REST API',
								$rest_meta_count,
								'wpshadow'
							),
							number_format_i18n( $rest_meta_count )
						);
					}
				}
			}

			// Check if CPT has _builtin meta boxes removed.
			$removed_meta_boxes = array();

			if ( isset( $wp_meta_boxes[ $post_type ] ) ) {
				$core_boxes = array( 'submitdiv', 'slugdiv', 'authordiv', 'revisionsdiv' );

				foreach ( $core_boxes as $box ) {
					$found = false;

					foreach ( $wp_meta_boxes[ $post_type ] as $context => $priority ) {
						if ( isset( $priority['core'][ $box ] ) || isset( $priority['high'][ $box ] ) ) {
							$found = true;
							break;
						}
					}

					if ( ! $found ) {
						$removed_meta_boxes[] = $box;
					}
				}
			}

			if ( count( $removed_meta_boxes ) >= 3 ) {
				$cpt_issues[] = sprintf(
					/* translators: %d: number of removed meta boxes */
					__( 'Has %d core meta boxes removed - may confuse editors', 'wpshadow' ),
					number_format_i18n( count( $removed_meta_boxes ) )
				);
			}

			if ( ! empty( $cpt_issues ) ) {
				$problematic_cpts[ $post_type ] = array(
					'label'           => $post_type_obj->label,
					'supports'        => get_all_post_type_supports( $post_type ),
					'meta_box_count'  => $meta_box_count,
					'issues'          => $cpt_issues,
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
					'Found meta box issues in %d custom post type: ',
					'Found meta box issues in %d custom post types: ',
					count( $problematic_cpts ),
					'wpshadow'
				) . implode( ' ', $issues ),
				number_format_i18n( count( $problematic_cpts ) )
			),
			'severity'    => 'low',
			'threat_level' => 45,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/cpt-meta-box-support',
			'details'     => array(
				'problematic_cpts' => $problematic_cpts,
			),
		);
	}
}
