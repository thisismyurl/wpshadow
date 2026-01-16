<?php
/**
 * WPS Admin UI Component - WordPress Settings API
 * Shared admin renderer for spoke plugins.
 *
 * @package wpshadow_SUPPORT
 * @version 1.2601.0819
 */

declare(strict_types=1);

namespace WPShadow\Core\Spoke;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPSHADOW_Admin_v1 {

	public function __construct( private readonly WPSHADOW_Spoke_Base $core ) {
		\add_action( 'admin_init', array( $this, 'register_settings_api' ) );
		\add_action( 'admin_menu', array( $this, 'handle_master_suite_menu' ), 8 );
		\add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_core_scripts' ) );
		\add_action( 'updated_option', array( $this->core, 'clear_option_cache' ) );
		\add_action( 'added_option', array( $this->core, 'clear_option_cache' ) );
	}

	public function register_settings_api(): void {
		if ( ! \is_admin() || ! \function_exists( 'register_setting' ) ) {
			return;
		}

		\register_setting(
			$this->core->options_group,
			$this->core->plugin_slug . '_options',
			array(
				'type'              => 'object',
				'sanitize_callback' => array( $this, 'sanitize_plugin_options' ),
				'show_in_rest'      => true,
			)
		);

		$instance_count = \count( WPSHADOW_Spoke_Base::$instances ?? array() );
		$first_slug     = \array_key_first( WPSHADOW_Spoke_Base::$instances ?? array() );

		if ( $instance_count > 1 && $this->core->plugin_slug === $first_slug ) {
			\register_setting(
				'wpshadow_global_group',
				'wpshadow_global_options',
				array(
					'type'              => 'object',
					'sanitize_callback' => array( $this, 'sanitize_global_options' ),
					'show_in_rest'      => true,
				)
			);
		}

		if ( ! empty( $this->core->settings_blueprint ) ) {
			foreach ( $this->core->settings_blueprint as $section_id => $section ) {
				\add_settings_section( $section_id, $section['title'] ?? '', null, $this->core->plugin_slug );

				foreach ( (array) ( $section['fields'] ?? array() ) as $field_id => $args ) {
					\add_settings_field(
						$field_id,
						$args['label'] ?? '',
						array( $this, 'render_field' ),
						$this->core->plugin_slug,
						$section_id,
						\array_merge(
							$args,
							array(
								'id'   => $field_id,
								'name' => $this->core->plugin_slug . '_options[' . $field_id . ']',
							)
						)
					);
				}
			}
		}
	}

	public function sanitize_plugin_options( $input ): array {
		if ( ! \is_array( $input ) ) {
			return array();
		}

		$sanitized = array();
		$blueprint = $this->core->settings_blueprint ?? array();

		foreach ( $input as $key => $value ) {
			$field_def = null;
			foreach ( $blueprint as $section ) {
				foreach ( (array) ( $section['fields'] ?? array() ) as $field_id => $field_args ) {
					if ( $field_id === $key ) {
						$field_def = $field_args;
						break 2;
					}
				}
			}
			$sanitized[ $key ] = $this->sanitize_field_value( $key, $value, $field_def );
		}

		return $sanitized;
	}

	private function sanitize_field_value( string $key, $value, ?array $field_def ): mixed {
		if ( $field_def === null ) {
			return \sanitize_text_field( (string) $value );
		}

		$type = $field_def['type'] ?? 'text';

		return match ( $type ) {
			'toggle'  => (int) ( ! empty( $value ) ),
			'range'   => (int) $value,
			'number'  => (int) $value,
			'radio'   => \in_array( (string) $value, \array_keys( (array) ( $field_def['options'] ?? array() ) ), true ) ? (string) $value : '',
			'select'  => \in_array( (string) $value, \array_keys( (array) ( $field_def['options'] ?? array() ) ), true ) ? (string) $value : '',
			'email'   => \sanitize_email( (string) $value ),
			'url'     => \esc_url_raw( (string) $value ),
			'textarea' => \wp_kses_post( (string) $value ),
			default   => \sanitize_text_field( (string) $value ),
		};
	}

