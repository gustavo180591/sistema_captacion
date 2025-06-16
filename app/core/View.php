<?php
namespace Core;

class View {
    private $data = [];
    private $layout = 'default.php';
    private $viewsPath;
    private $layoutPath;
    
    public function __construct() {
        $this->viewsPath = dirname(__DIR__, 2) . '/app/views/';
        $this->layoutPath = $this->viewsPath . 'layouts/';
    }
    
    /**
     * Set layout for the view
     * @param string $layout
     */
    public function setLayout($layout) {
        $this->layout = $layout;
    }
    
    /**
     * Set view data
     * @param string|array $key
     * @param mixed $value
     */
    public function set($key, $value = null) {
        if (is_array($key)) {
            $this->data = array_merge($this->data, $key);
        } else {
            $this->data[$key] = $value;
        }
    }
    
    /**
     * Render a view
     * @param string $view
     * @param array $data
     */
    public function render($view, $data = []) {
        // Extract data to variables
        extract(array_merge($this->data, $data));
        
        // Start output buffering
        ob_start();
        
        // Include the view file
        $viewFile = $this->viewsPath . str_replace('.', '/', $view) . '.php';
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            throw new \Exception("View file not found: " . $viewFile);
        }
        
        // Get the view content
        $content = ob_get_clean();
        
        // Include the layout
        $layoutFile = $this->layoutPath . $this->layout;
        if (file_exists($layoutFile)) {
            include $layoutFile;
        } else {
            // If no layout, just output the content
            echo $content;
        }
    }
    
    /**
     * Escape output to prevent XSS
     * @param string $value
     * @return string
     */
    public function e($value) {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Include a partial view
     * @param string $partial
     * @param array $data
     */
    public function partial($partial, $data = []) {
        extract(array_merge($this->data, $data));
        $partialFile = $this->viewsPath . 'partials/' . str_replace('.', '/', $partial) . '.php';
        if (file_exists($partialFile)) {
            include $partialFile;
        } else {
            throw new \Exception("Partial view not found: " . $partialFile);
        }
    }
    
    /**
     * Get asset URL
     * @param string $path
     * @return string
     */
    public function asset($path) {
        return URL_ROOT . '/public/' . ltrim($path, '/');
    }
    
    /**
     * Generate URL for a route
     * @param string $path
     * @return string
     */
    public function url($path = '') {
        return URL_ROOT . '/' . ltrim($path, '/');
    }
    
    /**
     * Include CSS file
     * @param string $path
     * @param array $attributes
     * @return string
     */
    public function css($path, $attributes = []) {
        $attributes['rel'] = 'stylesheet';
        $attributes['href'] = $this->asset('css/' . ltrim($path, '/'));
        return $this->htmlElement('link', $attributes);
    }
    
    /**
     * Include JavaScript file
     * @param string $path
     * @param array $attributes
     * @return string
     */
    public function js($path, $attributes = []) {
        $attributes['src'] = $this->asset('js/' . ltrim($path, '/'));
        return $this->htmlElement('script', $attributes, '');
    }
    
    /**
     * Create HTML element
     * @param string $tag
     * @param array $attributes
     * @param string $content
     * @return string
     */
    private function htmlElement($tag, $attributes = [], $content = null) {
        $html = '<' . $tag;
        
        foreach ($attributes as $key => $value) {
            if ($value === true) {
                $html .= ' ' . $key;
            } elseif ($value !== false && $value !== null) {
                $html .= ' ' . $key . '="' . $this->e($value) . '"';
            }
        }
        
        if ($content === null) {
            $html .= ' />';
        } else {
            $html .= '>' . $content . '</' . $tag . '>';
        }
        
        return $html;
    }
}
