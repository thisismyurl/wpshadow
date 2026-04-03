<?php
/**
 * Treatment Input Requirements
 *
 * Defines optional user-supplied inputs required before specific
 * treatment/diagnostic fixes can be safely applied.
 *
 * @package WPShadow
 * @since 0.6093.1400
 */

declare(strict_types=1);

namespace WPShadow\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registry and sanitizer for fix input requirements.
 */
final class Treatment_Input_Requirements {
	/**
	 * Option key storing persisted input values.
	 */
	private const STORAGE_OPTION = 'wpshadow_treatment_input_values';

	/**
	 * Get all curated requirements keyed by finding ID.
	 *
	 * @return array<string, array<string,mixed>>
	 */
	public static function get_all(): array {
		return array(
			'login-url-hardening' => array(
				'title'  => __( 'Before You Enable This Fix', 'wpshadow' ),
				'fields' => array(
					array(
						'key'         => 'confirmed_login_url_storage',
						'type'        => 'toggle',
						'label'       => __( 'I can safely store the protected login URL before enabling this fix', 'wpshadow' ),
						'description' => __( 'This fix changes how wp-login.php is accessed by requiring a secret token in the URL.', 'wpshadow' ),
						'why'         => __( 'If you do not save the new tokenized URL, you can be locked out until the token option is removed directly in the database.', 'wpshadow' ),
						'manual'      => __( 'Manual fallback: open wp_options and delete the wpshadow_login_url_token option, then wp-login.php will be accessible normally again.', 'wpshadow' ),
						'required'    => true,
					),
				),
			),
			'database-prefix-intentional' => array(
				'title'  => __( 'Before You Start This Manual Fix', 'wpshadow' ),
				'fields' => array(
					array(
						'key'         => 'new_prefix',
						'type'        => 'text',
						'label'       => __( 'New database table prefix', 'wpshadow' ),
						'placeholder' => 'mywp7_',
						'description' => __( 'Choose the new prefix you plan to use when renaming WordPress tables.', 'wpshadow' ),
						'why'         => __( 'Prefix changes touch every core table and several option/meta keys. Planning and validating the exact prefix up front reduces outage risk.', 'wpshadow' ),
						'manual'      => __( 'Manual method: rename each WordPress table, update option_name/meta_key values that reference the old prefix, then update $table_prefix in wp-config.php.', 'wpshadow' ),
						'required'    => true,
					),
				),
			),
			'site-title-tagline-intentional' => array(
				'title'  => __( 'Set Your Site Identity', 'wpshadow' ),
				'fields' => array(
					array(
						'key'          => 'site_title',
						'type'         => 'text',
						'label'        => __( 'Site Title', 'wpshadow' ),
						'placeholder'  => __( 'Your Brand Name', 'wpshadow' ),
						'description'  => __( 'This is shown in browser tabs and often used in search snippets.', 'wpshadow' ),
						'why'          => __( 'An intentional title helps users recognize your brand and improves click confidence.', 'wpshadow' ),
						'manual'       => __( 'Manual method: go to Settings -> General or Appearance -> Customize -> Site Identity and update Site Title.', 'wpshadow' ),
						'required'     => true,
						'apply_option' => 'blogname',
					),
					array(
						'key'          => 'site_tagline',
						'type'         => 'text',
						'label'        => __( 'Site Tagline', 'wpshadow' ),
						'placeholder'  => __( 'What your site is about', 'wpshadow' ),
						'description'  => __( 'A short tagline communicates purpose and can appear in theme metadata and previews.', 'wpshadow' ),
						'why'          => __( 'Leaving a default or empty tagline can look unfinished and reduce trust.', 'wpshadow' ),
						'manual'       => __( 'Manual method: in Settings -> General, update Tagline and save changes.', 'wpshadow' ),
						'required'     => true,
						'apply_option' => 'blogdescription',
					),
				),
			),
			'site-icon' => array(
				'title'  => __( 'Set Your Site Icon', 'wpshadow' ),
				'fields' => array(
					array(
						'key'         => 'site_icon_source',
						'type'        => 'text',
						'label'       => __( 'Site icon image URL or attachment ID', 'wpshadow' ),
						'placeholder' => __( 'Paste a Media Library image URL or numeric attachment ID', 'wpshadow' ),
						'description' => __( 'Use an image that already exists in the Media Library so WordPress can assign it as the site icon.', 'wpshadow' ),
						'why'         => __( 'The favicon appears in browser tabs, bookmarks, app shortcuts, and some search results. Leaving it empty makes the site feel unfinished.', 'wpshadow' ),
						'manual'      => __( 'Manual method: open Appearance -> Customize -> Site Identity and choose a Site Icon from the Media Library.', 'wpshadow' ),
						'required'    => true,
					),
				),
			),
			'timezone' => array(
				'title'  => __( 'Set Your Site Timezone', 'wpshadow' ),
				'fields' => array(
					array(
						'key'          => 'timezone_string',
						'type'         => 'text',
						'label'        => __( 'Named timezone', 'wpshadow' ),
						'placeholder'  => 'America/New_York',
						'description'  => __( 'Enter a PHP/WordPress timezone identifier for the site.', 'wpshadow' ),
						'why'          => __( 'Using a named timezone keeps scheduled posts, timestamps, and event plugins aligned with the site’s real location.', 'wpshadow' ),
						'manual'       => __( 'Manual method: go to Settings -> General and select a city-based timezone from the Timezone dropdown.', 'wpshadow' ),
						'required'     => true,
						'apply_option' => 'timezone_string',
					),
				),
			),
		);
	}

