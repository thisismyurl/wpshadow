<?php
/**
 * Mobile Code Block Rendering Treatment
 *
 * Tests if code blocks render legibly on mobile.
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
 * Mobile Code Block Rendering Treatment Class
 *
 * Checks for code block markup and styling hints on the homepage.
 *
 * @since 1.6050.0000
 */
class Treatment_Mobile_Code_Block_Rendering extends Treatment_Base {

	protected static $slug = 'mobile-code-block-rendering';
	protected static $title = 'Mobile Code Block Rendering';
	protected static $description = 'Tests if code blocks render legibly on mobile';
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

		$pres = $dom->getElementsByTagName( 'pre' );
		if ( $pres->length < 1 ) {
			return null; // No code blocks found
		}

		$styled = false;
		foreach ( $pres as $pre ) {
			$class = $pre->getAttribute( 'class' );
			if ( strpos( $class, 'wp-block-code' ) !== false || strpos( $class, 'language-' ) !== false ) {
				$styled = true;
				break;
			}
		}

		if ( ! $styled ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Code blocks found without mobile styling. Add code block styles or syntax highlighting for readability.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/mobile-code-block-rendering',
				'persona'      => 'publisher',
			);
		}

		return null;
	}
}
