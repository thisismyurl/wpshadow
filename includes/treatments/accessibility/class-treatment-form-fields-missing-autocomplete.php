<?php
/**
 * Form Fields Missing Autocomplete Treatment
 *
 * Checks if form fields have autocomplete attributes.
 *
 * @since   1.6035.1400
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Form Autocomplete Treatment Class
 *
 * Validates that form fields use autocomplete attributes for common data types.
 *
 * @since 1.6035.1400
 */
class Treatment_Form_Fields_Missing_Autocomplete extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'form-fields-missing-autocomplete';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Form Fields Missing Autocomplete Attributes';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if form fields have autocomplete attributes';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6035.1400
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Fields that should have autocomplete.
		$autocomplete_fields = array(
			'email'    => 'email',
			'name'     => 'name',
			'first'    => 'given-name',
			'last'     => 'family-name',
			'phone'    => 'tel',
			'address'  => 'street-address',
			'city'     => 'address-level2',
			'state'    => 'address-level1',
			'zip'      => 'postal-code',
			'country'  => 'country',
			'username' => 'username',
			'password' => 'current-password',
		);

		// Check theme templates for forms.
		$form_templates = array(
			get_template_directory() . '/comments.php',
			get_template_directory() . '/contact.php',
			get_template_directory() . '/template-parts/contact-form.php',
		);

		$fields_found = 0;
		$fields_with_autocomplete = 0;

		foreach ( $form_templates as $template ) {
			if ( ! file_exists( $template ) ) {
				continue;
			}

			$content = file_get_contents( $template );

			// Check each field type.
			foreach ( $autocomplete_fields as $field_pattern => $autocomplete_value ) {
				// Count fields matching pattern.
				if ( preg_match_all( '/<input[^>]+(?:name|id)=["\'][^"\']*' . $field_pattern . '[^"\']*["\'][^>]*>/i', $content, $matches ) ) {
					$fields_found += count( $matches[0] );

					// Count how many have autocomplete.
					foreach ( $matches[0] as $field ) {
						if ( preg_match( '/autocomplete=/i', $field ) ) {
							$fields_with_autocomplete++;
						}
					}
				}
			}
		}

		if ( $fields_found > 0 && $fields_with_autocomplete === 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of form fields without autocomplete */
				__( 'Found %d form fields that should have autocomplete attributes but don\'t', 'wpshadow' ),
				$fields_found
			);
		}

		// Check if WordPress core fields have autocomplete.
		if ( file_exists( get_template_directory() . '/comments.php' ) ) {
			$comments = file_get_contents( get_template_directory() . '/comments.php' );
			
			if ( strpos( $comments, 'comment_form()' ) !== false && strpos( $comments, 'autocomplete' ) === false ) {
				$issues[] = __( 'Comment form should include autocomplete attributes for name, email, and URL fields', 'wpshadow' );
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Your forms make users manually type everything—like having to fill out a paper form when you already gave them all your information last week. The autocomplete attribute lets browsers securely fill in common fields like name, email, and address from saved data. This is critical for users with motor disabilities (typing is exhausting), cognitive disabilities (remembering spelling/format is hard), and mobile users (tiny keyboards). Example: autocomplete="email" on email fields.', 'wpshadow' ) . ' ' . implode( ' ', $issues ),
				'severity'     => 'low',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/autocomplete-attributes',
			);
		}

		return null;
	}
}
