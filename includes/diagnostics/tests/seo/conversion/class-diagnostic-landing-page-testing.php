<?php
/**
 * Landing Page Testing Diagnostic
 *
 * Tests if landing pages are tested upon creation.
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
 * Landing Page Testing Diagnostic Class
 *
 * Evaluates whether landing pages are regularly tested and optimized.
 * Checks for landing page builders, A/B testing, analytics, and optimization tools.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Landing_Page_Testing extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'tests_landing_pages';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Landing Page Testing';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if landing pages are tested upon creation';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'conversion';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$stats         = array();
		$issues        = array();
		$warnings      = array();
		$score         = 0;
		$total_points  = 0;
		$earned_points = 0;

		// Check for landing page builder plugins.
		$landing_page_builders = array(
			'elementor/elementor.php'                     => 'Elementor',
			'elementor-pro/elementor-pro.php'             => 'Elementor Pro',
			'beaver-builder-lite-version/fl-builder.php'  => 'Beaver Builder',
			'thrive-visual-editor/thrive-visual-editor.php' => 'Thrive Architect',
			'divi-builder/divi-builder.php'               => 'Divi Builder',
			'seedprod-coming-soon-pro-5/seedprod-coming-soon-pro-5.php' => 'SeedProd',
			'landing-pages/landing-pages.php'             => 'Inbound Now Landing Pages',
			'convertpro/convertpro.php'                   => 'Convert Pro',
			'unbounce/unbounce.php'                       => 'Unbounce',
		);

		$active_builders = array();
		foreach ( $landing_page_builders as $plugin => $name ) {
			$total_points += 15;
			if ( is_plugin_active( $plugin ) ) {
				$active_builders[] = $name;
				$earned_points    += 15;
				break; // Only need one builder.
			}
		}

		$stats['landing_page_builders'] = array(
			'found' => count( $active_builders ),
			'list'  => $active_builders,
		);

		if ( empty( $active_builders ) ) {
			$issues[] = __( 'No dedicated landing page builder detected', 'wpshadow' );
		}

		// Check for landing pages in site.
		$total_points += 15;
		$landing_pages = get_posts(
			array(
				'post_type'      => 'page',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
				'meta_query'     => array(
					'relation' => 'OR',
					array(
						'key'     => '_wp_page_template',
						'value'   => 'landing',
						'compare' => 'LIKE',
					),
					array(
						'key'     => '_wp_page_template',
						'value'   => 'lp-',
						'compare' => 'LIKE',
					),
					array(
						'key'     => '_wp_page_template',
						'value'   => 'template-landing',
						'compare' => 'LIKE',
					),
				),
			)
		);

		// Also search by title/content.
		$landing_pages_by_search = get_posts(
			array(
				'post_type'      => 'page',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
				's'              => 'landing page',
			)
		);

		$total_landing_pages = count( $landing_pages ) + count( $landing_pages_by_search );

		if ( $total_landing_pages > 0 ) {
			$earned_points += 15;
			$stats['landing_pages_count'] = $total_landing_pages;
		} else {
			$stats['landing_pages_count'] = 0;
			$warnings[] = __( 'No landing pages detected', 'wpshadow' );
		}

		// Check for A/B testing tools.
		$total_points += 20;
		$ab_testing_tools = array(
			'google-optimize' => 'Google Optimize',
			'optimizely'      => 'Optimizely',
			'vwo'             => 'VWO',
			'ab-tasty'        => 'AB Tasty',
			'unbounce'        => 'Unbounce',
		);

		$active_ab_tools = array();
		foreach ( $ab_testing_tools as $handle => $name ) {
			if ( wp_script_is( $handle, 'enqueued' ) || wp_script_is( $handle, 'registered' ) ) {
				$active_ab_tools[] = $name;
			}
		}

		if ( ! empty( $active_ab_tools ) ) {
			$earned_points += 20;
		}

		$stats['ab_testing_tools'] = array(
			'found' => count( $active_ab_tools ),
			'list'  => $active_ab_tools,
		);

		if ( empty( $active_ab_tools ) ) {
			$issues[] = __( 'No A/B testing tools detected for landing page optimization', 'wpshadow' );
		}

		// Check for analytics tracking.
		$total_points += 15;
		if ( wp_script_is( 'google-analytics', 'enqueued' ) ||
			 wp_script_is( 'gtag', 'enqueued' ) ||
			 is_plugin_active( 'google-analytics-for-wordpress/googleanalytics.php' ) ||
			 is_plugin_active( 'google-site-kit/google-site-kit.php' ) ) {
			$earned_points += 15;
			$stats['analytics_enabled'] = true;
		} else {
			$stats['analytics_enabled'] = false;
			$issues[] = __( 'No analytics platform detected for landing page tracking', 'wpshadow' );
		}

		// Check for heatmap/behavior analysis tools.
		$total_points += 15;
		$heatmap_tools = array(
			'hotjar-async' => 'Hotjar',
			'crazyegg'     => 'CrazyEgg',
			'mouseflow'    => 'Mouseflow',
			'fullstory'    => 'FullStory',
			'lucky-orange' => 'Lucky Orange',
		);

		$active_heatmap_tools = array();
		foreach ( $heatmap_tools as $handle => $name ) {
			if ( wp_script_is( $handle, 'enqueued' ) || wp_script_is( $handle, 'registered' ) ) {
				$active_heatmap_tools[] = $name;
			}
		}

		if ( ! empty( $active_heatmap_tools ) ) {
			$earned_points += 15;
		}

		$stats['heatmap_tools'] = array(
			'found' => count( $active_heatmap_tools ),
			'list'  => $active_heatmap_tools,
		);

		if ( empty( $active_heatmap_tools ) ) {
			$warnings[] = __( 'No heatmap or behavior analysis tools detected', 'wpshadow' );
		}

		// Check for conversion tracking.
		$total_points += 10;
		$conversion_tracking = array(
			'gtag',
			'fbq',
			'linkedin-insight',
			'google-tag-manager',
		);

		$active_conversion_tracking = array();
		foreach ( $conversion_tracking as $handle ) {
			if ( wp_script_is( $handle, 'enqueued' ) || wp_script_is( $handle, 'registered' ) ) {
				$active_conversion_tracking[] = $handle;
			}
		}

		if ( ! empty( $active_conversion_tracking ) ) {
			$earned_points += 10;
		}

		$stats['conversion_tracking'] = array(
			'found' => count( $active_conversion_tracking ),
			'list'  => $active_conversion_tracking,
		);

		// Check for lead capture forms.
		$total_points += 10;
		$form_plugins = array(
			'gravityforms/gravityforms.php' => 'Gravity Forms',
			'wpforms-lite/wpforms.php'      => 'WPForms',
			'wpforms/wpforms.php'           => 'WPForms Pro',
			'formidable/formidable.php'     => 'Formidable Forms',
			'ninja-forms/ninja-forms.php'   => 'Ninja Forms',
			'fluentform/fluentform.php'     => 'Fluent Forms',
		);

		$active_form_plugins = array();
		foreach ( $form_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_form_plugins[] = $name;
				break;
			}
		}

		if ( ! empty( $active_form_plugins ) ) {
			$earned_points += 10;
		}

		$stats['form_plugins'] = array(
			'found' => count( $active_form_plugins ),
			'list'  => $active_form_plugins,
		);

		// Calculate final score.
		if ( $total_points > 0 ) {
			$score = round( ( $earned_points / $total_points ) * 100 );
		}

		$stats['score']         = $score;
		$stats['total_points']  = $total_points;
		$stats['earned_points'] = $earned_points;

		// Determine severity.
		$severity     = 'medium';
		$threat_level = 40;

		if ( $score < 30 ) {
			$severity     = 'high';
			$threat_level = 55;
		} elseif ( $score > 70 ) {
			$severity     = 'low';
			$threat_level = 25;
		}

		// Return finding if landing page testing is insufficient.
		if ( $score < 50 ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %d: testing score percentage */
					__( 'Landing page testing infrastructure score: %d%%. Regular testing of landing pages can significantly improve conversion rates.', 'wpshadow' ),
					$score
				),
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/landing-page-testing',
				'context'      => array(
					'stats'    => $stats,
					'issues'   => $issues,
					'warnings' => $warnings,
				),
			);
		}

		return null;
	}
}
