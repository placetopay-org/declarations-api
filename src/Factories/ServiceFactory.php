<?php

namespace PlacetoPay\DeclarationClient\Factories;

use PlacetoPay\DeclarationClient\Constants\ServiceType;
use PlacetoPay\DeclarationClient\Models\RestService;
use PlacetoPay\DeclarationClient\Models\SoapService;

/**
 * Class ServiceFactory
 * @package app\Factories
 */
class ServiceFactory
{

    /**
     * ServiceFactory constructor.
     */
    private function __construct()
    {
    }

    /**
     * @param $type
     * @param $url
     * @param $action
     * @return mixed
     */
    public static function instance($type, $url, $action)
    {
        switch ($type) {
            case ServiceType::SOAP:
                $factory = SoapService::class;
                break;
            case ServiceType::REST:
            default:
                $factory = RestService::class;
                break;
        }

        return new $factory($url, $action);
    }
}
