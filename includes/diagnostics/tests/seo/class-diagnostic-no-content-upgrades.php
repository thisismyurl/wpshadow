<?php
/**
 * No Content Upgrades Diagnostic
 *
 * Tests whether post-specific lead magnets (content upgrades) are offered.
 * Content upgrades convert 5-10x better than generic lead magnets.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_No_Content_Upgrades Class
 *
 * Detects when sites lack post-specific downloadable resources. Content
 * upgrades (related to specific post) convert dramatically better than generic offers.
 *
 * @since 0.6093.1200
 */
class Diagnostic_No_Content_Upgrades extends Diagnostic_Base {

	protected static $slug = 'no-content-upgrades';
	protected static $title = 'No Content Upgrades';
	protected static $description = 'Tests whether post-specific lead magnets are offered';
	protected static $family = 'user-engagement';

	public static function check() {
		$score          = 0;
		$max_score      = 3;
		$score_details  = array();
		$recommendations = array();

		// Check for content upgrade indicators.
		$posts_with_upgrades = get_posts(
			array(
				'post_type'      => 'post',
				'posts_per_page' => 30,
				'post_status'    => 'publish',
				's'              => 'download checklist pdf exclusive bonus worksheet',
			)
		);

		if ( count( $posts_with_upgrades ) >= 10 ) {
			$score += 2;
			$score_details[] = sprintf( __( '✓ %d+ posts offer content upgrades', 'wpshadow' ), count( $posts_with_upgrades ) );
		} elseif ( count( $posts_with_upgrades ) > 0 ) {
			++$score;
			$score_details[]   = sprintf( __( '◐ %d post(s) with content upgrades', 'wpshadow' ), count( $posts_with_upgrades ) );
			$recommendations[] = __( 'Add post-specific downloads to more content', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No content upgrades detected', 'wpshadow' );
			$recommendations[] = __( 'Create post-specific downloads: checklists, templates, worksheets related to each post', 'wpshadow' );
		}

		// Check for inline opt-in forms/plugins.
		if ( is_plugin_active( 'thrive-leads/thrive-leads.php' ) || is_plugin_active( 'bloom/bloom.php' ) ) {
			++$score;
			$score_details[] = __( '✓ Opt-in form plugin active (enables content upgrades)', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No opt-in form plugin', 'wpshadow' );
			$recommendations[] = __( 'Install Thrive Leads or similar for inline content upgrade delivery', 'wpshadow' );
		}

		$score_percentage = ( $score / $max_score ) * 100;

		if ( $score_percentage >= 70 ) {
			return null;
		}

		$severity = 'medium';
		$threat_level = 25;

		return array(
			'id'               => self::$slug,
			'title'            => self::$title,
			'description'      => sprintf(
				/* translators: %d: score percentage */
				__( 'Content upgrade score: %d%%. Post-specific lead magnets convert 5-10x better than generic sidebar forms. Example: SEO post offers "SEO Checklist", not generic newsletter. Create relevant upgrades: checklists, templates, worksheets, bonus tips. Place inline after introducing topic when interest peaks.', 'wpshadow' ),
				$score_percentage
			),
			'severity'         => $severity,
			'threat_level'     => $threat_level,
			'auto_fixable'     => false,
			'kb_link'          => 'https://wpshadow.com/kb/content-upgrades?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'          => $score_details,
			'recommendations'  => $recommendations,
			'impact'           => __( 'Content upgrades leverage reader interest in specific topics, converting at much higher rates than generic opt-ins.', 'wpshadow' ),
		);
	}
}
