<?php
/**
 * CPT REST API Exposure Diagnostic
 *
 * Checks if custom post types are exposed to REST API when intended.
 * Tests show_in_rest setting and REST API functionality.
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
 * CPT REST API Exposure Class
 *
 * Verifies custom post types have correct REST API exposure settings
 * for headless WordPress, Gutenberg, and external integrations.
 *
 * @since 1.6093.1200
 */
class Diagnostic_CPT_REST_API_Exposure extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'cpt-rest-api-exposure';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'CPT REST API Exposure';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if CPTs are exposed to REST API when intended';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Run the diagnostic check.
	 *
	 * Validates REST API exposure for custom post types and detects
	 * misconfigurations that break Gutenberg or API access.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if REST API issues found, null otherwise.
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

			// Check if Gutenberg is enabled but REST API is not.
			if ( $post_type_obj->show_in_rest === false ) {
				// Check if posts exist.
				$post_count = wp_count_posts( $post_type );
				$total = isset( $post_count->publish ) ? $post_count->publish : 0;

				if ( $total > 0 ) {
					$cpt_issues[] = sprintf(
						/* translators: %d: number of posts */
						_n(
							'Has %d post but REST API is disabled (breaks Gutenberg)',
							'Has %d posts but REST API is disabled (breaks Gutenberg)',
							$total,
							'wpshadow'
						),
						number_format_i18n( $total )
					);
				}

				// Check if publicly_queryable is true (inconsistent).
				if ( $post_type_obj->publicly_queryable ) {
					$cpt_issues[] = __( 'Publicly queryable but not accessible via REST API', 'wpshadow' );
				}
			} else {
				// REST API is enabled - validate configuration.
				$rest_base = $post_type_obj->rest_base ? $post_type_obj->rest_base : $post_type;

				// Check for REST base conflicts.
				foreach ( $post_types as $other_type => $other_obj ) {
					if ( $other_type === $post_type || ! $other_obj->show_in_rest ) {
						continue;
					}

					$other_rest_base = $other_obj->rest_base ? $other_obj->rest_base : $other_type;

					if ( $rest_base === $other_rest_base ) {
						$cpt_issues[] = sprintf(
							/* translators: %s: conflicting post type */
							__( 'REST base conflicts with post type "%s"', 'wpshadow' ),
							$other_obj->label
						);
					}
				}

				// Check if REST controller is set properly.
				if ( ! $post_type_obj->rest_controller_class ) {
					$cpt_issues[] = __( 'No REST controller class defined', 'wpshadow' );
				}
			}

			if ( ! empty( $cpt_issues ) ) {
				$problematic_cpts[ $post_type ] = array(
					'label'         => $post_type_obj->label,
					'show_in_rest'  => $post_type_obj->show_in_rest,
					'rest_base'     => $post_type_obj->rest_base,
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
					'Found REST API issues in %d custom post type: ',
					'Found REST API issues in %d custom post types: ',
					count( $problematic_cpts ),
					'wpshadow'
				) . implode( ' ', $issues ),
				number_format_i18n( count( $problematic_cpts ) )
			),
			'severity'    => 'medium',
			'threat_level' => 60,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/cpt-rest-api-exposure',
			'details'     => array(
				'problematic_cpts' => $problematic_cpts,
			),
		);
	}
}
