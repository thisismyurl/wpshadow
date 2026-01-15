<?php
/**
 * WPS Feature Validator
 *
 * Validates and normalizes feature metadata to ensure consistency.
 *
 * @package WPS\CoreSupport
 * @since 1.2601.76000
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPSHADOW_Feature_Validator
 *
 * Validates feature metadata and provides defaults for missing fields.
 */
final class WPSHADOW_Feature_Validator {

	/**
	 * Required feature metadata fields.
	 */
	private const REQUIRED_FIELDS = array(
		'id',
		'name',
		'description',
		'scope',
		'version',
	);

	/**
	 * Valid category values.
	 */
	private const VALID_CATEGORIES = array(
		'security',
		'performance',
		'accessibility',
		'tools',
		'reporting',
		'privacy',
		'diagnostic',
		'admin',
		'content',
		'media',
	);

	/**
	 * Valid scope values.
	 */
	private const VALID_SCOPES = array(
		'core',
		'spoke',
		'hub',
	);

	/**
	 * Validate and normalize feature metadata.
	 *
	 * @param array $metadata Raw metadata.
	 * @return array Normalized metadata.
	 * @throws \InvalidArgumentException If validation fails.
	 */
	public static function validate( array $metadata ): array {
		// Check required fields
		foreach ( self::REQUIRED_FIELDS as $field ) {
			if ( ! isset( $metadata[ $field ] ) || '' === $metadata[ $field ] ) {
				throw new \InvalidArgumentException(
					sprintf( 'Feature metadata missing required field: %s', $field )
				);
			}
		}

		// Normalize 'enabled' to 'default_enabled'
		if ( isset( $metadata['enabled'] ) && ! isset( $metadata['default_enabled'] ) ) {
			$metadata['default_enabled'] = $metadata['enabled'];
			unset( $metadata['enabled'] );
		}

		// Validate category
		if ( isset( $metadata['category'] ) && ! in_array( $metadata['category'], self::VALID_CATEGORIES, true ) ) {
			throw new \InvalidArgumentException(
				sprintf(
					'Invalid category "%s". Must be one of: %s',
					$metadata['category'],
					implode( ', ', self::VALID_CATEGORIES )
				)
			);
		}

		// Validate scope
		if ( ! in_array( $metadata['scope'], self::VALID_SCOPES, true ) ) {
			throw new \InvalidArgumentException(
				sprintf(
					'Invalid scope "%s". Must be one of: %s',
					$metadata['scope'],
					implode( ', ', self::VALID_SCOPES )
				)
			);
		}

		// Apply defaults
		$metadata = wp_parse_args(
			$metadata,
			array(
				'default_enabled'    => false,
				'category'           => 'tools',
				'icon'               => 'dashicons-admin-generic',
				'priority'           => 50,
				'widget_group'       => 'general',
				'widget_label'       => $metadata['name'],
				'widget_description' => $metadata['description'],
				'license_level'      => 1,
				'minimum_capability' => 'manage_options',
				'dashboard'          => 'overview',
				'widget_column'      => 'left',
				'widget_priority'    => 50,
			)
		);

		// Validate priority range
		$metadata['priority'] = max( 1, min( 100, (int) $metadata['priority'] ) );

		// Validate license level
		$metadata['license_level'] = max( 1, min( 3, (int) $metadata['license_level'] ) );

		return $metadata;
	}

	/**
	 * Validate feature ID format.
	 *
	 * Feature IDs must be lowercase alphanumeric with hyphens only.
	 *
	 * @param string $id Feature ID to validate.
	 * @return bool True if valid.
	 */
	public static function validate_id( string $id ): bool {
		return (bool) preg_match( '/^[a-z0-9\-]+$/', $id );
	}

	/**
	 * Get list of valid categories.
	 *
	 * @return array Valid category values.
	 */
	public static function get_valid_categories(): array {
		return self::VALID_CATEGORIES;
	}

	/**
	 * Get list of valid scopes.
	 *
	 * @return array Valid scope values.
	 */
	public static function get_valid_scopes(): array {
		return self::VALID_SCOPES;
	}
}
