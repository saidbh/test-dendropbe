<?php

    namespace App\Message;

    class AddProfilMessage {

        private $name;
        private $droit;
        private $groupeType;
        public $isInit;

        public function __construct(string $name, $groupeType, $droit, $isInit = 0){
            // VARIABLE
            $this->setName($name);
            $this->isInit = $isInit;
            $this->droit = $droit;
            $this->setGroupeType($groupeType);
        }

        public function getName(){
            return $this->name;
        }
        public function setName($name){
            $this->name = $name;
        }
        public function getGroupeType(){
            return $this->groupeType;
        }
        public function setGroupeType($chaine){
            $this->groupeType = $chaine;
        }
        public function getDroit() {
            return $this->droit;
        }
        public function setDroit($droit){
            $this->droit = $droit;
        }
    }