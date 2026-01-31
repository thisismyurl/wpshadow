<?php
/**
 * Portfolio Accessibility Standards Diagnostic
 *
 * Verifies portfolio sites meet accessibility requirements
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
 * Diagnostic_PortfolioAccessibility Class
 *
 * Checks for alt text, accessibility plugins, WCAG compliance
 *
 * @since 1.6031.1445
 */
class Diagnostic_PortfolioAccessibility extends Diagnostic_Base {

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
protected static $title = 'Portfolio Accessibility Standards';

/**
 * The diagnostic description
 *
 * @var string
 */
protected static $description = 'Verifies portfolio sites meet accessibility requirements';

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
		$post_types = get_post_types( array( 'public' => true ), 'names' );
		$has_portfolio = false;

		foreach ( $post_types as $post_type ) {
			if ( in_array( $post_type, array( 'portfolio', 'gallery', 'project', 'work' ), true ) ) {
				$has_portfolio = true;
				break;
			}
		}

		if ( ! $has_portfolio ) {
			$active_plugins = get_option( 'active_plugins', array() );
			$portfolio_plugins = array( 'portfolio', 'gallery', 'envira', 'nextgen' );

			foreach ( $active_plugins as $plugin ) {
				foreach ( $portfolio_plugins as $p_plugin ) {
					if ( stripos( $plugin, $p_plugin ) !== false ) {
						$has_portfolio = true;
						break 2;
					}
				}
			}
		}

		if ( ! $has_portfolio ) {
			return null;
		}

		$issues = array();

		// Check for images without alt text (sample recent portfolio items).
		$args = array(
			'post_type'      => array( 'portfolio', 'project', 'gallery', 'post' ),
			'posts_per_page' => 10,
			'post_status'    => 'publish',
		);

		$query = new \WP_Query( $args );
		$images_without_alt = 0;

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$thumbnail_id = get_post_thumbnail_id();
				if ( $thumbnail_id ) {
					$alt_text = get_post_meta( $thumbnail_id, '_wp_attachment_image_alt', true );
					if ( empty( $alt_text ) ) {
						$images_without_alt++;
					}
				}
			}
			wp_reset_postdata();
		}

		if ( $images_without_alt > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of images */
				__( '%d portfolio images missing alt text', 'wpshadow' ),
				$images_without_alt
			);
		}

		// Check for accessibility plugins.
		$active_plugins = get_option( 'active_plugins', array() );
		$a11y_plugins = array( 'accessibility', 'wp-accessibility', 'one-click-accessibility' );
		$has_a11y = false;

		foreach ( $active_plugins as $plugin ) {
			foreach ( $a11y_plugins as $a11y_plugin ) {
				if ( stripos( $plugin, $a11y_plugin ) !== false ) {
					$has_a11y = true;
					break 2;
				}
			}
		}

		if ( ! $has_a11y && $images_without_alt > 3 ) {
			$issues[] = __( 'No accessibility enhancement plugin detected', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( 'Portfolio accessibility concerns: %s. Ensure all portfolio images have descriptive alt text for screen readers.', 'wpshadow' ),
				implode( ', ', $issues )
			),
			'severity'     => 'medium',
			'threat_level' => 55,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/portfolio-accessibility',
		);
