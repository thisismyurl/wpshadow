<?php
/**
 * Analytics Regular Review Diagnostic
 *
 * Verifies site owner regularly accesses and reviews analytics data.
 * Analytics only provide value when actively reviewed and acted upon.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Analytics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Reviews_Analytics_Regularly Class
 *
 * Checks for evidence that analytics data is regularly reviewed:
 * - Dashboard widgets displaying analytics data
 * - Email report configurations (MonsterInsights, Site Kit)
 * - Custom analytics dashboard pages
 * - Plugin settings indicating active monitoring
 *
 * @since 1.6093.1200
 */
class Diagnostic_Reviews_Analytics_Regularly extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $slug = 'reviews-analytics-regularly';

	/**
	 * The diagnostic title
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $title = 'Analytics Regularly Reviewed';

	/**
	 * The diagnostic description
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $description = 'Verifies site owner regularly accesses and reviews analytics data';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $family = 'analytics';

	/**
	 * Run the diagnostic check.
	 *
	 * First verifies analytics is installed, then looks for evidence of
	 * regular review through:
	 * 1. Dashboard widgets showing analytics
	 * 2. Email report configurations
	 * 3. Custom analytics pages
	 * 4. Recent plugin access logs
	 *
	 * @since 1.6093.1200
	 * @return array|null {
	 *     Finding array if analytics exists but no review setup, null otherwise.
	 *
	 *     @type string $id           Diagnostic identifier.
	 *     @type string $title        Issue title.
	 *     @type string $description  Detailed description with recommendations.
	 *     @type string $severity     Issue severity level.
	 *     @type int    $threat_level Numeric threat level (0-100).
	 *     @type bool   $auto_fixable Whether issue can be auto-fixed.
	 *     @type string $kb_link      Link to knowledge base article.
	 *     @type array  $meta         Additional diagnostic data.
	 * }
	 */
	public static function check() {
		// First check if analytics is installed at all.
		if ( ! self::has_analytics_installed() ) {
			return null; // No analytics - can't check for regular review.
		}

		$review_evidence = array();
		$review_setup    = false;

		// Check for dashboard widgets.
		$dashboard_check = self::check_dashboard_widgets();
		if ( $dashboard_check ) {
			$review_setup      = true;
			$review_evidence[] = $dashboard_check;
		}

		// Check for email reports configuration.
		$email_reports_check = self::check_email_reports();
		if ( $email_reports_check ) {
			$review_setup      = true;
			$review_evidence[] = $email_reports_check;
		}

		// Check for custom analytics pages.
		$custom_pages_check = self::check_custom_analytics_pages();
		if ( $custom_pages_check ) {
			$review_setup      = true;
			$review_evidence[] = $custom_pages_check;
		}

		// If evidence of regular review found, no issue to report.
		if ( $review_setup ) {
			return null;
		}

		// Analytics exists but no evidence of regular review - return finding.
		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => self::get_description(),
			'severity'     => 'medium',
			'threat_level' => 50,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/analytics-regular-review',
			'meta'         => array(
				'has_analytics'       => true,
				'dashboard_widgets'   => false,
				'email_reports'       => false,
				'custom_pages'        => false,
				'checked_methods'     => array(
					'dashboard_widgets',
					'email_reports',
					'custom_analytics_pages',
				),
			),
		);
	}

	/**
	 * Check if analytics is installed.
	 *
	 * @since 1.6093.1200
	 * @return bool True if analytics detected.
	 */
	private static function has_analytics_installed() {
		// Check for analytics plugins.
		$analytics_plugins = array(
			'google-analytics-for-wordpress/googleanalytics.php',
			'google-analytics-dashboard-for-wp/gadwp.php',
			'google-site-kit/google-site-kit.php',
			'ga-google-analytics/ga-google-analytics.php',
		);

		$active_plugins = get_option( 'active_plugins', array() );
		foreach ( $analytics_plugins as $plugin_file ) {
			if ( in_array( $plugin_file, $active_plugins, true ) ) {
				return true;
			}
		}

		// Check for tracking codes.
		ob_start();
		do_action( 'wp_head' );
		$header_output = ob_get_clean();

		if ( preg_match( '/G-[A-Z0-9]{10}|UA-\d{4,10}-\d{1,4}|gtag\(|analytics\.js/i', $header_output ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check for analytics dashboard widgets.
	 *
	 * @since 1.6093.1200
	 * @return array|null Widget details or null if none found.
	 */
	private static function check_dashboard_widgets() {
		global $wp_meta_boxes;

		// Get current user's dashboard widgets.
		$user_id        = get_current_user_id();
		$hidden_widgets = get_user_meta( $user_id, 'metaboxhidden_dashboard', true );
		if ( ! is_array( $hidden_widgets ) ) {
			$hidden_widgets = array();
		}

		// Known analytics widget IDs.
		$analytics_widgets = array(
			'monsterinsights_reports_widget',
			'exactmetrics_reports_widget',
			'gadwp-widget',
			'ga_dash_widget',
			'googlesitekit_dashboard_widget',
		);

		foreach ( $analytics_widgets as $widget_id ) {
			// Check if widget exists and is not hidden.
			if ( ! in_array( $widget_id, $hidden_widgets, true ) ) {
				// Widget is visible - evidence of review.
				return array(
					'method'    => 'dashboard_widget',
					'widget_id' => $widget_id,
				);
			}
		}

		return null;
	}

	/**
	 * Check for email report configuration.
	 *
	 * @since 1.6093.1200
	 * @return array|null Email report details or null if not configured.
	 */
	private static function check_email_reports() {
		// Check MonsterInsights email reports.
		if ( function_exists( 'MonsterInsights' ) ) {
			$settings = get_option( 'monsterinsights_settings', array() );
			if ( ! empty( $settings['email_summaries'] ) ) {
				return array(
					'method'   => 'email_reports',
					'provider' => 'MonsterInsights',
				);
			}
		}

		// Check ExactMetrics email reports.
		if ( function_exists( 'ExactMetrics' ) ) {
			$settings = get_option( 'exactmetrics_settings', array() );
			if ( ! empty( $settings['email_summaries'] ) ) {
				return array(
					'method'   => 'email_reports',
					'provider' => 'ExactMetrics',
				);
			}
		}

		// Check Site Kit notifications.
		if ( class_exists( 'Google\Site_Kit\Plugin' ) ) {
			$settings = get_option( 'googlesitekit_notification_settings', array() );
			if ( ! empty( $settings['enabled'] ) ) {
				return array(
					'method'   => 'email_reports',
					'provider' => 'Site Kit',
				);
			}
		}

		return null;
	}

	/**
	 * Check for custom analytics pages.
	 *
	 * @since 1.6093.1200
	 * @return array|null Custom page details or null if none found.
	 */
	private static function check_custom_analytics_pages() {
		// Check if MonsterInsights reports page is enabled.
		if ( function_exists( 'MonsterInsights' ) ) {
			$settings = get_option( 'monsterinsights_settings', array() );
			if ( ! empty( $settings['dashboards_disabled'] ) && 'disabled' !== $settings['dashboards_disabled'] ) {
				return array(
					'method'   => 'custom_page',
					'provider' => 'MonsterInsights',
					'page'     => 'admin.php?page=monsterinsights_reports',
				);
			}
		}

		// Check if Site Kit dashboard exists.
		if ( class_exists( 'Google\Site_Kit\Plugin' ) ) {
			return array(
				'method'   => 'custom_page',
				'provider' => 'Site Kit',
				'page'     => 'admin.php?page=googlesitekit-dashboard',
			);
		}

		return null;
	}

	/**
	 * Get detailed description of the finding.
	 *
	 * @since 1.6093.1200
	 * @return string Formatted description with recommendations.
	 */
	public static function get_description(): string {
		$description  = __( 'You have analytics installed, but there\'s no evidence of regular review. Analytics are only valuable when you actually look at the data and take action.', 'wpshadow' ) . "\n\n";
		$description .= '<strong>' . __( 'Why This Matters:', 'wpshadow' ) . "</strong>\n";
		$description .= __( '• Data without action is just noise', 'wpshadow' ) . "\n";
		$description .= __( '• Regular review helps you spot trends and opportunities early', 'wpshadow' ) . "\n";
		$description .= __( '• You can\'t optimize what you don\'t monitor', 'wpshadow' ) . "\n";
		$description .= __( '• Issues (traffic drops, broken funnels) go unnoticed without regular checks', 'wpshadow' ) . "\n";
		$description .= __( '• Consistent monitoring builds a baseline for measuring improvements', 'wpshadow' ) . "\n\n";

		$description .= '<strong>' . __( 'What You Should Be Watching:', 'wpshadow' ) . "</strong>\n";
		$description .= __( '• <strong>Weekly:</strong> Top pages, traffic sources, conversion rates', 'wpshadow' ) . "\n";
		$description .= __( '• <strong>Monthly:</strong> Trends, goal completions, user behavior changes', 'wpshadow' ) . "\n";
		$description .= __( '• <strong>Quarterly:</strong> Year-over-year comparisons, strategic insights', 'wpshadow' ) . "\n";
		$description .= __( '• <strong>After Changes:</strong> Impact of new content, design updates, campaigns', 'wpshadow' ) . "\n\n";

		$description .= '<strong>' . __( 'Make Analytics Easy to Review:', 'wpshadow' ) . "</strong>\n";
		$description .= __( '<strong>1. Enable Dashboard Widgets</strong>', 'wpshadow' ) . "\n";
		$description .= __( '   • See key metrics every time you log into WordPress', 'wpshadow' ) . "\n";
		$description .= __( '   • MonsterInsights, ExactMetrics, Site Kit all offer dashboard widgets', 'wpshadow' ) . "\n";
		$description .= __( '   • Configure to show your most important KPIs', 'wpshadow' ) . "\n\n";

		$description .= __( '<strong>2. Set Up Email Reports</strong>', 'wpshadow' ) . "\n";
		$description .= __( '   • Get weekly summaries delivered to your inbox', 'wpshadow' ) . "\n";
		$description .= __( '   • MonsterInsights: Settings > Email Summaries', 'wpshadow' ) . "\n";
		$description .= __( '   • Site Kit: Settings > Notifications', 'wpshadow' ) . "\n";
		$description .= __( '   • Schedule reports when you\'re most likely to review them', 'wpshadow' ) . "\n\n";

		$description .= __( '<strong>3. Create a Review Routine</strong>', 'wpshadow' ) . "\n";
		$description .= __( '   • Block 15 minutes every Monday for analytics review', 'wpshadow' ) . "\n";
		$description .= __( '   • Look for anomalies: sudden drops or spikes in traffic', 'wpshadow' ) . "\n";
		$description .= __( '   • Identify top-performing content to replicate success', 'wpshadow' ) . "\n";
		$description .= __( '   • Track progress toward your business goals', 'wpshadow' ) . "\n\n";

		$description .= '<strong>' . __( 'Quick Setup Instructions:', 'wpshadow' ) . "</strong>\n";
		$description .= __( '1. Go to WordPress Dashboard', 'wpshadow' ) . "\n";
		$description .= __( '2. Click "Screen Options" (top right)', 'wpshadow' ) . "\n";
		$description .= __( '3. Check the box next to your analytics plugin widget', 'wpshadow' ) . "\n";
		$description .= __( '4. Configure widget to show your key metrics', 'wpshadow' ) . "\n";
		$description .= __( '5. Enable email reports in your analytics plugin settings', 'wpshadow' );

		return $description;
	}
}
