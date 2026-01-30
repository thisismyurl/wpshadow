<?php
/**
 * Accessible Poetry Semantics Diagnostic
 *
 * Accessible Poetry Semantics not compliant.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1097.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Accessible Poetry Semantics Diagnostic Class
 *
 * @since 1.1097.0000
 */
class Diagnostic_AccessiblePoetrySemantics extends Diagnostic_Base {

	protected static $slug = 'accessible-poetry-semantics';
	protected static $title = 'Accessible Poetry Semantics';
	protected static $description = 'Accessible Poetry Semantics not compliant';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'Accessible_Poetry' ) && ! defined( 'ACCESSIBLE_POETRY_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Semantic HTML enabled for poems.
		$semantic_html = get_option( 'accessible_poetry_semantic_html', '1' );
		if ( '0' === $semantic_html ) {
			$issues[] = 'semantic HTML disabled (screen readers cannot identify poem structure)';
		}
		
		// Check 2: ARIA labels configured.
		$aria_labels = get_option( 'accessible_poetry_aria_labels', '1' );
		if ( '0' === $aria_labels ) {
			$issues[] = 'ARIA labels disabled (reduces accessibility)';
		}
		
		// Check 3: Line break handling.
		$line_breaks = get_option( 'accessible_poetry_line_breaks', 'semantic' );
		if ( 'br' === $line_breaks ) {
			$issues[] = 'using <br> tags for line breaks (use semantic line elements instead)';
		}
		
		// Check 4: Stanza grouping.
		global $wpdb;
		$poems_without_stanzas = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_content NOT LIKE %s",
				'poem',
				'%<div class="stanza"%'
			)
		);
		if ( $poems_without_stanzas > 0 ) {
			$issues[] = "{$poems_without_stanzas} poems without stanza grouping (improves navigation)";
		}
		
		// Check 5: Alt text for visual poetry.
		$visual_poems = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %s",
				'poem_type',
				'visual'
			)
		);
		$visual_with_alt = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value != %s",
				'poem_alt_text',
				''
			)
		);
		if ( $visual_poems > 0 && $visual_with_alt < $visual_poems ) {
			$missing = $visual_poems - $visual_with_alt;
			$issues[] = "{$missing} visual poems missing alt text descriptions";
		}
		
		// Check 6: Screen reader optimization.
		$sr_optimized = get_option( 'accessible_poetry_screen_reader_mode', '0' );
		if ( '0' === $sr_optimized ) {
			$issues[] = 'screen reader optimization mode disabled (enables better poem narration)';
		}
		
		if ( ! empty( $issues ) ) {
			$threat_level = min( 70, 40 + ( count( $issues ) * 6 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Accessible Poetry semantics issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/accessible-poetry-semantics',
			);
		}
		
		return null;
	}
}
