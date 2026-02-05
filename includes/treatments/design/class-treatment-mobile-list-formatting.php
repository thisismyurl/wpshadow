<?php
/**
 * Mobile List Formatting Treatment
 *
 * Tests if lists are formatted clearly on mobile.
 *
 * @since   1.6050.0000
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile List Formatting Treatment Class
 *
 * Checks for list markup and block list classes.
 *
 * @since 1.6050.0000
 */
class Treatment_Mobile_List_Formatting extends Treatment_Base {

	protected static $slug = 'mobile-list-formatting';
	protected static $title = 'Mobile List Formatting';
	protected static $description = 'Tests if lists are formatted clearly on mobile';
	protected static $family = 'design';

	/**
	 * Run the treatment check.
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

		$lists = array_merge(
			iterator_to_array( $dom->getElementsByTagName( 'ul' ) ),
			iterator_to_array( $dom->getElementsByTagName( 'ol' ) )
		);

		if ( empty( $lists ) ) {
			return null;
		}

		$formatted = false;
		foreach ( $lists as $list ) {
			$class = $list->getAttribute( 'class' );
			if ( strpos( $class, 'wp-block-list' ) !== false ) {
				$formatted = true;
				break;
			}
		}

		if ( ! $formatted ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Lists found without block list styling. Ensure list spacing and markers are visible on mobile.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/mobile-list-formatting',
				'persona'      => 'publisher',
			);
		}

		return null;
	}
}
