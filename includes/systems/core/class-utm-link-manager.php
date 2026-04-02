<?php
/**
 * UTM Link Manager
 *
 * Minimal helper for appending UTM parameters to WPShadow knowledge base
 * and academy links referenced from diagnostic findings.
 *
 * @package    WPShadow
 * @subpackage Core
 */

namespace WPShadow\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * UTM_Link_Manager Class
 *
 * Provides static helpers to build KB and academy URLs with UTM tracking params.
 */
class UTM_Link_Manager {

	/** Base URL for knowledge-base articles */
	private const KB_BASE = 'https://wpshadow.com/kb/';

	/** Base URL for academy content */
	private const ACADEMY_BASE = 'https://wpshadow.com/academy/';

	/**
	 * Build a knowledge-base article URL with UTM params.
	 *
	 * @param string $slug   Article slug.
	 * @param string $source UTM source / context identifier.
	 * @return string Full URL with UTM parameters.
	 */
	public static function kb_link( string $slug, string $source = 'plugin' ): string {
		return add_query_arg(
			array(
				'utm_source'   => sanitize_key( $source ),
				'utm_medium'   => 'plugin',
				'utm_campaign' => 'kb',
			),
			self::KB_BASE . ltrim( $slug, '/' )
		);
	}

	/**
	 * Build an academy content URL with UTM params.
	 *
	 * @param string $slug   Content slug.
	 * @param string $source UTM source / context identifier.
	 * @return string Full URL with UTM parameters.
	 */
	public static function academy_link( string $slug, string $source = 'plugin' ): string {
		return add_query_arg(
			array(
				'utm_source'   => sanitize_key( $source ),
				'utm_medium'   => 'plugin',
				'utm_campaign' => 'academy',
			),
			self::ACADEMY_BASE . ltrim( $slug, '/' )
		);
	}
}

