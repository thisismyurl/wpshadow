<?php
/**
 * Mobile Screen Reader Compatibility
 *
 * Validates ARIA labels and landmarks for mobile screen readers.
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
 * Mobile Screen Reader Compatibility
 *
 * Validates that ARIA labels, landmarks, and semantic HTML work correctly
 * with mobile screen readers (VoiceOver, TalkBack).
 * WCAG 4.1.2 Level A requirement.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Mobile_Screen_Reader extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-screen-reader-issues';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Screen Reader Compatibility';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Validates ARIA labels and landmarks for screen readers';

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
		$issues = self::find_screen_reader_issues();

		if ( empty( $issues['all'] ) ) {
			return null; // No issues found
		}

		return array(
			'id'             => self::$slug,
			'title'          => self::$title,
			'description'    => sprintf(
				/* translators: %d: number of accessibility issues */
				__( 'Found %d accessibility issues for screen readers', 'wpshadow' ),
				count( $issues['all'] )
			),
			'severity'       => 'critical',
			'threat_level'   => 70,
			'issues'         => array_slice( $issues['all'], 0, 5 ),
			'total_issues'   => count( $issues['all'] ),
			'wcag_violation' => '4.1.2 Name, Role, Value (Level A)',
			'user_impact'    => __( 'Screen reader users cannot navigate site', 'wpshadow' ),
			'auto_fixable'   => true,
			'kb_link'        => 'https://wpshadow.com/kb/screen-reader-compat',
		);
	}

	/**
	 * Find screen reader accessibility issues.
	 *
	 * @since 1.6093.1200
	 * @return array Issues found.
	 */
	private static function find_screen_reader_issues(): array {
		$issues = array();

		// Check for landmark regions
		$html = self::get_page_html();
		if ( $html ) {
			$landmarks = self::check_landmarks( $html );
			$issues    = array_merge( $issues, $landmarks );

			// Check for ARIA labels
			$aria_issues = self::check_aria_labels( $html );
			$issues      = array_merge( $issues, $aria_issues );

			// Check for form labels
			$form_issues = self::check_form_labels( $html );
			$issues      = array_merge( $issues, $form_issues );
		}

		return array(
			'all' => array_unique( $issues ),
		);
	}

	/**
	 * Check for proper landmark regions.
	 *
	 * @since 1.6093.1200
	 * @param  string $html Page HTML.
	 * @return array Landmark issues.
	 */
	private static function check_landmarks( string $html ): array {
		$issues = array();

		// Check for nav landmark
		if ( ! preg_match( '/<nav|role\s*=\s*["\']navigation["\']/i', $html ) ) {
			$issues[] = array(
				'type'    => 'Missing landmark',
				'issue'   => 'No navigation landmark detected',
				'element' => 'nav',
			);
		}

		// Check for main landmark
		if ( ! preg_match( '/<main|role\s*=\s*["\']main["\']/i', $html ) ) {
			$issues[] = array(
				'type'    => 'Missing landmark',
				'issue'   => 'No main content landmark detected',
				'element' => 'main',
			);
		}

		return $issues;
	}

	/**
	 * Check for ARIA labels on interactive elements.
	 *
	 * @since 1.6093.1200
	 * @param  string $html Page HTML.
	 * @return array ARIA issues.
	 */
	private static function check_aria_labels( string $html ): array {
		$issues = array();

		// Check for icon buttons without labels
		$icon_buttons = preg_match_all( '/<button[^>]*class="[^"]*icon[^"]*"[^>]*>(\s|<[^>]*>)*<\/button>/i', $html, $matches );
		if ( $icon_buttons > 0 ) {
			$unlabeled = 0;
			foreach ( $matches[0] as $button ) {
				if ( ! preg_match( '/aria-label|title/i', $button ) ) {
					++$unlabeled;
				}
			}
			if ( $unlabeled > 0 ) {
				$issues[] = array(
					'type'     => 'Missing ARIA label',
					'count'    => $unlabeled,
					'elements' => 'Icon buttons',
				);
			}
		}

		return $issues;
	}

	/**
	 * Check for proper form labels.
	 *
	 * @since 1.6093.1200
	 * @param  string $html Page HTML.
	 * @return array Form label issues.
	 */
	private static function check_form_labels( string $html ): array {
		$issues = array();

		// Count unlabeled inputs
		$inputs = preg_match_all( '/<input[^>]*type=["\'](?!hidden)[^"\']*["\'][^>]*>/i', $html, $matches );
		if ( $inputs > 0 ) {
			$unlabeled = 0;
			foreach ( $matches[0] as $input ) {
				if ( ! preg_match( '/aria-label|id=["\']?[^"\'>\s]+["\']?.*<label[^>]*for=["\']?[^"\'>\s]+["\']?/is', $html, $label_check ) ) {
					++$unlabeled;
				}
			}
			if ( $unlabeled > 0 ) {
				$issues[] = array(
					'type'     => 'Unlabeled form input',
					'count'    => $unlabeled,
					'location' => 'Form elements',
				);
			}
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
