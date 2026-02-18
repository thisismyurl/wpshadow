<?php
/**
 * Mobile Form Field Labels
 *
 * Ensures all form inputs have associated labels for accessibility.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Forms
 * @since      1.602.1445
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Diagnostics\Helpers\Diagnostic_HTML_Helper;
use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Form Field Labels
 *
 * Validates that all form inputs have proper labels via <label> tags
 * or aria-label attributes. WCAG 3.3.2 Level A requirement.
 *
 * @since 1.602.1445
 */
class Diagnostic_Mobile_Form_Labels extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-form-field-labels';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Form Field Labels';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Ensures all form inputs have proper labels';

	/**
	 * The diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'forms';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.602.1445
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = self::find_label_issues();

		if ( empty( $issues ) ) {
			return null;
		}

		$threat = 60;
		if ( count( $issues ) > 10 ) {
			$threat = 75;
		}

		return array(
			'id'              => self::$slug,
			'title'           => self::$title,
			'description'     => sprintf(
				/* translators: %d: number of unlabeled inputs */
				__( 'Found %d form inputs without proper labels', 'wpshadow' ),
				count( $issues )
			),
			'severity'        => count( $issues ) > 5 ? 'high' : 'medium',
			'threat_level'    => $threat,
			'issues'          => array_slice( $issues, 0, 10 ),
			'total_issues'    => count( $issues ),
			'wcag_violation'  => '3.3.2 Labels or Instructions (Level A)',
			'user_impact'     => __( 'Users cannot identify form fields, especially with screen readers', 'wpshadow' ),
			'auto_fixable'    => true,
			'kb_link'         => 'https://wpshadow.com/kb/form-labels',
		);
	}

	/**
	 * Find inputs without proper labels.
	 *
	 * @since  1.602.1445
	 * @return array Issues found.
	 */
	private static function find_label_issues(): array {
		$html = self::get_page_html();
		if ( ! $html ) {
			return array();
		}

		try {
			$dom = Diagnostic_HTML_Helper::parse_html( $html );
			if ( ! $dom ) {
				return array();
			}

			$xpath = Diagnostic_HTML_Helper::create_xpath( $dom );

			$inputs = $xpath->query( '//input[@type!="hidden" and @type!="submit" and @type!="button"] | //textarea | //select' );

			$issues = array();
			foreach ( $inputs as $input ) {
				$has_label = self::check_input_label( $input, $xpath );
				if ( ! $has_label ) {
					$issues[] = array(
						'type'        => 'missing-label',
						'element'     => $input->tagName,
						'input_type'  => $input->getAttribute( 'type' ) ?: 'text',
						'input_name'  => $input->getAttribute( 'name' ) ?: 'unknown',
						'input_id'    => $input->getAttribute( 'id' ) ?: 'none',
						'placeholder' => $input->getAttribute( 'placeholder' ) ?: '',
					);
				}
			}

			return $issues;
		} catch ( \Exception $e ) {
			return array();
		}
	}

	/**
	 * Check if input has a proper label.
	 *
	 * @since  1.602.1445
	 * @param  \DOMElement $input Input element.
	 * @param  \DOMXPath   $xpath XPath object.
	 * @return bool Has label.
	 */
	private static function check_input_label( \DOMElement $input, \DOMXPath $xpath ): bool {
		// Check for aria-label
		if ( $input->hasAttribute( 'aria-label' ) && $input->getAttribute( 'aria-label' ) ) {
			return true;
		}

		// Check for aria-labelledby
		if ( $input->hasAttribute( 'aria-labelledby' ) ) {
			return true;
		}

		// Check for associated <label> via id
		$input_id = $input->getAttribute( 'id' );
		if ( $input_id ) {
			$labels = $xpath->query( "//label[@for='{$input_id}']" );
			if ( $labels->length > 0 ) {
				return true;
			}
		}

		// Check if input is wrapped in <label>
		$parent = $input->parentNode;
		while ( $parent ) {
			if ( 'label' === $parent->nodeName ) {
				return true;
			}
			$parent = $parent->parentNode;
		}

		return false;
	}

	/**
	 * Get page HTML for analysis.
	 *
	 * @since  1.602.1445
	 * @return string|null HTML content.
	 */
	private static function get_page_html(): ?string {
		return Diagnostic_HTML_Helper::fetch_homepage_html(
			array(
				'timeout'   => 5,
				'sslverify' => false,
			)
		);
	}
}
