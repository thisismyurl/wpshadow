<?php
/**
 * Trait WordPress\Plugin_Check\Traits\AI_Connect
 *
 * @package plugin-check
 */

namespace WordPress\Plugin_Check\Traits;

use WordPress\AiClient\AiClient;
use WordPress\AiClient\Builders\PromptBuilder;
use WordPress\AiClient\ProviderImplementations\Anthropic\AnthropicApiKeyRequestAuthentication;
use WordPress\AiClient\ProviderImplementations\Google\GoogleApiKeyRequestAuthentication;
use WordPress\AiClient\Providers\Http\DTO\ApiKeyRequestAuthentication;
use WordPress\AiClient\Providers\Http\HttpTransporterFactory;
use WP_Error;

/**
 * Trait for AI provider and model connectivity.
 *
 * @since 1.8.0
 */
trait AI_Connect {

	/**
	 * Gets available AI providers.
	 *
	 * @since 1.8.0
	 *
	 * @return array Array of provider keys and labels.
	 */
	protected function get_available_providers() {
		if ( ! class_exists( AiClient::class ) ) {
			return array();
		}

		try {
			$registry     = AiClient::defaultRegistry();
			$provider_ids = $registry->getRegisteredProviderIds();
			$providers    = array();

			foreach ( $provider_ids as $provider_id ) {
				$provider_class_name = $registry->getProviderClassName( $provider_id );

				$provider_metadata = $provider_class_name::metadata();

				if ( $provider_metadata->getType()->isCloud() ) {
					$providers[ $provider_id ] = $provider_metadata->getName();
				}
			}

			return $providers;
		} catch ( \Throwable $e ) {
			return array();
		}
	}

	/**
	 * Gets available models for a provider.
	 *
	 * @since 1.8.0
	 *
	 * @param string $provider Provider key.
	 * @param string $api_key  Optional API key to use for the request.
	 * @return array Array of model keys and labels.
	 */
	protected function get_models_for_provider( $provider, $api_key = '' ) {
		if ( empty( $provider ) || ! class_exists( AiClient::class ) ) {
			return array();
		}

		try {
			// If no API key passed, fall back to stored credentials.
			if ( empty( $api_key ) ) {
				$credentials = $this->get_stored_credentials();
				if ( ! isset( $credentials[ $provider ] ) || empty( $credentials[ $provider ] ) ) {
					return array();
				}

				$api_key = $credentials[ $provider ];
			}

			$registry            = AiClient::defaultRegistry();
			$provider_class_name = $registry->getProviderClassName( $provider );

			$model_directory = $provider_class_name::modelMetadataDirectory();

			$http_transporter = HttpTransporterFactory::createTransporter();
			$model_directory->setHttpTransporter( $http_transporter );

			$model_directory->setRequestAuthentication(
				$this->get_request_authentication_for_provider( $provider, $api_key )
			);

			$model_metadata_list = $model_directory->listModelMetadata();

			$models = array();
			foreach ( $model_metadata_list as $model_metadata ) {
				$models[ $model_metadata->getId() ] = $model_metadata->getName();
			}

			return $models;
		} catch ( \Throwable $e ) {
			return array();
		}
	}

	/**
	 * Gets the label for a provider.
	 *
	 * @since 1.8.0
	 *
	 * @param string $provider Provider key.
	 * @return string Provider label.
	 */
	protected function get_provider_label( $provider ) {
		$providers = $this->get_available_providers();
		return isset( $providers[ $provider ] ) ? $providers[ $provider ] : $provider;
	}

	/**
	 * Gets stored AI credentials.
	 *
	 * @since 1.8.0
	 *
	 * @return array Array of provider credentials.
	 */
	protected function get_stored_credentials() {
		return get_option( 'plugin_check_ai_credentials', array() );
	}

	/**
	 * Updates stored AI credentials.
	 *
	 * @since 1.8.0
	 *
	 * @param array $credentials Array of provider credentials to store.
	 * @return void
	 */
	protected function update_stored_credentials( $credentials ) {
		update_option( 'plugin_check_ai_credentials', $credentials );
	}

	/**
	 * Gets a request authentication instance for a provider.
	 *
	 * @since 1.8.0
	 *
	 * @param string $provider Provider key.
	 * @param string $api_key  API key.
	 * @return \WordPress\AiClient\Providers\Http\Contracts\RequestAuthenticationInterface
	 */
	protected function get_request_authentication_for_provider( $provider, $api_key ) {
		if ( 'anthropic' === $provider ) {
			return new AnthropicApiKeyRequestAuthentication( $api_key );
		}

		if ( 'google' === $provider ) {
			return new GoogleApiKeyRequestAuthentication( $api_key );
		}

		return new ApiKeyRequestAuthentication( $api_key );
	}

	/**
	 * Stores AI credentials temporarily for testing.
	 *
	 * @since 1.8.0
	 *
	 * @param string $provider Provider key.
	 * @param string $api_key  API key.
	 * @return array Original credentials before update.
	 */
	protected function store_credentials_temporarily( $provider, $api_key ) {
		$stored_credentials            = $this->get_stored_credentials();
		$temp_credentials              = $stored_credentials;
		$temp_credentials[ $provider ] = $api_key;
		$this->update_stored_credentials( $temp_credentials );

		return $stored_credentials;
	}

