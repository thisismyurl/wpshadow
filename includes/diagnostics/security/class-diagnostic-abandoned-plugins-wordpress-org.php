<?php
/**
 * Abandoned Plugins Detection Diagnostic
 *
 * Scans all installed plugins to detect those that haven't been updated in a long time,
 * indicating they may be abandoned by their developers. Abandoned plugins are security
 * risks because they won't receive security patches.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since      1.6035.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Security;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Security\Security_API_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Abandoned_Plugins_WordPress_Org Class
 *
 * Detects plugins that haven't been updated in 2+ years, indicating they may be
 * abandoned. Abandoned plugins are security risks because their developers aren't
 * providing security patches for new vulnerabilities.
 *
 * Uses WordPress.org REST API (free, no API key required) to check plugin update status.
 * Respects WordPress.org rate limiting and caches results for 24 hours.
 *
 * @since 1.6035.0000
 */
class Diagnostic_Abandoned_Plugins_WordPress_Org extends Diagnostic_Base {

	/**
	 * The diagnostic slug (unique identifier).
	 *
	 * @var string
	 */
	protected static $slug = 'abandoned-plugins-wordpress-org';

	/**
	 * The diagnostic title shown to users.
	 *
	 * @var string
	 */
	protected static $title = 'Abandoned Plugins Detection';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks for plugins that haven\'t been updated in 2+ years';

	/**
	 * The diagnostic family (for grouping related diagnostics).
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Minimum days of inactivity before marking plugin as abandoned.
	 *
	 * @var int
	 */
	const ABANDONMENT_THRESHOLD_DAYS = 730; // 2 years

	/**
	 * Maximum plugins to check per run (prevent timeouts).
	 *
	 * @var int
	 */
	const MAX_PLUGINS_TO_CHECK = 100;

	/**
	 * API transient cache duration (24 hours).
	 *
	 * @var int
	 */
	const CACHE_TTL = 86400; // 24 hours

	/**
	 * Run the diagnostic check.
	 *
	 * Scans all installed plugins and queries WordPress.org API to determine
	 * their last update date. Returns findings for plugins not updated in 2+ years.
	 *
	 * @since  1.6035.0000
	 * @return array|null Finding array if abandoned plugins found, null otherwise.
	 */
	public static function check() {
		// Get all installed plugins.
		$all_plugins = get_plugins();

		if ( empty( $all_plugins ) ) {
			return null; // No plugins installed.
		}

		// Limit to prevent timeouts on sites with 100+ plugins.
		$plugins_to_check = array_slice( $all_plugins, 0, self::MAX_PLUGINS_TO_CHECK, true );

		$abandoned_plugins = array();
		$total_checked     = 0;
		$total_skipped     = 0;

		// Check each plugin against WordPress.org.
		foreach ( $plugins_to_check as $plugin_file => $plugin_data ) {
			$total_checked++;

			// Extract plugin slug from plugin file path (e.g., "hello/hello.php" -> "hello").
			$plugin_slug = explode( '/', $plugin_file );
			$plugin_slug = $plugin_slug[0];

			// Skip mu-plugins (must-use plugins in /wp-content/mu-plugins/).
			if ( strpos( $plugin_file, '..' ) === 0 ) {
				$total_skipped++;
				continue;
			}

			// Check cache first.
			$cached_plugin_data = self::get_cache( $plugin_slug );
			if ( false !== $cached_plugin_data ) {
				// Use cached data.
				$plugin_info = $cached_plugin_data;
			} else {
				// Fetch from WordPress.org API.
				$plugin_info = self::fetch_plugin_info( $plugin_slug );

				// Cache the result (even if empty, to avoid repeated API calls).
				if ( $plugin_info ) {
					self::set_cache( $plugin_slug, $plugin_info );
				}
			}

			// Skip if couldn't fetch info.
			if ( ! $plugin_info ) {
				$total_skipped++;
				continue;
			}

			// Check if plugin is abandoned.
			$last_updated = strtotime( $plugin_info['last_updated'] ?? '' );
			$abandonment_days = round( ( time() - $last_updated ) / ( 60 * 60 * 24 ) );

			if ( $abandonment_days >= self::ABANDONMENT_THRESHOLD_DAYS ) {
				$abandoned_plugins[] = array(
					'plugin'              => $plugin_slug,
					'name'                => $plugin_data['Name'] ?? $plugin_slug,
					'version'             => $plugin_data['Version'] ?? 'Unknown',
					'last_updated_date'   => gmdate( 'Y-m-d', $last_updated ),
					'days_without_update' => $abandonment_days,
					'years_without_update'=> round( $abandonment_days / 365, 1 ),
					'wp_org_link'         => "https://wordpress.org/plugins/{$plugin_slug}/",
				);
			}
		}

		// No abandoned plugins found.
		if ( empty( $abandoned_plugins ) ) {
			return null;
		}

		// Calculate severity and threat level.
		$severity     = self::determine_severity( $abandoned_plugins );
		$threat_level = self::calculate_threat_level( $abandoned_plugins, count( $all_plugins ) );
		$description  = self::build_description( $abandoned_plugins );

		return array(
			'id'              => self::$slug,
			'title'           => self::$title,
			'description'     => $description,
			'severity'        => $severity,
			'threat_level'    => $threat_level,
			'auto_fixable'    => false,
			'affected_items'  => $abandoned_plugins,
			'item_count'      => count( $abandoned_plugins ),
			'total_checked'   => $total_checked,
			'total_skipped'   => $total_skipped,
			'kb_link'         => 'https://wpshadow.com/kb/abandoned-plugins-fix',
		);
	}

