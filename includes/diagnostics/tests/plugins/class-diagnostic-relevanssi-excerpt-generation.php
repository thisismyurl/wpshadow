<?php
/**
 * Relevanssi Excerpt Generation Diagnostic
 *
 * Relevanssi excerpt generation slow.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.401.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Relevanssi Excerpt Generation Diagnostic Class
 *
 * @since 1.401.0000
 */
class Diagnostic_RelevanssiExcerptGeneration extends Diagnostic_Base {

	protected static $slug = 'relevanssi-excerpt-generation';
	protected static $title = 'Relevanssi Excerpt Generation';
	protected static $description = 'Relevanssi excerpt generation slow';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'RELEVANSSI_PREMIUM_VERSION' ) && ! function_exists( 'relevanssi_search' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Excerpt length
		$excerpt_length = get_option( 'relevanssi_excerpt_length', 30 );
		if ( $excerpt_length > 100 ) {
			$issues[] = sprintf( __( 'Long excerpts (%d words, slow generation)', 'wpshadow' ), $excerpt_length );
		}

		// Check 2: Excerpt caching
		$cache_excerpts = get_option( 'relevanssi_cache_excerpts', 'off' );
		if ( 'off' === $cache_excerpts ) {
			$issues[] = __( 'Excerpts not cached (repeated generation)', 'wpshadow' );
		}

		// Check 3: Highlighting
		$highlight_docs = get_option( 'relevanssi_highlight_docs', 'off' );
		if ( 'on' === $highlight_docs ) {
			$issues[] = __( 'Full document highlighting (CPU intensive)', 'wpshadow' );
		}

		// Check 4: Excerpt building
		$excerpt_type = get_option( 'relevanssi_excerpt_type', 'chars' );
		$excerpt_chars = get_option( 'relevanssi_excerpt_chars', 200 );

		if ( 'chars' === $excerpt_type && $excerpt_chars > 500 ) {
			$issues[] = sprintf( __( 'Long character count (%d chars)', 'wpshadow' ), $excerpt_chars );
		}

		// Check 5: Throttle searches
		$throttle = get_option( 'relevanssi_throttle', 'off' );
		if ( 'off' === $throttle ) {
			$issues[] = __( 'No search throttling (DoS risk)', 'wpshadow' );
		}

		// Check 6: Excerpt custom fields
		$excerpt_custom_fields = get_option( 'relevanssi_excerpt_custom_fields', 'off' );
		if ( 'on' === $excerpt_custom_fields ) {
			$issues[] = __( 'Excerpts from custom fields (slow queries)', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		$threat_level = 40;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 52;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 46;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of Relevanssi excerpt generation issues */
				__( 'Relevanssi excerpt generation has %d issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/relevanssi-excerpt-generation',
		);
	}
}
