<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_SEO_RSS_Feed extends Diagnostic_Base {


	protected static $slug        = 'test-seo-rss-feed';
	protected static $title       = 'RSS Feed Test';
	protected static $description = 'Tests for RSS feed accessibility and validity';

	public static function check( ?string $url = null, ?string $html = null ): ?array {
		$feed_url = home_url( '/feed/' );
		$response = wp_remote_get(
			$feed_url,
			array(
				'timeout'   => 10,
				'sslverify' => false,
			)
		);

		if ( is_wp_error( $response ) ) {
			return array(
				'id'            => 'seo-rss-feed-error',
				'title'         => 'RSS Feed Not Accessible',
				'description'   => 'RSS feed at /feed/ cannot be accessed. RSS feeds help with content syndication and discovery.'
				'kb_link' => 'https://wpshadow.com/kb/rss-feed/',
				'training_link' => 'https://wpshadow.com/training/content-distribution/',
				'auto_fixable'  => false,
				'threat_level'  => 25,
				'module'        => 'SEO',
				'priority'      => 4,
				'meta'          => array( 'checked_url' => $feed_url ),
			);
		}

		$content = wp_remote_retrieve_body( $response );

		// Check for valid RSS/Atom
		$is_valid = preg_match( '/<rss|<feed[^>]*xmlns=["\']http:\/\/www\.w3\.org\/2005\/Atom/i', $content );

		if ( ! $is_valid ) {
			return array(
				'id'            => 'seo-rss-feed-invalid',
				'title'         => 'RSS Feed Invalid',
				'description'   => 'RSS feed exists but doesn\'t appear to be valid RSS/Atom XML. This may prevent content syndication.'
				'kb_link' => 'https://wpshadow.com/kb/rss-feed-errors/',
				'training_link' => 'https://wpshadow.com/training/content-distribution/',
				'auto_fixable'  => false,
				'threat_level'  => 30,
				'module'        => 'SEO',
				'priority'      => 3,
				'meta'          => array(
					'is_valid'    => false,
					'checked_url' => $feed_url,
				),
			);
		}

		return null;
	}

	public static function get_name(): string {
		return __( 'RSS Feed', 'wpshadow' );
	}

	public static function get_description(): string {
		return __( 'Checks for RSS feed accessibility and validity.', 'wpshadow' );
	}
}
