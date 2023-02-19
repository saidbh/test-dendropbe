<?php

namespace App\Service;

use App\Constant\DataConstZone;
use Symfony\Component\HttpFoundation\Response;

class DataZoneService
{

    public function __construct(DataConstZone $dataConstZone)
    {
        $this->_dataConstZone = $dataConstZone;
    }

    /**
     * Getting caractere zone
     * @return array
     */
    public function caractereZone(): array
    {
        return self::load($this->_dataConstZone::CARACT_TAB);
    }

    /**
     * Getting etat houppier zone
     * @return array
     */
    public function houppierZone(): array
    {
        return self::load($this->_dataConstZone::HOUPPIER_TAB);
    }

    /**
     * Getting etat generale zone
     * @return array
     */
    public function etatGeneralZone(): array
    {
        return self::load($this->_dataConstZone::ETAT_GENERAL_TAB);
    }

    /**
     * Getting list travaux Zone
     * @return array
     */
    public function travauxZone(): array
    {
        return self::load($this->_dataConstZone::TRAVAUX_TAB);
    }


    private function load(array $data): array
    {
        return [
            'data' => $data,
            'statusCode' => Response::HTTP_OK
        ];
    }
}