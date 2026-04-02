<?php
/**
 * Third-Party Scripts Loaded from Unverified Sources Diagnostic
 *
 * Checks if external scripts are loaded from verified, secure sources.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Third-Party Scripts Verification Diagnostic
 *
 * Detects when external JavaScript files are loaded from unverified sources without
 * proper security validation. Loading scripts from untrusted sources is a critical
 * security vulnerability (XSS attack vector). All external scripts should be from
 * official, verified CDNs or your own servers.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Third_Party_Scripts_Loaded_From_Unverified_Sources extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'third-party-scripts-unverified-sources';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Third-Party Scripts Load from Verified Sources';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if external JavaScript is loaded from verified, secure sources only';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$unverified_scripts = self::check_unverified_scripts();

		if ( ! empty( $unverified_scripts ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %d: number of unverified scripts */
					__( 'Found %d external JavaScript files loaded from unverified sources. Unverified scripts are a critical security risk (XSS attacks). Every external script can steal user data, inject malware, or redirect visitors. Load scripts only from official CDNs (Cloudflare, jsDelivr, CDNjs) or your own server. Here\'s the list: [KB link]', 'wpshadow' ),
					count( $unverified_scripts )
				),
				'severity'    => 'high',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/third-party-script-security',
				'details'     => array(
					'unverified_count' => count( $unverified_scripts ),
					'scripts'          => array_slice( $unverified_scripts, 0, 5 ),
					'trusted_sources'  => self::get_trusted_sources(),
					'recommendation'   => __( 'Replace with official CDN versions or remove if not needed', 'wpshadow' ),
				),
			);
		}

		return null; // No issue found
	}

	/**
	 * Check for unverified external scripts
	 *
	 * @since 1.6093.1200
	 * @return array Array of unverified script sources
	 */
	private static function check_unverified_scripts(): array {
		$unverified = array();

		// Get all enqueued scripts
		global $wp_scripts;

		if ( ! $wp_scripts ) {
			return array();
		}

		$trusted_domains = self::get_trusted_domains();

		foreach ( $wp_scripts->queue as $handle ) {
			$script = $wp_scripts->registered[ $handle ];

			if ( ! isset( $script->src ) || empty( $script->src ) ) {
				continue;
			}

			$src = $script->src;

			// Skip local scripts
			if ( strpos( $src, site_url() ) === 0 || strpos( $src, home_url() ) === 0 ) {
				continue;
			}

			// Check if from trusted source
			$is_trusted = false;

			foreach ( $trusted_domains as $trusted ) {
				if ( stripos( $src, $trusted ) !== false ) {
					$is_trusted = true;
					break;
				}
			}

			if ( ! $is_trusted ) {
				$unverified[] = array(
					'handle' => $handle,
					'src'    => esc_url( $src ),
					'risk'   => 'Medium to High',
				);
			}
		}

		return $unverified;
	}

	/**
	 * Get list of trusted CDN domains
	 *
	 * @since 1.6093.1200
	 * @return array Array of trusted domain names
	 */
	private static function get_trusted_domains(): array {
		return array(
			'cdn.jsdelivr.net',
			'cdnjs.cloudflare.com',
			'unpkg.com',
			'code.jquery.com',
			'stackpath.bootstrapcdn.com',
			'cdn.jsdelivr.net',
			'fonts.googleapis.com',
			'fonts.gstatic.com',
			'apis.google.com',
			'platform.twitter.com',
			'connect.facebook.net',
		);
	}

	/**
	 * Get list of trusted CDN sources for replacement
	 *
	 * @since 1.6093.1200
	 * @return array Array of trusted source options
	 */
	private static function get_trusted_sources(): array {
		return array(
			array(
				'name'    => 'jsDelivr',
				'url'     => 'https://www.jsdelivr.com/',
				'example' => 'https://cdn.jsdelivr.net/npm/library@version/file.js',
			),
			array(
				'name'    => 'Cloudflare CDN',
				'url'     => 'https://cdnjs.com/',
				'example' => 'https://cdnjs.cloudflare.com/ajax/libs/library/version/file.js',
			),
			array(
				'name'    => 'Your Own Server',
				'url'     => 'https://wordpress.org/',
				'example' => home_url( '/wp-content/plugins/your-plugin/js/file.js' ),
			),
		);
	}
}
