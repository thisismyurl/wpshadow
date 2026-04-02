<?php
/**
 * Class WordPress\Plugin_Check\Admin\Settings_Page
 *
 * @package plugin-check
 */

namespace WordPress\Plugin_Check\Admin;

use WordPress\Plugin_Check\Traits\AI_Connect;

/**
 * Class to handle the Settings page for Plugin Check.
 *
 * @since 1.8.0
 */
final class Settings_Page {

	use AI_Connect;

	/**
	 * Option group name.
	 *
	 * @since 1.8.0
	 * @var string
	 */
	const OPTION_GROUP = 'plugin_check_settings';

	/**
	 * Option name.
	 *
	 * @since 1.8.0
	 * @var string
	 */
	const OPTION_NAME = 'plugin_check_settings';

	/**
	 * Page slug.
	 *
	 * @since 1.8.0
	 * @var string
	 */
	const PAGE_SLUG = 'plugin-check-settings';

	/**
	 * Admin page hook suffix.
	 *
	 * @since 1.8.0
	 * @var string
	 */
	protected $hook_suffix = '';

	/**
	 * Registers WordPress hooks for the settings page.
	 *
	 * @since 1.8.0
	 */
	public function add_hooks() {
		add_action( 'admin_menu', array( $this, 'add_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_init', array( $this, 'maybe_sync_existing_credentials' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		add_action( 'wp_ajax_plugin_check_get_models', array( $this, 'ajax_get_models' ) );
	}

	/**
	 * Enqueues admin scripts and styles.
	 *
	 * @since 1.8.0
	 *
	 * @param string $hook_suffix Current admin page hook suffix.
	 */
	public function enqueue_admin_scripts( $hook_suffix ) {
		if ( $hook_suffix !== $this->hook_suffix ) {
			return;
		}

		wp_enqueue_script(
			'plugin-check-admin-settings',
			plugins_url( 'assets/js/admin-settings.js', WP_PLUGIN_CHECK_MAIN_FILE ),
			array(),
			WP_PLUGIN_CHECK_VERSION,
			true
		);

		wp_localize_script(
			'plugin-check-admin-settings',
			'pluginCheckSettings',
			array(
				'ajaxUrl'         => admin_url( 'admin-ajax.php' ),
				'nonce'           => wp_create_nonce( 'plugin_check_get_models' ),
				'loadingText'     => __( 'Loading models...', 'plugin-check' ),
				'selectModelText' => __( '-- Select Model --', 'plugin-check' ),
				'noModelsText'    => __( 'No models available. Please check your API key.', 'plugin-check' ),
				'errorText'       => __( 'Error loading models', 'plugin-check' ),
			)
		);
	}

	/**
	 * AJAX handler to get models for a provider.
	 *
	 * @since 1.8.0
	 */
	public function ajax_get_models() {
		check_ajax_referer( 'plugin_check_get_models', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'plugin-check' ) ) );
		}

		$provider = isset( $_POST['provider'] ) ? sanitize_text_field( wp_unslash( $_POST['provider'] ) ) : '';
		$api_key  = isset( $_POST['api_key'] ) ? sanitize_text_field( wp_unslash( $_POST['api_key'] ) ) : '';

		if ( empty( $provider ) ) {
			wp_send_json_error( array( 'message' => __( 'Provider is required', 'plugin-check' ) ) );
		}

		$models = $this->get_models_for_provider( $provider, $api_key );

		if ( empty( $models ) ) {
			wp_send_json_success( array() );
		}

		wp_send_json_success( $models );
	}

	/**
	 * Syncs existing credentials to wp-ai-client on init if not already synced.
	 *
	 * @since 1.8.0
	 */
	public function maybe_sync_existing_credentials() {
		$settings = get_option( self::OPTION_NAME, array() );

		if ( ! empty( $settings['ai_provider'] ) && ! empty( $settings['ai_api_key'] ) ) {
			$ai_client_credentials = $this->get_stored_credentials();

			if ( ! isset( $ai_client_credentials[ $settings['ai_provider'] ] ) ||
				$ai_client_credentials[ $settings['ai_provider'] ] !== $settings['ai_api_key'] ) {
				$this->sync_credentials_to_ai_client( $settings );
			}
		}
	}

	/**
	 * Adds the settings page under the Settings menu.
	 *
	 * @since 1.8.0
	 */
	public function add_page() {
		$this->hook_suffix = add_submenu_page(
			'options-general.php',
			__( 'Plugin Check', 'plugin-check' ),
			__( 'Plugin Check', 'plugin-check' ),
			'manage_options',
			self::PAGE_SLUG,
			array( $this, 'render_page' )
		);
	}

