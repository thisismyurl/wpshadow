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

	protected int $license_level;

	protected string $minimum_capability;

	protected array $sub_features;

	protected string $icon;

	protected string $category;

	protected int $priority;

	protected string $dashboard;

	protected string $widget_column;

	protected int $widget_priority;

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
		$this->license_level      = max( 1, min( 5, (int) ( $config['license_level'] ?? 1 ) ) );
		$this->minimum_capability = (string) ( $config['minimum_capability'] ?? 'manage_options' );
		$this->sub_features       = (array) ( $config['sub_features'] ?? array() );
		$this->icon               = (string) ( $config['icon'] ?? 'dashicons-admin-generic' );
		$this->category           = sanitize_key( (string) ( $config['category'] ?? 'general' ) );
		$this->priority           = (int) ( $config['priority'] ?? 10 );
		$this->dashboard          = sanitize_key( (string) ( $config['dashboard'] ?? 'overview' ) );
		$this->widget_column      = (string) ( $config['widget_column'] ?? 'left' );
		$this->widget_priority    = (int) ( $config['widget_priority'] ?? 10 );
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

	public function get_license_level(): int {
		return $this->license_level;
	}

	public function get_minimum_capability(): string {
		return $this->minimum_capability;
	}

	public function get_sub_features(): array {
		return $this->sub_features;
	}

	public function get_icon(): string {
		return $this->icon;
	}

	public function get_category(): string {
		return $this->category;
	}

	public function get_priority(): int {
		return $this->priority;
	}

	public function get_dashboard(): string {
		return $this->dashboard;
	}

	public function get_widget_column(): string {
		return $this->widget_column;
	}

	public function get_widget_priority(): int {
		return $this->widget_priority;
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

	/**
	 * Get default options for this feature.
	 *
	 * Override this method in child classes to define default options.
	 * Allows centralized default option management.
	 *
	 * @return array Default options.
	 */
	protected function get_default_options(): array {
		return array();
	}

	/**
	 * Get feature options with defaults merged.
	 *
	 * Retrieves options from database and merges with defaults.
	 *
	 * @return array Feature options.
	 */
	public function get_options(): array {
		$option_name = 'wps_' . $this->id . '_options';
		$options     = get_option( $option_name, array() );

		if ( ! is_array( $options ) ) {
			$options = array();
		}

		return wp_parse_args( $options, $this->get_default_options() );
	}

	/**
	 * Update feature options.
	 *
	 * @param array $options Feature options to save.
	 * @return bool True if updated successfully.
	 */
	public function update_options( array $options ): bool {
		$option_name = 'wps_' . $this->id . '_options';
		return update_option( $option_name, $options );
	}

	/**
	 * Register a scheduled cron event.
	 *
	 * @param string   $hook       Cron hook name.
	 * @param string   $recurrence Recurrence (hourly, daily, weekly, etc.).
	 * @param callable $callback   Callback function.
	 * @param array    $args       Optional arguments to pass to callback.
	 * @return void
	 */
	protected function register_cron_event( string $hook, string $recurrence, callable $callback, array $args = array() ): void {
		if ( ! wp_next_scheduled( $hook, $args ) ) {
			wp_schedule_event( time(), $recurrence, $hook, $args );
		}
		add_action( $hook, $callback );
	}

	/**
	 * Unregister a scheduled cron event.
	 *
	 * @param string $hook Cron hook name.
	 * @param array  $args Optional arguments that were passed to wp_schedule_event().
	 * @return void
	 */
	protected function unregister_cron_event( string $hook, array $args = array() ): void {
		$timestamp = wp_next_scheduled( $hook, $args );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, $hook, $args );
		}
	}

	/**
	 * Get cached data using WPS_Cache_Helper.
	 *
	 * Shorthand for accessing cache with feature ID as prefix.
	 *
	 * @param string $key Cache key suffix.
	 * @return mixed|false Cached data or false if not found.
	 */
	protected function get_cache( string $key ) {
		if ( ! class_exists( '\\WPS\\CoreSupport\\WPS_Cache_Helper' ) ) {
			return false;
		}

		$cache_key = \WPS\CoreSupport\WPS_Cache_Helper::generate_key( $this->id, $key );
		return \WPS\CoreSupport\WPS_Cache_Helper::get( $cache_key );
	}

	/**
	 * Set cached data using WPS_Cache_Helper.
	 *
	 * Shorthand for setting cache with feature ID as prefix.
	 *
	 * @param string $key        Cache key suffix.
	 * @param mixed  $data       Data to cache.
	 * @param int    $expiration Expiration in seconds (default: 1 hour).
	 * @return bool True on success.
	 */
	protected function set_cache( string $key, $data, int $expiration = HOUR_IN_SECONDS ): bool {
		if ( ! class_exists( '\\WPS\\CoreSupport\\WPS_Cache_Helper' ) ) {
			return false;
		}

		$cache_key = \WPS\CoreSupport\WPS_Cache_Helper::generate_key( $this->id, $key );
		return \WPS\CoreSupport\WPS_Cache_Helper::set( $cache_key, $data, $expiration );
	}

	/**
	 * Delete cached data using WPS_Cache_Helper.
	 *
	 * @param string $key Cache key suffix.
	 * @return bool True on success.
	 */
	protected function delete_cache( string $key ): bool {
		if ( ! class_exists( '\\WPS\\CoreSupport\\WPS_Cache_Helper' ) ) {
			return false;
		}

		$cache_key = \WPS\CoreSupport\WPS_Cache_Helper::generate_key( $this->id, $key );
		return \WPS\CoreSupport\WPS_Cache_Helper::delete( $cache_key );
	}

	/**
	 * Clear all caches for this feature.
	 *
	 * @return int Number of cache entries deleted.
	 */
	protected function clear_feature_cache(): int {
		if ( ! class_exists( '\\WPS\\CoreSupport\\WPS_Cache_Helper' ) ) {
			return 0;
		}

		return \WPS\CoreSupport\WPS_Cache_Helper::delete_by_prefix( $this->id );
	}
}
