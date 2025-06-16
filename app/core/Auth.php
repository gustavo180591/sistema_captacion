<?php
namespace Core;

use App\Models\User;

class Auth {
    private $user = null;
    
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if ($this->isLoggedIn()) {
            $this->user = $this->getUserFromSession();
        }
    }
    
    /**
     * Attempt to log in a user
     * @param string $email
     * @param string $password
     * @return bool
     */
    public function login($email, $password) {
        $user = User::findByEmail($email);
        
        if ($user && password_verify($password, $user->password)) {
            $this->setUserSession($user);
            return true;
        }
        
        return false;
    }
    
    /**
     * Log out the current user
     */
    public function logout() {
        // Unset all session variables
        $_SESSION = [];
        
        // Delete the session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Destroy the session
        session_destroy();
        
        $this->user = null;
    }
    
    /**
     * Check if a user is logged in
     * @return bool
     */
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    /**
     * Get the current user
     * @return User|null
     */
    public function getUser() {
        return $this->user;
    }
    
    /**
     * Check if the current user has a specific role
     * @param string $role
     * @return bool
     */
    public function hasRole($role) {
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        return $this->user->rol_nombre === $role;
    }
    
    /**
     * Set the user session after successful login
     * @param User $user
     */
    private function setUserSession($user) {
        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_role'] = $user->rol_nombre;
        $_SESSION['last_activity'] = time();
        
        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);
    }
    
    /**
     * Get the user from the session
     * @return User|null
     */
    private function getUserFromSession() {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }
        
        return User::findById($_SESSION['user_id']);
    }
    
    /**
     * Check if the current user is an admin
     * @return bool
     */
    public function isAdmin() {
        return $this->hasRole('Administrador');
    }
    
    /**
     * Check if the current user is an evaluator
     * @return bool
     */
    public function isEvaluator() {
        return $this->hasRole('Evaluador');
    }
    
    /**
     * Check if the current user is an athlete
     * @return bool
     */
    public function isAthlete() {
        return $this->hasRole('Atleta');
    }
    
    /**
     * Check if the current user can access a specific resource
     * @param string $resource
     * @param int $resourceId
     * @return bool
     */
    public function canAccess($resource, $resourceId) {
        if ($this->isAdmin()) {
            return true;
        }
        
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        // Add specific access control rules here
        switch ($resource) {
            case 'atleta':
                if ($this->isEvaluator()) {
                    // Evaluators can only access their own athletes
                    $atleta = \App\Models\Atleta::find($resourceId);
                    return $atleta && $atleta->evaluador_id == $this->user->id;
                } elseif ($this->isAthlete()) {
                    // Athletes can only access their own data
                    return $this->user->id == $resourceId;
                }
                break;
                
            case 'sesion':
                if ($this->isEvaluator()) {
                    // Evaluators can only access their own sessions
                    $sesion = \App\Models\SesionEvaluacion::find($resourceId);
                    return $sesion && $sesion->evaluador_id == $this->user->id;
                }
                break;
        }
        
        return false;
    }
}
