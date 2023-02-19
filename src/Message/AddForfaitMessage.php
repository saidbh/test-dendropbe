<?php

namespace App\Message;

class AddForfaitMessage
{

    private $_name;

    public $isInit;

    public function __construct(Array $data, $isInit = 0)
    {
        if (!empty($data)) {
            $this->hydrate($data);
        }
        $this->isInit = $isInit;
    }

    public function hydrate($donnees)
    {
        foreach ($donnees as $attribut => $valeur) {
            $methode = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $attribut)));

            if (is_callable(array($this, $methode))) {
                $this->$methode($valeur);
            }
        }
    }

    public function name()
    {
        return $this->_name;
    }

    public function codeForfait()
    {
        return $this->_codeForfait;
    }

    public function setName($name)
    {
        $this->_name = $name;
    }

    public function setCodeForfait($name)
    {
        $this->_codeForfait = $name;
    }

}
