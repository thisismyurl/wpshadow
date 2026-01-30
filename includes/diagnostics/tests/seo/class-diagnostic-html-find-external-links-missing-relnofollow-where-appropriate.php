<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Diagnostic_Html_Find_External_Links_Missing_Relnofollow_Where_Appropriate extends Diagnostic_Base {
	protected static $slug = 'html-find-external-links-missing-relnofollow-where-appropriate';
	protected static $title = 'External Links Missing rel="nofollow"';
	protected static $description = 'Detects external links missing rel="nofollow" where appropriate';
	protected static $family = 'seo';
	public static function check() {
		if ( is_admin() ) { return null; }
		global $post;
		if ( empty( $post ) || ! ( $post instanceof \WP_Post ) ) { return null; }
		$content = $post->post_content;
		$site_url = home_url();
		$external_links = array();
		if ( preg_match_all( '/<a[^>]*href=["\']([^"\']+)["\'][^>]*>([^<]+)<\/a>/i', $content, $matches ) ) {
			foreach ( $matches[1] as $idx => $href ) {
				if ( strpos( $href, $site_url ) !== 0 && strpos( $href, '/' ) !== 0 && strpos( $href, '#' ) !== 0 ) {
					$full_tag = $matches[0][ $idx ];
					if ( ! preg_match( '/rel=["\']([^"\']*nofollow[^"\']*)["\']/', $full_tag ) ) {
						$external_links[] = array(
							'href' => substr( $href, 0, 60 ),
							'text' => trim( $matches[2][ $idx ] ),
						);
					}
				}
			}
		}
		if ( empty( $external_links ) || count( $external_links ) < 3 ) { return null; }
		$items_list = '';
		foreach ( array_slice( $external_links, 0, 5 ) as $item ) {
			$items_list .= sprintf( "\n- %s", esc_html( $item['href'] ) );
		}
		return array(
			'id' => self::$slug,
			'title' => self::$title,
			'description' => sprintf( __( 'Found %d external link(s) without rel="nofollow". For user-generated or low-trust external links, use rel="nofollow" to prevent passing SEO value to questionable sites.%s', 'wpshadow' ), count( $external_links ), $items_list ),
			'severity' => 'low',
			'threat_level' => 30,
			'auto_fixable' => false,
			'kb_link' => 'https://wpshadow.com/kb/html-find-external-links-missing-relnofollow-where-appropriate',
			'meta' => array( 'external_count' => count( $external_links ) ),
		);
	}
}
