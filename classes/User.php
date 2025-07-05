<?php
/**
 * Clase User
 * Manejo de usuarios y autenticación
 */

class User {
    private $db;
    
    public function __construct($database = null) {
        $this->db = $database ?: Database::getInstance();
    }
    
    /**
     * Autenticar usuario
     */
    public function authenticate($username, $password) {
        try {
            $sql = "SELECT * FROM usuarios WHERE (username = ? OR email = ?) AND activo = 1";
            $user = $this->db->fetch($sql, [$username, $username]);
            
            if ($user && password_verify($password, $user['password'])) {
                $this->createSession($user);
                return [
                    'success' => true,
                    'user' => $user
                ];
            }
            
            return [
                'success' => false,
                'error' => 'Credenciales incorrectas'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error de conexión: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Crear sesión de usuario
     */
    private function createSession($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['nombre'] . ' ' . $user['apellido'];
        $_SESSION['logged_in'] = true;
    }
    
    /**
     * Cerrar sesión
     */
    public function logout() {
        session_destroy();
        return true;
    }
    
    /**
     * Verificar si usuario está logueado
     */
    public function isLoggedIn() {
        return isset($_SESSION['user_id']) && $_SESSION['logged_in'] === true;
    }
    
    /**
     * Obtener datos del usuario actual
     */
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'role' => $_SESSION['user_role'],
            'name' => $_SESSION['user_name']
        ];
    }
    
    /**
     * Obtener usuario por ID
     */
    public function getUserById($id) {
        $sql = "SELECT * FROM usuarios WHERE id = ? AND activo = 1";
        return $this->db->fetch($sql, [$id]);
    }
    
    /**
     * Crear nuevo usuario
     */
    public function createUser($data) {
        try {
            $sql = "INSERT INTO usuarios (username, email, password, nombre, apellido, role, activo, fecha_creacion) 
                    VALUES (?, ?, ?, ?, ?, ?, 1, NOW())";
            
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            
            $id = $this->db->insert($sql, [
                $data['username'],
                $data['email'],
                $hashedPassword,
                $data['nombre'],
                $data['apellido'],
                $data['role']
            ]);
            
            return [
                'success' => true,
                'id' => $id
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Verificar permisos del usuario
     */
    public function hasPermission($permission) {
        $user = $this->getCurrentUser();
        if (!$user) return false;
        
        // Definir permisos por rol
        $permissions = [
            'admin' => ['all'],
            'manager' => ['read', 'write', 'search'],
            'user' => ['read', 'search']
        ];
        
        $userRole = $user['role'];
        
        if (isset($permissions[$userRole])) {
            return in_array('all', $permissions[$userRole]) || 
                   in_array($permission, $permissions[$userRole]);
        }
        
        return false;
    }
}
?>