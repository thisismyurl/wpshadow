<?php
declare(strict_types=1);

namespace WPShadow\Guardian;

/**
 * Editor Performance Analyzer
 * 
 * Monitors WordPress block editor (Gutenberg) performance in admin.
 * Identifies slow editor loading and performance issues affecting content creation.
 * 
 * Philosophy: Show value (#9) - Optimize content creation experience.
 * 
 * @package WPShadow
 * @subpackage Guardian
 * @since 1.2601.2200
 */
class Editor_Performance_Analyzer {
    
    /**
     * @var float Editor load start time
     */
    private static $editor_start_time = 0;
    
    /**
     * Initialize editor monitoring
     * 
     * @return void
     */
    public static function init(): void {
        // Track editor load time
        add_action('admin_init', [__CLASS__, 'start_editor_timer']);
        add_action('admin_footer', [__CLASS__, 'end_editor_timer']);
        
        // Run hourly analysis
        if (!wp_next_scheduled('wpshadow_analyze_editor_performance')) {
            wp_schedule_event(time(), 'hourly', 'wpshadow_analyze_editor_performance');
        }
        add_action('wpshadow_analyze_editor_performance', [__CLASS__, 'analyze']);
    }
    
    /**
     * Start editor timer
     * 
     * @return void
     */
    public static function start_editor_timer(): void {
        $screen = get_current_screen();
        if (!$screen || $screen->base !== 'post') {
            return;
        }
        
        self::$editor_start_time = microtime(true);
    }
    
    /**
     * End editor timer
     * 
     * @return void
     */
    public static function end_editor_timer(): void {
        if (self::$editor_start_time === 0) {
            return;
        }
        
        $screen = get_current_screen();
        if (!$screen || $screen->base !== 'post') {
            return;
        }
        
        $load_time_ms = (int) ((microtime(true) - self::$editor_start_time) * 1000);
        
        $loads = get_transient('wpshadow_editor_load_times');
        if (!is_array($loads)) {
            $loads = array();
        }
        
        $loads[] = array(
            'time_ms' => $load_time_ms,
            'post_type' => $screen->post_type,
            'timestamp' => time(),
        );
        
        // Keep only last 50 loads
        if (count($loads) > 50) {
            $loads = array_slice($loads, -50);
        }
        
        set_transient('wpshadow_editor_load_times', $loads, WEEK_IN_SECONDS);
    }
    
    /**
     * Analyze editor performance
     * 
     * @return array Analysis results
     */
    public static function analyze(): array {
        $loads = get_transient('wpshadow_editor_load_times');
        
        $results = array(
            'total_loads' => 0,
            'avg_load_time_ms' => 0,
            'slow_loads' => 0,
            'editor_blocks_count' => 0,
            'editor_plugins_count' => 0,
            'is_slow' => false,
        );
        
        // Analyze load times
        if (is_array($loads) && !empty($loads)) {
            $results['total_loads'] = count($loads);
            
            $total_time = array_sum(array_column($loads, 'time_ms'));
            $results['avg_load_time_ms'] = (int) ($total_time / count($loads));
            
            // Count slow loads (>3 seconds)
            foreach ($loads as $load) {
                if ($load['time_ms'] > 3000) {
                    $results['slow_loads']++;
                }
            }
            
            $results['is_slow'] = $results['avg_load_time_ms'] > 2000;
        }
        
        // Count registered blocks
        if (function_exists('WP_Block_Type_Registry::get_instance')) {
            $registry = \WP_Block_Type_Registry::get_instance();
            $results['editor_blocks_count'] = count($registry->get_all_registered());
        }
        
        // Count editor plugins (extensions)
        $results['editor_plugins_count'] = self::count_editor_plugins();
        
        // Set transient for diagnostic
        set_transient('wpshadow_editor_performance', $results, HOUR_IN_SECONDS);
        
        return $results;
    }
    
    /**
     * Count editor plugins/extensions
     * 
     * @return int Count
     */
    private static function count_editor_plugins(): int {
        $count = 0;
        
        // Check for common editor enhancement plugins
        $editor_plugins = array(
            'gutenberg/gutenberg.php',
            'classic-editor/classic-editor.php',
            'advanced-custom-fields/acf.php',
            'ultimate-addons-for-gutenberg/ultimate-addons-for-gutenberg.php',
            'kadence-blocks/kadence-blocks.php',
            'generateblocks/plugin.php',
            'stackable-ultimate-gutenberg-blocks/plugin.php',
            'wp-seo/wp-seo.php', // Yoast adds editor panels
            'wordpress-seo/wp-seo.php',
        );
        
        foreach ($editor_plugins as $plugin) {
            if (is_plugin_active($plugin)) {
                $count++;
            }
        }
        
        return $count;
    }
    
    /**
     * Get summary
     * 
     * @return array Summary data
     */
    public static function get_summary(): array {
        $results = get_transient('wpshadow_editor_performance');
        return is_array($results) ? $results : array(
            'total_loads' => 0,
            'avg_load_time_ms' => 0,
            'is_slow' => false,
        );
    }
    
    /**
     * Clear cached data
     * 
     * @return void
     */
    public static function clear_cache(): void {
        delete_transient('wpshadow_editor_load_times');
        delete_transient('wpshadow_editor_performance');
    }
}