	public function sanitize_global_options( $input ): array {
		if ( ! \is_array( $input ) ) {
			return array();
		}

		$sanitized = array();
		if ( isset( $input['enabled'] ) ) {
			$sanitized['enabled'] = (int) ! empty( $input['enabled'] );
		}

		if ( isset( $input['multisite_scope'] ) && \is_multisite() ) {
			if ( \current_user_can( 'manage_network_options' ) ) {
				$sanitized['multisite_scope'] = \in_array( (string) $input['multisite_scope'], array( 'site', 'network' ), true ) ? (string) $input['multisite_scope'] : 'site';
			} else {
				$sanitized['multisite_scope'] = 'site';
			}
		}

		$dynamic_fields = $this->get_dynamic_global_fields();
		foreach ( $dynamic_fields as $field_id => $field_config ) {
			if ( isset( $input[ $field_id ] ) ) {
				$sanitized[ $field_id ] = $this->sanitize_field_value( $field_id, $input[ $field_id ], $field_config );
			}
		}

		$scope = $sanitized['multisite_scope'] ?? 'site';
		if ( \is_multisite() && $scope === 'network' && \current_user_can( 'manage_network_options' ) ) {
			$this->propagate_global_settings_network_wide( $sanitized );
		} else {
			$this->propagate_global_settings_to_modules( $sanitized );
		}

		return $sanitized;
	}

	private function get_dynamic_global_fields(): array {
		$dynamic_fields = array();
		$instances      = WPSHADOW_Spoke_Base::$instances ?? array();

		foreach ( $instances as $instance ) {
			$blueprint = $instance->settings_blueprint ?? array();
			foreach ( $blueprint as $section ) {
				foreach ( (array) ( $section['fields'] ?? array() ) as $field_id => $field_config ) {
					if ( isset( $field_config['globalizable'] ) && $field_config['globalizable'] ) {
						$dynamic_fields[ $field_id ] = $field_config;
					}
				}
			}
		}
		return $dynamic_fields;
	}

	private function propagate_global_settings_network_wide( array $global_settings ): void {
		if ( ! \is_multisite() || ! \current_user_can( 'manage_network_options' ) ) {
			return;
		}

		\update_site_option( 'wpshadow_network_global_options', $global_settings );
		$sites = \get_sites( array( 'number' => 0 ) );
		foreach ( $sites as $site ) {
			\switch_to_blog( $site->blog_id );
			$this->propagate_global_settings_to_modules( $global_settings );
			\restore_current_blog();
		}
	}

	private function propagate_global_settings_to_modules( array $global_settings ): void {
		$instances = WPSHADOW_Spoke_Base::$instances ?? array();
		foreach ( $instances as $slug => $instance ) {
			$current_options = (array) \get_option( $slug . '_options', array() );
			$updated         = false;
			foreach ( $global_settings as $setting_key => $setting_value ) {
				if ( $setting_key === 'multisite_scope' ) {
					continue;
				}
				$current_options[ $setting_key ] = $setting_value;
				$updated                         = true;
			}
			if ( $updated ) {
				\update_option( $slug . '_options', $current_options, false );
				if ( method_exists( $instance, 'clear_option_cache' ) ) {
					$instance->clear_option_cache();
				}
			}
		}
	}