	/**
	 * Registers settings and settings fields.
	 *
	 * @since 1.8.0
	 */
	public function register_settings() {
		register_setting(
			self::OPTION_GROUP,
			self::OPTION_NAME,
			array(
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'default'           => array(
					'ai_provider' => '',
					'ai_api_key'  => '',
					'ai_model'    => '',
				),
			)
		);

		add_settings_section(
			'ai_settings_section',
			__( 'AI Integration', 'plugin-check' ),
			array( $this, 'render_ai_section_description' ),
			self::PAGE_SLUG
		);

		add_settings_field(
			'ai_provider',
			__( 'Provider', 'plugin-check' ),
			array( $this, 'render_provider_field' ),
			self::PAGE_SLUG,
			'ai_settings_section',
			array(
				'label_for' => 'ai_provider',
			)
		);

		add_settings_field(
			'ai_api_key',
			__( 'API Key / Credentials', 'plugin-check' ),
			array( $this, 'render_api_key_field' ),
			self::PAGE_SLUG,
			'ai_settings_section',
			array(
				'label_for' => 'ai_api_key',
			)
		);

		add_settings_field(
			'ai_model',
			__( 'Model', 'plugin-check' ),
			array( $this, 'render_model_field' ),
			self::PAGE_SLUG,
			'ai_settings_section',
			array(
				'label_for' => 'ai_model',
			)
		);
	}

	/**
	 * Renders the AI settings section description.
	 *
	 * @since 1.8.0
	 */
	public function render_ai_section_description() {
		?>
		<p>
			<?php esc_html_e( 'Configure AI integration settings for false positive detection. Select your AI provider, enter your credentials, and choose the model to use for analysis.', 'plugin-check' ); ?>
		</p>
		<?php
	}

