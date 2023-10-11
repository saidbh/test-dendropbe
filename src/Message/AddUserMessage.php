<?php

namespace App\Message;

class AddUserMessage
{

    private $_nom,
        $_prenom,
        $_email,
        $_password,
        $_username,
        $_img,
        $_groupe,
        $_isAuth,
        $_profil,

        // COORD FACTURATION
        $_address,
        $_address2,
        $_phoneNumber,
        $_zipCode,
        $_city;

    public $isInit,
        $emailActive,
        $isActive,
        $isRoot;

    public function __construct(Array $data, $emailActive = 0, $isActive = 1, $isInit = 0, $isRoot = 0)
    {

        if (!empty($data)) {
            $this->hydrate($data);
        }
        $this->isInit = $isInit;
        $this->isActive = $isActive;
        $this->emailActive = $emailActive;
        $this->isRoot = $isRoot;
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

    public function email()
    {
        return $this->_email;
    }

    public function nom()
    {
        return $this->_nom;
    }

    public function isAuth()
    {
        return $this->_isAuth;
    }

    public function prenom()
    {
        return $this->_prenom;
    }

    public function username()
    {
        return $this->_username;
    }

    public function password()
    {
        return $this->_password;
    }

    public function groupe()
    {
        return $this->_groupe;
    }

    public function img()
    {
        return $this->_img;
    }

    public function profil()
    {
        return $this->_profil;
    }

    public function address()
    {
        return $this->_address;
    }

    public function address2()
    {
        return $this->_address2;
    }

    public function city()
    {
        return $this->_city;
    }

    public function zipCode()
    {
        return $this->_zipCode;
    }

    public function phoneNumber()
    {
        return $this->_phoneNumber;
    }

    public function setIsAuth(string $chaine)
    {
        $this->_isAuth = $chaine;
    }

    public function setNom(string $nom)
    {
        $this->_nom = $nom;
    }

    public function setPrenom($prenom)
    {
        $this->_prenom = $prenom;
    }

    public function setEmail($email)
    {
        $this->_email = $email;
    }

    public function setPassword($password)
    {
        $this->_password = $password;
    }

    public function setUsername($username)
    {
        $this->_username = $username;
    }

    public function setImg($img)
    {
        $this->_img = $img;
    }

    public function setProfil($profil)
    {
        $this->_profil = $profil;
    }

    public function setGroupe($groupe)
    {
        $this->_groupe = $groupe;
    }

    public function setAddress($chaine)
    {
        $this->_address = $chaine;
    }

    public function setAddress2($chaine)
    {
        $this->_address2 = $chaine;
    }

    public function setCity($chaine)
    {
        $this->_city = $chaine;
    }

    public function setZipCode($chaine)
    {
        $this->_zipCode = $chaine;
    }

    public function setPhoneNumber($chaine)
    {
        $this->_phoneNumber = $chaine;
    }
}
