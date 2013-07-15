<?php

/*
 * This file is part of the Omnipay package.
 *
 * (c) Adrian Macneil <adrian@adrianmacneil.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omnipay\SagePay\Message;

/**
 * Sage Pay Abstract Request
 */
abstract class AbstractRequest extends \Omnipay\Common\Message\AbstractRequest
{
    public static $debugExceptions = false;
    
    protected $liveEndpoint = 'https://live.sagepay.com/gateway/service';
    protected $testEndpoint = 'https://test.sagepay.com/gateway/service';
    protected $simulatorEndpoint = 'https://test.sagepay.com/Simulator';

    public function getVendor()
    {
        return $this->getParameter('vendor');
    }

    public function setVendor($value)
    {
        return $this->setParameter('vendor', $value);
    }

    public function getSimulatorMode()
    {
        return $this->getParameter('simulatorMode');
    }

    public function setSimulatorMode($value)
    {
        return $this->setParameter('simulatorMode', $value);
    }

    public function getService()
    {
        return $this->getParameter('action');
    }

    protected function getBaseData()
    {
        $data = array();
        $data['VPSProtocol'] = '2.23';
        $data['TxType'] = $this->action;
        $data['Vendor'] = $this->getVendor();

        return $data;
    }

    public function send()
    {
        $httpResponse = $this->httpClient->post($this->getEndpoint(), null, $this->getData())->send();

        return $this->createResponse($httpResponse->getBody());
    }

    public function getEndpoint()
    {
        $service = strtolower($this->getService());

        if ($this->getSimulatorMode()) {
            // hooray for consistency
            if ($service == 'vspdirect-register') {
                return $this->simulatorEndpoint.'/VSPDirectGateway.asp';
            } elseif ($service == 'vspserver-register') {
                return $this->simulatorEndpoint.'/VSPServerGateway.asp?Service=VendorRegisterTx';
            } elseif ($service == 'direct3dcallback') {
                return $this->simulatorEndpoint.'/VSPDirectCallback.asp';
            }

            return $this->simulatorEndpoint.'/VSPServerGateway.asp?Service=Vendor'.ucfirst($service).'Tx';
        }

        if ($this->getTestMode()) {
            return $this->testEndpoint."/$service.vsp";
        }

        return $this->liveEndpoint."/$service.vsp";
    }

    protected function checkSignature($fields, $throwExceptionOnFail = true)
    {
        $transactionReference = json_decode($this->getTransactionReference(), true);
        
        $toHash = "";
        $debugData = array();
        foreach ($fields as $fieldNameDetails) {
            $fieldNameParts = explode('.', $fieldNameDetails, 2);

            $fieldSource = (count($fieldNameParts) > 1) ? $fieldNameParts[0] : 'request';
            $fieldName = (count($fieldNameParts) > 1) ? $fieldNameParts[1] : $fieldNameDetails;

            if ($fieldSource === 'request') {
                $fieldValue = $this->httpRequest->request->get($fieldName);
            } else if ($fieldSource === 'tref') {
                $fieldValue = $transactionReference[$fieldName];
            } else if ($fieldSource === 'this') {
                $fieldValue = $this->{$fieldName}();
            }
            
            $debugData[$fieldName] = $fieldValue;
            $toHash .= $fieldValue;
        }

        // validate VPSSignature
        $ourSig = md5($toHash);
        $theirSig = strtolower($this->httpRequest->request->get('VPSSignature'));

        if (($ourSig !== $theirSig) && $throwExceptionOnFail) {
            if (self::$debugExceptions) {
                throw new \Omnipay\Common\Exception\InvalidResponseExceptionInvalidResponseException(
                        "Signature Mismatch\r\n".
                        "#ours:[{$ourSig}]\r\n".
                        "#theirs:[{$theirSig}]\r\n".
                        "#ourSigData:[{$debugData[$fieldName]}]\r\n".
                        "Request:[".print_r($this->httpRequest->request, true)."]\r\n"
                );
            } else {
                throw new \Omnipay\Common\Exception\InvalidResponseException("Signature Mismatch");
            }
        }
        
        return ($ourSig === $theirSig);
    }

    protected function createResponse($data)
    {
        return $this->response = new Response($this, $data);
    }
}
