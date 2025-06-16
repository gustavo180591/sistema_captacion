<?php
namespace Core;

class Hash
{
    /**
     * Hash a password using PHP's password_hash()
     * 
     * @param string $password The password to hash
     * @return string The hashed password
     */
    public static function make($password)
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }
    
    /**
     * Verify a password against a hash
     * 
     * @param string $password The password to verify
     * @param string $hash The hash to verify against
     * @return bool True if the password matches the hash, false otherwise
     */
    public static function check($password, $hash)
    {
        return password_verify($password, $hash);
    }
    
    /**
     * Check if a password needs to be rehashed
     * 
     * @param string $hash The hash to check
     * @return bool True if the password needs to be rehashed, false otherwise
     */
    public static function needsRehash($hash)
    {
        return password_needs_rehash($hash, PASSWORD_BCRYPT, ['cost' => 12]);
    }
    
    /**
     * Generate a random token
     * 
     * @param int $length The length of the token in bytes
     * @return string The generated token
     */
    public static function token($length = 32)
    {
        return bin2hex(random_bytes($length));
    }
}
