<?php

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPSHADOW_Feature_Registry {
	private const OPTION_KEY_SITE    = 'wpshadow_feature_toggles';
	private const OPTION_KEY_NETWORK = 'wpshadow_feature_toggles_network';

	private static array $feature_objects = array();

	private static array $feature_data = array();

	private static array $site_toggles = array();

	private static array $network_toggles = array();

	public static function init(): void {
		self::load_toggles();

		if ( did_action( 'plugins_loaded' ) ) {
			self::auto_discover_features();
			self::trigger_registration();
		} else {

			add_action( 'plugins_loaded', array( __CLASS__, 'auto_discover_features' ), 5 );
			add_action( 'plugins_loaded', array( __CLASS__, 'trigger_registration' ), 12 );
		}
	}

	public static function trigger_registration(): void {

		do_action( 'wpshadow_register_features' );

		foreach ( self::$feature_objects as $feature ) {
			if ( method_exists( $feature, 'register' ) ) {
				$feature->register();
			}
		}
	}

	public static function register_feature( $feature, array $data = array() ): void {
		if ( $feature instanceof WPSHADOW_Feature_Interface ) {
			$feature_id                           = $feature->get_id();
			self::$feature_objects[ $feature_id ] = $feature;
			self::$feature_data[ $feature_id ]    = self::feature_to_array( $feature );
			return;
		}

		$feature_id = sanitize_key( (string) $feature );
		if ( '' === $feature_id ) {
			return;
		}

		self::$feature_data[ $feature_id ] = array_merge(
			array(
				'id'              => $feature_id,
				'name'            => ucfirst( str_replace( '_', ' ', $feature_id ) ),
				'description'     => '',
				'version'         => '1.0.0',
				'scope'           => 'core',
				'hub'             => '',
				'spoke'           => '',
				'default_enabled' => true,
			),
			$data
		);
	}

	private static function load_toggles(): void {
		self::$site_toggles    = (array) get_option( self::OPTION_KEY_SITE, array() );
		self::$network_toggles = is_multisite() ? (array) get_site_option( self::OPTION_KEY_NETWORK, array() ) : array();
	}

	private static function persist_toggles( array $toggles, bool $network ): void {
		if ( $network && is_multisite() ) {
			self::$network_toggles = $toggles;
			update_site_option( self::OPTION_KEY_NETWORK, $toggles );
			return;
		}

		self::$site_toggles = $toggles;
		update_option( self::OPTION_KEY_SITE, $toggles );
	}

	private static function get_toggle_state( string $feature_id, bool $default, bool $network ): bool {
		$store = $network && is_multisite() ? self::$network_toggles : self::$site_toggles;
		if ( array_key_exists( $feature_id, $store ) ) {
			return (bool) $store[ $feature_id ];
		}

		return $default;
	}

	public static function is_feature_enabled( string $feature_id, bool $default = false, bool $network = false ): bool {
		return self::get_toggle_state( $feature_id, $default, $network );
	}

	public static function save_feature_states( array $features, array $enabled_ids, bool $network ): void {
		$toggles = $network && is_multisite() ? self::$network_toggles : self::$site_toggles;

		foreach ( $features as $feature ) {
			$id = isset( $feature['id'] ) ? sanitize_key( (string) $feature['id'] ) : '';
			if ( '' === $id ) {
				continue;
			}

			$toggles[ $id ] = in_array( $id, $enabled_ids, true ) ? 1 : 0;
		}

		self::persist_toggles( $toggles, $network );
	}

	public static function set_feature_enabled( string $feature_id, bool $enabled, bool $network = false ): bool {
		if ( empty( $feature_id ) ) {
			return false;
		}

		$toggles = $network && is_multisite() ? self::$network_toggles : self::$site_toggles;
		$toggles[ $feature_id ] = $enabled ? 1 : 0;

		self::persist_toggles( $toggles, $network );

		return true;
	}

	public static function get_feature( string $feature_id, bool $network = false ): ?array {
		$feature = self::$feature_data[ $feature_id ] ?? null;

		if ( isset( self::$feature_objects[ $feature_id ] ) ) {
			$feature = self::feature_to_array( self::$feature_objects[ $feature_id ], $network );
		}

		if ( null === $feature ) {
			return null;
		}

		$feature['enabled'] = self::get_toggle_state(
			$feature_id,
			(bool) ( $feature['default_enabled'] ?? false ),
			$network
		);

		return $feature;
	}

	public static function get_feature_object( string $feature_id ) {
		return self::$feature_objects[ $feature_id ] ?? null;
	}

	public static function get_features( bool $network = false ): array {
		$features = array();

		foreach ( self::$feature_objects as $feature ) {
			$feature_array                    = self::feature_to_array( $feature, $network );
			$features[ $feature_array['id'] ] = $feature_array;
		}

		foreach ( self::$feature_data as $feature_id => $data ) {
			if ( isset( $features[ $feature_id ] ) ) {
				continue;
			}

			$data['id']      = $feature_id;
			$data['enabled'] = self::get_toggle_state(
				$feature_id,
				(bool) ( $data['default_enabled'] ?? false ),
				$network
			);

			$features[ $feature_id ] = $data;
		}

		return $features;
	}

	public static function get_all_features( bool $network = false ): array {
		return self::get_features( $network );
	}

	public static function get_features_by_scope( ?string $scope = null, ?string $hub_id = null, ?string $spoke_id = null, bool $network = false ): array {
		$all      = self::get_features( $network );
		$filtered = array();
		$scope    = $scope ? sanitize_key( $scope ) : null;
		$hub_id   = $hub_id ? sanitize_key( $hub_id ) : null;
		$spoke_id = $spoke_id ? sanitize_key( $spoke_id ) : null;

		foreach ( $all as $feature ) {
			$feature_scope = $feature['scope'] ?? 'core';
			$feature_hub   = $feature['hub'] ?? '';
			$feature_spoke = $feature['spoke'] ?? '';

			if ( $scope && $feature_scope !== $scope ) {
				continue;
			}

			if ( 'hub' === $scope ) {
				if ( $hub_id && $feature_hub !== $hub_id ) {
					continue;
				}
			}

			if ( 'spoke' === $scope ) {
				if ( $hub_id && $feature_hub !== $hub_id ) {
					continue;
				}
				if ( $spoke_id && $feature_spoke !== $spoke_id ) {
					continue;
				}
			}

			$filtered[ $feature['id'] ] = $feature;
		}

		return $filtered;
	}

	public static function has_feature( string $feature ): bool {
		$feature_id = sanitize_key( $feature );
		return isset( self::$feature_objects[ $feature_id ] ) || isset( self::$feature_data[ $feature_id ] );
	}

	public static function has_any_feature( array $features ): bool {
		foreach ( $features as $feature ) {
			$feature_data = self::get_feature( sanitize_key( $feature ) );
			if ( $feature_data && ! empty( $feature_data['enabled'] ) ) {
				return true;
			}
		}

		return false;
	}

	public static function has_all_features( array $features ): bool {
		foreach ( $features as $feature ) {
			$feature_data = self::get_feature( sanitize_key( $feature ) );
			if ( ! $feature_data || empty( $feature_data['enabled'] ) ) {
				return false;
			}
		}

		return true;
	}

	private static function feature_to_array( WPSHADOW_Feature_Interface $feature, bool $network = false ): array {
		$scope = $feature->get_scope();

		return array(
			'id'                 => $feature->get_id(),
			'parent'             => method_exists( $feature, 'get_parent' ) ? $feature->get_parent() : null,
			'name'               => $feature->get_name(),
			'description'        => $feature->get_description(),
			'scope'              => $scope,
			'hub'                => in_array( $scope, array( 'hub', 'spoke' ), true ) ? ( $feature->get_hub() ?? '' ) : '',
			'spoke'              => 'spoke' === $scope ? ( $feature->get_spoke() ?? '' ) : '',
			'version'            => $feature->get_version(),
			'default_enabled'    => $feature->get_default_state(),
			'enabled'            => self::get_toggle_state( $feature->get_id(), $feature->get_default_state(), $network ),
			'widget_group'       => $feature->get_widget_group(),
			'widget_label'       => $feature->get_widget_label(),
			'widget_description' => $feature->get_widget_description(),

			'license_level'      => method_exists( $feature, 'get_license_level' ) ? $feature->get_license_level() : 1,
			'minimum_capability' => method_exists( $feature, 'get_minimum_capability' ) ? $feature->get_minimum_capability() : 'manage_options',
			'sub_features'       => method_exists( $feature, 'get_sub_features' ) ? $feature->get_sub_features() : array(),
			'icon'               => method_exists( $feature, 'get_icon' ) ? $feature->get_icon() : 'dashicons-admin-generic',
			'category'           => method_exists( $feature, 'get_category' ) ? $feature->get_category() : 'general',
			'priority'           => method_exists( $feature, 'get_priority' ) ? $feature->get_priority() : 50,
			'dashboard'          => method_exists( $feature, 'get_dashboard' ) ? $feature->get_dashboard() : 'overview',
			'widget_column'      => method_exists( $feature, 'get_widget_column' ) ? $feature->get_widget_column() : 'left',
			'widget_priority'    => method_exists( $feature, 'get_widget_priority' ) ? $feature->get_widget_priority() : 50,
		);
	}

	public static function auto_discover_features(): void {

		$features_dir = WPSHADOW_PATH . 'includes/features';

		if ( ! is_dir( $features_dir ) ) {
			return;
		}

		$feature_files = glob( $features_dir . '/class-wps-feature-*.php' );
		if ( false === $feature_files ) {
			return;
		}

		foreach ( $feature_files as $file ) {

			$basename = basename( $file );
			if ( strpos( $basename, 'abstract' ) !== false || strpos( $basename, 'interface' ) !== false || $basename === 'class-wps-feature-registry.php' ) {
				continue;
			}

			$class_name = str_replace( 'class-', '', $basename );
			$class_name = str_replace( '.php', '', $class_name );
			$parts      = explode( '-', $class_name );
			$parts      = array_map( 'ucfirst', $parts );
			$class_name = implode( '_', $parts );
			$class_name = str_replace( 'Wps_', 'WPSHADOW_', $class_name );
			$class_name = 'WPShadow\\CoreSupport\\' . $class_name;

			if ( ! class_exists( $class_name ) ) {
				require_once $file;
			}

			if ( class_exists( $class_name ) && is_subclass_of( $class_name, 'WPShadow\\CoreSupport\\WPSHADOW_Feature_Interface' ) ) {
				try {
					$feature = new $class_name();
					self::register_feature( $feature );
				} catch ( \Exception $e ) {

					continue;
				}
			}
		}
	}
}
