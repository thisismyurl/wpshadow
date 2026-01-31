<?php
/**
 * Github Updater Rate Limits Diagnostic
 *
 * Github Updater Rate Limits issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1078.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Github Updater Rate Limits Diagnostic Class
 *
 * @since 1.1078.0000
 */
class Diagnostic_GithubUpdaterRateLimits extends Diagnostic_Base {

	protected static $slug = 'github-updater-rate-limits';
	protected static $title = 'Github Updater Rate Limits';
	protected static $description = 'Github Updater Rate Limits issue detected';
	protected static $family = 'functionality';

	public static function check() {
		// Check for GitHub Updater plugin
		if ( ! class_exists( 'Fragen\\GitHub_Updater\\Base' ) && ! function_exists( 'github_updater_init' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: GitHub personal access token configured
		$github_token = get_site_option( 'github_updater_github_access_token', '' );
		if ( empty( $github_token ) ) {
			$issues[] = __( 'No GitHub access token (60 requests/hour limit)', 'wpshadow' );
		}
		
		// Check 2: Rate limit status
		$rate_limit = get_transient( 'github_updater_rate_limit' );
		if ( is_array( $rate_limit ) && isset( $rate_limit['remaining'] ) ) {
			$remaining = (int) $rate_limit['remaining'];
			$limit = isset( $rate_limit['limit'] ) ? (int) $rate_limit['limit'] : 60;
			
			if ( $remaining < 10 ) {
				$issues[] = sprintf( __( 'GitHub API: %d of %d requests remaining', 'wpshadow' ), $remaining, $limit );
			}
		}
		
		// Check 3: Rate limit exceeded errors
		$rate_limit_errors = get_option( 'github_updater_rate_limit_errors', 0 );
		if ( $rate_limit_errors > 5 ) {
			$issues[] = sprintf( __( '%d rate limit errors in past 24 hours', 'wpshadow' ), $rate_limit_errors );
		}
		
		// Check 4: Multiple GitHub-hosted plugins/themes
		$github_repos = array();
		$all_plugins = get_plugins();
		
		foreach ( $all_plugins as $plugin_file => $plugin_data ) {
			if ( isset( $plugin_data['GitHub Plugin URI'] ) ) {
				$github_repos[] = $plugin_file;
			}
		}
		
		$all_themes = wp_get_themes();
		foreach ( $all_themes as $theme ) {
			$theme_data = $theme->get( 'GitHub Theme URI' );
			if ( ! empty( $theme_data ) ) {
				$github_repos[] = $theme->get_stylesheet();
			}
		}
		
		if ( count( $github_repos ) > 10 && empty( $github_token ) ) {
			$issues[] = sprintf( __( '%d GitHub-hosted items without auth token (frequent rate limits)', 'wpshadow' ), count( $github_repos ) );
		}
		
		// Check 5: Update check caching
		$cache_duration = get_option( 'github_updater_cache_duration', 12 );
		if ( $cache_duration < 6 && count( $github_repos ) > 5 ) {
			$issues[] = sprintf( __( 'Update check cache: %d hours (increase to reduce API calls)', 'wpshadow' ), $cache_duration );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 50;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 65;
		} elseif ( count( $issues ) >= 2 ) {
			$threat_level = 58;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of rate limit issues */
				__( 'GitHub Updater has %d rate limit issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/github-updater-rate-limits',
		);
	}
}
