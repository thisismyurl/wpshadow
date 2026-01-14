<?php
/**
 * Feature Registry for Suite Plugin Dependencies
 *
 * Allows plugins to register and check for capabilities/features they provide or require.
 * This enables flexible dependency management without hardcoding plugin names.
 *
 * @package wp_support_SUPPORT
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Feature Registry Class
 *
 * Manages registration, discovery, and toggle state for features.
 */
class WPS_Feature_Registry {
	private const OPTION_KEY_SITE    = 'WPS_feature_toggles';
	private const OPTION_KEY_NETWORK = 'WPS_feature_toggles_network';

	/**
	 * Registered feature instances (class-based).
	 *
	 * @var array<string, WPS_Feature_Interface>
	 */
	private static array $feature_objects = array();

	/**
	 * Registered feature metadata (legacy array-based).
	 *
	 * @var array<string, array<string, mixed>>
	 */
	private static array $feature_data = array();

	/**
	 * Cached toggle state for site scope.
	 *
	 * @var array<string, int|bool>
	 */
	private static array $site_toggles = array();

	/**
	 * Cached toggle state for network scope.
	 *
	 * @var array<string, int|bool>
	 */
	private static array $network_toggles = array();

	/**
	 * Initialize the registry and trigger discovery.
	 *
	 * @return void
	 */
	public static function init(): void {
		self::load_toggles();
		add_action( 'plugins_loaded', array( __CLASS__, 'trigger_registration' ), 12 );
	}

	/**
	 * Allow plugins to register features.
	 *
	 * @return void
	 */
	public static function trigger_registration(): void {
		do_action( 'WPS_register_features' );

		// After registration, initialize features that have a register() method.
		foreach ( self::$feature_objects as $feature ) {
			if ( method_exists( $feature, 'register' ) ) {
				$feature->register();
			}
		}
	}