	public function render_field( array $args ): void {
		$options = (array) $this->core->get_plugin_option();
		$value   = isset( $args['value'] ) ? $args['value'] : ( $options[ $args['id'] ] ?? ( $args['default'] ?? '' ) );

		switch ( $args['type'] ?? 'text' ) {
			case 'toggle':
				$checked = ! empty( $value ) ? 'checked' : '';
				echo '<label><input type="checkbox" name="' . \esc_attr( $args['name'] ) . '" value="1" ' . \esc_attr( $checked ) . ' /> ' . \esc_html( $args['label'] ?? '' ) . '</label>';
				break;
			case 'range':
				$min  = $args['min'] ?? 0;
				$max  = $args['max'] ?? 100;
				$step = $args['step'] ?? 10;
				$id   = 'range_' . \sanitize_html_class( $args['id'] );
				echo '<input type="range" id="' . \esc_attr( $id ) . '" name="' . \esc_attr( $args['name'] ) . '" value="' . \esc_attr( (string) $value ) . '" min="' . \esc_attr( (string) $min ) . '" max="' . \esc_attr( (string) $max ) . '" step="' . \esc_attr( (string) $step ) . '" style="flex-grow:1;" />';
				echo '<output for="' . \esc_attr( $id ) . '" style="font-weight:bold; min-width:30px; margin-left:10px;">%</output>';
				echo '<script>(function(){const s=document.getElementById(' . \wp_json_encode( $id ) . ');const o=document.querySelector("output[for=\\"' . \esc_attr( $id ) . '\\"]");if(s&&o){const u=()=>o.textContent=s.value+"%";u();s.addEventListener("input",u);}})();</script>';
				break;
			case 'radio':
				$options_list = (array) ( $args['options'] ?? array() );
				echo '<fieldset>';
				foreach ( $options_list as $opt_val => $opt_label ) {
					echo '<label style="display:block; margin-bottom:8px;"><input type="radio" name="' . \esc_attr( $args['name'] ) . '" value="' . \esc_attr( (string) $opt_val ) . '" ' . \checked( (string) $value, (string) $opt_val, false ) . ' /> <strong>' . \esc_html( (string) $opt_label ) . '</strong></label>';
					if ( ! empty( $args['descriptions'][ $opt_val ] ) ) {
						echo '<p style="margin:0 0 12px 24px; font-size:12px; color:#666;">' . \wp_kses_post( $args['descriptions'][ $opt_val ] ) . '</p>';
					}
				}
				echo '</fieldset>';
				break;
			case 'select':
				$options_list = (array) ( $args['options'] ?? array() );
				echo '<select name="' . \esc_attr( $args['name'] ) . '" class="postform">';
				foreach ( $options_list as $opt_val => $opt_label ) {
						echo '<option value="' . \esc_attr( (string) $opt_val ) . '" ' . \selected( (string) $value, (string) $opt_val, false ) . '>' . \esc_html( (string) $opt_label ) . '</option>';
				}
				echo '</select>';
				break;
			case 'textarea':
				echo '<textarea name="' . \esc_attr( $args['name'] ) . '" class="large-text code" rows="5">' . \esc_textarea( (string) $value ) . '</textarea>';
				break;
			case 'email':
				echo '<input type="email" name="' . \esc_attr( $args['name'] ) . '" value="' . \esc_attr( (string) $value ) . '" class="regular-text" />';
				break;
			case 'url':
				echo '<input type="url" name="' . \esc_attr( $args['name'] ) . '" value="' . \esc_attr( (string) $value ) . '" class="regular-text" />';
				break;
			case 'number':
				echo '<input type="number" name="' . \esc_attr( $args['name'] ) . '" value="' . \esc_attr( (string) $value ) . '" class="regular-text" />';
				break;
			default:
				echo '<input type="text" name="' . \esc_attr( $args['name'] ) . '" value="' . \esc_attr( (string) $value ) . '" class="regular-text" />';
				break;
		}

		if ( ! empty( $args['desc'] ) ) {
			echo '<p class="description">' . \wp_kses_post( $args['desc'] ) . '</p>';
		}
	}

	public function handle_master_suite_menu(): void {
		// Legacy method - menu system now handled by WPSHADOW_Spoke_Base class.
		// Do not add old management pages or menus here.
	}

	public function render_single_plugin_page(): void {
		$instances       = WPSHADOW_Spoke_Base::$instances ?? array();
		$single_instance = \reset( $instances );
		if ( ! $single_instance ) {
			echo '<div class="notice notice-error"><p>' . \esc_html( 'No plugin instance found.' ) . '</p></div>';
			return;
		}

		echo '<div class="wrap">';
		echo '<h1>' . \esc_html( \get_admin_page_title() ) . '</h1>';
		if ( ! empty( $single_instance->settings_blueprint ) ) {
			echo '<form method="post" action="options.php">';
			\settings_fields( $single_instance->options_group );
			foreach ( $single_instance->settings_blueprint as $section_id => $section ) {
				echo '<div class="postbox">';
				echo '<h2 class="hndle">' . \esc_html( $section['title'] ?? '' ) . '</h2>';
				echo '<div class="inside"><table class="form-table">';
				\do_settings_fields( $single_instance->plugin_slug, $section_id );
				echo '</table></div></div>';
			}
			\submit_button();
			echo '</form>';
		} else {
			echo '<div class="notice notice-info"><p>' . \esc_html( 'No settings available for this plugin.' ) . '</p></div>';
		}
		echo '</div>';
	}

