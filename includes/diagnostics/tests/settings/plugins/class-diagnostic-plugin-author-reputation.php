<?php
/**
 * Plugin Author Reputation Diagnostic
 *
 * Validates plugin author credentials and development history.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5030.1045
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Author Reputation Class
 *
 * Checks plugin authors against WordPress.org reputation data.
 *
 * @since 1.5030.1045
 */
class Diagnostic_Plugin_Author_Reputation extends Diagnostic_Base {

	protected static $slug        = 'plugin-author-reputation';
	protected static $title       = 'Plugin Author Reputation';
	protected static $description = 'Validates plugin author credentials';
	protected static $family      = 'plugins';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.5030.1045
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$cache_key = 'wpshadow_plugin_author_reputation';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all_plugins      = get_plugins();
		$active_plugins   = get_option( 'active_plugins', array() );
		$reputation_risks = array();

		foreach ( $all_plugins as $plugin_path => $plugin_data ) {
			if ( ! in_array( $plugin_path, $active_plugins, true ) ) {
				continue;
			}

			$slug        = dirname( $plugin_path );
			$author      = $plugin_data['Author'] ?? '';
			$author_uri  = $plugin_data['AuthorURI'] ?? '';
			$plugin_uri  = $plugin_data['PluginURI'] ?? '';
			$risk_flags  = array();

			// Check if author info is missing.
			if ( empty( $author ) ) {
				$risk_flags[] = 'No author information provided';
			}

			// Check if author URI is missing or invalid.
			if ( empty( $author_uri ) || ! filter_var( $author_uri, FILTER_VALIDATE_URL ) ) {
				$risk_flags[] = 'No valid author website';
			}

			// Query WordPress.org API for plugin info.
			$api_url  = "https://api.wordpress.org/plugins/info/1.0/{$slug}.json";
			$response = wp_remote_get( $api_url, array( 'timeout' => 5 ) );

			if ( ! is_wp_error( $response ) ) {
				$body = wp_remote_retrieve_body( $response );
				$data = json_decode( $body, true );

				if ( ! empty( $data ) ) {
					// Check ratings.
					$rating      = $data['rating'] ?? 0;
					$num_ratings = $data['num_ratings'] ?? 0;

					if ( $num_ratings < 5 ) {
						$risk_flags[] = 'Very few user ratings (less than 5)';
					}

					if ( $rating < 60 && $num_ratings > 0 ) {
						$risk_flags[] = sprintf( 'Low rating (%.1f%%)', $rating );
					}

					// Check author activity.
					$last_updated = isset( $data['last_updated'] ) ? strtotime( $data['last_updated'] ) : 0;
					if ( $last_updated && ( time() - $last_updated ) > ( 2 * YEAR_IN_SECONDS ) ) {
						$risk_flags[] = 'Not updated in over 2 years';
					}

					// Check support forum response rate.
					if ( isset( $data['support_threads'] ) && isset( $data['support_threads_resolved'] ) ) {
						$threads  = (int) $data['support_threads'];
						$resolved = (int) $data['support_threads_resolved'];
						
						if ( $threads > 10 && $resolved < ( $threads * 0.3 ) ) {
							$risk_flags[] = 'Low support forum response rate';
						}
					}
				}
			} else {
				// Plugin not on WordPress.org.
				$risk_flags[] = 'Plugin not from WordPress.org repository';
			}

			if ( ! empty( $risk_flags ) ) {
				$reputation_risks[] = array(
					'name'       => $plugin_data['Name'],
					'slug'       => $slug,
					'author'     => $author,
					'risk_flags' => $risk_flags,
				);
			}

			// Limit to 15 plugins to avoid long execution times.
			if ( count( $reputation_risks ) >= 15 ) {
				break;
			}
		}

		if ( ! empty( $reputation_risks ) ) {
			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of plugins */
					__( '%d active plugins have author reputation concerns. Review and consider alternatives.', 'wpshadow' ),
					count( $reputation_risks )
				),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/plugins-author-reputation',
				'data'         => array(
					'plugins_with_risks' => $reputation_risks,
					'total_at_risk'      => count( $reputation_risks ),
				),
			);

			set_transient( $cache_key, $result, 7 * DAY_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 7 * DAY_IN_SECONDS );
		return null;
	}
}
