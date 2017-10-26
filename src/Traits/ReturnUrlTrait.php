<?php

namespace FreddieGar\DeclarationApi\Traits;

/**
 * Trait ReturnUrlTrait
 * @package app\Traits
 */
trait ReturnUrlTrait
{
    use HelperTrait;

    /**
     * @return string
     */
    public function returnUrl()
    {
        $returnUrl = 'https://www.google.com.co/';

        if (!isConsole()) {
            $protocol = ($_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';
            $returnUrl = sprintf('%s%s%s', $protocol, $_SERVER['SERVER_NAME'], str_replace('index.php', 'response.php', $_SERVER['SCRIPT_NAME']));
        }

        return $returnUrl;
    }
}