<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_WordPress_Default_Content extends Diagnostic_Base {


	protected static $slug        = 'test-wordpress-default-content';
	protected static $title       = 'Default Content Visible Test';
	protected static $description = 'Tests for default WordPress content in sidebar widgets.';

	public static function check( ?string $url = null, ?string $html = null ): ?array {
		$body = $html ?? self::fetch_html( $url ?? home_url( '/' ) );
		if ( $body === false ) {
			return null;
		}

		if ( ! self::has_sidebar( $body ) ) {
			return null;
		}

		$has_admin_comments = preg_match( '/Mr WordPress|Hello world!/i', $body );

		if ( $has_admin_comments ) {
			return array(
				'id'            => 'wordpress-default-content-visible',
				'title'         => 'Default WordPress Content Visible',
				'description'   => 'Default WordPress content ("Mr WordPress", "Hello world!") visible in sidebar widgets. Replace with real content.'
				'kb_link' => 'https://wpshadow.com/kb/default-content/',
				'training_link' => 'https://wpshadow.com/training/content-setup/',
				'auto_fixable'  => false,
				'threat_level'  => 25,
				'module'        => 'WordPress',
				'priority'      => 3,
				'meta'          => array( 'has_default_content' => true ),
			);
		}

		return null;
	}

	protected static function has_sidebar( string $html ): bool {
		return (bool) preg_match( '/<(?:aside|div)[^>]+(?:class|id)=["\'][^"\']*(?:sidebar|widget-area)[^"\']*["\']/i', $html );
	}

	protected static function fetch_html( string $url ) {
		$response = wp_remote_get(
			$url,
			array(
				'timeout'   => 10,
				'sslverify' => false,
			)
		);
		return is_wp_error( $response ) ? false : wp_remote_retrieve_body( $response );
	}

	public static function get_name(): string {
		return __( 'Default Sidebar Content', 'wpshadow' );
	}

	public static function get_description(): string {
		return __( 'Checks if default WordPress content is still present in sidebar widgets.', 'wpshadow' );
	}
}
