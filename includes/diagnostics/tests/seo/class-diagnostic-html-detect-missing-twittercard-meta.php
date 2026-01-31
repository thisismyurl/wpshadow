<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Diagnostic_Html_Detect_Missing_Twittercard_Meta extends Diagnostic_Base {
	protected static $slug = 'html-detect-missing-twittercard-meta';
	protected static $title = 'Missing Twitter Card Meta';
	protected static $description = 'Detects missing twitter:card meta tags';
	protected static $family = 'seo';
	public static function check() {
		if ( is_admin() ) { return null; }
		global $post;
		if ( empty( $post ) || ! ( $post instanceof \WP_Post ) ) { return null; }
		$required_metas = array( 'twitter:card', 'twitter:title', 'twitter:description' );
		$missing = array();
		foreach ( $required_metas as $meta ) {
			if ( ! preg_match( '/<meta[^>]*name="' . $meta . '"/', $post->post_content ) ) {
				$missing[] = $meta;
			}
		}
		if ( count( $missing ) < 2 ) 
		// SEO validation checks
		if ( ! function_exists( 'wp_get_document_title' ) ) {
			$issues[] = __( 'Document title function unavailable', 'wpshadow' );
		}
		if ( get_option( 'blog_public' ) === '0' ) {
			$issues[] = __( 'Site set to private in search engines', 'wpshadow' );
		}
		// Check meta robots
		if ( ! function_exists( 'wp_robots' ) ) {
			$issues[] = __( 'Robots meta tag function unavailable', 'wpshadow' );
		}
{ return null; }
		return array(
			'id' => self::$slug,
			'title' => self::$title,
			'description' => sprintf( __( 'Missing Twitter Card meta tags: %s. Twitter Cards display rich previews when shared. Add: twitter:card, twitter:title, twitter:description.', 'wpshadow' ), implode( ', ', $missing ) ),
			'severity' => 'low',
			'threat_level' => 30,
			'auto_fixable' => true,
			'kb_link' => 'https://wpshadow.com/kb/html-detect-missing-twittercard-meta',
			'meta' => array( 'missing' => $missing ),
		);
	}
}
