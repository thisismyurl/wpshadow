<?php

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class WPSHADOW_Abstract_Feature implements WPSHADOW_Feature_Interface {
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

	protected ?string $parent;

	protected int $license_level;

	protected string $minimum_capability;

	protected array $sub_features;

	protected array $sub_feature_descriptions;

	protected string $icon;

	protected string $category;

	protected int $priority;

	protected string $dashboard;

	protected string $widget_column;

	protected int $widget_priority;

	protected array $aliases;

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

		if ( isset( $config['widget_label'] ) ) {
			$this->widget_label = (string) $config['widget_label'];
		} else {
			$this->widget_label = \WPShadow\CoreSupport\WPSHADOW_Widget_Groups::get_label( $this->widget_group );
		}

		if ( isset( $config['widget_description'] ) ) {
			$this->widget_description = (string) $config['widget_description'];
		} else {
			$this->widget_description = \WPShadow\CoreSupport\WPSHADOW_Widget_Groups::get_description( $this->widget_group );
		}

		$this->license_level      = max( 1, min( 5, (int) ( $config['license_level'] ?? 1 ) ) );
		$this->minimum_capability = (string) ( $config['minimum_capability'] ?? 'manage_options' );
		$this->parent             = isset( $config['parent'] ) ? sanitize_key( (string) $config['parent'] ) : null;
		$this->sub_features       = (array) ( $config['sub_features'] ?? array() );
		$this->sub_feature_descriptions = (array) ( $config['sub_feature_descriptions'] ?? array() );
		$this->icon               = (string) ( $config['icon'] ?? 'dashicons-admin-generic' );
		$this->category           = sanitize_key( (string) ( $config['category'] ?? 'general' ) );
		$this->priority           = (int) ( $config['priority'] ?? 10 );

		$this->dashboard       = isset( $config['dashboard'] ) ? sanitize_key( (string) $config['dashboard'] ) : \WPShadow\CoreSupport\WPSHADOW_Widget_Groups::get_dashboard( $this->widget_group );
		$this->widget_column   = isset( $config['widget_column'] ) ? (string) $config['widget_column'] : \WPShadow\CoreSupport\WPSHADOW_Widget_Groups::get_column( $this->widget_group );
		$this->widget_priority = isset( $config['widget_priority'] ) ? (int) $config['widget_priority'] : \WPShadow\CoreSupport\WPSHADOW_Widget_Groups::get_priority( $this->widget_group );

		$aliases = (array) ( $config['aliases'] ?? array() );
		$this->aliases = array_filter( array_map( 'trim', $aliases ) );
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

	public function get_parent(): ?string {
		return $this->parent;
	}

	public function get_minimum_capability(): string {
		return $this->minimum_capability;
	}

	public function get_sub_features(): array {
		return $this->sub_features;
	}

	public function get_sub_feature_descriptions(): array {
		return $this->sub_feature_descriptions;
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

	public function get_aliases(): array {
		return $this->aliases;
	}

	public function is_sub_feature_enabled( string $sub_feature_key, bool $default = true ): bool {
		$sub_feature_key = sanitize_key( $sub_feature_key );

		$default_state = $default;
		$sub_features  = $this->get_sub_features();
		if ( isset( $sub_features[ $sub_feature_key ]['default_enabled'] ) ) {
			$default_state = (bool) $sub_features[ $sub_feature_key ]['default_enabled'];
		}

		$option_name  = "wpshadow_{$this->id}_{$sub_feature_key}";
		$option_value = is_multisite() ? get_site_option( $option_name, null ) : get_option( $option_name, null );

		if ( null === $option_value ) {
			return $default_state;
		}

		return (bool) $option_value;
	}

	public static function is_enabled( bool $network = false ): bool {

		$class_name = get_called_class();
		$short_name = preg_replace( '/^.*\\\WPSHADOW_Feature_/', '', $class_name );
		$feature_id = strtolower( str_replace( '_', '-', $short_name ) );

		return WPSHADOW_Feature_Registry::is_feature_enabled( $feature_id, true, $network );
	}

	protected function sanitize_scope( string $scope ): string {
		$scope = sanitize_key( $scope );
		if ( ! in_array( $scope, array( 'core', 'hub', 'spoke' ), true ) ) {
			return 'core';
		}

		return $scope;
	}

	protected function get_setting( string $setting_name, $default = null, bool $network = false ) {
		if ( class_exists( '\\WPShadow\\WPSHADOW_Settings_Cache' ) ) {
			$option_name = $this->id . '_' . $setting_name;
			return \WPShadow\WPSHADOW_Settings_Cache::get( $option_name, $default, $network );
		}

		$option_name = 'wpshadow_' . $this->id . '_' . $setting_name;
		return $network && is_multisite() ? get_site_option( $option_name, $default ) : get_option( $option_name, $default );
	}

	protected function update_setting( string $setting_name, $value, bool $network = false ): bool {
		if ( class_exists( '\\WPShadow\\WPSHADOW_Settings_Cache' ) ) {
			$option_name = $this->id . '_' . $setting_name;
			return \WPShadow\WPSHADOW_Settings_Cache::update( $option_name, $value, $network );
		}

		$option_name = 'wpshadow_' . $this->id . '_' . $setting_name;
		return $network && is_multisite() ? update_site_option( $option_name, $value ) : update_option( $option_name, $value );
	}

	protected function register_default_settings( array $defaults ): void {
		if ( ! class_exists( '\\WPShadow\\WPSHADOW_Settings_Cache' ) ) {
			return;
		}

		foreach ( $defaults as $setting_name => $value ) {
			$option_name = $this->id . '_' . $setting_name;
			\WPShadow\WPSHADOW_Settings_Cache::register_defaults( $option_name, $value );
		}
	}

	protected function get_default_options(): array {
		return array();
	}

	public function get_options(): array {
		$option_name = 'wpshadow_' . $this->id . '_options';
		$options     = get_option( $option_name, array() );

		if ( ! is_array( $options ) ) {
			$options = array();
		}

		return wp_parse_args( $options, $this->get_default_options() );
	}

	public function update_options( array $options ): bool {
		$option_name = 'wpshadow_' . $this->id . '_options';
		return update_option( $option_name, $options );
	}

	protected function register_cron_event( string $hook, string $recurrence, callable $callback, array $args = array() ): void {
		if ( ! wp_next_scheduled( $hook, $args ) ) {
			wp_schedule_event( time(), $recurrence, $hook, $args );
		}
		add_action( $hook, $callback );
	}

	protected function unregister_cron_event( string $hook, array $args = array() ): void {
		$timestamp = wp_next_scheduled( $hook, $args );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, $hook, $args );
		}
	}

	protected function get_cache( string $key ) {
		if ( ! class_exists( '\\WPShadow\\WPSHADOW_Cache_Helper' ) ) {
			return false;
		}

		$cache_key = \WPShadow\WPSHADOW_Cache_Helper::generate_key( $this->id, $key );
		return \WPShadow\WPSHADOW_Cache_Helper::get( $cache_key );
	}

	protected function set_cache( string $key, $data, int $expiration = HOUR_IN_SECONDS ): bool {
		if ( ! class_exists( '\\WPShadow\\WPSHADOW_Cache_Helper' ) ) {
			return false;
		}

		$cache_key = \WPShadow\WPSHADOW_Cache_Helper::generate_key( $this->id, $key );
		return \WPShadow\WPSHADOW_Cache_Helper::set( $cache_key, $data, $expiration );
	}

	protected function delete_cache( string $key ): bool {
		if ( ! class_exists( '\\WPShadow\\WPSHADOW_Cache_Helper' ) ) {
			return false;
		}

		$cache_key = \WPShadow\WPSHADOW_Cache_Helper::generate_key( $this->id, $key );
		return \WPShadow\WPSHADOW_Cache_Helper::delete( $cache_key );
	}

	protected function clear_feature_cache(): int {
		if ( ! class_exists( '\\WPShadow\\WPSHADOW_Cache_Helper' ) ) {
			return 0;
		}

		return \WPShadow\WPSHADOW_Cache_Helper::delete_by_prefix( $this->id );
	}

	public function has_details_page(): bool {
		return false;
	}

	protected function log_activity( string $action, string $message, string $level = 'info' ): bool {
		if ( ! class_exists( '\\WPShadow\\CoreSupport\\WPSHADOW_Feature_Details_Page' ) ) {
			return false;
		}

		return \WPShadow\CoreSupport\WPSHADOW_Feature_Details_Page::log_feature_activity(
			$this->id,
			$action,
			$message,
			$level
		);
	}

	public function get_details_url(): string {
		if ( ! class_exists( '\\WPShadow\\CoreSupport\\WPSHADOW_Feature_Details_Page' ) ) {
			return '';
		}

		return \WPShadow\CoreSupport\WPSHADOW_Feature_Details_Page::get_feature_url( $this->id );
	}
}
