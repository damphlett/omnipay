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

use Omnipay\Common\Message\RequestInterface;

/**
 * Sage Pay Server Complete Authorize Response
 */
class ServerCompleteAuthorizeResponse extends Response
{
    public function __construct(RequestInterface $request, $data)
    {
        $this->request = $request;
        $this->data = $data;
    }

    public function getTransactionReference()
    {
        if (isset($this->data['TxAuthNo'])) {
            $reference = json_decode($this->getRequest()->getTransactionReference(), true);
            $reference['VendorTxCode'] = $this->getRequest()->getTransactionId();
            $reference['TxAuthNo'] = $this->data['TxAuthNo'];

            return json_encode($reference);
        }
    }

    /**
     * SagePay unique Authorisation Code for successfully authorised transactions
     * aka VPSAuthCode.
     * Only present if the transaction was successfully authorised (Status OK)
     * @return string
     */
    public function getTxAuthNo()
    {
        return (isset($this->data['TxAuthNo'])) ? $this->data['TxAuthNo'] : null;
    }

    /**
     * Response from AVS and CV2 checks. Will be one of the following:
     * ALL MATCH, SECURITY CODE MATCH ONLY, ADDRESS MATCH ONLY, NO DATA MATCHES,
     * DATA NOT CHECKED
     * @return string
     */
    public function getAVSCV2() 
    {
        return (isset($this->data['AVSCV2'])) ? $this->data['AVSCV2'] : null;
    }

    /** 
     * NOTPROVIDED, NOTCHECKED, MATCHED, NATMATCHED
     * @return string
     */
    public function getAddressResult() 
    {
        return (isset($this->data['AddressResult'])) ? $this->data['AddressResult'] : null;
    }
    
    /** 
     * NOTPROVIDED, NOTCHECKED, MATCHED, NATMATCHED
     * @return string
     */
    public function getPostCodeResult() 
    {
        return (isset($this->data['PostCodeResult'])) ? $this->data['PostCodeResult'] : null;
    }
    
    /** 
     * NOTPROVIDED, NOTCHECKED, MATCHED, NATMATCHED
     * @return string
     */
    public function getCV2Result() 
    {
        return (isset($this->data['CV2Result'])) ? $this->data['CV2Result'] : null;
    }
    
    /**
     * 0 = The Gift Aid box was not checked for this transaction
     * 1 = The user checked teh Gift Aid box on the payment page
     * @return string
     */
    public function getGitAid() 
    {
        return (isset($this->data['GiftAid'])) ? $this->data['GiftAid'] : null;
    }
    
    /**
     * OK = 3D Secure checks carried out and user authenticated correctly.
     * NOTCHECKED = 3D Secure checks were not performed.
     * NOTAVAILABLE = The card used was either not part of the 3D Secure Scheme,
     *    or the authorisation was not possible.
     * NOTAUTHED = 3D Secure authentication checked, but the user failed the
     *    authentication.
     * INCOMPLETE = 3D Secure authentication was unable to complete. No
     *    authentication occured.
     * ERROR = Authentication could not be attempted due to data errors or service
     *    unavailability in one of the parties involved in the check.
     * @return string
     */
    public function get3DSecureStatus() 
    {
        return (isset($this->data['3DSecureStatus'])) ? $this->data['3DSecureStatus'] : null;
    }
    
    /**
     * The encoded result code from the 3D Secure checks (CAVV or UCAF)
     * @return string
     */
    public function getCAVV() 
    {
        return (isset($this->data['CAVV'])) ? $this->data['CAVV'] : null;
    }

    /** 
     * PayPal Transactions Only. If AddressStatus is confirmed and
     * PayerStatus is verified, the transaction may be eligible for
     * PayPal Seller Protection.
     * NONE, CONFIRMED, UNCONFIRMED
     * @return string
     */
    public function getAddressStatus() 
    {
        return (isset($this->data['AddressStatus'])) ? $this->data['AddressStatus'] : null;
    }
    
    /** 
     * PayPal Transactions Only.
     * VERIFIED, UNVERIFIED
     * @return string
     */
    public function getPayerStatus() 
    {
        return (isset($this->data['PayerStatus'])) ? $this->data['PayerStatus'] : null;
    }
    
    /**
     * VISA, MC, DELTA, MAESTRO, UKE, AMEX, DC, JCB, LASER, PAYPAL
     * @return string
     */
    public function getCardType()
    {
        return (isset($this->data['CardType'])) ? $this->data['CardType'] : null;
    }
    
    /**
     * The last 4 digits of the card number used in this transaction.
     * PayPal transactions have 0000
     * @return string
     */
    public function getLast4Digits() 
    {
        return (isset($this->data['Last4Digits'])) ? $this->data['Last4Digits'] : null;
    }
    
    
    /**
     * Confirm (Sage Pay Server only)
     *
     * Sage Pay Server does things backwards compared to every other gateway (including Sage Pay
     * Direct). The return URL is called by their server, and they expect you to confirm receipt
     * and then pass a URL for them to forward the customer to.
     *
     * Because of this, an extra step is required. In your return controller, after calling
     * $gateway->completePurchase(), you should update your database with details of the
     * successful payment. You must then call $response->confirm() to notify Sage Pay you
     * received the payment details, and provide a URL to forward the customer to.
     *
     * Keep in mind your original confirmPurchase() script is being called by Sage Pay, not
     * the customer.
     *
     * @param string URL to foward the customer to. Note this is different to your standard
     *               return controller action URL.
     */
    public function confirm($nextUrl)
    {
        exit("Status=OK\r\nRedirectUrl=".$nextUrl);
    }
}
