<?php
/**
 * HTML Detect Missing Manifest File Link Diagnostic
 *
 * Detects missing manifest file link for PWA support.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\HTML
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * HTML Detect Missing Manifest File Link Diagnostic Class
 *
 * Identifies pages missing manifest file link for Progressive Web App
 * (PWA) and mobile installation support.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Html_Detect_Missing_Manifest_File_Link extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'html-detect-missing-manifest-file-link';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Missing Web App Manifest';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects missing manifest.json link for PWA support';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'mobile';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( is_admin() ) {
			return null;
		}

		$manifest_found = false;

		// Check scripts for manifest link.
		global $wp_scripts;

		if ( ! empty( $wp_scripts ) && isset( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script_obj ) {
				if ( isset( $script_obj->extra['data'] ) ) {
					$data = (string) $script_obj->extra['data'];

					// Check for manifest link tag.
					if ( preg_match( '/<link[^>]*rel=["\']manifest["\'][^>]*>/i', $data ) ) {
						$manifest_found = true;
						break;
					}
				}
			}
		}

		// Missing manifest is optional—it enhances PWA experience but isn't critical.
		if ( ! $manifest_found ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: */
					__( 'No web app manifest link detected. A manifest.json file enables Progressive Web App (PWA) features: users can install your site as an app, customize homescreen icon, and set app name. Recommended for mobile-first sites. Add: <link rel="manifest" href="/manifest.json">', 'wpshadow' )
				),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/html-detect-missing-manifest-file-link',
				'meta'         => array(
					'optional'      => true,
					'benefits'      => array(
						'installable_app',
						'custom_branding',
						'offline_support',
					),
				),
			);
		}

		return null;
	}
}
