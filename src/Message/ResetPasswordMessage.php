<?php

namespace App\Message;

    class ResetPasswordMessage {

        private $_password;
        private $_id;
        private $_confirmPassword;


        public function __construct(array $data) {
            if(!empty($data)) {
                $this->hydrate($data);
            }
        }

        public function hydrate($donnees)
        {
            foreach ($donnees as $attribut => $valeur)
            {
                $methode = 'set'.str_replace(' ', '', ucwords(str_replace('_', ' ', $attribut)));
                    
                if (is_callable(array($this, $methode)))
                {
                    $this->$methode($valeur);
                }
            }
        }

        public function password() { return $this->_password;}
        public function confirmPassword() { return $this->_confirmPassword;}
        public function id() { return $this->_id;}
        public function setId($chaine) { $this->_id = $chaine;}
        public function setPassword($chaine) { $this->_password = $chaine;}
        public function setConfirmPassword($chaine) { $this->_confirmPassword = $chaine;}


    }