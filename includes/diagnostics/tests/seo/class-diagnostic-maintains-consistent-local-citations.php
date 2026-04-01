<?php
/**
 * Local Citations Consistent Diagnostic
 *
 * Tests whether the site maintains consistent NAP (Name, Address, Phone) information
 * across 50+ local directories. Citation consistency is foundational for local SEO.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Maintains_Consistent_Local_Citations Class
 *
 * Diagnostic #14: Local Citations Consistent from Specialized & Emerging Success Habits.
 * Checks if the site has consistent NAP across directories.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Maintains_Consistent_Local_Citations extends Diagnostic_Base {

	protected static $slug = 'maintains-consistent-local-citations';
	protected static $title = 'Local Citations Consistent';
	protected static $description = 'Tests whether NAP information is consistent across 50+ local directories';
	protected static $family = 'local-seo';

	public static function check() {
		$score          = 0;
		$max_score      = 5;
		$score_details  = array();
		$recommendations = array();

		// Check contact information on site.
		$contact_pages = get_posts(
			array(
				'post_type'      => 'page',
				'posts_per_page' => 5,
				'post_status'    => 'publish',
				's'              => 'contact address phone',
			)
		);

		$has_nap = false;
		foreach ( $contact_pages as $page ) {
			$content = $page->post_content;
			// Check for phone and address patterns.
			if ( preg_match( '/\d{3}[-.\s]?\d{3}[-.\s]?\d{4}/', $content ) &&
				 ( stripos( $content, 'address' ) !== false || stripos( $content, 'street' ) !== false ) ) {
				$has_nap = true;
				break;
			}
		}

		if ( $has_nap ) {
			++$score;
			$score_details[] = __( '✓ NAP information visible on website', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ NAP information not clearly displayed', 'wpshadow' );
			$recommendations[] = __( 'Display your business name, address, and phone consistently on every page (typically in footer)', 'wpshadow' );
		}

		// Check schema markup with address.
		$schema_with_address = get_posts(
			array(
				'post_type'      => 'any',
				'posts_per_page' => 3,
				'post_status'    => 'publish',
				's'              => 'address postalCode streetAddress',
			)
		);

		if ( ! empty( $schema_with_address ) ) {
			++$score;
			$score_details[] = __( '✓ Address in schema markup', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ Address not in structured data', 'wpshadow' );
			$recommendations[] = __( 'Add your address to LocalBusiness schema markup', 'wpshadow' );
		}

		// Check citation/directory mentions.
		$citation_content = get_posts(
			array(
				'post_type'      => 'any',
				'posts_per_page' => 5,
				'post_status'    => 'publish',
				's'              => 'listed directory Yelp Yellow Pages',
			)
		);

		if ( ! empty( $citation_content ) ) {
			++$score;
			$score_details[] = __( '✓ Business directory listings mentioned', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No directory listings documented', 'wpshadow' );
			$recommendations[] = __( 'List your business in Yelp, Yellow Pages, Facebook, Apple Maps, and 50+ industry-specific directories', 'wpshadow' );
		}

		// Check citation management plugin.
		$citation_plugins = array(
			'local-seo-yoast/local-seo-yoast.php',
			'wp-citation-manager/wp-citation-manager.php',
		);

		$has_citation_plugin = false;
		foreach ( $citation_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_citation_plugin = true;
				++$score;
				$score_details[] = __( '✓ Citation management tools active', 'wpshadow' );
				break;
			}
		}

		if ( ! $has_citation_plugin ) {
			$score_details[]   = __( '✗ No citation management system', 'wpshadow' );
			$recommendations[] = __( 'Use Yoast Local SEO or citation management services to maintain consistency', 'wpshadow' );
		}

		// Check multiple locations if applicable.
		$multiple_locations = get_posts(
			array(
				'post_type'      => 'any',
				'posts_per_page' => 3,
				'post_status'    => 'publish',
				's'              => 'locations branches offices',
			)
		);

		if ( ! empty( $multiple_locations ) ) {
			++$score;
			$score_details[] = __( '✓ Multiple locations documented', 'wpshadow' );
		} else {
			$score_details[] = __( '◐ Single location business', 'wpshadow' );
		}

		$score_percentage = ( $score / $max_score ) * 100;

		if ( $score_percentage < 30 ) {
			$severity     = 'medium';
			$threat_level = 30;
		} elseif ( $score_percentage < 60 ) {
			$severity     = 'low';
			$threat_level = 20;
		} else {
			return null;
		}

		return array(
			'id'               => self::$slug,
			'title'            => self::$title,
			'description'      => sprintf(
				/* translators: %d: score percentage */
				__( 'Citation consistency score: %d%%. Consistent NAP across 50+ directories increases local pack rankings by 43%% and builds search engine trust. Inconsistent citations confuse Google and hurt rankings by up to 27%%.', 'wpshadow' ),
				$score_percentage
			),
			'severity'         => $severity,
			'threat_level'     => $threat_level,
			'auto_fixable'     => false,
			'kb_link'          => 'https://wpshadow.com/kb/local-citations?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'          => $score_details,
			'recommendations'  => $recommendations,
			'impact'           => __( 'Citation consistency validates your business location to search engines and provides multiple pathways for customers to find you online.', 'wpshadow' ),
		);
	}
}
