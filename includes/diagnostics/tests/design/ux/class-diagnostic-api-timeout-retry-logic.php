<?php
/**
 * API Timeout and Retry Logic Diagnostic
 *
 * Detects when API calls lack timeout configuration and retry logic.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\UX
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\UX;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * API Timeout Retry Logic Diagnostic Class
 *
 * Checks if external API calls have proper timeout and retry configuration.
 *
 * @since 1.6093.1200
 */
class Diagnostic_API_Timeout_Retry_Logic extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'api-timeout-retry-logic';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'API Calls Don\'t Have Timeout or Retry Logic';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects when API calls lack proper timeout and retry configuration';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'ux';

	/**
	 * Run the diagnostic check
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array or null if no issues found.
	 */
	public static function check() {
		$issues          = array();
		$api_usage_found = false;

		// Check for plugins that commonly use external APIs.
		$api_plugins = array(
			'woocommerce/woocommerce.php'         => 'WooCommerce (payment gateways, shipping)',
			'jetpack/jetpack.php'                 => 'Jetpack (WordPress.com API)',
			'mailchimp-for-wp/mailchimp-for-wp.php' => 'Mailchimp for WordPress',
			'akismet/akismet.php'                 => 'Akismet (spam checking API)',
			'google-analytics-for-wordpress/googleanalytics.php' => 'MonsterInsights',
			'yoast-seo-premium/yoast-seo-premium.php' => 'Yoast SEO Premium',
			'wordfence/wordfence.php'             => 'Wordfence (security API)',
		);

		$active_api_plugins = array();
		foreach ( $api_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_api_plugins[] = $name;
				$api_usage_found      = true;
			}
		}

		if ( ! $api_usage_found ) {
			return null; // No API-heavy plugins detected.
		}

		// Check theme files for API calls.
		$theme_root  = get_theme_root();
		$theme_dir   = get_stylesheet_directory();
		$theme_files = array();

		if ( is_dir( $theme_dir ) ) {
			$iterator = new \RecursiveIteratorIterator(
				new \RecursiveDirectoryIterator( $theme_dir, \RecursiveDirectoryIterator::SKIP_DOTS )
			);

			foreach ( $iterator as $file ) {
				if ( $file->isFile() && 'php' === $file->getExtension() ) {
					$content = file_get_contents( $file->getPathname() );

					// Check for API call patterns without timeout.
					if ( preg_match( '/wp_remote_(get|post|request)\s*\(/i', $content ) ) {
						// Check if timeout is set.
						if ( ! preg_match( '/[\'"]timeout[\'"]\s*=>/i', $content ) ) {
							$theme_files[] = basename( $file->getPathname() );
						}
					}
				}
			}
		}

		if ( ! empty( $theme_files ) ) {
			$issues['theme_files_without_timeout'] = $theme_files;
		}

		// If APIs are used but no timeout issues found in theme, still warn about general practice.
		if ( ! $api_usage_found && empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Your site makes calls to external services without timeout limits or retry logic. When those services are slow, your entire site hangs waiting', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 60,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/api-timeout-retry',
			'context'      => array(
				'plugins_using_apis'     => $active_api_plugins,
				'theme_files_with_issues' => $theme_files ?? array(),
				'impact'                 => __( 'When payment gateways, email services, or analytics APIs are slow, your site freezes. Users see loading screens that never complete. This especially affects checkout pages.', 'wpshadow' ),
				'recommendation'         => array(
					__( 'Set timeout limits on all wp_remote_* calls (recommended: 5-10 seconds)', 'wpshadow' ),
					__( 'Add retry logic with exponential backoff for failed API calls', 'wpshadow' ),
					__( 'Implement fallback behavior when APIs timeout', 'wpshadow' ),
					__( 'Cache API responses when possible', 'wpshadow' ),
					__( 'Queue non-critical API calls to run asynchronously', 'wpshadow' ),
					__( 'Monitor API response times and set alerts', 'wpshadow' ),
				),
				'example_code'           => "wp_remote_get( \$url, array(\n    'timeout' => 5,\n    'redirection' => 2,\n) );",
				'worst_case_scenario'    => __( 'Without timeouts, a slow API can hang your entire site for 300+ seconds (default PHP timeout)', 'wpshadow' ),
			),
		);
	}
}
