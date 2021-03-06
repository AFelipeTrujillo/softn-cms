<?php

/**
 * Modulo del modelo del formulario de inicio de sesión.
 * Gestiona el inicio de sesión.
 */

namespace SoftnCMS\models;

use SoftnCMS\models\admin\User;

/**
 * Clase que gestiona el inicio de sesión.
 *
 * @author Nicolás Marulanda P.
 */
class Login {

    /** @var string Nombre de usuario. */
    private $username;

    /** @var string Contraseña. */
    private $password;

    /** @var bool Recordar sesión. */
    private $userRememberMe;

    /**
     * Constructor.
     * @param string $username Nombre de usuario.
     * @param string $password Contraseña.
     * @param bool $userRememberMe Recordar sesión.
     */
    public function __construct($username, $password, $userRememberMe) {
        $this->username = $username;
        $this->password = User::encrypt($password);
        $this->userRememberMe = $userRememberMe;
    }

    /**
     * Metodo que comprueba si ha iniciado sesión.
     * @return bool Si es FALSE, el usuario no tiene un sesión activa 
     * ni tiene la opción de recordar sesión
     */
    public static function isLogin() {
        if (!isset($_SESSION['usernameID']) && !isset($_COOKIE['userRememberMe'])) {
            return \FALSE;
        }

        if (!isset($_SESSION['usernameID']) && isset($_COOKIE['userRememberMe'])) {
            $_SESSION['usernameID'] = $_COOKIE['userRememberMe'];
        }

        return self::checkSesion();
    }

    /**
     * Metodo que realiza el proceso de inicio de sesión.
     * @global string $urlSite
     * @return bool Retorna FALSE en caso de error.
     */
    public function login() {
        $user = User::selectByLogin($this->username);

        //Se comprueba si el nombre de usuario existe y si su contraseña es correcta.
        if ($user !== \FALSE && $user->getUserPass() == $this->password) {
            $_SESSION['usernameID'] = $user->getID();

            if ($this->userRememberMe) {
                setcookie('userRememberMe', $user->getID(), \COOKIE_EXPIRE);
            }
            
            return \TRUE;
        }

        return \FALSE;
    }
    
    /**
     * Metodo que comprueba si el valor de la variable de sesión corresponde 
     * a un usuario valido.
     * @return bool
     */
    private static function checkSesion(){
        $user = User::selectByID($_SESSION['usernameID']);
        
        if($user !== \FALSE){
            return \TRUE;
        }
        
        unset($_SESSION['usernameID']);
        
        return \FALSE;
    }

}
