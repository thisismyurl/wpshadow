<?php
/**
 * Multisite Plugin Conflicts Diagnostic
 *
 * Detects plugins that cause issues when network-activated, such as
 * breaking specific sites or causing performance degradation network-wide.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Multisite_Plugin_Conflicts Class
 *
 * Detects network plugin conflicts.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Multisite_Plugin_Conflicts extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'multisite-plugin-conflicts';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Multisite Plugin Conflicts';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects plugins causing multisite conflicts';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'multisite';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if conflicts found, null otherwise.
	 */
	public static function check() {
		// Only run on multisite
		if ( ! is_multisite() ) {
			return null;
		}

		$conflict_check = self::detect_plugin_conflicts();

		if ( empty( $conflict_check['problematic_plugins'] ) ) {
			return null; // No conflicts detected
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of problematic plugins */
				__( '%d network-activated plugins may cause conflicts. Network plugins affect ALL sites - one broken plugin = entire network down.', 'wpshadow' ),
				count( $conflict_check['problematic_plugins'] )
			),
			'severity'     => 'high',
			'threat_level' => 70,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/multisite-plugins',
			'family'       => self::$family,
			'meta'         => array(
				'network_plugins'       => $conflict_check['network_count'],
				'problematic_plugins'   => $conflict_check['problematic_plugins'],
			),
			'details'      => array(
				'network_vs_site_activation' => array(
					'Network-Activated' => array(
						'Active on ALL sites automatically',
						'Cannot be deactivated by site admins',
						'Network admin only can deactivate',
						'Use for: Essential functionality all sites need',
					),
					'Site-Activated' => array(
						'Site admins choose to activate',
						'Active on specific sites only',
						'More flexible, less risky',
						'Use for: Optional features',
					),
				),
				'plugins_unsuitable_for_network' => array(
					'Caching Plugins' => array(
						'Problem: May cache wrong site content',
						'Example: WP Super Cache (multisite mode needed)',
						'Fix: Use multisite-aware caching only',
					),
					'SEO Plugins' => array(
						'Problem: Site-specific SEO settings',
						'Example: Yoast SEO (ok but per-site config)',
						'Best: Allow site-level activation',
					),
					'Security Plugins' => array(
						'Problem: Network-wide lockouts',
						'Example: Wordfence blocks all sites if triggered',
						'Best: Test on single site first',
					),
					'Form Plugins' => array(
						'Problem: Form submissions go to network admin',
						'Example: Contact Form 7 (ok but check email routing)',
						'Best: Verify email delivery per site',
					),
				),
				'safe_network_activation'  => array(
					'Before Network Activating' => array(
						'Test on single staging site first',
						'Check plugin multisite compatibility',
						'Read plugin docs for multisite notes',
						'Have rollback plan ready',
					),
					'After Network Activating' => array(
						'Test 3-5 different sites immediately',
						'Monitor error logs for all sites',
						'Check site admin feedback',
						'Be ready to deactivate quickly',
					),
				),
				'deactivating_network_plugin' => array(
					'Via Network Admin' => array(
						'Network Admin → Plugins',
						'Click "Network Deactivate"',
						'Affects all sites immediately',
					),
					'Via WP-CLI (If Broken)' => array(
						'SSH into server',
						'wp plugin deactivate plugin-name --network',
						'Or: wp plugin deactivate --all --network',
					),
					'Via FTP (Emergency)' => array(
						'Rename: /wp-content/plugins/plugin-name',
						'To: /wp-content/plugins/plugin-name-disabled',
						'Deactivates immediately',
					),
				),
				'recommended_network_plugins' => array(
					'Multisite-Safe Plugins' => array(
						'Akismet (spam protection)',
						'Jetpack (multisite mode)',
						'WP Rocket (multisite support)',
						'MainWP (multisite management)',
						'ManageWP (multisite dashboard)',
					),
				),
			),
		);
	}

	/**
	 * Detect plugin conflicts.
	 *
	 * @since  1.2601.2148
	 * @return array Plugin conflict analysis.
	 */
	private static function detect_plugin_conflicts() {
		if ( ! is_multisite() ) {
			return array(
				'network_count'        => 0,
				'problematic_plugins' => array(),
			);
		}

		$network_plugins = (array) get_site_option( 'active_sitewide_plugins', array() );
		$network_count = count( $network_plugins );

		$problematic = array();

		// Check for commonly problematic plugins
		$problematic_slugs = array(
			'wp-super-cache/wp-cache.php'     => 'WP Super Cache (needs multisite mode)',
			'w3-total-cache/w3-total-cache.php' => 'W3 Total Cache (complex multisite setup)',
		);

		foreach ( $network_plugins as $plugin_file => $timestamp ) {
			if ( isset( $problematic_slugs[ $plugin_file ] ) ) {
				$problematic[] = $problematic_slugs[ $plugin_file ];
			}
		}

		// Flag if too many network plugins (performance concern)
		if ( $network_count > 15 ) {
			$problematic[] = sprintf(
				/* translators: %d: number of network plugins */
				__( '%d network plugins active - consider site-level activation', 'wpshadow' ),
				$network_count
			);
		}

		return array(
			'network_count'        => $network_count,
			'problematic_plugins'  => $problematic,
		);
	}
}