	/**
	 * Register a feature provided by a plugin.
	 * Accepts either a Feature Interface implementation or legacy array payload.
	 *
	 * @param WPS_Feature_Interface|string $feature Feature instance or identifier.
	 * @param array<string, mixed>         $data    Optional metadata (legacy path).
	 * @return void
	 */
	public static function register_feature( $feature, array $data = array() ): void {
		if ( $feature instanceof WPS_Feature_Interface ) {
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

	/**
	 * Load persisted toggle state.
	 *
	 * @return void
	 */
	private static function load_toggles(): void {
		self::$site_toggles    = (array) get_option( self::OPTION_KEY_SITE, array() );
		self::$network_toggles = is_multisite() ? (array) get_site_option( self::OPTION_KEY_NETWORK, array() ) : array();
	}

	/**
	 * Persist toggle changes.
	 *
	 * @param array<string, int|bool> $toggles Toggle map.
	 * @param bool                    $network Whether to store in network scope.
	 * @return void
	 */
	private static function persist_toggles( array $toggles, bool $network ): void {
		if ( $network && is_multisite() ) {
			self::$network_toggles = $toggles;
			update_site_option( self::OPTION_KEY_NETWORK, $toggles );
			return;
		}

		self::$site_toggles = $toggles;
		update_option( self::OPTION_KEY_SITE, $toggles );
	}

	/**
	 * Resolve toggle state for a feature.
	 *
	 * @param string $feature_id Feature identifier.
	 * @param bool   $default    Default enabled value when unset.
	 * @param bool   $network    Whether to read network scope.
	 * @return bool
	 */
	private static function get_toggle_state( string $feature_id, bool $default, bool $network ): bool {
		$store = $network && is_multisite() ? self::$network_toggles : self::$site_toggles;
		if ( array_key_exists( $feature_id, $store ) ) {
			return (bool) $store[ $feature_id ];
		}

		return $default;
	}

	/**
	 * Check if a feature is enabled, respecting toggle persistence.
	 *
	 * @param string $feature_id Feature identifier.
	 * @param bool   $default    Default enabled value when unset.
	 * @param bool   $network    Whether to read network scope.
	 * @return bool
	 */
	public static function is_feature_enabled( string $feature_id, bool $default = false, bool $network = false ): bool {
		return self::get_toggle_state( $feature_id, $default, $network );
	}

	/**
	 * Persist updated states for the provided feature list.
	 *
	 * @param array<int, array<string, mixed>> $features    Features being updated.
	 * @param array<int, string>               $enabled_ids Feature IDs marked enabled.
	 * @param bool                             $network     Whether to store network-wide.
	 * @return void
	 */
	public static function save_feature_states( array $features, array $enabled_ids, bool $network ): void {
		$toggles     = $network && is_multisite() ? self::$network_toggles : self::$site_toggles;
		$old_toggles = $toggles; // Store previous state to detect changes.

		foreach ( $features as $feature ) {
			$id = isset( $feature['id'] ) ? sanitize_key( (string) $feature['id'] ) : '';
			if ( '' === $id ) {
				continue;
			}

			$new_state = in_array( $id, $enabled_ids, true ) ? 1 : 0;
			$old_state = isset( $old_toggles[ $id ] ) ? $old_toggles[ $id ] : 0;

			// Fire action when feature is newly enabled.
			if ( 1 === $new_state && 0 === $old_state ) {
				$user_id = get_current_user_id();
				do_action( 'wps_feature_enabled', $id, $user_id );
			}

			$toggles[ $id ] = $new_state;
		}

		self::persist_toggles( $toggles, $network );
	}

	/**
	 * Get a specific feature's metadata and resolved state.
	 *
	 * @param string $feature_id Feature identifier.
	 * @param bool   $network    Whether to read network scope.
	 * @return array<string, mixed>|null
	 */
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

	/**
	 * Get all registered features with resolved state.
	 *
	 * @param bool $network Whether to read network scope.
	 * @return array<string, array<string, mixed>>
	 */
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

	/**
	 * Get features filtered by scope/hub/spoke.
	 *
	 * @param string|null $scope   Scope filter: core|hub|spoke.
	 * @param string|null $hub_id  Hub identifier for filtering.
	 * @param string|null $spoke_id Spoke identifier for filtering.
	 * @param bool        $network Whether to read network scope.
	 * @return array<string, array<string, mixed>>
	 */
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

	/**
	 * Check if a feature identifier is known.
	 *
	 * @param string $feature Feature identifier.
	 * @return bool
	 */
	public static function has_feature( string $feature ): bool {
		$feature_id = sanitize_key( $feature );
		return isset( self::$feature_objects[ $feature_id ] ) || isset( self::$feature_data[ $feature_id ] );
	}

	/**
	 * Check if any features in a list are registered and enabled.
	 *
	 * @param string[] $features Feature identifiers.
	 * @return bool
	 */
	public static function has_any_feature( array $features ): bool {
		foreach ( $features as $feature ) {
			$feature_data = self::get_feature( sanitize_key( $feature ) );
			if ( $feature_data && ! empty( $feature_data['enabled'] ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if all features in a list are registered and enabled.
	 *
	 * @param string[] $features Feature identifiers.
	 * @return bool
	 */
	public static function has_all_features( array $features ): bool {
		foreach ( $features as $feature ) {
			$feature_data = self::get_feature( sanitize_key( $feature ) );
			if ( ! $feature_data || empty( $feature_data['enabled'] ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Convert a feature object to an array with resolved state.
	 *
	 * @param WPS_Feature_Interface $feature Feature instance.
	 * @param bool                  $network Whether to read network scope.
	 * @return array<string, mixed>
	 */
	private static function feature_to_array( WPS_Feature_Interface $feature, bool $network = false ): array {
		$scope = $feature->get_scope();

		return array(
			'id'                 => $feature->get_id(),
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
		);
	}
}