	/**
	 * Get requirement config for a finding.
	 *
	 * @param string $finding_id Finding/diagnostic ID.
	 * @return array<string,mixed>
	 */
	public static function get_for_finding( string $finding_id ): array {
		$all = self::get_all();
		return isset( $all[ $finding_id ] ) && is_array( $all[ $finding_id ] ) ? $all[ $finding_id ] : array();
	}

	/**
	 * Retrieve saved values for one finding.
	 *
	 * @param string $finding_id Finding/diagnostic ID.
	 * @return array<string,string>
	 */
	public static function get_saved_values( string $finding_id ): array {
		$stored = get_option( self::STORAGE_OPTION, array() );
		if ( ! is_array( $stored ) || ! isset( $stored[ $finding_id ] ) || ! is_array( $stored[ $finding_id ] ) ) {
			return array();
		}

		$values = array();
		foreach ( $stored[ $finding_id ] as $key => $value ) {
			$values[ sanitize_key( (string) $key ) ] = is_scalar( $value ) ? (string) $value : '';
		}

		return $values;
	}

	/**
	 * Persist normalized values for one finding.
	 *
	 * @param string              $finding_id Finding/diagnostic ID.
	 * @param array<string,mixed> $values     Normalized values.
	 * @return void
	 */
	public static function save_values( string $finding_id, array $values ): void {
		$stored = get_option( self::STORAGE_OPTION, array() );
		if ( ! is_array( $stored ) ) {
			$stored = array();
		}

		$stored[ $finding_id ] = $values;
		update_option( self::STORAGE_OPTION, $stored, false );
	}

