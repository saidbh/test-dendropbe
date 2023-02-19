<?php

namespace App\IService;

interface IPaginate
{
    /**
     * Method to interface with Paginate Service Api
     * @param $page
     * @param $maxPage
     */
    function paginate($page, $maxPage): void;

    /**
     * Method to get all params when paginate successfully
     * @param array $data
     * @param $page /** currentPage
     * @param $numberPage
     * @return array
     */
    public static function paginateList(array $data, $page, $numberPage): array;
}
