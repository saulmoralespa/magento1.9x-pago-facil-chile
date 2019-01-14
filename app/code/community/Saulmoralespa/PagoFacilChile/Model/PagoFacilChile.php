<?php

class Saulmoralespa_PagoFacilChile_Model_PagoFacilChile
    extends Mage_Payment_Model_Method_Abstract
{

    protected $_formBlockType = 'pagofacilchile/form';
    protected $_infoBlockType = 'pagofacilchile/info';

    protected $_code = 'pagofacilchile';

    protected $_isGateway = true;
    protected $_canOrder = true;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = true;
    protected $_canRefund = false;
    protected $_canRefundInvoicePartial = false;
    protected $_canVoid = true;
    protected $_canFetchTransactionInfo = true;
    protected $_canCreateBillingAgreement = true;
    protected $_canReviewPayment = true;
    protected $_supportedCurrencyCodes = array('CLP');


    /**
     * Return Order place direct url
     *
     * @return string
     */
    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('pagofacilchile/payment/redirect', array('_secure' => true));
    }

    /**
     * Return true if the method can be used at this time
     *
     * @return bool
     */
    public function isAvailable($quote = null)
    {

        $parent = parent::isAvailable($quote);
        $user_token = Mage::helper('pagofacilchile')->getUserToken();
        $account_id = Mage::helper('pagofacilchile')->accountId();
        $payment = (!empty($user_token) && !empty($account_id));

        if (!$parent || !$payment)
            return false;

        return true;
    }

    public function canUseForCurrency($currencyCode)
    {
        if (!in_array($currencyCode, $this->_supportedCurrencyCodes)) {
            return false;
        }
        return true;
    }

    public function getAmount($order)
    {
        $amount = $order->getGrandTotal();
        //$currencyCode = $order->getOrderCurrencyCode();
        //if ($currencyCode === 'USD')
        //return number_format($amount, 2, ".", "");
        return round($amount);
    }

}