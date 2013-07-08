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

use Omnipay\Common\Exception\InvalidResponseException;

/**
 * Sage Pay Server Complete Authorize Request
 */
class ServerCompleteCreateCardRequest extends AbstractRequest
{
    public function getRequestTransactionId()
	{
		return $this->httpRequest->request->get('VendorTxCode');
	}

    public function getCardReference()
    {
        return isset($this->data['Token']) ? $this->data['Token'] : null;
    }

    public function getCardType()
    {
        return isset($this->data['CardType']) ? $this->data['CardType'] : null;
    }

    public function getLast4Digits()
    {
        return isset($this->data['Last4Digits']) ? $this->data['Last4Digits'] : null;
    }

    public function getExpiryDate()
    {
        return isset($this->data['ExpiryDate']) ? $this->data['ExpiryDate'] : null;
    }

    public function getData()
    {
        $this->validate('vendor', 'transactionReference');

        $reference = json_decode($this->getTransactionReference(), true);

        // validate VPSSignature
        $signature = md5(
            $reference['VPSTxId'].
            $reference['VendorTxCode'].
            $this->httpRequest->request->get('Status').
            $this->httpRequest->request->get('TxAuthNo').
            $this->getVendor().
            $this->httpRequest->request->get('TOPKEN').
            $reference['SecurityKey']
        );

        if (strtolower($this->httpRequest->request->get('VPSSignature')) !== $signature) {
            throw new InvalidResponseException;
        }

        return $this->httpRequest->request->all();
    }

    public function send()
    {
        return $this->response = new ServerCompleteCreateCardResponse($this, $this->getData());
    }
}
