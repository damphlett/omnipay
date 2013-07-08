<?php

/*
 * This file is part of the Omnipay package.
 *
 * (c) Dave Amphlett <dave@davelopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Omnipay\SagePay\Message;

/**
 * Sage Pay Server CreateCard Request
 */
class ServerCreateCardRequest extends ServerPurchaseRequest
{
    protected $action = 'TOKEN';

    public function getData()
    {
        $this->validate('returnUrl', 'transactionId');

        $data = $this->getBaseData();
        $data['VendorTxCode'] = $this->getTransactionId();
        $data['Currency'] = $this->getCurrency();
        $data['NotificationURL'] = $this->getReturnUrl();
        $data['Profile'] = $this->getProfile();

        return $data;
    }

    public function getService()
    {
        return 'token';
    }

    protected function createResponse($data)
    {
        return $this->response = new ServerCreateCardResponse($this, $data);
    }
}