	/**
	 * Validate and sanitize submitted values for a finding.
	 *
	 * @param string              $finding_id Finding/diagnostic ID.
	 * @param array<string,mixed> $submitted  Raw submitted values.
	 * @return array{success:bool, values:array<string,string>, message:string}
	 */
	public static function sanitize_values( string $finding_id, array $submitted ): array {
		$config = self::get_for_finding( $finding_id );
		$fields = isset( $config['fields'] ) && is_array( $config['fields'] ) ? $config['fields'] : array();

		if ( empty( $fields ) ) {
			return array(
				'success' => false,
				'values'  => array(),
				'message' => __( 'No input requirements are configured for this diagnostic.', 'wpshadow' ),
			);
		}

		$values = array();
		foreach ( $fields as $field ) {
			$key      = isset( $field['key'] ) ? sanitize_key( (string) $field['key'] ) : '';
			$type     = isset( $field['type'] ) ? (string) $field['type'] : 'text';
			$required = ! empty( $field['required'] );

			if ( '' === $key ) {
				continue;
			}

			$raw = isset( $submitted[ $key ] ) ? $submitted[ $key ] : '';

			if ( 'toggle' === $type ) {
				$clean = rest_sanitize_boolean( $raw ) ? '1' : '0';
				if ( $required && '1' !== $clean ) {
					return array(
						'success' => false,
						'values'  => array(),
						'message' => __( 'Please confirm all required toggle inputs before saving.', 'wpshadow' ),
					);
				}
				$values[ $key ] = $clean;
				continue;
			}

			$clean = sanitize_text_field( (string) $raw );
			if ( $required && '' === $clean ) {
				return array(
					'success' => false,
					'values'  => array(),
					'message' => __( 'Please complete all required text inputs before saving.', 'wpshadow' ),
				);
			}

			if ( 'database-prefix-intentional' === $finding_id && 'new_prefix' === $key && '' !== $clean ) {
				if ( 1 !== preg_match( '/^[A-Za-z0-9_]+_$/', $clean ) ) {
					return array(
						'success' => false,
						'values'  => array(),
						'message' => __( 'Database prefix must contain only letters, numbers, underscores, and end with an underscore.', 'wpshadow' ),
					);
				}
			}

			if ( 'timezone' === $finding_id && 'timezone_string' === $key && '' !== $clean ) {
				if ( ! in_array( $clean, timezone_identifiers_list(), true ) ) {
					return array(
						'success' => false,
						'values'  => array(),
						'message' => __( 'Please enter a valid named timezone such as America/New_York or Europe/London.', 'wpshadow' ),
					);
				}
			}

			if ( 'site-icon' === $finding_id && 'site_icon_source' === $key && '' !== $clean ) {
				$attachment_id = 0;
				if ( ctype_digit( $clean ) ) {
					$attachment_id = (int) $clean;
				} elseif ( function_exists( 'attachment_url_to_postid' ) ) {
					$attachment_id = (int) attachment_url_to_postid( $clean );
				}

				if ( $attachment_id <= 0 || ! wp_attachment_is_image( $attachment_id ) ) {
					return array(
						'success' => false,
						'values'  => array(),
						'message' => __( 'Please provide a Media Library image URL or numeric attachment ID for an existing image.', 'wpshadow' ),
					);
				}

				$clean = (string) $attachment_id;
			}

			$values[ $key ] = $clean;
		}

		return array(
			'success' => true,
			'values'  => $values,
			'message' => __( 'Input requirements saved.', 'wpshadow' ),
		);
	}

	/**
	 * Apply immediate option updates for fields that map to WP options.
	 *
	 * @param string              $finding_id Finding/diagnostic ID.
	 * @param array<string,mixed> $values     Sanitized values.
	 * @return array<int,string> Updated option names.
	 */
	public static function apply_immediate_updates( string $finding_id, array $values ): array {
		$config = self::get_for_finding( $finding_id );
		$fields = isset( $config['fields'] ) && is_array( $config['fields'] ) ? $config['fields'] : array();

		$updated = array();
		foreach ( $fields as $field ) {
			$key         = isset( $field['key'] ) ? sanitize_key( (string) $field['key'] ) : '';
			$apply_option = isset( $field['apply_option'] ) ? sanitize_key( (string) $field['apply_option'] ) : '';

			if ( '' === $key || '' === $apply_option || ! isset( $values[ $key ] ) ) {
				if ( 'site-icon' !== $finding_id || 'site_icon_source' !== $key || ! isset( $values[ $key ] ) ) {
					continue;
				}
			}

			if ( 'site-icon' === $finding_id && 'site_icon_source' === $key ) {
				$attachment_id = (int) $values[ $key ];
				if ( $attachment_id > 0 ) {
					update_option( 'site_icon', $attachment_id );
					$updated[] = 'site_icon';
				}
				continue;
			}

			$option_value = sanitize_text_field( (string) $values[ $key ] );
			update_option( $apply_option, $option_value );
			if ( 'timezone' === $finding_id && 'timezone_string' === $key ) {
				delete_option( 'gmt_offset' );
				$updated[] = 'gmt_offset';
			}
			$updated[] = $apply_option;
		}

		return $updated;
	}
}
