<?php

namespace App\Service;

use App\IService\IPaginate;
use Pagerfanta\Adapter\AdapterInterface;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Response;

class PaginatedService implements IPaginate
{
    private $_items;
    private $_total;
    private $_pages;
    private $_adapter;
    private $_perPage; // Total items per Page
    private $_currentPage; //

    /**
     * @param AdapterInterface $adpater
     */
    public function __construct(AdapterInterface $adpater)
    {
        $this->_adapter = $adpater;
    }

    function paginate($page = 1, $maxPage = 30): void
    {
        $pagerFanta = new Pagerfanta($this->_adapter);
        $pagerFanta->setMaxPerPage($maxPage);
        $pagerFanta->setCurrentPage($page);
        // Affect all datas
        $this->_items = $pagerFanta->getCurrentPageResults();
        $this->_total = $pagerFanta->getNbResults();
        $this->_pages = $pagerFanta->getNbPages();
        $this->_currentPage = $pagerFanta->getCurrentPage();
        $this->_perPage = $pagerFanta->getMaxPerPage();
    }

    /**
     * @return array
     */
    public function getItems(): array
    {
        return $this->_items;
    }

    /**
     * @return int
     */
    public function getTotal(): int
    {
        return $this->_total;
    }

    /**
     * @return int
     */
    public function getPages(): int
    {
        return $this->_pages;
    }

    /**
     * @return int
     */
    public function getCurrentPage(): int
    {
        return $this->_currentPage;
    }

    /**
     * @return int
     */
    public function getPerPage(): int
    {
        return $this->_perPage;
    }

    /**
     * @param array $data
     * @param $page
     * @param $numberPage
     * @return array
     */
    public static function paginateList(array $data, $page, $numberPage): array
    {
        $adapter = new ArrayAdapter($data);
        $paginatedService = new PaginatedService($adapter);
        $paginatedService->paginate($page, $numberPage);

        return [
            'data' => [
                "total" => $paginatedService->getTotal(),
                "pages" => $paginatedService->getPages(),
                "perPage" => $paginatedService->getPerPage(),
                "currentPage" => $paginatedService->getCurrentPage(),
                "datas" => $paginatedService->getItems()
            ],
            'statusCode' => Response::HTTP_OK
        ];
    }
}
