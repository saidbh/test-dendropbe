<?php
    namespace App\Service;

    use App\Entity\Essence;
    use App\Entity\Inventaire;

    class FilterMapService {

        public function __construct() {
        }

        /**
         * @param int $especeId
         * @param Inventaire $inventory
         * @return bool
         */
        public static function filterEspece(int $especeId, Inventaire $inventory): bool {
            if(strtoupper($inventory->getType()) === 'ARBRE') {
                return $inventory->getArbre()->getEspece()->getId() === $especeId;
            }
            /** @var Essence[] $essences */
            $essences = $inventory->getEpaysage()->getEssence();
            foreach ($essences as $essence) {
                if($essence->getEspece()->getId() === $especeId) {
                    return true;
                }
            }
            return false;
        }
        public static function filterCodeSiteOrNumSujet(string $codeSite, Inventaire $inventory):bool {
            if($inventory->getArbre()) {
                return str_contains(strtolower($inventory->getArbre()->getCodeSite()), strtolower($codeSite)) || str_contains(strtolower($inventory->getArbre()->getNumSujet()), strtolower($codeSite));
            }
            /** @var Essence[] $essences */
            $essences = $inventory->getEpaysage()->getEssence();
            foreach ($essences as $essence) {
                if(str_contains(strtolower($essence->getCodeSite()), strtolower($codeSite)) || str_contains(strtolower($essence->getNumSujet()), strtolower($codeSite))) {
                    return true;
                }
            }
            return false;
        }

        /**
         * @param Inventaire $inventory
         * @return bool
         */
        public static function filtertreeRemarquable(Inventaire $inventory): bool {
            if($inventory->getArbre()) {
                return count($inventory->getArbre()->getCritere()) >= 1;
            }
            /** @var Essence[] $essences */
            $essences = $inventory->getEpaysage()->getEssence();
            foreach ($essences as $essence) {
                if(count($essence->getCritere()) >= 1) {
                    return true;
                }
            }
            return false;
        }
        /**
         * @param Inventaire $inventory
         * @return bool
         */
        public static function filterBrouillon(Inventaire $inventory): bool {
            return !$inventory->getIsFinished();
        }
    }