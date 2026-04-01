<?php
/**
 * Treatment: Add Maximum Length Validation to Inputs
 *
 * Adds maxlength attributes to common input fields and textareas
 * to reduce abuse and improve data safety.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_No_Input_Validation_Maximum_Lengths Class
 *
 * Adds sensible maxlength attributes to common form fields.
 *
 * @since 0.6093.1200
 */
class Treatment_No_Input_Validation_Maximum_Lengths extends Treatment_Base {

	/**
	 * Get the finding ID this treatment addresses.
	 *
	 * @since 0.6093.1200
	 * @return string Finding ID.
	 */
	public static function get_finding_id() {
		return 'no-input-validation-maximum-lengths';
	}

	/**
	 * Apply the treatment.
	 *
	 * Adds maxlength attributes to input and textarea fields where missing.
	 *
	 * @since 0.6093.1200
	 * @return array {
	 *     Result array.
	 *
	 *     @type bool   $success Whether treatment succeeded.
	 *     @type string $message Human-readable result message.
	 *     @type array  $details Additional details about changes made.
	 * }
	 */
	public static function apply() {
		$posts = get_posts(
			array(
				'post_type'      => array( 'post', 'page' ),
				'posts_per_page' => -1,
				'post_status'    => 'publish',
			)
		);

		$posts_updated   = 0;
		$inputs_updated  = 0;
		$textareas_updated = 0;
		$updated_samples = array();

		foreach ( $posts as $post ) {
			$content  = $post->post_content;
			$original = $content;

			$content = preg_replace_callback(
				'/<input\s+[^>]*>/i',
				function ( $matches ) use ( &$inputs_updated ) {
					$tag = $matches[0];

					if ( preg_match( '/maxlength\s*=\s*["\"][0-9]+["\"]/i', $tag ) ) {
						return $tag;
					}

					$type = 'text';
					if ( preg_match( '/type\s*=\s*["\"]([^"\"]+)["\"]/i', $tag, $type_match ) ) {
						$type = strtolower( $type_match[1] );
					}

					$name = '';
					if ( preg_match( '/name\s*=\s*["\"]([^"\"]+)["\"]/i', $tag, $name_match ) ) {
						$name = strtolower( $name_match[1] );
					}

					$maxlength = self::get_maxlength_for_input( $type, $name );

					if ( null === $maxlength ) {
						return $tag;
					}

					$inputs_updated++;

					return rtrim( $tag, '>' ) . ' maxlength="' . intval( $maxlength ) . '">';
				},
				$content
			);

			$content = preg_replace_callback(
				'/<textarea\s+[^>]*>/i',
				function ( $matches ) use ( &$textareas_updated ) {
					$tag = $matches[0];

					if ( preg_match( '/maxlength\s*=\s*["\"][0-9]+["\"]/i', $tag ) ) {
						return $tag;
					}

					$name = '';
					if ( preg_match( '/name\s*=\s*["\"]([^"\"]+)["\"]/i', $tag, $name_match ) ) {
						$name = strtolower( $name_match[1] );
					}

					$maxlength = self::get_maxlength_for_textarea( $name );

					if ( null === $maxlength ) {
						return $tag;
					}

					$textareas_updated++;

					return rtrim( $tag, '>' ) . ' maxlength="' . intval( $maxlength ) . '">';
				},
				$content
			);

			if ( $content !== $original ) {
				wp_update_post(
					array(
						'ID'           => $post->ID,
						'post_content' => $content,
					)
				);

				$posts_updated++;
				$updated_samples[] = array(
					'id'    => $post->ID,
					'title' => $post->post_title,
				);
			}
		}

		$total_updates = $inputs_updated + $textareas_updated;

		if ( $total_updates > 0 ) {
			return array(
				'success' => true,
				'message' => sprintf(
					/* translators: 1: number of fields updated, 2: number of posts updated */
					__( 'Added maximum length limits to %1$d form fields across %2$d posts and pages. This helps protect your site from oversized inputs.', 'wpshadow' ),
					$total_updates,
					$posts_updated
				),
				'details' => array(
					'inputs_updated'    => $inputs_updated,
					'textareas_updated' => $textareas_updated,
					'posts_updated'     => $posts_updated,
					'samples'           => array_slice( $updated_samples, 0, 10 ),
				),
			);
		}

		return array(
			'success' => false,
			'message' => __( 'No form fields needed updates. All inputs already have maximum length limits.', 'wpshadow' ),
		);
	}

	/**
	 * Get maxlength for input fields based on type and name.
	 *
	 * @since 0.6093.1200
	 * @param  string $type Input type.
	 * @param  string $name Input name.
	 * @return int|null Maxlength or null if not applicable.
	 */
	private static function get_maxlength_for_input( string $type, string $name ): ?int {
		if ( 'email' === $type || strpos( $name, 'email' ) !== false ) {
			return 254;
		}

		if ( 'tel' === $type || strpos( $name, 'phone' ) !== false || strpos( $name, 'tel' ) !== false ) {
			return 20;
		}

		if ( 'url' === $type || strpos( $name, 'url' ) !== false || strpos( $name, 'website' ) !== false ) {
			return 2048;
		}

		if ( 'password' === $type || strpos( $name, 'password' ) !== false ) {
			return 128;
		}

		if ( strpos( $name, 'name' ) !== false ) {
			return 100;
		}

		if ( strpos( $name, 'address' ) !== false ) {
			return 255;
		}

		if ( strpos( $name, 'comment' ) !== false || strpos( $name, 'message' ) !== false || strpos( $name, 'note' ) !== false || strpos( $name, 'description' ) !== false ) {
			return 10000;
		}

		if ( in_array( $type, array( 'text', 'search' ), true ) ) {
			return 255;
		}

		return null;
	}

	/**
	 * Get maxlength for textarea fields.
	 *
	 * @since 0.6093.1200
	 * @param  string $name Textarea name attribute.
	 * @return int|null Maxlength or null if not applicable.
	 */
	private static function get_maxlength_for_textarea( string $name ): ?int {
		if ( strpos( $name, 'comment' ) !== false || strpos( $name, 'message' ) !== false || strpos( $name, 'note' ) !== false || strpos( $name, 'description' ) !== false ) {
			return 10000;
		}

		return 5000;
	}
}
