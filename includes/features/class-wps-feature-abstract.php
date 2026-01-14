<?php
/**
 * Base feature implementation.
 *
 * Provides common storage and helpers for feature metadata and state.
 *
 * @package wp_support_SUPPORT
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class WPS_Abstract_Feature implements WPS_Feature_Interface {
	protected string $id;

	protected string $name;

	protected string $description;

	protected string $scope;

	protected ?string $hub;

	protected ?string $spoke;

	protected string $version;

	protected bool $default_enabled;

	protected string $widget_group;

	protected string $widget_label;

	protected string $widget_description;

	/**
	 * @param array<string, mixed> $config Feature configuration.
	 */
	public function __construct( array $config ) {
		$this->id                 = sanitize_key( (string) ( $config['id'] ?? '' ) );
		$this->name               = (string) ( $config['name'] ?? '' );
		$this->description        = (string) ( $config['description'] ?? '' );
		$this->scope              = $this->sanitize_scope( (string) ( $config['scope'] ?? 'core' ) );
		$this->hub                = isset( $config['hub'] ) ? sanitize_key( (string) $config['hub'] ) : null;
		$this->spoke              = isset( $config['spoke'] ) ? sanitize_key( (string) $config['spoke'] ) : null;
		$this->version            = (string) ( $config['version'] ?? '1.0.0' );
		$this->default_enabled    = (bool) ( $config['default_enabled'] ?? false );
		$this->widget_group       = sanitize_key( (string) ( $config['widget_group'] ?? 'general' ) );
		$this->widget_label       = (string) ( $config['widget_label'] ?? __( 'General Features', 'plugin-wp-support-thisismyurl' ) );
		$this->widget_description = (string) ( $config['widget_description'] ?? __( 'Miscellaneous features.', 'plugin-wp-support-thisismyurl' ) );
	}

	public function get_id(): string {
		return $this->id;
	}

	public function get_name(): string {
		return $this->name;
	}

	public function get_description(): string {
		return $this->description;
	}

	public function get_scope(): string {
		return $this->scope;
	}

	public function get_hub(): ?string {
		return $this->hub;
	}

	public function get_spoke(): ?string {
		return $this->spoke;
	}

	public function get_version(): string {
		return $this->version;
	}

	public function get_default_state(): bool {
		return $this->default_enabled;
	}

	public function get_widget_group(): string {
		return $this->widget_group;
	}

	public function get_widget_label(): string {
		return $this->widget_label;
	}

	public function get_widget_description(): string {
		return $this->widget_description;
	}

	public static function is_enabled( bool $network = false ): bool {
		// For static calls, we need to derive the feature ID from the class name
		// WPS_Feature_FeatureName -> feature-name
		$class_name = get_called_class();
		$short_name = preg_replace( '/^.*\\\WPS_Feature_/', '', $class_name );
		$feature_id = strtolower( str_replace( '_', '-', $short_name ) );

		return WPS_Feature_Registry::is_feature_enabled( $feature_id, true, $network );
	}

	protected function sanitize_scope( string $scope ): string {
		$scope = sanitize_key( $scope );
		if ( ! in_array( $scope, array( 'core', 'hub', 'spoke' ), true ) ) {
			return 'core';
		}

		return $scope;
	}

	/**
	 * Get a feature-specific setting using the centralized cache.
	 *
	 * @param string $setting_name Setting name (will be prefixed with feature ID).
	 * @param mixed  $default      Default value.
	 * @param bool   $network      Whether to get network option.
	 * @return mixed Setting value.
	 */
	protected function get_setting( string $setting_name, $default = null, bool $network = false ) {
		if ( class_exists( '\\WPS\\CoreSupport\\WPS_Settings_Cache' ) ) {
			$option_name = $this->id . '_' . $setting_name;
			return \WPS\CoreSupport\WPS_Settings_Cache::get( $option_name, $default, $network );
		}

		// Fallback to direct get_option if cache not available.
		$option_name = 'wps_' . $this->id . '_' . $setting_name;
		return $network && is_multisite() ? get_site_option( $option_name, $default ) : get_option( $option_name, $default );
	}

	/**
	 * Update a feature-specific setting.
	 *
	 * @param string $setting_name Setting name (will be prefixed with feature ID).
	 * @param mixed  $value        New value.
	 * @param bool   $network      Whether to update network option.
	 * @return bool True if update succeeded.
	 */
	protected function update_setting( string $setting_name, $value, bool $network = false ): bool {
		if ( class_exists( '\\WPS\\CoreSupport\\WPS_Settings_Cache' ) ) {
			$option_name = $this->id . '_' . $setting_name;
			return \WPS\CoreSupport\WPS_Settings_Cache::update( $option_name, $value, $network );
		}

		// Fallback to direct update_option if cache not available.
		$option_name = 'wps_' . $this->id . '_' . $setting_name;
		return $network && is_multisite() ? update_site_option( $option_name, $value ) : update_option( $option_name, $value );
	}

	/**
	 * Register default settings for this feature.
	 * Call this from child constructor after parent::__construct().
	 *
	 * @param array<string, mixed> $defaults Default setting values.
	 * @return void
	 */
	protected function register_default_settings( array $defaults ): void {
		if ( ! class_exists( '\\WPS\\CoreSupport\\WPS_Settings_Cache' ) ) {
			return;
		}

		foreach ( $defaults as $setting_name => $value ) {
			$option_name = $this->id . '_' . $setting_name;
			\WPS\CoreSupport\WPS_Settings_Cache::register_defaults( $option_name, $value );
		}
	}
}