	public function render_master_page(): void {
		if ( ! \current_user_can( 'manage_options' ) ) {
			\wp_die( 'Insufficient permissions' );
		}

		$active_tab = isset( $_GET['tab'] ) ? \sanitize_key( $_GET['tab'] ) : 'image-wpshadow';
		$instances  = WPSHADOW_Spoke_Base::$instances ?? array();

		$asset_plugin = ( 'global' === $active_tab && isset( $instances['image-wpshadow'] ) ) ? $instances['image-wpshadow'] : ( $instances[ $active_tab ] ?? null );
		$icon_url     = '';
		$banner_url   = '';
		if ( $asset_plugin ) {
			$plugin_url  = $asset_plugin->plugin_url;
			$icon_path   = \str_replace( \content_url(), WP_CONTENT_DIR, $plugin_url ) . 'assets/images/icon-64x64.png';
			$banner_path = \str_replace( \content_url(), WP_CONTENT_DIR, $plugin_url ) . 'assets/images/banner-280x90.png';
			if ( \file_exists( $icon_path ) ) {
				$icon_url = $plugin_url . 'assets/images/icon-64x64.png';
			}
			if ( \file_exists( $banner_path ) ) {
				$banner_url = $plugin_url . 'assets/images/banner-280x90.png';
			}
		}

		echo '<div class="wrap">';
		echo '<h1 style="display:flex;align-items:center;gap:12px;">';
		if ( $icon_url ) {
			echo '<img src="' . \esc_url( $icon_url ) . '" alt="" style="width:64px;height:64px;">';
		}
		echo \esc_html( 'Support Suite by wpshadow' );
		echo '</h1>';

		echo '<nav class="nav-tab-wrapper">';
		if ( isset( $instances['image-wpshadow'] ) ) {
			echo '<a href="' . \esc_url( \admin_url( 'admin.php?page=wpshadow-support&tab=image-wpshadow' ) ) . '" class="nav-tab ' . ( 'image-wpshadow' === $active_tab ? 'nav-tab-active' : '' ) . '">Dashboard</a>';
		}
		if ( \count( $instances ) > 1 ) {
			echo '<a href="' . \esc_url( \admin_url( 'admin.php?page=wpshadow-support&tab=global' ) ) . '" class="nav-tab ' . ( 'global' === $active_tab ? 'nav-tab-active' : '' ) . '">Global</a>';
		}
		foreach ( $instances as $slug => $instance ) {
			if ( 'image-wpshadow' === $slug ) {
				continue;
			}
			echo '<a href="' . \esc_url( \add_query_arg( array( 'tab' => \esc_attr( $slug ) ), \admin_url( 'admin.php?page=wpshadow-support' ) ) ) . '" class="nav-tab ' . ( $active_tab === $slug ? 'nav-tab-active' : '' ) . '">' . \esc_html( \strtoupper( $instance->get_data_prefix() ) ) . '</a>';
		}
		echo '</nav>';

		echo '<div style="display:grid;grid-template-columns:1fr 300px;gap:20px;margin-top:20px;">';
		echo '<div class="wps-main-content">';
		if ( 'global' === $active_tab && \count( $instances ) > 1 ) {
			$this->render_global_dashboard();
		} elseif ( isset( $instances[ $active_tab ] ) ) {
			echo '<form method="post" action="options.php">';
			\settings_fields( $instances[ $active_tab ]->options_group );
			\do_settings_sections( $instances[ $active_tab ]->plugin_slug );
			\submit_button();
			echo '</form>';
		}
		echo '</div>';

		echo '<div class="wps-sidebar">';
		if ( $banner_url ) {
			echo '<div class="postbox"><div class="inside" style="padding:0;margin:0;"><img src="' . \esc_url( $banner_url ) . '" alt="" style="width:100%;height:auto;display:block;"></div></div>';
		}
		if ( isset( $instances[ $active_tab ] ) && $instances[ $active_tab ]->admin ) {
			$instances[ $active_tab ]->admin->render_default_sidebar_actions( $active_tab );
		}
		echo '</div>';
		echo '</div>'; // grid
		echo '</div>'; // wrap
	}

	private function render_global_dashboard(): void {
		$this->render_global_settings_form();
	}

