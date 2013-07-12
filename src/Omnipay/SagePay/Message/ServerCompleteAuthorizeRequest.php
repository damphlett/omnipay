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
class ServerCompleteAuthorizeRequest extends AbstractRequest
{
    /**
     * This retrieves the transaction id from the raw request so that you can
     * retrieve the full transactionReference from your stored
     * and setTransactionReference() into this request before calling getData()
     * since getData will validate the signature of this request and needs
     * the VPSTxId, VendorTxCode and SecurityKey that were stashed in the
     * transactionReference!
     * @return string
     */
    public function getRequestTransactionId()
    {
        return $this->httpRequest->request->get('VendorTxCode');
    }

    public function getData()
    {
        $this->validate('transactionId', 'transactionReference');

        $reference = json_decode($this->getTransactionReference(), true);

        $this->checkSignature(array(
            'VPSTxId',
            'VendorTxCode',
            'Status',
            'TxAuthNo',
            'this.getVendor',
            'AVSCV2',
            'tref.SecurityKey',
            'AddressResult',
            'PostCodeResult',
            'CV2Result',
            'GiftAid',
            '3DSecureStatus',
            'CAVV',
            'AddressStatus',
            'PayerStatus',
            'CardType',
            'Last4Digits',
        ));
 
        // we only get here if we passed the signature check
        return $this->httpRequest->request->all();
    }

    public function send()
    {
        return $this->response = new ServerCompleteAuthorizeResponse($this, $this->getData());
    }
}
