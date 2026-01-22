<?php
declare(strict_types=1);
/**
 * WP_Query Meta SQL Injection Diagnostic
 *
 * Philosophy: Query security - prevent meta_query injection
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for SQL injection in WP_Query meta_query.
 */
class Diagnostic_WP_Query_Meta_Injection extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Scan active plugins for dangerous WP_Query patterns
		$active_plugins = get_option( 'active_plugins', array() );
		$vulnerable_plugins = array();
		
		foreach ( array_slice( $active_plugins, 0, 5 ) as $plugin ) {
			$plugin_file = WP_PLUGIN_DIR . '/' . $plugin;
			if ( file_exists( $plugin_file ) ) {
				$content = file_get_contents( $plugin_file );
				
				// Look for meta_query with $_GET/$_POST
				if ( preg_match( '/[\'"]meta_query[\'"]\s*=>\s*array\s*\(.*\$_(GET|POST|REQUEST)\[/s', $content ) ||
				     preg_match( '/meta_query.*\$_(GET|POST|REQUEST)\[/s', $content ) ) {
					$vulnerable_plugins[] = dirname( $plugin );
				}
			}
		}
		
		if ( ! empty( $vulnerable_plugins ) ) {
			return array(
				'id'          => 'wp-query-meta-injection',
				'title'       => 'WP_Query Meta Injection Risk',
				'description' => sprintf(
					'Plugins with unsafe WP_Query meta_query: %s. User-controlled meta_query parameters enable SQL injection. Sanitize meta_key with sanitize_key() and validate meta_value.',
					implode( ', ', $vulnerable_plugins )
				),
				'severity'    => 'critical',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/secure-wp-query/',
				'training_link' => 'https://wpshadow.com/training/wordpress-query-security/',
				'auto_fixable' => false,
				'threat_level' => 85,
			);
		}
		
		return null;
	}
}
