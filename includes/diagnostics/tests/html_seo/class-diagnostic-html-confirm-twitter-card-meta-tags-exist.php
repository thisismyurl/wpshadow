<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Diagnostic_Html_Confirm_Twitter_Card_Meta_Tags_Exist extends Diagnostic_Base {
	protected static $slug = 'html-confirm-twitter-card-meta-tags-exist';
	protected static $title = 'Twitter Card Meta Tags Missing';
	protected static $description = 'Confirms Twitter Card meta tags exist';
	protected static $family = 'seo';
	public static function check() {
		if ( is_admin() ) { return null; }
		global $post;
		if ( empty( $post ) || ! ( $post instanceof \WP_Post ) ) { return null; }
		$has_twitter_card = preg_match( '/<meta[^>]*name="twitter:card"/', $post->post_content );
		if ( ! $has_twitter_card ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'No Twitter Card meta tags detected. Add twitter:card, twitter:title, and twitter:description for rich previews when content is shared on Twitter/X.', 'wpshadow' ),
				'severity' => 'low',
				'threat_level' => 30,
				'auto_fixable' => true,
				'kb_link' => 'https://wpshadow.com/kb/html-confirm-twitter-card-meta-tags-exist',
				'meta' => array(),
			);
		}
		return null;
	}
}
