<?php
/**
 * Mobile Focus Management
 *
 * Ensures focus order is logical and focus is not trapped in interactive elements.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Accessibility
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Diagnostics\Helpers\Diagnostic_HTML_Helper;
use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Focus Management
 *
 * Validates focus order is logical, focus is visible, and focus traps
 * are properly implemented in modals.
 * WCAG 2.4.3 Level A requirement.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Mobile_Focus extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-focus-management';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Focus Management';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Validates focus order and management';

	/**
	 * The diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = self::find_focus_issues();

		if ( empty( $issues ) ) {
			return null; // No issues found
		}

		$threat = 70;
		foreach ( $issues as $issue ) {
			if ( 'missing-focus-indicator' === $issue['type'] ) {
				$threat = 80; // Critical for keyboard users
			}
		}

		return array(
			'id'             => self::$slug,
			'title'          => self::$title,
			'description'    => sprintf(
				/* translators: %d: number of focus issues */
				__( 'Found %d focus management issues', 'wpshadow' ),
				count( $issues )
			),
			'severity'       => 'high',
			'threat_level'   => $threat,
			'issues'         => array_slice( $issues, 0, 5 ),
			'total_issues'   => count( $issues ),
			'wcag_violation' => '2.4.3 Focus Order (Level A)',
			'user_impact'    => __( 'Keyboard and screen reader users cannot navigate efficiently', 'wpshadow' ),
			'auto_fixable'   => false,
			'kb_link'        => 'https://wpshadow.com/kb/focus-management',
		);
	}

	/**
	 * Find focus management issues.
	 *
	 * @since 1.6093.1200
	 * @return array Issues found.
	 */
	private static function find_focus_issues(): array {
		$issues = array();
		$html   = self::get_page_html();

		if ( ! $html ) {
			return $issues;
		}

		// Check for focus indicators
		$focus_issues = self::check_focus_indicators( $html );
		$issues       = array_merge( $issues, $focus_issues );

		// Check for focus traps
		$trap_issues = self::check_focus_traps( $html );
		$issues      = array_merge( $issues, $trap_issues );

		// Check for tabindex misuse
		$tabindex_issues = self::check_tabindex( $html );
		$issues          = array_merge( $issues, $tabindex_issues );

		return $issues;
	}

	/**
	 * Check for CSS focus indicators.
	 *
	 * @since 1.6093.1200
	 * @param  string $html Page HTML.
	 * @return array Focus indicator issues.
	 */
	private static function check_focus_indicators( string $html ): array {
		$issues = array();

		// Extract CSS from style tags
		preg_match_all( '/<style[^>]*>(.*?)<\/style>/is', $html, $style_matches );
		$css = implode( ' ', $style_matches[1] ?? array() );

		// Check for outline: none on :focus
		if ( preg_match( '/:focus\s*{[^}]*outline\s*:\s*none/i', $css ) ) {
			$issues[] = array(
				'type'     => 'missing-focus-indicator',
				'issue'    => 'Focus outline disabled with outline: none',
				'severity' => 'critical',
				'element'  => ':focus',
			);
		}

		// Check for box-shadow: none on :focus-visible
		if ( preg_match( '/:focus-visible\s*{[^}]*(?:box-shadow|outline)\s*:\s*none/i', $css ) ) {
			$issues[] = array(
				'type'    => 'missing-focus-indicator',
				'issue'   => 'Focus-visible indicator removed',
				'element' => ':focus-visible',
			);
		}

		return $issues;
	}

	/**
	 * Check for focus trap issues in modals.
	 *
	 * @since 1.6093.1200
	 * @param  string $html Page HTML.
	 * @return array Focus trap issues.
	 */
	private static function check_focus_traps( string $html ): array {
		$issues = array();

		// Check for modals with aria-modal
		$modal_count = preg_match_all( '/aria-modal\s*=\s*["\']true["\']/i', $html, $matches );

		if ( $modal_count > 0 ) {
			// Check for ESC key handler
			if ( ! preg_match( '/key\s*===?\s*["\']Escape|keyCode\s*===?\s*27|\(e\.key|\(event\.key/i', $html ) ) {
				$issues[] = array(
					'type'     => 'modal-no-escape',
					'issue'    => 'Modal does not have ESC key handler',
					'severity' => 'medium',
					'count'    => $modal_count,
				);
			}

			// Check for focus trap implementation
			if ( ! preg_match( '/lastFocusable|firstFocusable|focusable|role\s*=\s*["\']dialog["\']/i', $html ) ) {
				$issues[] = array(
					'type'     => 'modal-focus-not-trapped',
					'issue'    => 'Modal may not trap focus properly',
					'severity' => 'high',
					'elements' => 'Modals',
				);
			}
		}

		return $issues;
	}

	/**
	 * Check for negative tabindex or excessive positive tabindex.
	 *
	 * @since 1.6093.1200
	 * @param  string $html Page HTML.
	 * @return array Tabindex issues.
	 */
	private static function check_tabindex( string $html ): array {
		$issues = array();

		// Check for positive tabindex (generally bad practice)
		$positive_tabindex = preg_match_all( '/tabindex\s*=\s*["\']?[1-9]/i', $html, $matches );
		if ( $positive_tabindex > 0 ) {
			$issues[] = array(
				'type'        => 'positive-tabindex',
				'issue'       => 'Found positive tabindex values (should be avoided)',
				'severity'    => 'medium',
				'count'       => $positive_tabindex,
				'explanation' => 'Positive tabindex can create confusing focus order',
			);
		}

		// Check for excessive negative tabindex (removed from tab order)
		$negative_tabindex = preg_match_all( '/tabindex\s*=\s*["\']?\-1["\']/i', $html, $matches );
		if ( $negative_tabindex > 5 ) {
			$issues[] = array(
				'type'        => 'excessive-negative-tabindex',
				'issue'       => 'Many elements removed from tab order',
				'severity'    => 'low',
				'count'       => $negative_tabindex,
				'explanation' => 'May indicate poor keyboard navigation design',
			);
		}

		return $issues;
	}

	/**
	 * Get page HTML for analysis.
	 *
	 * @since 1.6093.1200
	 * @return string|null HTML content.
	 */
	private static function get_page_html(): ?string {
		return Diagnostic_HTML_Helper::fetch_homepage_html(
			array(
				'timeout'   => 5,
				'sslverify' => false,
			)
		);
	}
}
