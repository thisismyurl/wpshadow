<?php
/**
 * Plugin Dependency Management Diagnostic
 *
 * Validates plugin dependency management and installation order.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Dependency Management Diagnostic
 *
 * Checks for proper plugin dependency management and handling.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Plugin_Dependency_Management extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-dependency-management';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Dependency Management';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates plugin dependency management and installation order';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'plugins';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$details = array();

		// Known plugin dependency mappings
		$dependencies = array(
			'woocommerce/woocommerce.php' => array(
				'children' => array(
					'woo-checkout-field-editor',
					'woocommerce-pdf-invoice',
					'flexible-invoices',
					'woo-bundle-offer',
				),
			),
			'elementor/elementor.php'     => array(
				'children' => array(
					'elementor-pro/elementor-pro.php',
					'woo-elementor-templates',
				),
			),
			'jet-plugins/jet-plugins.php' => array(
				'children' => array(
					'jet-blocks/jet-blocks.php',
					'jet-elements/jet-elements.php',
					'jet-woo-builder/jet-woo-builder.php',
				),
			),
			'acf-pro/acf.php'             => array(
				'children' => array(
					'acf-frontend-form-element/acf-frontend-form-element.php',
					'pods/pods.php',
				),
			),
			'buddypress/bp-loader.php'    => array(
				'children' => array(
					'buddyboss-platform/bp-loader.php',
					'buddypress-profile-photo-filter/index.php',
				),
			),
		);

		$active_plugins = get_option( 'active_plugins', array() );
		$plugin_order = array_flip( $active_plugins );

		// Check for missing parent plugins
		foreach ( $dependencies as $parent => $config ) {
			$parent_installed = false;

			foreach ( $active_plugins as $plugin ) {
				if ( strpos( $plugin, basename( dirname( $parent ) ) ) !== false ) {
					$parent_installed = true;
					break;
				}
			}

			if ( ! $parent_installed ) {
				// Check if any child plugins are installed
				foreach ( $config['children'] as $child ) {
					foreach ( $active_plugins as $plugin ) {
						if ( strpos( $plugin, basename( dirname( $child ) ) ) !== false ) {
							$issues[] = sprintf(
								/* translators: %1$s: child plugin, %2$s: parent plugin */
								__( 'Child plugin %1$s is installed but parent plugin %2$s is not active', 'wpshadow' ),
								basename( dirname( $child ) ),
								basename( dirname( $parent ) )
							);
							break;
						}
					}
				}
			} else {
				// Parent is installed, check order
				$parent_position = PHP_INT_MAX;
				foreach ( $active_plugins as $key => $plugin ) {
					if ( strpos( $plugin, basename( dirname( $parent ) ) ) !== false ) {
						$parent_position = $key;
						break;
					}
				}

				// Check if children load before parent
				foreach ( $config['children'] as $child ) {
					foreach ( $active_plugins as $key => $plugin ) {
						if ( strpos( $plugin, basename( dirname( $child ) ) ) !== false ) {
							if ( $key < $parent_position ) {
								$issues[] = sprintf(
									/* translators: %1$s: child plugin, %2$s: parent plugin */
									__( 'Child plugin %1$s loads before parent plugin %2$s (may cause conflicts)', 'wpshadow' ),
									basename( dirname( $child ) ),
									basename( dirname( $parent ) )
								);
							}
							break;
						}
					}
				}
			}
		}

		// Check for plugin managers that handle dependencies
		$plugin_managers = array(
			'plugin-organizer/plugin-organizer.php',
			'plugin-order-manager/plugin-order-manager.php',
		);

		$has_plugin_manager = false;
		foreach ( $plugin_managers as $manager ) {
			if ( in_array( $manager, $active_plugins, true ) ) {
				$has_plugin_manager = true;
				$details[] = __( 'Plugin dependency manager is installed', 'wpshadow' );
				break;
			}
		}

		// Check for composer-managed plugins
		$composer_file = ABSPATH . 'composer.json';
		if ( file_exists( $composer_file ) ) {
			$details[] = __( 'Composer detected - dependencies may be managed via Composer', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Plugin dependency management issues detected', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/plugin-dependency-management',
				'details'      => array(
					'issues'       => $issues,
					'info'         => $details,
					'has_manager'  => $has_plugin_manager,
					'total_active' => count( $active_plugins ),
				),
			);
		}

		return null;
	}
}
