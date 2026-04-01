<?php
/**
 * Google Analytics Implementation Diagnostic
 *
 * Checks if Google Analytics is properly configured for traffic tracking.
 *
 * @package WPShadow\Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Google Analytics Implementation
 *
 * Detects whether the site has Google Analytics properly configured.
 */
class Diagnostic_Google_Analytics_Implementation extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'google-analytics-implementation';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Google Analytics Implementation';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies Google Analytics is installed and configured';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'analytics';

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Finding array if issues detected, null otherwise
	 */
	public static function check() {
		$issues  = array();
		$stats   = array();
		$plugins = array(
			'google-analytics-for-wordpress/google-analytics-for-wordpress.php' => 'MonsterInsights',
			'ga-google-analytics/ga-google-analytics.php'                        => 'GA Google Analytics',
			'googleanalyticsdashboard/googleanalyticsdashboard.php'             => 'Dashboard for Google Analytics',
			'google-site-kit/google-site-kit.php'                              => 'Google Site Kit',
		);

		$active = array();
		foreach ( $plugins as $file => $name ) {
			if ( is_plugin_active( $file ) ) {
				$active[] = $name;
			}
		}

		$stats['active_analytics_tools'] = count( $active );
		$stats['analytics_plugins']      = $active;

		// Check for Google Analytics tracking code in theme
		$theme_footer = '';
		$footer_path  = get_theme_file_path( 'footer.php' );
		if ( file_exists( $footer_path ) ) {
			$theme_footer = file_get_contents( $footer_path );
		}
		$stats['ga_code_in_theme'] = ! empty( $theme_footer ) && preg_match( '/google-analytics|gtag|GA_', $theme_footer );

		if ( empty( $active ) && ! $stats['ga_code_in_theme'] ) {
			$issues[] = __( 'Google Analytics not detected', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Google Analytics provides essential data about your visitors, traffic sources, and user behavior. This data is crucial for making informed business decisions, understanding customer journeys, and optimizing your marketing strategy.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 70,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/google-analytics?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'       => array(
					'stats'  => $stats,
					'issues' => $issues,
				),
			);
		}

		return null;
	}
}
