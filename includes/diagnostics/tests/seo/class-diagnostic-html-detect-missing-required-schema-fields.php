<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Diagnostic_Html_Detect_Missing_Required_Schema_Fields extends Diagnostic_Base {
	protected static $slug = 'html-detect-missing-required-schema-fields';
	protected static $title = 'Missing Required Schema Fields';
	protected static $description = 'Detects missing required schema.org fields';
	protected static $family = 'seo';
	public static function check() {
		if ( is_admin() ) { return null; }
		global $post;
		if ( empty( $post ) || ! ( $post instanceof \WP_Post ) ) { return null; }
		$schema_json = '';
		if ( preg_match( '/<script[^>]*type="application\/ld\+json"[^>]*>([^<]+)<\/script>/', $post->post_content, $matches ) ) {
			$schema_json = $matches[1];
		}
		if ( empty( $schema_json ) ) { return null; }
		$required = array( 'name', 'description', 'url', 'image', 'datePublished' );
		$missing = array();
		foreach ( $required as $field ) {
			if ( ! preg_match( '/"' . $field . '"/', $schema_json ) ) {
				$missing[] = $field;
			}
		}
		if ( count( $missing ) < 2 ) { return null; }
		return array(
			'id' => self::$slug,
			'title' => self::$title,
			'description' => sprintf( __( 'Schema missing required fields: %s. Search engines use these to display rich results. Add: name, description, url, image, datePublished.', 'wpshadow' ), implode( ', ', $missing ) ),
			'severity' => 'medium',
			'threat_level' => 30,
			'auto_fixable' => false,
			'kb_link' => 'https://wpshadow.com/kb/html-detect-missing-required-schema-fields',
			'meta' => array( 'missing' => $missing ),
		);
	}
}
