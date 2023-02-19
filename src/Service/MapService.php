<?php

namespace App\Service;

use App\Entity\Arbre;
use App\Entity\Epaysage;
use App\Entity\Inventaire;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class MapService
{

    const BASE_URL_MAPS = 'https://maps.google.com/maps/api/geocode/json?';
    const API_KEY = 'AIzaSyBtsQeeMq9e8CkZiTiUd-DsdIBrJaOek5A';
    const RADIUS = 2;

    public static function getAddress($lat, $lng)
    {
        $url = self::BASE_URL_MAPS . 'latlng=' . $lat . ',' . '' . $lng . '&sensor=' . false . '&key=' . self::API_KEY;
        $client = new Client(['headers' => ['content-type' => 'application/json', 'Accept' => 'application/json']]);

        $request = new Request('GET', $url);
        $response = $client->send($request, ['timeout' => 5]);

        $data = json_decode($response->getBody());
        return self::getAddressInformation($data->results[0]->address_components);
    }

    /**
     * @param array $infos
     * @return array
     */
    private static function getAddressInformation(array $infos): array
    {
        $result = [];
        foreach ($infos as $key => $value) {
            $result[$value->types[0]] = $value->long_name;
        }

        $address = $result["route"] ? $result["street_number"] . " " . $result["route"] : $result['plus_code'];
        $city = $result["locality"] ?? $result["administrative_area_level_1"];
        $country = $result["country"];

        return [
            "address" => $address,
            "ville" => $city,
            "pays" => $country
        ];
    }

    /**
     * @param String $chaine
     * @return array
     */
    private static function formatAdress(string $chaine): array
    {
        $data = explode(',', $chaine);

        if (count($data) >= 4) {
            return [
                "address" => $data[0] . ', ' . $data[1],
                "ville" => $data[2]
            ];
        }
        return [
            "address" => $data[0],
            "ville" => $data[1]
        ];

    }

    /**
     * @param Arbre|Epaysage $object
     * @return array|array[]
     */
    public static function serializeCoord($object): array
    { // Get coords from server to client
        if ($object instanceof Epaysage) {
            return array_map(function ($coord) {
                return [
                    'lat' => $coord->getX(),
                    'long' => $coord->getY()
                ];
            }, $object->getCoord()->getRings()[0]->getPoints());
        } else if ($object instanceof Arbre) {
            return [
                'lat' => $object->getCoord()->getX(),
                'long' => $object->getCoord()->getY()
            ];
        }
        
        return [];
    }

    /**
     * @param array $position
     * @param $lat
     * @param $lng
     * @return bool
     */
    public static function isRadiusAround(array $position, $lat, $lng): bool
    {
        $data = sqrt(pow(($position['lat'] - $lat), 2) + pow(($position['lng'] - $lng), 2));
        return $data < self::RADIUS;
    }

    /**
     * @param Inventaire $inventory
     * @param array $data
     * @return bool
     */
    public static function isInventoryInZone(Inventaire $inventory, array $data):bool {
        if(strtoupper( $inventory->getType()) === 'ARBRE') {
            $coord = self::serializeCoord($inventory->getArbre());
            return self::isRadiusAround(['lat' => $data['lat'], 'lng' => $data['lng']], $coord['lat'], $coord['long']);
        } else {
            $coords = MapService::serializeCoord($inventory->getEpaysage());
            return MapService::isRadiusAround(['lat' => $data['lat'], 'lng' => $data['lng']], $coords[0]['lat'], $coords[0]['long']);
        }
    }
}

