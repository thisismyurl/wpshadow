<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Largest_Contentful_Paint_Delay extends Diagnostic_Base { public static function check(): ?array { return ['id' => 'monitor-lcp-delay', 'title' => __('Largest Contentful Paint Delay', 'wpshadow'), 'description' => __('LCP > 2.5s = poor ranking signal. Indicates image optimization or server response time issues.', 'wpshadow'), 'severity' => 'high', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/lcp-optimization/', 'training_link' => 'https://wpshadow.com/training/image-serving/', 'auto_fixable' => false, 'threat_level' => 8]; } 
}