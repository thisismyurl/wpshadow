<?php
/**
 * Portfolio Image Copyright Protection Diagnostic
 *
 * Verifies portfolio sites protect creative work from unauthorized use.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6031.1445
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
 * Checks for watermark plugins and copyright protection measures.
 *
 * @since 1.6031.1445
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
protected static $title = 'Portfolio Image Copyright Protection';

/**
 * The diagnostic description
 *
 * @var string
 */
protected static $description = 'Verifies portfolio sites protect creative work from unauthorized use';

/**
 * The family this diagnostic belongs to
 *
 * @var string
 */
protected static $family = 'portfolio';

/**
 * Run the diagnostic check.
 *
 * @since  1.6031.1445
 * @return array|null Finding array if issue found, null otherwise.
 */
public static function check() {
		// Check if site uses portfolio/gallery functionality.
		$active_plugins = get_option( 'active_plugins', array() );
		$portfolio_plugins = array( 'portfolio', 'gallery', 'envira', 'nextgen', 'photo', 'image', 'lightbox' );
		$is_portfolio_site = false;

		foreach ( $active_plugins as $plugin ) {
			foreach ( $portfolio_plugins as $p_plugin ) {
				if ( stripos( $plugin, $p_plugin ) !== false ) {
					$is_portfolio_site = true;
					break 2;
				}
			}
		}

		// Check for custom post types indicating portfolio.
		if ( ! $is_portfolio_site ) {
			$post_types = get_post_types( array( 'public' => true ), 'names' );
			foreach ( $post_types as $post_type ) {
				if ( in_array( $post_type, array( 'portfolio', 'gallery', 'project', 'work' ), true ) ) {
					$is_portfolio_site = true;
					break;
				}
			}
		}

		if ( ! $is_portfolio_site ) {
			return null;
		}

		$issues = array();

		// Check for watermark plugins.
		$watermark_plugins = array( 'watermark', 'image-watermark', 'easy-watermark' );
		$has_watermark = false;

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

		// Check for right-click protection plugins.
		$protection_plugins = array( 'right-click', 'disable-right-click', 'no-right-click', 'image-protect' );
		$has_protection = false;

		foreach ( $active_plugins as $plugin ) {
			foreach ( $protection_plugins as $prot_plugin ) {
				if ( stripos( $plugin, $prot_plugin ) !== false ) {
					$has_protection = true;
					break 2;
				}
			}
		}

		if ( ! $has_protection ) {
			$issues[] = __( 'No image protection plugin detected', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( 'Copyright protection concerns: %s. Portfolio sites should protect creative work with watermarks and copy protection.', 'wpshadow' ),
				implode( ', ', $issues )
			),
			'severity'     => 'medium',
			'threat_level' => 50,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/image-copyright-protection',
		);
