<?php

namespace App\Message;

class AddDroitMessage {
    
    private $name;
    public $isInit;

    public function __construct(string $name, $isInit = 0){
        $this->name = $name;
        $this->isInit = $isInit;
    }

    public function getName(){
        return $this->name;
    }

    public function setName(string $name){
        $this->name = $name;
    }
    
}