	/**
	 * Fetch plugin information from WordPress.org API.
	 *
	 * Queries the WordPress.org plugins REST API to get plugin metadata including
	 * last update date. Returns null if plugin not found or API error.
	 *
	 * @since  1.6035.0000
	 * @param  string $plugin_slug The plugin slug (directory name).
	 * @return array|null Plugin data array with 'last_updated' key, or null on error.
	 */
	private static function fetch_plugin_info( string $plugin_slug ) {
		// WordPress.org plugins API endpoint.
		$url = "https://api.wordpress.org/plugins/info/1.0/{$plugin_slug}.json";

		// Make request with 5-second timeout.
		$response = wp_remote_get(
			$url,
			array(
				'timeout'     => 5,
				'redirection' => 2,
				'sslverify'   => true,
			)
		);

		// Handle network errors.
		if ( is_wp_error( $response ) ) {
			return null;
		}

		// Check response code.
		$response_code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $response_code ) {
			// Plugin not found (404) or API error.
			return null;
		}

		// Parse JSON response.
		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( ! is_array( $data ) || empty( $data['last_updated'] ) ) {
			return null;
		}

		return $data;
	}

	/**
	 * Get cached plugin info from transient.
	 *
	 * @since  1.6035.0000
	 * @param  string $plugin_slug The plugin slug.
	 * @return array|false Cached data or false if not found.
	 */
	private static function get_cache( string $plugin_slug ) {
		$cache_key = 'wpshadow_abandoned_plugin_' . sanitize_key( $plugin_slug );
		return get_transient( $cache_key );
	}

	/**
	 * Set cached plugin info in transient.
	 *
	 * @since  1.6035.0000
	 * @param  string $plugin_slug The plugin slug.
	 * @param  array  $data Plugin data to cache.
	 * @return void
	 */
	private static function set_cache( string $plugin_slug, array $data ) {
		$cache_key = 'wpshadow_abandoned_plugin_' . sanitize_key( $plugin_slug );
		set_transient( $cache_key, $data, self::CACHE_TTL );
	}

	/**
	 * Determine the severity level based on abandoned plugins.
	 *
	 * Severity is based on how many plugins are abandoned and how long they've
	 * been abandoned. More abandoned plugins = higher severity.
	 *
	 * @since  1.6035.0000
	 * @param  array $abandoned_plugins Array of abandoned plugin data.
	 * @return string Severity level: critical, high, medium, low.
	 */
	private static function determine_severity( array $abandoned_plugins ) : string {
		$count = count( $abandoned_plugins );

		// Multiple abandoned plugins = critical.
		if ( $count >= 3 ) {
			return 'critical';
		}

		// Some abandoned plugins = high.
		if ( $count >= 2 ) {
			return 'high';
		}

		// One abandoned plugin = medium.
		return 'medium';
	}

	/**
	 * Calculate threat level (0-100 scale).
	 *
	 * Threat level is based on how many plugins are abandoned relative to total.
	 * More abandoned plugins and higher ratio = higher threat level.
	 *
	 * @since  1.6035.0000
	 * @param  array $abandoned_plugins Array of abandoned plugin data.
	 * @param  int   $total_plugins Total number of installed plugins.
	 * @return int Threat level from 0 to 100.
	 */
	private static function calculate_threat_level( array $abandoned_plugins, int $total_plugins ) : int {
		$abandoned_count = count( $abandoned_plugins );

		// No plugins = no threat.
		if ( 0 === $abandoned_count ) {
			return 0;
		}

		// Calculate percentage of plugins that are abandoned.
		$percentage = round( ( $abandoned_count / $total_plugins ) * 100 );

		// Base threat level on count and percentage.
		if ( $abandoned_count >= 5 ) {
			$threat_level = 90; // Many abandoned plugins.
		} elseif ( $abandoned_count >= 3 ) {
			$threat_level = 70; // Several abandoned plugins.
		} elseif ( $percentage >= 25 ) {
			$threat_level = 60; // High percentage abandoned.
		} elseif ( $abandoned_count >= 2 ) {
			$threat_level = 50; // Two abandoned plugins.
		} else {
			$threat_level = 40; // One abandoned plugin.
		}

		return $threat_level;
	}

	/**
	 * Build user-friendly description of findings.
	 *
	 * Creates a clear, actionable message explaining what abandoned plugins are,
	 * why they're a problem, and what users should do about them.
	 *
	 * @since  1.6035.0000
	 * @param  array $abandoned_plugins Array of abandoned plugin data.
	 * @return string Human-readable description.
	 */
	private static function build_description( array $abandoned_plugins ) : string {
		$count = count( $abandoned_plugins );

		// Start with what we found.
		$description = sprintf(
			/* translators: %d is the number of abandoned plugins */
			_n(
				'We found %d plugin that hasn\'t been updated in 2+ years.',
				'We found %d plugins that haven\'t been updated in 2+ years.',
				$count,
				'wpshadow'
			),
			$count
		);

		$description .= ' ';

		// Explain why this matters.
		$description .= __(
			'Think of plugins like apps on your phone—if the developer stops updating them, they won\'t get security patches. This leaves your site vulnerable to hackers who know about old bugs nobody fixed.',
			'wpshadow'
		);

		$description .= "\n\n";

		// List the abandoned plugins.
		$description .= __( 'Abandoned plugins found:', 'wpshadow' ) . "\n";
		foreach ( $abandoned_plugins as $plugin ) {
			$description .= sprintf(
				'• %s (v%s) - Last updated %s (%s)',
				esc_html( $plugin['name'] ),
				esc_html( $plugin['version'] ),
				esc_html( $plugin['last_updated_date'] ),
				sprintf(
					/* translators: %s is the number of years */
					_n(
						'%s year ago',
						'%s years ago',
						intval( $plugin['years_without_update'] ),
						'wpshadow'
					),
					number_format( $plugin['years_without_update'], 1 )
				)
			);
			$description .= "\n";
		}

		$description .= "\n";

		// Action steps.
		$description .= __( 'What you can do:', 'wpshadow' ) . "\n";
		$description .= __( '1. Update or remove abandoned plugins—look for active alternatives.', 'wpshadow' ) . "\n";
		$description .= __( '2. Check if the developer has moved the plugin elsewhere (GitHub, new maintainer).', 'wpshadow' ) . "\n";
		$description .= __( '3. Disable and test if you don\'t need the plugin anymore.', 'wpshadow' ) . "\n";
		$description .= __( '4. Run this check again after making changes.', 'wpshadow' ) . "\n";

		return $description;
	}
}
