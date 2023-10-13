<?php

namespace App\Service;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class InventoryConstService extends AbstractController
{
    const HEALTH_INDEX_TAB = [
        ["displayName" => "Centre ville", "name" => 10],
        ["displayName" => "Agglomération", "name" => 8],
        ["displayName" => "Zone rurale", "name" => 6]
    ];
    const HEALTH_INDEX_COLUMN_TAB = [
        ["displayName" => "Sain", "name" => 1],
        ["displayName" => "Malade", "name" => 2],
        ["displayName" => "Dépérissant", "name" => 3]
    ];
    const AESTHETIC_INDEX_COLUMN_TAB = [
        ["displayName" => "Remarquable", "name" => 1],
        ["displayName" => "Malade", "name" => 2],
        ["displayName" => "Dépérissant", "name" => 3]
    ];
    const AESTHETIC_INDEX_TAB = [
        ["displayName" => "Remarquable - Sujet solitaire", "name" => 6],
        ["displayName" => "Remarquable - En groupe < à 6 sujets", "name" => 5],
        ["displayName" => "Beau sujet - Solitaire", "name" => 5],
        ["displayName" => "Beau sujet  - En groupe < à 6 sujets", "name" => 4],
        ["displayName" => "Beau sujet  - En groupe de 6 sujet et plus, ou en alignement", "name" => 4],
        ["displayName" => "Mal formé ou âgé - Solitaire", "name" => 3],
        ["displayName" => "Mal formé ou âgé  - En groupe < à 6 sujets", "name" => 2],
        ["displayName" => "Mal formé ou âgé  - En groupe de 6 sujet et plus, ou en alignement", "name" => 2],
        ["displayName" => "Sans intérêt - Sujet solitaire", "name" => 1],
        ["displayName" => "Sans intérêt - En groupe < à 6 sujets", "name" => 1],
        ["displayName" => "Sans intérêt  - En groupe de 6 sujet et plus, ou en alignement", "name" => 1],
    ];

    const LOCATION_INDEX_TAB = [
        ["displayName" => "Centre ville", "name" => 10],
        ["displayName" => "Agglomération", "name" => 8],
        ["displayName" => "Zone rurale", "name" => 6]
    ];

    /**
     * @return array
     */
    public function getBevaConst(): array
    {
        return [
            'data' => [
                'heathIndexDatas' => self::HEALTH_INDEX_TAB,
                'aestheticIndexColumnDatas' => self::AESTHETIC_INDEX_COLUMN_TAB,
                'heathIndexColumnDatas' => self::HEALTH_INDEX_COLUMN_TAB,
                'aestheticIndexDatas' => self::AESTHETIC_INDEX_TAB,
                'locationIndexDatas' => self::LOCATION_INDEX_TAB
            ],
            'statusCode' => Response::HTTP_OK
        ];
    }
}
