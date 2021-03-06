<?php

namespace PlacetoPay\DeclarationClient\Models;

use PlacetoPay\DeclarationClient\Constants\ServiceResponse;
use PlacetoPay\DeclarationClient\Contracts\ServiceAbstract;
use PlacetoPay\DeclarationClient\Exceptions\DeclarationClientException;
use PlacetoPay\DeclarationClient\Factories\ServiceFactory;
use PlacetoPay\DeclarationClient\Traits\ActionResultTrait;
use PlacetoPay\DeclarationClient\Traits\HelperTrait;
use PlacetoPay\DeclarationClient\Traits\ServiceTrait;
use Exception;
use SoapFault;

/**
 * Class Service
 * @package app\Models
 */
abstract class Service extends ServiceAbstract
{
    use ServiceTrait;
    use ActionResultTrait;
    use HelperTrait;

    /**
     * @var null
     */
    private $action = null;

    /**
     * @var array
     */
    private $data = [];

    /**
     * Service constructor.
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->setOptions($options);
        $this->service(ServiceFactory::instance($this->serviceType(), $this->url(), $this->action()))
            ->setAuthentication($this->credentials())
            ->setRequest($this->data());
    }

    /**
     * @return $this
     */
    public function call()
    {
        try {
            if ($this->getRequest()) {
                $this->setResponse($this->service()->getServiceRequest());
            }

            if ($this->noMakeCall()) {
                return $this;
            }

            // Call WebService
            $response = $this->service()->serviceResponse(
                $this->service()->serviceCall(),
                $this->actionResult()
            );

            if (!$response) {
                $this->setResponse(sprintf('Response empty from %s', $this->service()->getServiceUrlFromAction()));
            } elseif (empty($response->status) || $response->status != ServiceResponse::SUCCESS) {
                $this->setResponse($response);
            } else {
                if ($this->isEnableDebug()) {
                    if (isset($response->requestId)) {
                        file_put_contents('../examples/tmp/request.log', $response->requestId);
                    }
                    if (isset($response->pdf)) {
                        file_put_contents('../examples/tmp/' . $response->reference . '.pdf', base64_decode($response->pdf));
                    }
                }

                $url = (isset($response->redirectTo)) ? $response->redirectTo : null;

                if ($this->isRedirection() && $url) {

                    if (isConsole()) {
                        // On console
                        $this->setResponse('Going to: ' . $url, false);
                    }

                    // On browser
                    header('Location: ' . $url);
                } else {
                    $this->setResponse($response);
                }

                if ($this->makeLink() && $url) {
                    $this->setResponse(sprintf('<a href="%s" target="_blank">Open in new tab</a>', $url), false);
                }
            }
        } catch (SoapFault $e) {
            $this->setResponse($e->getMessage(), false);
        } catch (DeclarationClientException $e) {
            $this->setResponse($e->getMessage(), false);
        } catch (Exception $e) {
            $this->setResponse($e->getMessage(), false);
        }

        return $this;
    }

    /**
     * @param null $action
     * @return $this|null
     */
    public function action($action = null)
    {
        if (!is_null($action)) {
            $this->action = $action;
            return $this;
        }

        return $this->action;
    }

    /**
     * @param array $data
     * @return $this|array
     */
    public function data($data = null)
    {
        if (!is_null($data)) {
            $this->data = $data;
            return $this;
        }

        return $this->data;
    }
}
