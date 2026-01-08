<?php
/**
 * Modules view
 *
 * Renders the modules table with hubs and spokes, including install/activate/update actions.
 * Assumes controller passes `$hub_modules` and `$spoke_modules` arrays and computes activation states.
 *
 * @package core-support-thisismyurl
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="wrap timu-modules-view">
    <h1 class="wp-heading-inline"><?php esc_html_e( 'Modules', 'core-support-thisismyurl' ); ?></h1>
    <span class="dashicons dashicons-editor-help" aria-label="<?php esc_attr_e( 'Modules help', 'core-support-thisismyurl' ); ?>" title="<?php esc_attr_e( 'Install or update modules from the catalog; activate/deactivate per site or network. Network Active items can only be deactivated from Network Admin.', 'core-support-thisismyurl' ); ?>">
        <span class="screen-reader-text"><?php esc_html_e( 'Install or update modules from the catalog; activate/deactivate per site or network. Network Active items can only be deactivated from Network Admin.', 'core-support-thisismyurl' ); ?></span>
    </span>
    <?php $override_allowed = class_exists( 'TIMU_Vault' ) ? TIMU_Vault::site_override_allowed() : true; ?>
    <div class="timu-dashboard-stats">
        <?php
        // Fallback counts when controller variables are missing.
        $total_fallback   = (int) ( ( isset( $hub_modules ) ? count( $hub_modules ) : 0 ) + ( isset( $spoke_modules ) ? count( $spoke_modules ) : 0 ) );
        $updates_fallback = 0;
        if ( ! empty( $hub_modules ) && is_array( $hub_modules ) ) {
            foreach ( $hub_modules as $m ) {
                if ( ! empty( $m['update_available'] ) ) {
                    $updates_fallback++;
                }
            }
        }
        if ( ! empty( $spoke_modules ) && is_array( $spoke_modules ) ) {
            foreach ( $spoke_modules as $m ) {
                if ( ! empty( $m['update_available'] ) ) {
                    $updates_fallback++;
                }
            }
        }
        ?>
        <div class="timu-stat-card" role="group" aria-label="<?php esc_attr_e( 'Total modules', 'core-support-thisismyurl' ); ?>">
            <div class="timu-stat-icon">
                <span class="dashicons dashicons-admin-plugins"></span>
            </div>
            <div class="timu-stat-content">
                <div class="timu-stat-value"><?php echo esc_html( number_format_i18n( (int) ( $total_count ?? $total_fallback ) ) ); ?></div>
                <div class="timu-stat-label"><?php esc_html_e( 'Total', 'core-support-thisismyurl' ); ?></div>
            </div>
        </div>

        <div class="timu-stat-card" role="group" aria-label="<?php esc_attr_e( 'Enabled modules', 'core-support-thisismyurl' ); ?>">
            <div class="timu-stat-icon timu-stat-enabled">
                <span class="dashicons dashicons-yes-alt"></span>
            </div>
            <div class="timu-stat-content">
                <div class="timu-stat-value"><?php echo esc_html( number_format_i18n( (int) ( $enabled_count ?? 0 ) ) ); ?></div>
                <div class="timu-stat-label"><?php esc_html_e( 'Enabled', 'core-support-thisismyurl' ); ?></div>
            </div>
        </div>

        <div class="timu-stat-card" role="group" aria-label="<?php esc_attr_e( 'Available modules', 'core-support-thisismyurl' ); ?>">
            <div class="timu-stat-icon timu-stat-available">
                <span class="dashicons dashicons-plus-alt"></span>
            </div>
            <div class="timu-stat-content">
                <div class="timu-stat-value"><?php echo esc_html( number_format_i18n( (int) ( $available_count ?? 0 ) ) ); ?></div>
                <div class="timu-stat-label"><?php esc_html_e( 'Available', 'core-support-thisismyurl' ); ?></div>
            </div>
        </div>

        <div class="timu-stat-card" role="group" aria-label="<?php esc_attr_e( 'Updates available', 'core-support-thisismyurl' ); ?>">
            <div class="timu-stat-icon timu-stat-update">
                <span class="dashicons dashicons-update"></span>
            </div>
            <div class="timu-stat-content">
                <div class="timu-stat-value"><?php echo esc_html( number_format_i18n( (int) ( $updates_count ?? $updates_fallback ) ) ); ?></div>
                <div class="timu-stat-label"><?php esc_html_e( 'Updates', 'core-support-thisismyurl' ); ?></div>
            </div>
        </div>

        <div class="timu-stat-card" role="group" aria-label="<?php esc_attr_e( 'Hubs', 'core-support-thisismyurl' ); ?>">
            <div class="timu-stat-icon timu-stat-hub">
                <span class="dashicons dashicons-networking"></span>
            </div>
            <div class="timu-stat-content">
                <div class="timu-stat-value"><?php echo esc_html( number_format_i18n( (int) ( $hubs_count ?? ( isset( $hub_modules ) ? count( $hub_modules ) : 0 ) ) ) ); ?></div>
                <div class="timu-stat-label"><?php esc_html_e( 'Hubs', 'core-support-thisismyurl' ); ?></div>
            </div>
        </div>

        <div class="timu-stat-card" role="group" aria-label="<?php esc_attr_e( 'Spokes', 'core-support-thisismyurl' ); ?>">
            <div class="timu-stat-icon timu-stat-spoke">
                <span class="dashicons dashicons-admin-tools"></span>
            </div>
            <div class="timu-stat-content">
                <div class="timu-stat-value"><?php echo esc_html( number_format_i18n( (int) ( $spokes_count ?? ( isset( $spoke_modules ) ? count( $spoke_modules ) : 0 ) ) ) ); ?></div>
                <div class="timu-stat-label"><?php esc_html_e( 'Spokes', 'core-support-thisismyurl' ); ?></div>
            </div>
        </div>
    </div>

    <?php
    // Group spokes under Image Support hub by convention.
    $groups = array();
    foreach ( $hub_modules as $hub ) {
        $groups[ $hub['slug'] ] = array(
            'hub'    => $hub,
            'spokes' => array(),
        );
    }
    foreach ( $spoke_modules as $spoke ) {
        $parent = 'image-support-thisismyurl';
        if ( isset( $groups[ $parent ] ) ) {
            $groups[ $parent ]['spokes'][] = $spoke;
        } else {
            // Fallback: attach to first hub if Image Support missing.
            $first = array_key_first( $groups );
            if ( $first ) {
                $groups[ $first ]['spokes'][] = $spoke;
            }
        }
    }
    ?>

    <div class="timu-modules-grid">
        <?php if ( empty( $groups ) ) : ?>
            <div class="timu-no-modules">
                <span class="dashicons dashicons-info"></span>
                <p><?php esc_html_e( 'No modules found.', 'core-support-thisismyurl' ); ?></p>
            </div>
        <?php else : ?>
            <table class="widefat fixed striped timu-modules-table">
                <thead>
                <tr>
                    <th><?php esc_html_e( 'Module', 'core-support-thisismyurl' ); ?></th>
                    <th><?php esc_html_e( 'Requires', 'core-support-thisismyurl' ); ?></th>
                    <th><?php esc_html_e( 'Status', 'core-support-thisismyurl' ); ?></th>
                    <th><?php esc_html_e( 'Version', 'core-support-thisismyurl' ); ?></th>
                    <th><?php esc_html_e( 'Author', 'core-support-thisismyurl' ); ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ( $groups as $group ) : ?>
                    <?php
                    $module = $group['hub'];
                    $slug   = $module['slug'];
                    // Real-time plugin state detection.
                    $plugin_file      = WP_PLUGIN_DIR . '/' . $slug . '/' . $slug . '.php';
                    $installed        = file_exists( $plugin_file );
                    $plugin_base      = $slug . '/' . $slug . '.php';
                    $is_network_active = is_multisite() && is_plugin_active_for_network( $plugin_base );
                    $is_enabled       = $installed && ( is_plugin_active( $plugin_base ) || $is_network_active );
                    $update_available = ! empty( $module['update_available'] );
                    $type_class       = 'timu-type-hub';
                    $status_class     = $installed ? ( $is_enabled ? 'timu-module-enabled' : 'timu-module-disabled' ) : 'timu-module-available';
                    ?>
                    <tr class="timu-module-card <?php echo esc_attr( $type_class . ' ' . $status_class ); ?>" data-type="hub" data-group="<?php echo esc_attr( $slug ); ?>" data-status="<?php echo esc_attr( $installed ? ( $update_available ? 'update' : 'installed' ) : 'available' ); ?>">
                        <td class="timu-module-name">
                            <?php if ( $module['slug'] !== 'core-support-thisismyurl' ) : ?>
                                <button type="button" class="button-link timu-hub-toggle" data-group="<?php echo esc_attr( $module['slug'] ); ?>" aria-expanded="true" aria-label="<?php esc_attr_e( 'Toggle spokes', 'core-support-thisismyurl' ); ?>">
                                    <span class="dashicons dashicons-arrow-down-alt2"></span>
                                </button>
                            <?php endif; ?>
                            <strong><a href="<?php echo esc_url( $module['uri'] ?? '#' ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $module['name'] ); ?></a></strong>
                            <br><span class="description"><?php echo esc_html( $module['description'] ); ?></span>
                        </td>
                        <td>
                            <?php
                            $requires = ( $module['slug'] === 'core-support-thisismyurl' ) ? 'None' : 'Core ' . ( $module['requires_core'] ?? '' );
                            echo esc_html( $requires === 'None' ? '-' : $requires );
                            ?>
                        </td>
                        <td>
                            <?php
                            $dependents_active = array_filter(
                                $group['spokes'],
                                static function ( $spoke ) {
                                    $slug = $spoke['slug'] ?? '';
                                    return $slug && is_plugin_active( $slug . '/' . $slug . '.php' );
                                }
                            );
                            ?>
                            <?php if ( ! $installed ) : ?>
                                <a href="#" class="timu-btn-install-activate" data-slug="<?php echo esc_attr( $module['slug'] ); ?>"><?php esc_html_e( 'Install and Activate', 'core-support-thisismyurl' ); ?></a>
                            <?php elseif ( $is_enabled && ( $is_network_active && ! is_network_admin() ) ) : ?>
                                <?php esc_html_e( 'Active', 'core-support-thisismyurl' ); ?>
                                <span class="timu-badge timu-badge-network" aria-label="<?php esc_attr_e( 'Network Active', 'core-support-thisismyurl' ); ?>"><?php esc_html_e( 'Network Active', 'core-support-thisismyurl' ); ?></span>
                                <?php if ( ! $override_allowed ) : ?>
                                    <br><small><?php esc_html_e( 'Final (no site overrides)', 'core-support-thisismyurl' ); ?></small>
                                <?php else : ?>
                                    <br><small><?php esc_html_e( 'Site settings override allowed', 'core-support-thisismyurl' ); ?></small>
                                <?php endif; ?>
                            <?php elseif ( $is_enabled && ! empty( $dependents_active ) ) : ?>
                                <?php esc_html_e( 'Active', 'core-support-thisismyurl' ); ?><br>
                                <small>-</small>
                            <?php elseif ( $is_enabled && $is_network_active && is_network_admin() ) : ?>
                                <?php esc_html_e( 'Active', 'core-support-thisismyurl' ); ?>
                                <span class="timu-badge timu-badge-network" aria-label="<?php esc_attr_e( 'Network Active', 'core-support-thisismyurl' ); ?>"><?php esc_html_e( 'Network Active', 'core-support-thisismyurl' ); ?></span>
                                <br><small><a href="#" class="timu-action-link timu-deactivate-network" data-slug="<?php echo esc_attr( $module['slug'] ); ?>"><?php esc_html_e( '(Deactivate Network)', 'core-support-thisismyurl' ); ?></a></small>
                            <?php else : ?>
                                <?php esc_html_e( 'Installed', 'core-support-thisismyurl' ); ?><br>
                                <small><a href="#" class="timu-action-link timu-activate" data-slug="<?php echo esc_attr( $module['slug'] ); ?>"><?php esc_html_e( 'activate', 'core-support-thisismyurl' ); ?></a></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php echo esc_html( $module['version'] ); ?>
                            <?php if ( $update_available && ! empty( $module['download_url'] ) ) : ?>
                                <br><small><a href="#" class="timu-btn-update" data-slug="<?php echo esc_attr( $module['slug'] ); ?>"><?php esc_html_e( 'Update', 'core-support-thisismyurl' ); ?></a></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $author_github = 'https://github.com/thisismyurl';
                            if ( ! empty( $module['author_uri'] ) && strpos( $module['author_uri'], 'github.com' ) !== false ) {
                                $author_github = $module['author_uri'];
                            }
                            ?>
                            <a href="<?php echo esc_url( $author_github ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $module['author'] ); ?></a>
                        </td>
                    </tr>

                    <?php if ( ! empty( $group['spokes'] ) ) : ?>
                        <?php foreach ( $group['spokes'] as $module ) : ?>
                            <?php
                            $slug = $module['slug'];
                            // Real-time plugin state detection.
                            $plugin_file      = WP_PLUGIN_DIR . '/' . $slug . '/' . $slug . '.php';
                            $installed        = file_exists( $plugin_file );
                            $plugin_base      = $slug . '/' . $slug . '.php';
                            $is_network_active = is_multisite() && is_plugin_active_for_network( $plugin_base );
                            $is_enabled       = $installed && ( is_plugin_active( $plugin_base ) || $is_network_active );
                            $update_available = ! empty( $module['update_available'] );
                            $type_class       = 'timu-type-spoke';
                            $status_class     = $installed ? ( $is_enabled ? 'timu-module-enabled' : 'timu-module-disabled' ) : 'timu-module-available';
                            ?>
                            <tr class="timu-module-card timu-child-module <?php echo esc_attr( $type_class . ' ' . $status_class ); ?>" data-type="spoke" data-parent="<?php echo esc_attr( $group['hub']['slug'] ?? '' ); ?>" data-status="<?php echo esc_attr( $installed ? ( $update_available ? 'update' : 'installed' ) : 'available' ); ?>">
                                <td class="timu-module-name">
                                    <span class="timu-indent">&#8212; </span>
                                    <a href="<?php echo esc_url( $module['uri'] ?? '#' ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $module['name'] ); ?></a>
                                    <br><span class="description"><?php echo esc_html( $module['description'] ); ?></span>
                                </td>
                                <td>
                                    <?php
                                    $requires = $group['hub']['name'] ?? 'Unknown';
                                    echo esc_html( $requires === 'None' ? '-' : $requires );
                                    ?>
                                </td>
                                <td>
                                    <?php if ( ! $installed ) : ?>
                                        <a href="#" class="timu-btn-install-activate" data-slug="<?php echo esc_attr( $module['slug'] ); ?>"><?php esc_html_e( 'Install and Activate', 'core-support-thisismyurl' ); ?></a>
                                    <?php elseif ( $is_enabled && $is_network_active && is_network_admin() ) : ?>
                                        <?php esc_html_e( 'Active', 'core-support-thisismyurl' ); ?>
                                        <span class="timu-badge timu-badge-network" aria-label="<?php esc_attr_e( 'Network Active', 'core-support-thisismyurl' ); ?>"><?php esc_html_e( 'Network Active', 'core-support-thisismyurl' ); ?></span>
                                        <br><small><a href="#" class="timu-action-link timu-deactivate-network" data-slug="<?php echo esc_attr( $module['slug'] ); ?>"><?php esc_html_e( '(Deactivate Network)', 'core-support-thisismyurl' ); ?></a></small>
                                    <?php elseif ( $is_enabled && $is_network_active && ! is_network_admin() ) : ?>
                                        <?php esc_html_e( 'Active', 'core-support-thisismyurl' ); ?>
                                        <span class="timu-badge timu-badge-network" aria-label="<?php esc_attr_e( 'Network Active', 'core-support-thisismyurl' ); ?>"><?php esc_html_e( 'Network Active', 'core-support-thisismyurl' ); ?></span>
                                        <?php if ( ! $override_allowed ) : ?>
                                            <br><small><?php esc_html_e( 'Final (no site overrides)', 'core-support-thisismyurl' ); ?></small>
                                        <?php else : ?>
                                            <br><small><?php esc_html_e( 'Site settings override allowed', 'core-support-thisismyurl' ); ?></small>
                                        <?php endif; ?>
                                    <?php else : ?>
                                        <?php esc_html_e( 'Installed', 'core-support-thisismyurl' ); ?><br>
                                        <small><a href="#" class="timu-action-link timu-activate" data-slug="<?php echo esc_attr( $module['slug'] ); ?>"><?php esc_html_e( 'activate', 'core-support-thisismyurl' ); ?></a></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo esc_html( $module['version'] ); ?>
                                    <?php if ( $update_available && ! empty( $module['download_url'] ) ) : ?>
                                        <br><small><a href="#" class="timu-btn-update" data-slug="<?php echo esc_attr( $module['slug'] ); ?>"><?php esc_html_e( 'Update', 'core-support-thisismyurl' ); ?></a></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $author_github = 'https://github.com/thisismyurl';
                                    if ( ! empty( $module['author_uri'] ) && strpos( $module['author_uri'], 'github.com' ) !== false ) {
                                        $author_github = $module['author_uri'];
                                    }
                                    ?>
                                    <a href="<?php echo esc_url( $author_github ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $module['author'] ); ?></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
