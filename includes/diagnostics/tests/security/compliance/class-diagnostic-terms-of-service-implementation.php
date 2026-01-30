<?php
/**
 * Terms of Service Implementation Diagnostic
 *
 * Validates terms of service are present and properly implemented.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26028.1905
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Terms of Service Implementation Class
 *
 * Tests whether terms of service are properly implemented.
 *
 * @since 1.26028.1905
 */
class Diagnostic_Terms_Of_Service_Implementation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'terms-of-service-implementation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Terms of Service Implementation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates terms of service are present and properly implemented';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'compliance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26028.1905
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Find terms of service page.
		$tos_page = self::find_tos_page();
		
		if ( ! $tos_page ) {
			$issues[] = __( 'No terms of service page found', 'wpshadow' );
		} else {
			// Check if page is published.
			if ( 'publish' !== $tos_page->post_status ) {
				$issues[] = __( 'Terms of service page exists but is not published', 'wpshadow' );
			}

			// Check if content is generic template.
			if ( self::is_generic_template( $tos_page->post_content ) ) {
				$issues[] = __( 'Terms of service appears to be generic template (not customized)', 'wpshadow' );
			}

			// Check if terms are dated.
			if ( ! self::has_date_or_version( $tos_page->post_content ) ) {
				$issues[] = __( 'Terms of service not dated or versioned', 'wpshadow' );
			}

			// Check if terms are outdated.
			if ( self::are_terms_outdated( $tos_page ) ) {
				$issues[] = __( 'Terms of service not updated in 5+ years', 'wpshadow' );
			}

			// Check if terms cover e-commerce.
			if ( class_exists( 'WooCommerce' ) && ! self::terms_cover_ecommerce( $tos_page->post_content ) ) {
				$issues[] = __( 'WooCommerce active but terms don\'t cover refunds/returns/subscriptions', 'wpshadow' );
			}
		}

		// Check for acceptance checkbox on registration.
		if ( get_option( 'users_can_register' ) && ! self::has_registration_tos_checkbox() ) {
			$issues[] = __( 'User registration enabled but no terms acceptance checkbox', 'wpshadow' );
		}

		// Check for acceptance checkbox on WooCommerce checkout.
		if ( class_exists( 'WooCommerce' ) && ! self::has_checkout_tos_checkbox() ) {
			$issues[] = __( 'WooCommerce active but no terms acceptance at checkout', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $issues ),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/terms-of-service-implementation',
				'meta'         => array(
					'tos_page_id'  => $tos_page ? $tos_page->ID : 0,
					'issues_found' => count( $issues ),
					'issues'       => $issues,
				),
			);
		}

		return null;
	}

	/**
	 * Find terms of service page.
	 *
	 * @since  1.26028.1905
	 * @return \WP_Post|false Post object or false if not found.
	 */
	private static function find_tos_page() {
		// Search for common TOS page names.
		$common_slugs = array( 'terms-of-service', 'terms', 'tos', 'terms-and-conditions', 'terms-conditions' );
		
		foreach ( $common_slugs as $slug ) {
			$page = get_page_by_path( $slug );
			if ( $page ) {
				return $page;
			}
		}

		// Search by title.
		$pages = get_posts(
			array(
				'post_type'      => 'page',
				'posts_per_page' => 1,
				's'              => 'terms of service',
				'post_status'    => 'any',
			)
		);

		return ! empty( $pages ) ? $pages[0] : false;
	}

	/**
	 * Check if content is generic template.
	 *
	 * @since  1.26028.1905
	 * @param  string $content Page content.
	 * @return bool True if content appears to be generic template.
	 */
	private static function is_generic_template( $content ) {
		$template_phrases = array(
			'[your company name]',
			'[company name]',
			'[your name]',
			'[website name]',
			'[insert',
			'[replace',
			'example.com',
			'yoursite.com',
			'your-website.com',
		);

		$content_lower = strtolower( $content );

		foreach ( $template_phrases as $phrase ) {
			if ( false !== strpos( $content_lower, $phrase ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if terms have a date or version number.
	 *
	 * @since  1.26028.1905
	 * @param  string $content Page content.
	 * @return bool True if date or version found.
	 */
	private static function has_date_or_version( $content ) {
		// Look for common date patterns.
		$patterns = array(
			'/effective date/i',
			'/last updated/i',
			'/version \d+/i',
			'/\d{4}-\d{2}-\d{2}/', // ISO date.
			'/\d{1,2}\/\d{1,2}\/\d{4}/', // US date.
		);

		foreach ( $patterns as $pattern ) {
			if ( preg_match( $pattern, $content ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if terms are outdated (5+ years).
	 *
	 * @since  1.26028.1905
	 * @param  \WP_Post $page Terms page object.
	 * @return bool True if terms are outdated.
	 */
	private static function are_terms_outdated( $page ) {
		$last_modified = strtotime( $page->post_modified );
		$five_years_ago = strtotime( '-5 years' );

		return $last_modified < $five_years_ago;
	}

	/**
	 * Check if terms cover e-commerce topics.
	 *
	 * @since  1.26028.1905
	 * @param  string $content Page content.
	 * @return bool True if e-commerce topics are covered.
	 */
	private static function terms_cover_ecommerce( $content ) {
		$ecommerce_terms = array( 'refund', 'return', 'subscription', 'payment', 'shipping', 'order' );
		$content_lower = strtolower( $content );

		$found_count = 0;
		foreach ( $ecommerce_terms as $term ) {
			if ( false !== strpos( $content_lower, $term ) ) {
				++$found_count;
			}
		}

		// Require at least 3 e-commerce terms.
		return $found_count >= 3;
	}

	/**
	 * Check if registration form has terms acceptance checkbox.
	 *
	 * @since  1.26028.1905
	 * @return bool True if checkbox exists.
	 */
	private static function has_registration_tos_checkbox() {
		// Check if there's a hook for terms acceptance.
		return has_filter( 'register_form' ) !== false;
	}

	/**
	 * Check if WooCommerce checkout has terms acceptance checkbox.
	 *
	 * @since  1.26028.1905
	 * @return bool True if checkbox exists.
	 */
	private static function has_checkout_tos_checkbox() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return true;
		}

		// WooCommerce has built-in terms checkbox.
		$terms_page_id = wc_get_page_id( 'terms' );
		return $terms_page_id > 0;
	}
}
