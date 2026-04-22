<?php
/**
 * GitHub Plugin Updater
 * * Handles the background updates for GitHub-hosted plugins.
 * 
 * Updated: 1.251229
 * 
 */

if ( ! class_exists( 'FWO_GitHub_Updater' ) ) {

    class FWO_GitHub_Updater {

        private $config;
        private $github_data;

        public function __construct( $config ) {
            $this->config = $config;

            add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_update' ) );
            add_filter( 'plugins_api', array( $this, 'plugin_popup' ), 10, 3 );
            add_filter( 'upgrader_post_install', array( $this, 'after_install' ), 10, 3 );
        }

        private function get_github_data() {
            if ( ! empty( $this->github_data ) ) {
                return $this->github_data;
            }

            $response = wp_remote_get( $this->config['api_url'] );

            if ( is_wp_error( $response ) ) {
                return false;
            }

            $this->github_data = json_decode( wp_remote_retrieve_body( $response ) );
            return $this->github_data;
        }

        public function check_update( $transient ) {
            if ( empty( $transient->checked ) ) {
                return $transient;
            }

            $github_data = $this->get_github_data();
            $plugin_slug = plugin_basename( $this->config['plugin_file'] );

            if ( $github_data && version_compare( $github_data->tag_name, $transient->checked[ $plugin_slug ], '>' ) ) {
                $obj              = new stdClass();
                $obj->slug        = $this->config['slug'];
                $obj->new_version = $github_data->tag_name;
                $obj->url         = $this->config['github_url'];
                $obj->package     = $github_data->zipball_url;

                $transient->response[ $plugin_slug ] = $obj;
            }

            return $transient;
        }

        public function plugin_popup( $result, $action, $args ) {
            if ( $action !== 'plugin_information' || $args->slug !== $this->config['slug'] ) {
                return $result;
            }

            $github_data = $this->get_github_data();

            $result = new stdClass();
            $result->name           = $this->config['proper_folder_name'];
            $result->slug           = $this->config['slug'];
            $result->version        = $github_data->tag_name;
            $result->author         = 'thisismyurl';
            $result->homepage       = $this->config['github_url'];
            $result->last_updated   = $github_data->published_at;
            $result->sections       = array( 'description' => $github_data->body );
            $result->download_link  = $github_data->zipball_url;

            return $result;
        }

        public function after_install( $response, $hook_extra, $result ) {
            global $wp_filesystem;
            $install_directory = plugin_dir_path( $this->config['plugin_file'] );
            $wp_filesystem->move( $result['destination'], $install_directory );
            $result['destination'] = $install_directory;
            return $result;
        }
    }
}