	/**
	 * Restores original AI credentials.
	 *
	 * @since 1.8.0
	 *
	 * @param array $original_credentials Original credentials to restore.
	 * @return void
	 */
	protected function restore_credentials( $original_credentials ) {
		$this->update_stored_credentials( $original_credentials );
	}

	/**
	 * Tests the AI connection with provided credentials.
	 *
	 * @since 1.8.0
	 *
	 * @param string $provider Provider key.
	 * @param string $api_key  API key.
	 * @param string $model    Model name.
	 * @return bool|WP_Error True if connection successful, WP_Error on failure.
	 */
	protected function test_ai_connection( $provider, $api_key, $model ) {
		if ( ! class_exists( '\WordPress\AI_Client\AI_Client' ) ) {
			return new WP_Error(
				'ai_client_not_available',
				__( 'AI client library is not available. Please ensure wp-ai-client is installed.', 'plugin-check' )
			);
		}

		if ( empty( $provider ) || empty( $api_key ) || empty( $model ) ) {
			return new WP_Error(
				'ai_missing_parameters',
				__( 'Provider, API key, and model are required to test the connection.', 'plugin-check' )
			);
		}

		try {
			/*
			 * Validate the connection by listing models using the provided API key.
			 * This avoids relying on WP AI Client initialization state and provides
			 * a cheap, provider-native connectivity check.
			 */
			$registry            = AiClient::defaultRegistry();
			$provider_class_name = $registry->getProviderClassName( $provider );
			$model_directory     = $provider_class_name::modelMetadataDirectory();

			$model_directory->setHttpTransporter( HttpTransporterFactory::createTransporter() );
			$model_directory->setRequestAuthentication(
				$this->get_request_authentication_for_provider( $provider, $api_key )
			);

			if ( method_exists( $model_directory, 'hasModelMetadata' ) && $model_directory->hasModelMetadata( $model ) ) {
				return true;
			}

			foreach ( $model_directory->listModelMetadata() as $model_metadata ) {
				if ( $model_metadata->getId() === $model ) {
					return true;
				}
			}

			return new WP_Error(
				'ai_model_not_found',
				__( 'The selected model is not available. Please check your model selection.', 'plugin-check' )
			);
		} catch ( \Throwable $e ) {
			$error_message = $e->getMessage();

			// Provide more user-friendly error messages for common issues.
			if ( false !== strpos( strtolower( $error_message ), 'authentication' ) || false !== strpos( strtolower( $error_message ), 'unauthorized' ) ) {
				return new WP_Error(
					'ai_authentication_failed',
					__( 'Authentication failed. Please check your API key.', 'plugin-check' )
				);
			}

			if ( false !== strpos( strtolower( $error_message ), 'model' ) || false !== strpos( strtolower( $error_message ), 'not found' ) ) {
				return new WP_Error(
					'ai_model_not_found',
					__( 'The selected model is not available. Please check your model selection.', 'plugin-check' )
				);
			}

			return new WP_Error(
				'ai_connection_error',
				sprintf(
					/* translators: %s: Error message */
					__( 'Connection error: %s', 'plugin-check' ),
					$error_message
				)
			);
		}
	}

	/**
	 * Executes an AI request with the provided parameters.
	 *
	 * This method centralizes the common AI request logic to follow DRY principles.
	 *
	 * @since 1.8.0
	 *
	 * @param string        $provider       Provider key.
	 * @param string        $api_key        API key.
	 * @param string        $model          Model ID.
	 * @param string        $prompt         The prompt to send to the AI.
	 * @param callable|null $builder_config Optional callback to configure the PromptBuilder before execution.
	 *                                      Receives the PromptBuilder instance as parameter.
	 * @return array|WP_Error Array with 'text' and 'token_usage' keys, or WP_Error on failure.
	 *                        token_usage contains: prompt_tokens, completion_tokens, total_tokens.
	 */
	protected function execute_ai_request( $provider, $api_key, $model, $prompt, $builder_config = null ) {
		if ( ! class_exists( '\WordPress\AiClient\AiClient' ) ) {
			return new WP_Error(
				'ai_client_not_available',
				__( 'AI client library is not available.', 'plugin-check' )
			);
		}

		try {
			$registry = AiClient::defaultRegistry();
			$registry->setHttpTransporter( HttpTransporterFactory::createTransporter() );
			$registry->setProviderRequestAuthentication(
				$provider,
				$this->get_request_authentication_for_provider( $provider, $api_key )
			);

			$model_instance = $registry->getProviderModel( $provider, $model );

			$builder = new PromptBuilder( $registry, $prompt );
			$builder->usingModel( $model_instance );

			// Allow custom configuration of the builder.
			if ( is_callable( $builder_config ) ) {
				call_user_func( $builder_config, $builder );
			}

			// Generate result to get both text and token usage.
			$result      = $builder->generateTextResult();
			$token_usage = $result->getTokenUsage();

			return array(
				'text'        => $result->toText(),
				'token_usage' => array(
					'prompt_tokens'     => $token_usage->getPromptTokens(),
					'completion_tokens' => $token_usage->getCompletionTokens(),
					'total_tokens'      => $token_usage->getTotalTokens(),
				),
			);

		} catch ( \Throwable $e ) {
			return new WP_Error( 'ai_request_failed', $e->getMessage() );
		}
	}
}
