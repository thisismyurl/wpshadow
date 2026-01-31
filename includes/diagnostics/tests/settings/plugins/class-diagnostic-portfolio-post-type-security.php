<?php
/**
 * Portfolio Post Type Security Diagnostic
 *
 * Portfolio post type exposed publicly.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.496.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Portfolio Post Type Security Diagnostic Class
 *
 * @since 1.496.0000
 */
class Diagnostic_PortfolioPostTypeSecurity extends Diagnostic_Base {

	protected static $slug = 'portfolio-post-type-security';
	protected static $title = 'Portfolio Post Type Security';
	protected static $description = 'Portfolio post type exposed publicly';
	protected static $family = 'security';

	public static function check() {
		// Check if portfolio post type exists
		if ( ! post_type_exists( 'portfolio' ) && ! post_type_exists( 'jetpack-portfolio' ) ) {
			return null;
		}

		$issues = array();
		$threat_level = 0;

		$post_type = post_type_exists( 'portfolio' ) ? 'portfolio' : 'jetpack-portfolio';
		$post_type_obj = get_post_type_object( $post_type );

		// Check if publicly queryable
		if ( $post_type_obj && $post_type_obj->publicly_queryable ) {
			$issues[] = 'publicly_queryable';
			$threat_level += 15;
		}

		// Check REST API exposure
		if ( $post_type_obj && $post_type_obj->show_in_rest ) {
			$issues[] = 'rest_api_enabled';
			$threat_level += 20;
		}

		// Check archive visibility
		if ( $post_type_obj && $post_type_obj->has_archive ) {
			$issues[] = 'archive_publicly_accessible';
			$threat_level += 15;
		}

		// Check for password-protected portfolios
		global $wpdb;
		$password_protected = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} 
				 WHERE post_type = %s AND post_password != ''",
				$post_type
			)
		);
		$total_posts = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} 
				 WHERE post_type = %s AND post_status = %s",
				$post_type,
				'publish'
			)
		);
		if ( $password_protected === 0 && $total_posts > 0 ) {
			$issues[] = 'no_password_protection';
			$threat_level += 10;
		}

		// Check author enumeration
		if ( $post_type_obj && isset( $post_type_obj->rewrite['with_front'] ) && $post_type_obj->rewrite['with_front'] ) {
			$issues[] = 'author_enumeration_possible';
			$threat_level += 10;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of security issues */
				__( 'Portfolio post type has security exposures: %s. This can reveal private work, client information, or unreleased projects.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => $description,
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/portfolio-post-type-security',
			);
		}
		
		return null;
	}
}
