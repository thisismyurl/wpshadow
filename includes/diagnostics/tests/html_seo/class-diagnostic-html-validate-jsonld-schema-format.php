<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Diagnostic_Html_Validate_Jsonld_Schema_Format extends Diagnostic_Base {
	protected static $slug = 'html-validate-jsonld-schema-format';
	protected static $title = 'Invalid JSON-LD Format';
	protected static $description = 'Validates JSON-LD schema markup format';
	protected static $family = 'seo';
	public static function check() {
		if ( is_admin() ) { return null; }
		global $post;
		if ( empty( $post ) || ! ( $post instanceof \WP_Post ) ) { return null; }
		if ( preg_match_all( '/<script[^>]*type="application\/ld\+json"[^>]*>([^<]+)<\/script>/', $post->post_content, $matches ) ) {
			$invalid = 0;
			foreach ( $matches[1] as $schema ) {
				if ( ! @json_decode( $schema ) ) {
					$invalid++;
				}
			}
			if ( $invalid > 0 ) {
				return array(
					'id' => self::$slug,
					'title' => self::$title,
					'description' => sprintf( __( 'Found %d invalid JSON-LD block(s). Invalid JSON breaks rich results. Check syntax and escape quotes properly.', 'wpshadow' ), $invalid ),
					'severity' => 'high',
					'threat_level' => 30,
					'auto_fixable' => false,
					'kb_link' => 'https://wpshadow.com/kb/html-validate-jsonld-schema-format',
					'meta' => array( 'invalid' => $invalid ),
				);
			}
		}
		return null;
	}
}
