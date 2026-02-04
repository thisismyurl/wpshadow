<?php
/**
 * Mobile Blockquote Styling Diagnostic
 *
 * Tests if blockquotes are styled clearly on mobile.
 *
 * @since   1.6050.0000
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Blockquote Styling Diagnostic Class
 *
 * Checks for blockquote styling classes on the homepage.
 *
 * @since 1.6050.0000
 */
class Diagnostic_Mobile_Blockquote_Styling extends Diagnostic_Base {

	protected static $slug = 'mobile-blockquote-styling';
	protected static $title = 'Mobile Blockquote Styling';
	protected static $description = 'Tests if blockquotes are styled clearly on mobile';
	protected static $family = 'design';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6050.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$response = wp_remote_get( home_url( '/' ) );
		if ( is_wp_error( $response ) ) {
			return null;
		}

		$body = wp_remote_retrieve_body( $response );
		if ( empty( $body ) ) {
			return null;
		}

		libxml_use_internal_errors( true );
		$dom = new \DOMDocument();
		$dom->loadHTML( $body );
		libxml_clear_errors();

		$quotes = $dom->getElementsByTagName( 'blockquote' );
		if ( $quotes->length < 1 ) {
			return null;
		}

		$styled = false;
		foreach ( $quotes as $quote ) {
			$class = $quote->getAttribute( 'class' );
			if ( strpos( $class, 'wp-block-quote' ) !== false ) {
				$styled = true;
				break;
			}
		}

		if ( ! $styled ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Blockquotes found without block styling. Add blockquote styles so quotes remain readable on mobile.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/mobile-blockquote-styling',
				'persona'      => 'publisher',
			);
		}

		return null;
	}
}
