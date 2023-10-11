<?php

    namespace App\Message;

    Class ChangePasswordMessage {

        private $nouveau,
                $ancien,
                $id,
                $confirm;

        public function __construct(Array $data){
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

        public function nouveau(){ return $this->nouveau;}
        public function ancien() { return $this->ancien;}
        public function confirm() { return $this->confirm;}
        public function id() { return $this->id;}


        public function setNouveau($chaine) {
            $this->nouveau = $chaine;
        }

        public function setId($id) {
            $this->id = $id;
        }

        public function setAncien($chaine){
            $this->ancien = $chaine;
        }
        public function setConfirm($chaine){
            $this->confirm = $chaine;
        }

    }