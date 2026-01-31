<?php
/**
 * Plugin/Theme Active Count Diagnostic
 *
 * Monitors the number of active plugins and themes to detect excessive counts
 * that could impact performance and security.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6028.1650
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin/Theme Active Count Diagnostic Class
 *
 * Detects excessive plugin/theme counts that could:
 * - Slow site performance
 * - Increase security vulnerability surface
 * - Complicate maintenance
 * - Create plugin conflicts
 *
 * @since 1.6028.1650
 */
class Diagnostic_Active_Count extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @since 1.6028.1650
	 * @var   string
	 */
	protected static $slug = 'plugin-theme-active-count';

	/**
	 * The diagnostic title
	 *
	 * @since 1.6028.1650
	 * @var   string
	 */
	protected static $title = 'Plugin/Theme Active Count';

	/**
	 * The diagnostic description
	 *
	 * @since 1.6028.1650
	 * @var   string
	 */
	protected static $description = 'Monitors active plugin and theme count for performance and security impact';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @since 1.6028.1650
	 * @var   string
	 */
	protected static $family = 'settings';

	/**
	 * Cache duration in seconds (30 minutes)
	 *
	 * @since 1.6028.1650
	 */
	private const CACHE_DURATION = 1800;

	/**
	 * Recommended maximum active plugins
	 *
	 * @since 1.6028.1650
	 */
	private const RECOMMENDED_MAX_PLUGINS = 30;

	/**
	 * Warning threshold for active plugins
	 *
	 * @since 1.6028.1650
	 */
	private const WARNING_THRESHOLD = 50;

	/**
	 * Run the diagnostic check
	 *
	 * Analyzes active plugin and theme count by:
	 * - Counting active plugins
	 * - Identifying must-use and drop-in plugins
	 * - Checking for multiple active themes (multisite)
	 * - Calculating performance impact
	 *
	 * @since  1.6028.1650
	 * @return array|null Finding array if issues detected, null otherwise.
	 */
	public static function check() {
		// Check transient cache first.
		$cache_key = 'wpshadow_diagnostic_active_count';
		$cached    = get_transient( $cache_key );
		if ( false !== $cached ) {
			return self::evaluate_results( $cached );
		}

		// Analyze plugin and theme counts.
		$analysis = self::analyze_active_counts();

		// Cache results.
		set_transient( $cache_key, $analysis, self::CACHE_DURATION );

		return self::evaluate_results( $analysis );
	}

	/**
	 * Analyze active plugin and theme counts
	 *
	 * @since  1.6028.1650
	 * @return array Analysis results containing count data.
	 */
	private static function analyze_active_counts(): array {
		$analysis = array(
			'active_plugins'       => 0,
			'must_use_plugins'     => 0,
			'drop_in_plugins'      => 0,
			'total_plugins'        => 0,
			'active_theme_count'   => 1,
			'theme_name'           => '',
			'inactive_plugins'     => 0,
			'plugin_list'          => array(),
			'heaviest_plugins'     => array(),
			'issues'               => array(),
			'recommendations'      => array(),
		);

		// Get active plugins.
		$active_plugins = get_option( 'active_plugins', array() );
		if ( is_multisite() ) {
			$network_active = get_site_option( 'active_sitewide_plugins', array() );
			$active_plugins = array_merge( $active_plugins, array_keys( $network_active ) );
			$active_plugins = array_unique( $active_plugins );
		}

		$analysis['active_plugins'] = count( $active_plugins );

		// Get must-use plugins.
		$mu_plugins                  = get_mu_plugins();
		$analysis['must_use_plugins'] = count( $mu_plugins );

		// Get drop-in plugins.
		$dropins                      = get_dropins();
		$analysis['drop_in_plugins']  = count( $dropins );

		// Total plugin count.
		$analysis['total_plugins'] = $analysis['active_plugins'] + $analysis['must_use_plugins'] + $analysis['drop_in_plugins'];

		// Get all plugins for inactive count.
		$all_plugins                   = get_plugins();
		$analysis['inactive_plugins']  = count( $all_plugins ) - $analysis['active_plugins'];

		// Get plugin details.
		$analysis['plugin_list'] = self::get_plugin_details( $active_plugins, $all_plugins );

		// Get active theme info.
		$active_theme              = wp_get_theme();
		$analysis['theme_name']    = $active_theme->get( 'Name' );

		// For multisite, check if child sites have different themes.
		if ( is_multisite() ) {
			$analysis['active_theme_count'] = self::count_multisite_themes();
		}

		// Identify heaviest plugins (by size).
		$analysis['heaviest_plugins'] = self::identify_heavy_plugins( $analysis['plugin_list'] );

		// Evaluate and build issues/recommendations.
		$analysis = self::evaluate_counts( $analysis );

		return $analysis;
	}

	/**
	 * Get detailed plugin information
	 *
	 * @since  1.6028.1650
	 * @param  array $active_plugins List of active plugin basenames.
	 * @param  array $all_plugins    All plugin data.
	 * @return array Plugin details with size and metadata.
	 */
	private static function get_plugin_details( array $active_plugins, array $all_plugins ): array {
		$details = array();

		foreach ( $active_plugins as $plugin_file ) {
			if ( ! isset( $all_plugins[ $plugin_file ] ) ) {
				continue;
			}

			$plugin_data = $all_plugins[ $plugin_file ];
			$plugin_path = WP_PLUGIN_DIR . '/' . plugin_dir_path( $plugin_file );
			$plugin_size = self::get_directory_size( $plugin_path );

			$details[] = array(
				'name'    => $plugin_data['Name'],
				'version' => $plugin_data['Version'],
				'author'  => $plugin_data['Author'],
				'file'    => $plugin_file,
				'size'    => $plugin_size,
			);
		}

		return $details;
	}

	/**
	 * Calculate directory size recursively
	 *
	 * @since  1.6028.1650
	 * @param  string $path Directory path.
	 * @return int Directory size in bytes.
	 */
	private static function get_directory_size( string $path ): int {
		if ( ! is_dir( $path ) ) {
			return 0;
		}

		$size  = 0;
		$items = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $path, \FilesystemIterator::SKIP_DOTS ),
			\RecursiveIteratorIterator::SELF_FIRST
		);

		foreach ( $items as $item ) {
			if ( $item->isFile() ) {
				$size += $item->getSize();
			}
		}

		return $size;
	}

	/**
	 * Identify heaviest plugins by size
	 *
	 * @since  1.6028.1650
	 * @param  array $plugins Plugin details array.
	 * @return array Top 5 heaviest plugins.
	 */
	private static function identify_heavy_plugins( array $plugins ): array {
		// Sort by size descending.
		usort( $plugins, function( $a, $b ) {
			return $b['size'] - $a['size'];
		});

		// Return top 5.
		return array_slice( $plugins, 0, 5 );
	}

	/**
	 * Count unique themes across multisite network
	 *
	 * @since  1.6028.1650
	 * @return int Number of unique active themes.
	 */
	private static function count_multisite_themes(): int {
		global $wpdb;

		// Query all site themes.
		$themes = $wpdb->get_col(
			"SELECT DISTINCT meta_value 
			FROM {$wpdb->sitemeta} 
			WHERE meta_key = 'theme' 
			OR meta_key = 'stylesheet'"
		);

		return count( array_unique( $themes ) );
	}

	/**
	 * Evaluate plugin/theme counts and build issues
	 *
	 * @since  1.6028.1650
	 * @param  array $analysis Current analysis data.
	 * @return array Updated analysis with issues and recommendations.
	 */
	private static function evaluate_counts( array $analysis ): array {
		$issues          = array();
		$recommendations = array();

		// Check if plugin count is excessive.
		if ( $analysis['total_plugins'] > self::WARNING_THRESHOLD ) {
			$issues[] = sprintf(
				/* translators: %d: number of active plugins */
				__( 'Site has %d active plugins - excessive count can significantly impact performance', 'wpshadow' ),
				$analysis['total_plugins']
			);
			$recommendations[] = __( 'Review installed plugins and deactivate/delete unused ones', 'wpshadow' );
			$recommendations[] = __( 'Consider consolidating functionality with multi-purpose plugins', 'wpshadow' );
		} elseif ( $analysis['total_plugins'] > self::RECOMMENDED_MAX_PLUGINS ) {
			$issues[] = sprintf(
				/* translators: %d: number of active plugins */
				__( 'Site has %d active plugins - above recommended maximum of %d', 'wpshadow' ),
				$analysis['total_plugins'],
				self::RECOMMENDED_MAX_PLUGINS
			);
			$recommendations[] = __( 'Audit plugins for redundancy and consolidation opportunities', 'wpshadow' );
		}

		// Check for too many inactive plugins.
		if ( $analysis['inactive_plugins'] > 10 ) {
			$issues[] = sprintf(
				/* translators: %d: number of inactive plugins */
				__( '%d inactive plugins detected - security risk if not maintained', 'wpshadow' ),
				$analysis['inactive_plugins']
			);
			$recommendations[] = __( 'Delete inactive plugins to reduce security surface area', 'wpshadow' );
		}

		// Check must-use plugins.
		if ( $analysis['must_use_plugins'] > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of must-use plugins */
				__( '%d must-use plugins detected - these cannot be disabled by users', 'wpshadow' ),
				$analysis['must_use_plugins']
			);
		}

		// Multisite theme diversity.
		if ( is_multisite() && $analysis['active_theme_count'] > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of active themes */
				__( '%d different themes active across network - increases maintenance burden', 'wpshadow' ),
				$analysis['active_theme_count']
			);
			$recommendations[] = __( 'Standardize on fewer themes across network sites', 'wpshadow' );
		}

		$analysis['issues']          = $issues;
		$analysis['recommendations'] = $recommendations;

		return $analysis;
	}

	/**
	 * Evaluate analysis results and build finding
	 *
	 * @since  1.6028.1650
	 * @param  array $analysis Analysis results.
	 * @return array|null Finding array if issues detected, null otherwise.
	 */
	private static function evaluate_results( array $analysis ) {
		// No issues found.
		if ( empty( $analysis['issues'] ) ) {
			return null;
		}

		// Build finding.
		return self::build_finding( $analysis );
	}

	/**
	 * Build finding array
	 *
	 * @since  1.6028.1650
	 * @param  array $analysis Analysis results.
	 * @return array Finding array with full diagnostic information.
	 */
	private static function build_finding( array $analysis ): array {
		$issue_count  = count( $analysis['issues'] );
		$threat_level = self::calculate_threat_level( $analysis );
		$severity     = ( $analysis['total_plugins'] > self::WARNING_THRESHOLD ) ? 'high' : 'medium';

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: total plugins, 2: number of issues */
				__( 'Site has %1$d active plugins. Found %2$d configuration issues.', 'wpshadow' ),
				$analysis['total_plugins'],
				$issue_count
			),
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/settings-active-count',
			'family'       => self::$family,
			'meta'         => array(
				'active_plugins'      => $analysis['active_plugins'],
				'must_use_plugins'    => $analysis['must_use_plugins'],
				'drop_in_plugins'     => $analysis['drop_in_plugins'],
				'total_plugins'       => $analysis['total_plugins'],
				'inactive_plugins'    => $analysis['inactive_plugins'],
				'theme_name'          => $analysis['theme_name'],
				'active_theme_count'  => $analysis['active_theme_count'],
				'performance_impact'  => self::calculate_performance_impact( $analysis['total_plugins'] ),
			),
			'details'      => self::build_finding_details( $analysis ),
		);
	}

	/**
	 * Calculate threat level based on plugin count
	 *
	 * @since  1.6028.1650
	 * @param  array $analysis Analysis results.
	 * @return int Threat level (25-50).
	 */
	private static function calculate_threat_level( array $analysis ): int {
		$total = $analysis['total_plugins'];

		if ( $total > 70 ) {
			return 50;
		} elseif ( $total > self::WARNING_THRESHOLD ) {
			return 45;
		} elseif ( $total > 40 ) {
			return 35;
		} elseif ( $total > self::RECOMMENDED_MAX_PLUGINS ) {
			return 30;
		} else {
			return 25;
		}
	}

	/**
	 * Calculate performance impact message
	 *
	 * @since  1.6028.1650
	 * @param  int $plugin_count Number of active plugins.
	 * @return string Performance impact description.
	 */
	private static function calculate_performance_impact( int $plugin_count ): string {
		if ( $plugin_count > 70 ) {
			return __( 'Severe: Likely causing significant performance degradation', 'wpshadow' );
		} elseif ( $plugin_count > self::WARNING_THRESHOLD ) {
			return __( 'High: Measurable performance impact expected', 'wpshadow' );
		} elseif ( $plugin_count > 40 ) {
			return __( 'Moderate: Some performance impact likely', 'wpshadow' );
		} elseif ( $plugin_count > self::RECOMMENDED_MAX_PLUGINS ) {
			return __( 'Minor: Slight performance impact possible', 'wpshadow' );
		} else {
			return __( 'Minimal: Plugin count within healthy range', 'wpshadow' );
		}
	}

	/**
	 * Build detailed information for finding
	 *
	 * @since  1.6028.1650
	 * @param  array $analysis Analysis results.
	 * @return array Detailed information array.
	 */
	private static function build_finding_details( array $analysis ): array {
		return array(
			'issues_found'       => $analysis['issues'],
			'recommendations'    => $analysis['recommendations'],
			'heaviest_plugins'   => array_map( function( $plugin ) {
				return array(
					'name' => $plugin['name'],
					'size' => size_format( $plugin['size'] ),
				);
			}, $analysis['heaviest_plugins'] ),
			'why_this_matters'   => __( 'Each active plugin increases your site\'s attack surface, potential for conflicts, and performance overhead. While plugins add valuable functionality, excessive plugin count is a common cause of slow page loads, security vulnerabilities, and difficult troubleshooting.', 'wpshadow' ),
			'industry_benchmark' => sprintf(
				/* translators: %d: recommended maximum */
				__( 'Most well-optimized WordPress sites run on %d or fewer plugins', 'wpshadow' ),
				self::RECOMMENDED_MAX_PLUGINS
			),
			'next_steps'         => array(
				__( 'Audit each plugin for necessity and usage', 'wpshadow' ),
				__( 'Identify plugins with overlapping functionality', 'wpshadow' ),
				__( 'Replace multiple single-purpose plugins with multi-purpose alternatives', 'wpshadow' ),
				__( 'Delete (not just deactivate) unused plugins', 'wpshadow' ),
				__( 'Measure performance before/after removing plugins', 'wpshadow' ),
			),
		);
	}
}