	/**
	 * Renders the provider field.
	 *
	 * @since 1.8.0
	 *
	 * @param array $args Field arguments.
	 */
	public function render_provider_field( $args ) {
		$settings  = get_option( self::OPTION_NAME, array() );
		$value     = isset( $settings['ai_provider'] ) ? esc_attr( $settings['ai_provider'] ) : '';
		$providers = $this->get_available_providers();
		?>
		<select
			id="<?php echo esc_attr( $args['label_for'] ); ?>"
			name="<?php echo esc_attr( self::OPTION_NAME . '[ai_provider]' ); ?>"
			class="regular-text"
		>
			<option value=""><?php esc_html_e( '-- Select Provider --', 'plugin-check' ); ?></option>
			<?php foreach ( $providers as $provider_key => $provider_label ) : ?>
				<option value="<?php echo esc_attr( $provider_key ); ?>" <?php selected( $value, $provider_key ); ?>>
					<?php echo esc_html( $provider_label ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<p class="description">
			<?php esc_html_e( 'Select the AI service provider you want to use for analysis.', 'plugin-check' ); ?>
		</p>
		<?php
	}

	/**
	 * Renders the API key field.
	 *
	 * @since 1.8.0
	 *
	 * @param array $args Field arguments.
	 */
	public function render_api_key_field( $args ) {
		$settings = get_option( self::OPTION_NAME, array() );
		$provider = isset( $settings['ai_provider'] ) ? esc_attr( $settings['ai_provider'] ) : '';
		$has_key  = isset( $settings['ai_api_key'] ) && ! empty( $settings['ai_api_key'] );
		?>
		<input
			type="password"
			id="<?php echo esc_attr( $args['label_for'] ); ?>"
			name="<?php echo esc_attr( self::OPTION_NAME . '[ai_api_key]' ); ?>"
			value=""
			class="regular-text"
			placeholder="<?php echo $has_key ? esc_attr__( 'Leave blank to keep current key, or enter new key', 'plugin-check' ) : esc_attr__( 'Enter your API key', 'plugin-check' ); ?>"
			autocomplete="new-password"
			<?php echo empty( $provider ) ? 'disabled' : ''; ?>
		/>
		<?php if ( $has_key ) : ?>
			<p class="description" style="color: #46b450;">
				<span class="dashicons dashicons-yes-alt" aria-hidden="true"></span>
				<?php esc_html_e( 'API key is currently set. Leave blank to keep it unchanged.', 'plugin-check' ); ?>
			</p>
		<?php endif; ?>
		<p class="description">
			<?php
			if ( empty( $provider ) ) {
				esc_html_e( 'Please select a provider first.', 'plugin-check' );
			} else {
				printf(
					/* translators: %s: Provider name */
					esc_html__( 'Enter your %s API key or credentials. This is required for AI-based false positive detection.', 'plugin-check' ),
					esc_html( $this->get_provider_label( $provider ) )
				);
			}
			?>
		</p>
		<?php
	}

	/**
	 * Renders the AI model field.
	 *
	 * @since 1.8.0
	 *
	 * @param array $args Field arguments.
	 */
	public function render_model_field( $args ) {
		$settings = get_option( self::OPTION_NAME, array() );
		$value    = isset( $settings['ai_model'] ) ? esc_attr( $settings['ai_model'] ) : '';
		$provider = isset( $settings['ai_provider'] ) ? esc_attr( $settings['ai_provider'] ) : '';
		$models   = $this->get_models_for_provider( $provider );
		?>
		<select
			id="<?php echo esc_attr( $args['label_for'] ); ?>"
			name="<?php echo esc_attr( self::OPTION_NAME . '[ai_model]' ); ?>"
			class="regular-text"
			data-initial-value="<?php echo esc_attr( $value ); ?>"
			<?php echo empty( $provider ) ? 'disabled' : ''; ?>
		>
			<option value=""><?php esc_html_e( '-- Select Model --', 'plugin-check' ); ?></option>
			<?php foreach ( $models as $model_key => $model_label ) : ?>
				<option value="<?php echo esc_attr( $model_key ); ?>" <?php selected( $value, $model_key ); ?>>
					<?php echo esc_html( $model_label ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<p class="description">
			<?php
			if ( empty( $provider ) ) {
				esc_html_e( 'Please select a provider first.', 'plugin-check' );
			} else {
				esc_html_e( 'Select the AI model to use for analysis. Different models have different capabilities and costs.', 'plugin-check' );
			}
			?>
		</p>
		<?php
	}


	/**
	 * Sanitizes settings input.
	 *
	 * @since 1.8.0
	 *
	 * @param array $input Settings input.
	 * @return array Sanitized settings.
	 */
	public function sanitize_settings( $input ) {
		$current_settings = get_option( self::OPTION_NAME, array() );
		$sanitized        = array();

		$sanitized['ai_provider'] = $this->sanitize_provider( $input, $current_settings );
		$sanitized['ai_api_key']  = $this->sanitize_api_key( $input, $current_settings );
		$sanitized['ai_model']    = $this->sanitize_model( $input, $current_settings );

		if ( $this->should_test_connection( $sanitized, $current_settings ) ) {
			$connection_test = $this->test_ai_connection( $sanitized['ai_provider'], $sanitized['ai_api_key'], $sanitized['ai_model'] );
			if ( is_wp_error( $connection_test ) ) {
				$this->add_connection_error( $connection_test );
				return $current_settings;
			}
		}

		// Sync credentials to wp-ai-client's credential storage.
		$this->sync_credentials_to_ai_client( $sanitized );

		return $sanitized;
	}

	/**
	 * Sanitizes provider setting.
	 *
	 * @since 1.8.0
	 *
	 * @param array $input            Input array.
	 * @param array $current_settings Current settings.
	 * @return string Sanitized provider.
	 */
	protected function sanitize_provider( $input, $current_settings ) {
		if ( ! isset( $input['ai_provider'] ) ) {
			return isset( $current_settings['ai_provider'] ) ? $current_settings['ai_provider'] : '';
		}

		$providers = array_keys( $this->get_available_providers() );
		return in_array( $input['ai_provider'], $providers, true ) ? $input['ai_provider'] : '';
	}

	/**
	 * Sanitizes API key setting.
	 *
	 * @since 1.8.0
	 *
	 * @param array $input            Input array.
	 * @param array $current_settings Current settings.
	 * @return string Sanitized API key.
	 */
	protected function sanitize_api_key( $input, $current_settings ) {
		if ( ! isset( $input['ai_api_key'] ) ) {
			return isset( $current_settings['ai_api_key'] ) ? $current_settings['ai_api_key'] : '';
		}

		if ( ! empty( $input['ai_api_key'] ) ) {
			return sanitize_text_field( $input['ai_api_key'] );
		}

		return isset( $current_settings['ai_api_key'] ) && ! empty( $current_settings['ai_api_key'] )
			? $current_settings['ai_api_key']
			: '';
	}

	/**
	 * Sanitizes model setting.
	 *
	 * @since 1.8.0
	 *
	 * @param array $input            Input array.
	 * @param array $current_settings Current settings.
	 * @return string Sanitized model.
	 */
	protected function sanitize_model( $input, $current_settings ) {
		if ( isset( $input['ai_model'] ) ) {
			return $input['ai_model'];
		}

		return isset( $current_settings['ai_model'] ) ? $current_settings['ai_model'] : '';
	}

	/**
	 * Checks if connection should be tested.
	 *
	 * @since 1.8.0
	 *
	 * @param array $sanitized        Sanitized settings.
	 * @param array $current_settings Current settings.
	 * @return bool True if should test connection.
	 */
	protected function should_test_connection( $sanitized, $current_settings ) {
		if ( empty( $sanitized['ai_provider'] ) || empty( $sanitized['ai_api_key'] ) || empty( $sanitized['ai_model'] ) ) {
			return false;
		}

		$provider_changed = ! isset( $current_settings['ai_provider'] ) || $current_settings['ai_provider'] !== $sanitized['ai_provider'];
		$api_key_changed  = ! isset( $current_settings['ai_api_key'] ) || $current_settings['ai_api_key'] !== $sanitized['ai_api_key'];
		$model_changed    = ! isset( $current_settings['ai_model'] ) || $current_settings['ai_model'] !== $sanitized['ai_model'];

		return $provider_changed || $api_key_changed || $model_changed;
	}

	/**
	 * Adds connection error to settings errors.
	 *
	 * @since 1.8.0
	 *
	 * @param \WP_Error $error Error object.
	 */
	protected function add_connection_error( \WP_Error $error ) {
		add_settings_error(
			self::OPTION_NAME,
			'ai_connection_failed',
			sprintf(
				/* translators: %s: Error message */
				__( 'AI connection test failed: %s. Settings were not saved.', 'plugin-check' ),
				$error->get_error_message()
			),
			'error'
		);
	}

	/**
	 * Syncs our credentials to the wp-ai-client credential storage.
	 *
	 * @since 1.8.0
	 *
	 * @param array $settings Settings array with provider and api_key.
	 */
	protected function sync_credentials_to_ai_client( $settings ) {
		$ai_client_credentials = $this->get_stored_credentials();

		if ( ! is_array( $ai_client_credentials ) ) {
			$ai_client_credentials = array();
		}

		if ( ! empty( $settings['ai_provider'] ) && ! empty( $settings['ai_api_key'] ) ) {
			$ai_client_credentials[ $settings['ai_provider'] ] = $settings['ai_api_key'];
		} elseif ( ! empty( $settings['ai_provider'] ) && empty( $settings['ai_api_key'] ) ) {
			unset( $ai_client_credentials[ $settings['ai_provider'] ] );
		}

		$this->update_stored_credentials( $ai_client_credentials );
	}

	/**
	 * Gets the AI provider.
	 *
	 * @since 1.8.0
	 *
	 * @return string AI provider.
	 */
	public static function get_provider() {
		$settings = get_option( self::OPTION_NAME, array() );
		return isset( $settings['ai_provider'] ) ? $settings['ai_provider'] : '';
	}

	/**
	 * Gets the AI API key.
	 *
	 * @since 1.8.0
	 *
	 * @return string AI API key.
	 */
	public static function get_api_key() {
		$settings = get_option( self::OPTION_NAME, array() );
		return isset( $settings['ai_api_key'] ) ? $settings['ai_api_key'] : '';
	}

	/**
	 * Gets the AI model.
	 *
	 * @since 1.8.0
	 *
	 * @return string AI model.
	 */
	public static function get_model() {
		$settings = get_option( self::OPTION_NAME, array() );
		return isset( $settings['ai_model'] ) ? $settings['ai_model'] : '';
	}

	/**
	 * Renders the settings page.
	 *
	 * @since 1.8.0
	 */
	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'plugin-check' ) );
		}

		if ( isset( $_GET['settings-updated'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			// Check if there are any error messages already set.
			$settings_errors = get_settings_errors( self::OPTION_NAME );
			$has_errors      = false;
			if ( ! empty( $settings_errors ) ) {
				foreach ( $settings_errors as $error ) {
					if ( 'error' === $error['type'] ) {
						$has_errors = true;
						break;
					}
				}
			}

			// Only show success message if no errors.
			if ( ! $has_errors ) {
				// Check if AI settings are configured.
				$settings = get_option( self::OPTION_NAME, array() );
				if ( ! empty( $settings['ai_provider'] ) && ! empty( $settings['ai_api_key'] ) && ! empty( $settings['ai_model'] ) ) {
					add_settings_error(
						self::OPTION_NAME,
						'settings_updated',
						__( 'Settings saved successfully. AI connection verified.', 'plugin-check' ),
						'success'
					);
				} else {
					add_settings_error(
						self::OPTION_NAME,
						'settings_updated',
						__( 'Settings saved.', 'plugin-check' ),
						'success'
					);
				}
			}
		}

		settings_errors( self::OPTION_NAME );

		// Enqueue script for dynamic model selection.
		wp_enqueue_script( 'jquery' );
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form method="post" action="options.php">
				<?php
				settings_fields( self::OPTION_GROUP );
				do_settings_sections( self::PAGE_SLUG );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Gets the hook suffix under which the settings page is added.
	 *
	 * @since 1.8.0
	 *
	 * @return string Hook suffix, or empty string if settings page was not added.
	 */
	public function get_hook_suffix() {
		return $this->hook_suffix;
	}
}
