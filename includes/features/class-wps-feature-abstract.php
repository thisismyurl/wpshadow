<?php
/**
 * Base feature implementation.
 *
 * Provides common storage and helpers for feature metadata and state.
 *
 * @package wp_support_SUPPORT
 */

declare(strict_types=1);

namespace WPS\CoreSupport\Features;

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
		$this->id                  = sanitize_key( (string) ( $config['id'] ?? '' ) );
		$this->name                = (string) ( $config['name'] ?? '' );
		$this->description         = (string) ( $config['description'] ?? '' );
		$this->scope               = $this->sanitize_scope( (string) ( $config['scope'] ?? 'core' ) );
		$this->hub                 = isset( $config['hub'] ) ? sanitize_key( (string) $config['hub'] ) : null;
		$this->spoke               = isset( $config['spoke'] ) ? sanitize_key( (string) $config['spoke'] ) : null;
		$this->version             = (string) ( $config['version'] ?? '1.0.0' );
		$this->default_enabled     = (bool) ( $config['default_enabled'] ?? false );
		$this->widget_group        = sanitize_key( (string) ( $config['widget_group'] ?? 'general' ) );
		$this->widget_label        = (string) ( $config['widget_label'] ?? __( 'General Features', 'plugin-wp-support-thisismyurl' ) );
		$this->widget_description  = (string) ( $config['widget_description'] ?? __( 'Miscellaneous features.', 'plugin-wp-support-thisismyurl' ) );
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

	public function is_enabled( bool $network = false ): bool {
		return WPS_Feature_Registry::is_feature_enabled( $this->id, $this->default_enabled, $network );
	}

	protected function sanitize_scope( string $scope ): string {
		$scope = sanitize_key( $scope );
		if ( ! in_array( $scope, array( 'core', 'hub', 'spoke' ), true ) ) {
			return 'core';
		}

		return $scope;
	}
}
