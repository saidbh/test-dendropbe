<?php

namespace App\Service;

use App\Constant\DataConstInventory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class DataInventoryService extends AbstractController
{
    private $_dataConst;

    public function __construct(DataConstInventory $dataConst)
    {
        $this->_dataConst = $dataConst;
    }

    /**
     * Getting plantations tree or Arbre
     * @return array
     */
    public function plantations(): array
    {
        return self::load($this->_dataConst::PLANTATIONS_TAB);
    }

    public function criteres(): array
    {
        return self::load($this->_dataConst::CRITERE_TAB);
    }

     public function proximites(): array
     {
         return self::load($this->_dataConst::PROXIMITE_TAB);
     }

    public function proximitesDict(): array
    {
        return self::load($this->_dataConst::PROXIMITE_WITH_DICT_TAB);
    }
    public function caracteresPied(): array
    {
        return self::load($this->_dataConst::CARACT_PIED_TAB);
    }
    public function caracteresTronc(): array
    {
        return self::load($this->_dataConst::CARACT_TRONC_TAB);
    }
    public function portArbre(): array
    {
        return self::load($this->_dataConst::PORT_ARBRE_TAB);
    }
    public function stadeDeveloppement(): array
    {
        return self::load($this->_dataConst::STADE_DEV_TAB);
    }
    public function etatSanteTronc(): array
    {
        return self::load($this->_dataConst::ETAT_SAN_TRONC_TAB);
    }
    public function etatSanteHouppier(): array
    {
        return self::load($this->_dataConst::ETAT_SAN_HOUPPIER_TAB);
    }
    public function etatSanteGenerale(): array
    {
        return self::load($this->_dataConst::ETAT_SAN_GENERAL_TAB);
    }
    public function risque(): array
    {
        return self::load($this->_dataConst::RISQUE_TAB);
    }
    public function risqueGeneral(): array
    {
        return self::load($this->_dataConst::RISQUE_GENERAL_TAB);
    }
    public function nuisance(): array
    {
        return self::load($this->_dataConst::NUISANCE_TAB);
    }
    public function tauxFrequent(): array
    {
        return self::load($this->_dataConst::TAUX_FREQ_TAB);
    }
    public function typePassage(): array
    {
        return self::load($this->_dataConst::TYPE_PASSAGE_TAB);
    }
    public function accessibilite(): array
    {
        return self::load($this->_dataConst::ACCESSIBILITE_TAB);
    }
    public function abattage(): array
    {
        return self::load($this->_dataConst::ABATTAGE_TAB);
    }
    public function travauxCollet(): array
    {
        return self::load($this->_dataConst::TRAVAUX_COLLET_TAB);
    }
    public function travauxTronc(): array
    {
        return self::load($this->_dataConst::TRAVAUX_TRONC_TAB);
    }
    public function travauxHouppier(): array
    {
        return self::load($this->_dataConst::TRAVAUX_HOUPPIER_TAB);
    }
    public function dateTravaux(): array
    {
        return self::load($this->_dataConst::DATE_TRAVAUX_TAB);
    }
    public function typeIntervention(): array
    {
        return self::load($this->_dataConst::TYPE_INTERVENTION_TAB);
    }
    private function load(array $data): array
    {
        return [
            'data' => $data,
            'statusCode' => Response::HTTP_OK
        ];
    }
}
