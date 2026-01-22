<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_PHP_Version_Compatibility extends Diagnostic_Base .php {
    public static function check(): ?array {
        return ['id' => 'monitor-php-compat', 'title' => __('PHP Version Compatibility Issues', 'wpshadow'), 'description' => __('Alerts when PHP version incompatible with plugins/themes. Delays in PHP upgrades cause deprecation warnings, broken features.', 'wpshadow'), 'severity' => 'high', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/php-compatibility/', 'training_link' => 'https://wpshadow.com/training/php-upgrades/', 'auto_fixable' => false, 'threat_level' => 7];
    }
}
