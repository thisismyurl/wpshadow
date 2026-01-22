<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

class Diagnostic_Monitor_JavaScript_Errors_Frequency extends Diagnostic_Base { public static function check(): ?array { return ['id' => 'monitor-js-errors', 'title' => __('JavaScript Error Frequency', 'wpshadow'), 'description' => __('Tracks JS errors from plugins, themes, custom code. Errors break functionality and user experience.', 'wpshadow'), 'severity' => 'high', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/js-debugging/', 'training_link' => 'https://wpshadow.com/training/browser-console/', 'auto_fixable' => false, 'threat_level' => 7]; } }
