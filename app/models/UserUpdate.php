<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace SoftnCMS\models;

use SoftnCMS\models\User;

/**
 * Description of UserUpdate
 *
 * @author Nicolás Marulanda P.
 */
class UserUpdate {
    
    /**
     *
     * @var User 
     */
    private $user;
    private $userLogin;
    private $userName;
    private $userEmail;
    private $userPass;
    private $userRol;
    private $userUrl;
    private $dataColumns;
    private $prepareStatement;
    
    public function __construct(User $user, $userLogin, $userName, $userEmail, $userPass, $userRol, $userUrl) {
        $this->user = $user;
        $this->userLogin = $userLogin;
        $this->userName = $userName;
        $this->userEmail = $userEmail;
        $this->userPass = $this->encrypt($userPass);
        $this->userRol = $userRol;
        $this->userUrl = $userUrl;
        $this->prepareStatement = [];
        $this->dataColumns = "";
    }
    
    public function update(){
        $db = \SoftnCMS\controllers\DBController::getConnection();
        $table = User::getTableName();
        $columns = '*';
        $where = 'ID = :id';
        $fetch = 'fetchAll';
        
        $this->prepare();
        $this->addPrepareStatement(':id', $this->user->getID(), \PDO::PARAM_INT);
        
        if(!$db->update($table, $this->dataColumns, $where, $this->prepareStatement)){
            return \FALSE;
        }
        
        $count = \count($this->prepareStatement) - 1;
        $prepare = [$this->prepareStatement[$count]];
        $select = $db->select($table, $fetch, $where, $prepare, $columns);
        $user = new User($select[0]);
        return $user;
    }
    
    private function prepare(){
        $this->checkFields($this->user->getUserLogin(), $this->userLogin, User::USER_LOGIN, \PDO::PARAM_STR);
        $this->checkFields($this->user->getUserName(), $this->userName, User::USER_NAME, \PDO::PARAM_STR);
        $this->checkFields($this->user->getUserEmail(), $this->userEmail, User::USER_EMAIL, \PDO::PARAM_STR);
        $this->checkFields($this->user->getUserPass(), $this->userPass, User::USER_PASS, \PDO::PARAM_STR);
        $this->checkFields($this->user->getUserRol(), $this->userRol, User::USER_ROL, \PDO::PARAM_INT);
        $this->checkFields($this->user->getUserUrl(), $this->userUrl, User::USER_URL, \PDO::PARAM_STR);
    }
    
    private function checkFields($oldData, $newData, $column, $dataType){
        if ($oldData != $newData) {
            $parameter = ':' . $column;
            $this->addSetDataSQL($column, $parameter);
            $this->addPrepareStatement($parameter, $newData, $dataType);
        }
    }

    private function addSetDataSQL($key, $data) {
        $this->dataColumns .= empty($this->dataColumns) ? '' : ', ';
        $this->dataColumns .= "$key = $data";
    }

    private function addPrepareStatement($parameter, $value, $dataType) {
        $this->prepareStatement[] = [
            'parameter' => $parameter,
            'value' => $value,
            'dataType' => $dataType,
        ];
    }
    
    /**
     * Metodo que realiza el HASH al valor pasado por parametro.
     * @param string $pass
     * @return string
     */
    public function encrypt($pass) {
        return hash('sha256', $pass . \LOGGED_KEY);
    }
}
