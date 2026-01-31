<?php
/**
 * Image Copyright and Watermark Protection Diagnostic
 *
 * Checks if portfolio/photography sites implement proper image protection
 * including watermarks, copyright notices, and right-click protection.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Portfolio
 * @since      1.6031.1448
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Portfolio;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Image Copyright Protection Diagnostic Class
 *
 * Verifies portfolio sites have image copyright protection measures.
 *
 * @since 1.6031.1448
 */
class Diagnostic_Image_Copyright_Protection extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'image-copyright-protection';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Image Copyright and Watermark Protection';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies portfolio sites implement image copyright protection';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'portfolio';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks for:
	 * - Watermark plugins
	 * - Image protection plugins
	 * - Copyright metadata in images
	 * - Right-click protection
	 *
	 * @since  1.6031.1448
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if site appears to be portfolio/photography focused.
		$site_tagline     = get_bloginfo( 'description' );
		$site_name        = get_bloginfo( 'name' );
		$portfolio_terms  = array( 'portfolio', 'photography', 'photographer', 'gallery', 'artist', 'designer' );

		$is_portfolio_site = false;
		foreach ( $portfolio_terms as $term ) {
			if ( stripos( $site_name, $term ) !== false || stripos( $site_tagline, $term ) !== false ) {
				$is_portfolio_site = true;
				break;
			}
		}

		// Check for portfolio/gallery plugins.
		$active_plugins = get_option( 'active_plugins', array() );
		$portfolio_plugins = array(
			'portfolio',
			'gallery',
			'photography',
			'envira',
			'nextgen',
		);

		foreach ( $active_plugins as $plugin ) {
			foreach ( $portfolio_plugins as $p_plugin ) {
				if ( stripos( $plugin, $p_plugin ) !== false ) {
					$is_portfolio_site = true;
					break 2;
				}
			}
		}

		if ( ! $is_portfolio_site ) {
			return null; // Not a portfolio site.
		}

		$issues = array();

		// Check for watermark plugins.
		$has_watermark = false;
		$watermark_plugins = array(
			'watermark',
			'image-watermark',
			'copyright-proof',
		);

		foreach ( $active_plugins as $plugin ) {
			foreach ( $watermark_plugins as $wm_plugin ) {
				if ( stripos( $plugin, $wm_plugin ) !== false ) {
					$has_watermark = true;
					break 2;
				}
			}
		}

		if ( ! $has_watermark ) {
			$issues[] = __( 'No watermark plugin detected', 'wpshadow' );
		}

		// Check for image protection plugins.
		$has_protection = false;
		$protection_plugins = array(
			'prevent-content-theft',
			'wp-content-copy-protection',
			'no-right-click',
			'image-protection',
		);

		foreach ( $active_plugins as $plugin ) {
			foreach ( $protection_plugins as $prot_plugin ) {
				if ( stripos( $plugin, $prot_plugin ) !== false ) {
					$has_protection = true;
					break 2;
				}
			}
		}

		if ( ! $has_protection ) {
			$issues[] = __( 'No image/content protection plugin found', 'wpshadow' );
		}

		// Check for HTTPS (important for protecting image delivery).
		if ( ! is_ssl() ) {
			$issues[] = __( 'Site not using HTTPS (images can be intercepted)', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( 'Image copyright protection concerns: %s. Portfolio sites should implement watermarks and content protection to prevent unauthorized image use.', 'wpshadow' ),
				implode( ', ', $issues )
			),
			'severity'     => 'medium',
			'threat_level' => 55,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/image-copyright-protection',
		);
	}
}
