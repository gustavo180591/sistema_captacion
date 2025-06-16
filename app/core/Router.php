<?php
namespace Core;

class Router {
    private $routes = [];
    private $params = [];
    private $namespace = 'App\\Controllers\\';
    
    /**
     * Add a new route
     * @param string $route
     * @param string $controller
     * @param string|array $methods
     * @param array $middleware
     */
    public function add($route, $controller, $methods = 'GET', $middleware = []) {
        if (is_string($methods)) {
            $methods = [$methods];
        }
        
        $this->routes[] = [
            'route' => $route,
            'controller' => $controller,
            'methods' => array_map('strtoupper', $methods),
            'middleware' => $middleware
        ];
    }
    
    /**
     * Add a group of routes with common prefix and middleware
     * @param string $prefix
     * @param callable $callback
     * @param array $middleware
     */
    public function group($prefix, $callback, $middleware = []) {
        $router = new self();
        $callback($router);
        
        foreach ($router->getRoutes() as $route) {
            $this->add(
                $prefix . '/' . ltrim($route['route'], '/'),
                $route['controller'],
                $route['methods'],
                array_merge($middleware, $route['middleware'])
            );
        }
    }
    
    /**
     * Get all registered routes
     * @return array
     */
    public function getRoutes() {
        return $this->routes;
    }
    
    /**
     * Match the current URL to a route
     * @param string $url
     * @param string $method
     * @return bool
     */
    public function match($url, $method) {
        foreach ($this->routes as $route) {
            $pattern = $this->compileRoute($route['route']);
            
            if (preg_match($pattern, $url, $matches) && in_array($method, $route['methods'])) {
                // Store the matched parameters
                foreach ($matches as $key => $value) {
                    if (is_string($key)) {
                        $this->params[$key] = $value;
                    }
                }
                
                // Store the route parameters
                $this->params['route'] = $route;
                
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Compile a route pattern to a regex pattern
     * @param string $route
     * @return string
     */
    private function compileRoute($route) {
        // Escape forward slashes
        $pattern = preg_replace('/\//', '\/', $route);
        
        // Convert URL parameters to named capture groups
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[a-zA-Z0-9_-]+)', $pattern);
        
        // Add start and end delimiters
        return '/^' . $pattern . '$/';
    }
    
    /**
     * Dispatch the request to the appropriate controller
     */
    public function dispatch() {
        $url = $this->getUrl();
        $method = $_SERVER['REQUEST_METHOD'];
        
        if ($this->match($url, $method)) {
            $route = $this->params['route'];
            
            // Apply middleware
            if (!empty($route['middleware'])) {
                $this->applyMiddleware($route['middleware']);
            }
            
            // Parse controller and method
            $controller = $this->getController($route['controller']);
            $method = $this->getMethod($route['controller']);
            
            if (class_exists($controller)) {
                $controllerInstance = new $controller();
                
                if (method_exists($controllerInstance, $method)) {
                    // Get method parameters
                    $methodParams = $this->getMethodParameters($controller, $method);
                    $params = [];
                    
                    // Match URL parameters to method parameters
                    foreach ($methodParams as $param) {
                        $name = $param->getName();
                        if (isset($this->params[$name])) {
                            $params[] = $this->params[$name];
                        } elseif ($param->isDefaultValueAvailable()) {
                            $params[] = $param->getDefaultValue();
                        } else {
                            $params[] = null;
                        }
                    }
                    
                    // Call the controller method
                    call_user_func_array([$controllerInstance, $method], $params);
                    return;
                }
            }
        }
        
        // No route matched or controller/method not found
        $this->notFound();
    }
    
    /**
     * Get the controller class name from route
     * @param string $controller
     * @return string
     */
    private function getController($controller) {
        $parts = explode('@', $controller);
        return $this->namespace . str_replace('/', '\\', $parts[0]);
    }
    
    /**
     * Get the method name from route
     * @param string $controller
     * @return string
     */
    private function getMethod($controller) {
        $parts = explode('@', $controller);
        return $parts[1] ?? 'index';
    }
    
    /**
     * Get method parameters using reflection
     * @param string $class
     * @param string $method
     * @return array
     */
    private function getMethodParameters($class, $method) {
        $reflection = new \ReflectionMethod($class, $method);
        return $reflection->getParameters();
    }
    
    /**
     * Apply middleware
     * @param array $middleware
     */
    private function applyMiddleware($middleware) {
        foreach ($middleware as $m) {
            $parts = explode(':', $m);
            $name = $parts[0];
            $params = isset($parts[1]) ? explode(',', $parts[1]) : [];
            
            $middlewareClass = 'App\\Middleware\\' . ucfirst($name) . 'Middleware';
            
            if (class_exists($middlewareClass)) {
                $middlewareInstance = new $middlewareClass();
                
                if (method_exists($middlewareInstance, 'handle')) {
                    call_user_func_array([$middlewareInstance, 'handle'], $params);
                }
            }
        }
    }
    
    /**
     * Get the current URL
     * @return string
     */
    private function getUrl() {
        $url = $_SERVER['REQUEST_URI'] ?? '/';
        $url = str_replace(parse_url(URL_ROOT, PHP_URL_PATH), '', $url);
        $url = trim($url, '/');
        return $url ?: '/';
    }
    
    /**
     * Handle 404 Not Found
     */
    private function notFound() {
        header("HTTP/1.0 404 Not Found");
        $controller = $this->namespace . 'ErrorController';
        
        if (class_exists($controller)) {
            $controller = new $controller();
            if (method_exists($controller, 'notFound')) {
                $controller->notFound();
                return;
            }
        }
        
        // Default 404 response
        header('Content-Type: text/html');
        echo '<h1>404 Not Found</h1>';
        echo '<p>The requested URL was not found on this server.</p>';
    }
    
    /**
     * Set the default controller namespace
     * @param string $namespace
     */
    public function setNamespace($namespace) {
        $this->namespace = rtrim($namespace, '\\') . '\\';
    }
}
