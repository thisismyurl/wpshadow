<?php
/**
 * MonsterInsights Custom Dimensions Diagnostic
 *
 * MonsterInsights dimensions misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.430.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * MonsterInsights Custom Dimensions Diagnostic Class
 *
 * @since 1.430.0000
 */
class Diagnostic_MonsterinsightsCustomDimensions extends Diagnostic_Base {

	protected static $slug = 'monsterinsights-custom-dimensions';
	protected static $title = 'MonsterInsights Custom Dimensions';
	protected static $description = 'MonsterInsights dimensions misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'MONSTERINSIGHTS_VERSION' ) ) {
			return null;
		}

		$issues = array();
		$settings = get_option( 'monsterinsights_settings', array() );

		// Check 1: Verify custom dimensions feature enabled
		$dimensions_enabled = isset( $settings['custom_dimensions'] ) ? (bool) $settings['custom_dimensions'] : false;
		if ( ! $dimensions_enabled ) {
			$issues[] = 'Custom dimensions not enabled';
		}

		// Check 2: Check for dimension mappings
		$dimensions = get_option( 'monsterinsights_custom_dimensions', array() );
		if ( empty( $dimensions ) ) {
			$issues[] = 'No custom dimensions configured';
		}

		// Check 3: Verify author tracking dimension
		$has_author = false;
		if ( ! empty( $dimensions ) ) {
			foreach ( $dimensions as $dimension ) {
				if ( isset( $dimension['type'] ) && 'author' === $dimension['type'] ) {
					$has_author = true;
					break;
				}
			}
		}
		if ( ! $has_author ) {
			$issues[] = 'Author dimension not configured';
		}

		// Check 4: Check for category dimension
		$has_category = false;
		if ( ! empty( $dimensions ) ) {
			foreach ( $dimensions as $dimension ) {
				if ( isset( $dimension['type'] ) && 'category' === $dimension['type'] ) {
					$has_category = true;
					break;
				}
			}
		}
		if ( ! $has_category ) {
			$issues[] = 'Category dimension not configured';
		}

		// Check 5: Verify custom dimensions count
		if ( is_array( $dimensions ) && count( $dimensions ) > 10 ) {
			$issues[] = 'Too many custom dimensions configured';
		}

		// Check 6: Check for enhanced measurement settings
		$enhanced = isset( $settings['enhanced_measurement'] ) ? (bool) $settings['enhanced_measurement'] : false;
		if ( ! $enhanced ) {
			$issues[] = 'Enhanced measurement not enabled';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 40;
			$threat_multiplier = 6;
			$max_threat = 70;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d MonsterInsights custom dimensions issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/monsterinsights-custom-dimensions',
			);
		}

		return null;
	}
}
