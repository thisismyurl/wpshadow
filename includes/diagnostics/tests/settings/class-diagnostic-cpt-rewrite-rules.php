<?php
/**
 * Custom Post Type Rewrite Rules Diagnostic
 *
 * Verifies that rewrite rules for custom post types are properly flushed
 * and permalinks are working correctly.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6034.1230
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_CPT_Rewrite_Rules Class
 *
 * Checks if rewrite rules are properly configured for all CPTs.
 *
 * @since 1.6034.1230
 */
class Diagnostic_CPT_Rewrite_Rules extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'cpt-rewrite-rules';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'CPT Rewrite Rules';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies permalink structure is working for custom post types';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * CPT slugs to check
	 *
	 * @var array
	 */
	private static $cpt_slugs = array(
		'testimonial',
		'team_member',
		'portfolio_item',
		'wps_event',
		'resource',
		'case_study',
		'service',
		'location',
		'documentation',
		'wps_product',
	);

	/**
	 * Run the diagnostic check.
	 *
	 * Verifies rewrite rules exist for all custom post types.
	 *
	 * @since  1.6034.1230
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wp_rewrite;

		$missing_rules = array();
		$rewrite_rules = get_option( 'rewrite_rules' );

		if ( empty( $rewrite_rules ) || ! is_array( $rewrite_rules ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'WordPress rewrite rules are not initialized. This will cause 404 errors when accessing custom post types. You need to flush rewrite rules.', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 90,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/fixing-404-errors',
				'academy_link' => 'https://wpshadow.com/academy/wordpress-permalinks-explained',
			);
		}

		// Check for each CPT in rewrite rules.
		foreach ( self::$cpt_slugs as $cpt_slug ) {
			if ( ! post_type_exists( $cpt_slug ) ) {
				continue; // Skip if CPT not registered.
			}

			$post_type_object = get_post_type_object( $cpt_slug );
			if ( ! $post_type_object || ! $post_type_object->rewrite ) {
				continue; // Skip if no rewrite configuration.
			}

			// Get expected rewrite slug.
			$rewrite_slug = is_array( $post_type_object->rewrite ) && isset( $post_type_object->rewrite['slug'] )
				? $post_type_object->rewrite['slug']
				: $cpt_slug;

			// Check if any rewrite rule contains the slug.
			$found = false;
			foreach ( array_keys( $rewrite_rules ) as $rule ) {
				if ( strpos( $rule, $rewrite_slug ) !== false ) {
					$found = true;
					break;
				}
			}

			if ( ! $found ) {
				$missing_rules[] = $rewrite_slug;
			}
		}

		// If any rewrite rules are missing, report finding.
		if ( ! empty( $missing_rules ) ) {
			$description = sprintf(
				/* translators: %s: comma-separated list of CPT slugs */
				__( 'Rewrite rules are missing or outdated for the following custom post types: %s. ', 'wpshadow' ),
				implode( ', ', $missing_rules )
			);

			$description .= __( 'This will cause 404 errors when users try to view these content types. Flush your rewrite rules to fix this issue.', 'wpshadow' );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => 'high',
				'threat_level' => 80,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/flushing-rewrite-rules',
				'academy_link' => 'https://wpshadow.com/academy/troubleshooting-permalinks',
			);
		}

		return null; // All rewrite rules are in place.
	}

	/**
	 * Get last rewrite rules flush timestamp
	 *
	 * @since  1.6034.1230
	 * @return int|false Timestamp or false if never flushed.
	 */
	public static function get_last_flush_time() {
		return get_option( 'wpshadow_rewrite_flush_time', false );
	}
}
