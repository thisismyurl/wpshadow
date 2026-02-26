<?php
/**
 * Custom Post Type Block Patterns Diagnostic
 *
 * Verifies that WPShadow block patterns for CPTs are properly registered
 * and available in the block editor.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6034.1230
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WP_Block_Patterns_Registry;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_CPT_Block_Patterns Class
 *
 * Checks if WPShadow block patterns are registered in Gutenberg.
 *
 * @since 1.6034.1230
 */
class Diagnostic_CPT_Block_Patterns extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'cpt-block-patterns';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'CPT Block Patterns';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies block patterns for custom post types are available';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Expected block pattern prefixes
	 *
	 * @var array
	 */
	private static $expected_pattern_prefixes = array(
		'wpshadow/testimonials',
		'wpshadow/team',
		'wpshadow/portfolio',
		'wpshadow/events',
		'wpshadow/resources',
		'wpshadow/case-studies',
		'wpshadow/services',
		'wpshadow/locations',
		'wpshadow/documentation',
	);

	/**
	 * Run the diagnostic check.
	 *
	 * Verifies that block patterns are registered for all CPTs.
	 *
	 * @since  1.6034.1230
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if block patterns registry is available.
		if ( ! class_exists( 'WP_Block_Patterns_Registry' ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Block Patterns Registry is not available. This may indicate the Gutenberg editor is not active or your WordPress version is outdated.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/block-editor-requirements',
				'academy_link' => 'https://wpshadow.com/academy/setting-up-gutenberg',
			);
		}

		$registry            = WP_Block_Patterns_Registry::get_instance();
		$registered_patterns = $registry->get_all_registered();

		// Count patterns by prefix.
		$pattern_counts   = array();
		$missing_prefixes = array();

		foreach ( self::$expected_pattern_prefixes as $prefix ) {
			$count = 0;
			foreach ( $registered_patterns as $pattern ) {
				if ( isset( $pattern['name'] ) && strpos( $pattern['name'], $prefix ) === 0 ) {
					++$count;
				}
			}
			$pattern_counts[ $prefix ] = $count;

			if ( 0 === $count ) {
				$missing_prefixes[] = $prefix;
			}
		}

		// If any pattern categories are missing, report finding.
		if ( ! empty( $missing_prefixes ) ) {
			$description = sprintf(
				/* translators: %s: comma-separated list of pattern prefixes */
				__( 'No block patterns found for the following content types: %s. ', 'wpshadow' ),
				implode(
					', ',
					array_map(
						function ( $prefix ) {
							return str_replace( 'wpshadow/', '', $prefix );
						},
						$missing_prefixes
					)
				)
			);

			$description .= __( 'Block patterns provide pre-designed layouts for faster content creation. Without them, users must build layouts from scratch.', 'wpshadow' );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/using-block-patterns',
				'academy_link' => 'https://wpshadow.com/academy/block-patterns-quick-start',
			);
		}

		return null; // All block patterns are registered.
	}

	/**
	 * Get total count of registered WPShadow patterns
	 *
	 * @since  1.6034.1230
	 * @return int Count of registered patterns.
	 */
	public static function get_registered_count() {
		if ( ! class_exists( 'WP_Block_Patterns_Registry' ) ) {
			return 0;
		}

		$registry            = WP_Block_Patterns_Registry::get_instance();
		$registered_patterns = $registry->get_all_registered();
		$count               = 0;

		foreach ( $registered_patterns as $pattern ) {
			if ( isset( $pattern['name'] ) && strpos( $pattern['name'], 'wpshadow/' ) === 0 ) {
				++$count;
			}
		}

		return $count;
	}
}