	private function render_global_settings_form(): void {
		$instances = WPSHADOW_Spoke_Base::$instances ?? array();
		if ( \count( $instances ) <= 1 ) {
			echo '<div class="notice notice-info"><p>' . \esc_html( 'Global settings are only available when multiple plugins are active.' ) . '</p></div>';
			return;
		}

		$default_options = array(
			'enabled'         => 1,
			'multisite_scope' => 'site',
		);
		if ( \is_multisite() && \current_user_can( 'manage_network_options' ) ) {
			$network_options = (array) \get_site_option( 'wpshadow_network_global_options', array() );
			$local_options   = (array) \get_option( 'wpshadow_global_options', array() );
			$global_options  = \array_merge( $default_options, $local_options, $network_options );
		} else {
			$global_options = (array) \get_option( 'wpshadow_global_options', $default_options );
		}

		$dynamic_fields = $this->get_dynamic_global_fields();
		echo '<div class="notice notice-info inline" style="margin:20px 0;"><p><strong>' . \esc_html__( 'Global Settings:', 'wps-core' ) . '</strong> ' . \esc_html__( 'Changes made here apply to all active modules.', 'wps-core' ) . '</p></div>';
		echo '<form method="post" action="options.php">';
		\settings_fields( 'wpshadow_global_group' );
		echo '<div class="postbox"><h2 class="hndle"><span>' . \esc_html( 'Global Settings' ) . '</span></h2><div class="inside"><table class="form-table">';
		echo '<tr><th scope="row"><label for="wps-global-enabled">' . \esc_html( 'Master Enable' ) . '</label></th><td><label><input type="checkbox" id="wps-global-enabled" name="wpshadow_global_options[enabled]" value="1" ' . \checked( 1, $global_options['enabled'] ?? 0, false ) . ' /> ' . \esc_html( 'Enable all modules' ) . '</label></td></tr>';
		if ( \is_multisite() ) {
			echo '<tr><th scope="row"><label>' . \esc_html( 'Apply To' ) . '</label></th><td><fieldset>';
				echo '<label><input type="radio" name="wpshadow_global_options[multisite_scope]" value="site" ' . \checked( $global_options['multisite_scope'] ?? 'site', 'site', false ) . ' /> ' . \esc_html( 'This site only' ) . '</label><br />';
			if ( \current_user_can( 'manage_network_options' ) ) {
				echo '<label><input type="radio" name="wpshadow_global_options[multisite_scope]" value="network" ' . \checked( $global_options['multisite_scope'] ?? 'site', 'network', false ) . ' /> ' . \esc_html( 'All sites in network' ) . '</label>';
			}
			echo '</fieldset></td></tr>';
		}
		foreach ( $dynamic_fields as $field_id => $field_config ) {
			$field_value          = $global_options[ $field_id ] ?? ( $field_config['default'] ?? '' );
			$field_name           = 'wpshadow_global_options[' . $field_id . ']';
			$field_config['id']   = $field_id;
			$field_config['name'] = $field_name;
			echo '<tr><th scope="row"><label for="wps-global-' . \esc_attr( $field_id ) . '">' . \esc_html( $field_config['label'] ?? \ucfirst( \str_replace( '_', ' ', $field_id ) ) ) . '</label></th><td>';
			$this->render_field( \array_merge( $field_config, array( 'value' => $field_value ) ) );
			if ( isset( $field_config['description'] ) ) {
				echo '<p class="description">' . \esc_html( $field_config['description'] ) . '</p>';
			}
			echo '</td></tr>';
		}
		if ( empty( $dynamic_fields ) ) {
			echo '<tr><td colspan="2"><p style="text-align:center; color:#666; font-style:italic;">' . \esc_html( 'No globalizable settings found.' ) . '</p></td></tr>';
		}
		echo '</table>';
		\submit_button();
		echo '</div></div></form>';
	}

	public function enqueue_core_scripts( string $hook_suffix ): void {
		if ( false === \strpos( $hook_suffix, 'wpshadow' ) ) {
			return;
		}
		\wp_enqueue_style( 'wp-admin' );
	}

	public function render_default_sidebar_actions( string $plugin_slug ): void {
		echo '<div class="wps-sidebar-actions" style="margin-top:1rem; padding-top:1rem; border-top:1px solid #e5e7eb;">';
		echo '<p style="margin:0 0 0.5rem 0; font-size:0.875rem; color:#6b7280;">' . \esc_html__( 'Quick Actions', $plugin_slug ) . '</p>';
		echo '<ul style="list-style:none; padding:0; margin:0;">';
		echo '<li style="margin:0.25rem 0;"><a href="' . \esc_url( \admin_url( 'admin.php?page=' . $plugin_slug ) ) . '" style="color:#0066cc; text-decoration:none;">' . \esc_html__( 'Plugin Settings', $plugin_slug ) . '</a></li>';
		echo '<li style="margin:0.25rem 0;"><a href="https://wpshadow.com/" target="_blank" rel="noopener noreferrer" style="color:#0066cc; text-decoration:none;">' . \esc_html__( 'Support', $plugin_slug ) . '</a></li>';
		echo '</ul></div>';
	}
}
