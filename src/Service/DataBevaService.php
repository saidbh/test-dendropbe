<?php

namespace App\Service;

use App\Constant\DataConstBeva;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class DataBevaService extends AbstractController
{
    private $_dataConstBeva;

    public function __construct(DataConstBeva $dataConstBeva)
    {
        $this->_dataConstBeva = $dataConstBeva;
    }

    /**
     * Getting location index
     * @return array
     */
    public function Location(): array
    {
        return self::load($this->_dataConstBeva::LOCATION_INDEX_TAB);
    }

    /**
     * Getting health index
     * @return array
     */
    public function HealthIndex(): array
    {
        return self::load($this->_dataConstBeva::HEALTH_INDEX_TAB);
    }

    /**
     * Getting aestheticIndex
     * @return array
     */
    public function aestheticIndex(): array
    {
        return self::load($this->_dataConstBeva::AESTHETIC_INDEX_TAB);
    }

    /**
     * Getting aestheticIndexValue
     * @return array
     */
    public function aestheticIndexValue(): array
    {
        return self::load($this->_dataConstBeva::AESTHETIC_INDEX_VALUE);
    }

    /**
     * Getting healthIndexValue
     * @return array
     */
    public function healthIndexValue(): array
    {
        return self::load($this->_dataConstBeva::HEALTH_INDEX_VALUE);
    }

    private function load(array $data): array
    {
        return [
            'data' => $data,
            'statusCode' => Response::HTTP_OK
        ];
    }
}