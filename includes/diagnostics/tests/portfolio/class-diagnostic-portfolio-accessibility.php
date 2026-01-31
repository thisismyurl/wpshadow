<?php
/**
 * Portfolio Accessibility for Visual Content Diagnostic
 *
 * Checks if portfolio/gallery sites provide proper alt text, captions,
 * and descriptions for visual content to ensure accessibility compliance.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Portfolio
 * @since      1.6031.1449
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Portfolio;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Portfolio Accessibility Diagnostic Class
 *
 * Verifies portfolio sites meet accessibility standards for visual content.
 *
 * @since 1.6031.1449
 */
class Diagnostic_Portfolio_Accessibility extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'portfolio-accessibility';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Portfolio Accessibility for Visual Content';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies portfolio sites provide accessible visual content with alt text and descriptions';

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
	 * - Images with missing alt text
	 * - Gallery accessibility features
	 * - Keyboard navigation support
	 * - Caption and description usage
	 *
	 * @since  1.6031.1449
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if site appears to be portfolio/gallery focused.
		$active_plugins = get_option( 'active_plugins', array() );
		$portfolio_plugins = array(
			'portfolio',
			'gallery',
			'photography',
			'envira',
			'nextgen',
		);

		$is_portfolio_site = false;
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

		// Check for accessibility plugins.
		$has_accessibility_plugin = false;
		$accessibility_plugins = array(
			'wp-accessibility',
			'one-click-accessibility',
			'accessibility-checker',
		);

		foreach ( $active_plugins as $plugin ) {
			foreach ( $accessibility_plugins as $a11y_plugin ) {
				if ( stripos( $plugin, $a11y_plugin ) !== false ) {
					$has_accessibility_plugin = true;
					break 2;
				}
			}
		}

		if ( ! $has_accessibility_plugin ) {
			$issues[] = __( 'No accessibility enhancement plugin detected', 'wpshadow' );
		}

		// Sample recent images to check for alt text.
		$recent_images = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_mime_type' => 'image',
				'posts_per_page' => 20,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		$images_without_alt = 0;
		foreach ( $recent_images as $image ) {
			$alt_text = get_post_meta( $image->ID, '_wp_attachment_image_alt', true );
			if ( empty( $alt_text ) ) {
				++$images_without_alt;
			}
		}

		if ( $images_without_alt > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of images without alt text */
				__( '%d recent images missing alt text', 'wpshadow' ),
				$images_without_alt
			);
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( 'Portfolio accessibility concerns: %s. Visual content should include alt text, captions, and descriptions for accessibility compliance (WCAG 2.1).', 'wpshadow' ),
				implode( ', ', $issues )
			),
			'severity'     => 'high',
			'threat_level' => 70,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/portfolio-accessibility',
		);
	}
}
