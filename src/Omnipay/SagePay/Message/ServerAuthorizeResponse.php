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
 * Sage Pay Server Authorize Response
 */
class ServerAuthorizeResponse extends Response
{
    public function isSuccessful()
    {
        return false;
    }

    public function isRedirect()
    {
        return isset($this->data['Status']) && 'OK' === $this->data['Status'];
    }

    public function getRedirectUrl()
    {
        return isset($this->data['NextURL']) ? $this->data['NextURL'] : null;
    }

    public function getRedirectMethod()
    {
        return 'GET';
    }

    public function getRedirectData()
    {
        return null;
    }

    /**
     * OK = Process executed without error
     * MALFORMED = Input message was missing fields or badly formatted - should
     *    only really occur during development!
     * INVALID = Transaction was not registered because although the POST format
     *    was valid, some information supplied was invalid. eg. incorrect
     *    vendor name or currency.
     * ERROR = A problem occurred at Sage Pay which prevented transaction
     *    registration
     * @return string
     */
    public function getStatus()
    {
        return (isset($this->data['Status'])) ? $this->data['Status'] : null;
    }

    /**
     * SagePay's ID to uniquely identify the Transaction on their system.
     * @return string
     */
    public function getVPSTxId()
    {
        return (isset($this->data['VPSTxId'])) ? $this->data['VPSTxId'] : null;
    }

    /**
     * A Security key which SagePay uses to generate a MD5 Hash with which to
     * "sign" the Notification message. The signature is called VPSSignature.
     * @return string
     */
    public function getSecurityKey()
    {
        return (isset($this->data['SecurityKey'])) ? $this->data['SecurityKey'] : null;
    }
}
