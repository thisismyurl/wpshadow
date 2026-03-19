<?php
/**
 * Input Maximum Length Validation Diagnostic
 *
 * Checks if text inputs have reasonable maximum lengths to prevent abuse.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Diagnostics\Helpers\Diagnostic_HTML_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Input Maximum Length Validation Diagnostic Class
 *
 * Unbounded inputs enable DOS attacks and database errors. Ensures text fields
 * have maxlength attributes with reasonable limits.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Input_Maximum_Length extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'input-maximum-length';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Input Maximum Length Validation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if text inputs have maximum length limits to prevent abuse';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$home_url = home_url( '/' );
		$html     = Diagnostic_HTML_Helper::fetch_url_with_cache( $home_url );

		if ( empty( $html ) ) {
			return null;
		}

		$dom = new \DOMDocument();
		@$dom->loadHTML( $html ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged

		$xpath = new \DOMXPath( $dom );

		// Find text inputs without maxlength.
		$text_inputs              = $xpath->query( '//input[@type="text" or @type="email" or @type="url" or not(@type)]' );
		$inputs_without_maxlength = array();

		foreach ( $text_inputs as $input ) {
			$maxlength = $input->getAttribute( 'maxlength' );
			$name      = $input->getAttribute( 'name' );
			$id        = $input->getAttribute( 'id' );

			if ( empty( $maxlength ) ) {
				$identifier                 = $name ? $name : ( $id ? $id : 'unnamed' );
				$inputs_without_maxlength[] = $identifier;
			}
		}

		// Find textareas without maxlength.
		$textareas                   = $xpath->query( '//textarea' );
		$textareas_without_maxlength = array();

		foreach ( $textareas as $textarea ) {
			$maxlength = $textarea->getAttribute( 'maxlength' );
			$name      = $textarea->getAttribute( 'name' );
			$id        = $textarea->getAttribute( 'id' );

			if ( empty( $maxlength ) ) {
				$identifier                    = $name ? $name : ( $id ? $id : 'unnamed' );
				$textareas_without_maxlength[] = $identifier;
			}
		}

		$total_issues = count( $inputs_without_maxlength ) + count( $textareas_without_maxlength );

		if ( 0 === $total_issues ) {
			return null;
		}

		$severity     = $total_issues > 5 ? 'high' : 'medium';
		$threat_level = $total_issues > 5 ? 65 : 50;

		$description_parts = array();
		if ( ! empty( $inputs_without_maxlength ) ) {
			$description_parts[] = sprintf(
				/* translators: %d: number of inputs */
				__( '%d text inputs without maxlength', 'wpshadow' ),
				count( $inputs_without_maxlength )
			);
		}
		if ( ! empty( $textareas_without_maxlength ) ) {
			$description_parts[] = sprintf(
				/* translators: %d: number of textareas */
				__( '%d textareas without maxlength', 'wpshadow' ),
				count( $textareas_without_maxlength )
			);
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: description of issues */
				__( 'Unbounded inputs enable DOS attacks and database errors. Found: %s. Add maxlength attributes (255 for names, 5000 for comments).', 'wpshadow' ),
				implode( ', ', $description_parts )
			),
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => true,
			'kb_link'      => 'https://wpshadow.com/kb/input-maximum-length',
			'meta'         => array(
				'inputs_without_maxlength'    => array_slice( $inputs_without_maxlength, 0, 10 ),
				'textareas_without_maxlength' => array_slice( $textareas_without_maxlength, 0, 10 ),
				'total_issues'                => $total_issues,
			),
		);
	}
}
