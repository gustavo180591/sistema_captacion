<?php
namespace Core;

use App\Models\User;

class Controller {
    protected $view;
    protected $auth;
    protected $user = null;
    
    public function __construct() {
        $this->view = new View();
        $this->auth = new Auth();
        
        // Set user if logged in
        if ($this->auth->isLoggedIn()) {
            $this->user = $this->auth->getUser();
            $this->view->set('user', $this->user);
        }
        
        // Set default layout based on user role
        if ($this->user) {
            $layout = strtolower($this->user->rol_nombre) . '.php';
            $this->view->setLayout($layout);
        } else {
            $this->view->setLayout('auth.php');
        }
    }
    
    /**
     * Redirect to a different page
     * @param string $url URL to redirect to
     */
    protected function redirect($url) {
        header('Location: ' . URL_ROOT . '/' . ltrim($url, '/'));
        exit;
    }
    
    /**
     * Check if user is logged in
     * @return bool
     */
    protected function isLoggedIn() {
        return $this->auth->isLoggedIn();
    }
    
    /**
     * Require user to be logged in
     * @param string $redirect URL to redirect to if not logged in
     */
    protected function requireLogin($redirect = 'login') {
        if (!$this->isLoggedIn()) {
            $this->redirect($redirect . '?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        }
    }
    
    /**
     * Require user to have a specific role
     * @param string|array $roles Role(s) to check for
     * @param string $redirect URL to redirect to if check fails
     */
    protected function requireRole($roles, $redirect = '403') {
        $this->requireLogin($redirect);
        
        if (!is_array($roles)) {
            $roles = [$roles];
        }
        
        $hasRole = false;
        foreach ($roles as $role) {
            if ($this->user && $this->user->rol_nombre === $role) {
                $hasRole = true;
                break;
            }
        }
        
        if (!$hasRole) {
            $this->redirect($redirect);
        }
    }
    
    /**
     * Get POST data
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function getPost($key = null, $default = null) {
        if ($key === null) {
            return $_POST;
        }
        return $_POST[$key] ?? $default;
    }
    
    /**
     * Get GET data
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function getQuery($key = null, $default = null) {
        if ($key === null) {
            return $_GET;
        }
        return $_GET[$key] ?? $default;
    }
    
    /**
     * Get uploaded file
     * @param string $key
     * @return array|null
     */
    protected function getFile($key) {
        return $_FILES[$key] ?? null;
    }
    
    /**
     * Send JSON response
     * @param mixed $data
     * @param int $statusCode
     */
    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    /**
     * Send error response
     * @param string $message
     * @param int $statusCode
     * @param array $errors
     */
    protected function error($message, $statusCode = 400, $errors = []) {
        $response = [
            'success' => false,
            'message' => $message
        ];
        
        if (!empty($errors)) {
            $response['errors'] = $errors;
        }
        
        $this->json($response, $statusCode);
    }
    
    /**
     * Send success response
     * @param mixed $data
     * @param string $message
     * @param int $statusCode
     */
    protected function success($data = null, $message = '', $statusCode = 200) {
        $response = [
            'success' => true,
            'message' => $message
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        $this->json($response, $statusCode);
    }
}
