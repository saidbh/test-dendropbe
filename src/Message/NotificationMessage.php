<?php

namespace App\Message;

class NotificationMessage
{
    public CONST TYPE_ABO_RENEW = 'ABO_AFTER_RENEW';
    public CONST TYPE_BEFORE_RENEW = 'ABO_BEFORE_RENEW';
    public CONST TYPE_UNSUBSCRIPTION_ABO = 'DELETE_ABO';
    //
    private $_id,
        $_groupeId,
        $_type,
        $_status = 0;

    public function __construct(array $data)
    {
        $this->hydrate($data);
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

    public function id()
    {
        return $this->_id;
    }

    public function groupeId()
    {
        return $this->_groupeId;
    }

    public function type()
    {
        return $this->_type;
    }

    public function status()
    {
        return $this->_status;
    }

    public function setId($chaine)
    {
        $this->_id = $chaine;
    }

    public function setGroupeId($chaine)
    {
        $this->_groupeId = $chaine;
    }

    public function setType($chaine)
    {
        $this->_type = $chaine;
    }

    public function setStatus($chaine)
    {
        $this->_status = $chaine;
    }
}
