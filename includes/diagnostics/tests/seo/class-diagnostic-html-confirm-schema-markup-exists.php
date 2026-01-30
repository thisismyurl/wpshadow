<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Diagnostic_Html_Confirm_Schema_Markup_Exists extends Diagnostic_Base {
	protected static $slug = 'html-confirm-schema-markup-exists';
	protected static $title = 'Missing Schema Markup';
	protected static $description = 'Confirms schema.org markup exists on page';
	protected static $family = 'seo';
	public static function check() {
		if ( is_admin() ) { return null; }
		global $post;
		if ( empty( $post ) || ! ( $post instanceof \WP_Post ) ) { return null; }
		$has_schema = preg_match( '/(application\/ld\+json|schema\.org|@context)/', $post->post_content );
		if ( ! $has_schema ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'No schema.org markup detected on this page. Schema helps search engines understand content and display rich results. Add JSON-LD schema for this content type.', 'wpshadow' ),
				'severity' => 'medium',
				'threat_level' => 30,
				'auto_fixable' => true,
				'kb_link' => 'https://wpshadow.com/kb/html-confirm-schema-markup-exists',
				'meta' => array(),
			);
		}
		return null;
	}
}
