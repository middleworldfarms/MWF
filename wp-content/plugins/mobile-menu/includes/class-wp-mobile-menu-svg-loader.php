<?php

if ( ! class_exists( 'WP_Mobile_Menu' ) ) {
	die();
}
class SVGLoader {
    private $svgPath;
    
    public function __construct($svgPath = 'icons/') {
        $this->svgPath = rtrim($svgPath, '/') . '/';
    }
    
    public function load($filename) {
        $fullPath = $this->svgPath . $filename;
        
        if (!file_exists($fullPath)) {
            throw new Exception("SVG file not found: {$fullPath}");
			die("SVG file not found: {$fullPath}");
        }
		        
        return file_get_contents($fullPath);
    }
    
    public function loadWithColor($filename, $color = '#000000') {
        $svg = $this->load($filename);
        
        // Replace fill attributes with new color
        $svg = preg_replace('/fill="[^"]*"/', "fill=\"{$color}\"", $svg);
        
        // Also replace fill: in style attributes
        $svg = preg_replace('/fill:\s*[^;]+;?/', "fill: {$color};", $svg);
        
        return $svg;
    }
    
    public function loadWithOptions($filename, $options = []) {
        $svg = $this->load($filename);
        
        // Change color
        if (isset($options['color'])) {
            $svg = preg_replace('/fill="[^"]*"/', "fill=\"{$options['color']}\"", $svg);
        }
        
        // Change size
        if (isset($options['width']) || isset($options['height'])) {
            $width = $options['width'] ?? 'auto';
            $height = $options['height'] ?? 'auto';
            
            $svg = preg_replace('/width="[^"]*"/', "width=\"{$width}\"", $svg);
            $svg = preg_replace('/height="[^"]*"/', "height=\"{$height}\"", $svg);
        }
        
        // Add CSS class
        if (isset($options['class'])) {
            $svg = preg_replace('/<svg/', "<svg class=\"{$options['class']}\"", $svg);
        }
        
        return $svg;
    }
}