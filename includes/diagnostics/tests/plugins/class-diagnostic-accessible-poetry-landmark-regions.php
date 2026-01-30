<?php
/**
 * Accessible Poetry Landmark Regions Diagnostic
 *
 * Accessible Poetry Landmark Regions not compliant.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1098.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Accessible Poetry Landmark Regions Diagnostic Class
 *
 * @since 1.1098.0000
 */
class Diagnostic_AccessiblePoetryLandmarkRegions extends Diagnostic_Base {

	protected static $slug = 'accessible-poetry-landmark-regions';
	protected static $title = 'Accessible Poetry Landmark Regions';
	protected static $description = 'Accessible Poetry Landmark Regions not compliant';
	protected static $family = 'functionality';

	public static function check() {
		// Check for Accessible Poetry plugin
		if ( ! function_exists( 'accessible_poetry_init' ) && ! defined( 'ACCESSIBLE_POETRY_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: ARIA landmark configuration
		$landmarks_enabled = get_option( 'accessible_poetry_landmarks', true );
		if ( ! $landmarks_enabled ) {
			$issues[] = __( 'ARIA landmarks not enabled', 'wpshadow' );
		}
		
		// Check 2: Main content landmark
		$main_landmark = get_option( 'accessible_poetry_main_landmark', '' );
		if ( empty( $main_landmark ) ) {
			$issues[] = __( 'Main content landmark not configured', 'wpshadow' );
		}
		
		// Check 3: Navigation landmark
		$nav_landmark = get_option( 'accessible_poetry_nav_landmark', true );
		if ( ! $nav_landmark ) {
			$issues[] = __( 'Navigation landmarks disabled', 'wpshadow' );
		}
		
		// Check 4: Heading hierarchy validation
		$validate_headings = get_option( 'accessible_poetry_validate_headings', false );
		if ( ! $validate_headings ) {
			$issues[] = __( 'Heading hierarchy validation not enabled', 'wpshadow' );
		}
		
		// Check 5: Skip to content links
		$skip_links = get_option( 'accessible_poetry_skip_links', true );
		if ( ! $skip_links ) {
			$issues[] = __( 'Skip to content links disabled', 'wpshadow' );
		}
		
		// Check 6: Complementary regions
		$complementary = get_option( 'accessible_poetry_complementary_regions', false );
		if ( ! $complementary ) {
			$issues[] = __( 'Complementary landmark regions not configured', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 50;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 62;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 56;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of accessibility issues */
				__( 'Accessible Poetry has %d landmark region issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => true,
			'kb_link'     => 'https://wpshadow.com/kb/accessible-poetry-landmark-regions',
		);
	}
}
