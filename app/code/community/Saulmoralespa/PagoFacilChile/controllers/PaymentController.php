<?php

require_once (Mage::getBaseDir(). "/lib/pstpagofacil/src/PSTPagoFacil.php");
require_once (Mage::getBaseDir(). "/lib/pstpagofacil/src/PSTPagoFacilException.php");

use PSTPagoFacil\PSTPagoFacil;

class Saulmoralespa_PagoFacilChile_PaymentController extends Mage_Core_Controller_Front_Action
{

    public function redirectAction()
    {

        $res = $this->generateTransaction();

        $urlPayment = '';

        if ($res){
            $data = (array)$res;
            $urlPayment = $data['payUrl'];
        }

        $array_assign = array('url' => $urlPayment);

        $this->loadLayout();
        $block = Mage::app()->getLayout()->createBlock('pagofacilchile/pay');
        $block->assign($array_assign);
        $this->getLayout()->getBlock('content')->append($block);
        $this->_initLayoutMessages('core/session');
        $this->renderLayout();
    }

    public function notifyAction()
    {
        $request = $this->getRequest();
        $params = $request->getParams();

        if (empty($params))
            exit;

        Mage::helper('pagofacilchile')->log('Received notification', $params);

        $reference = $request->getParam('x_reference');
        $reference_ex = explode('_', $reference);
        $order_id = $reference_ex[0];
        $transaction_id = $request->getParam('x_gateway_reference');
        $order = Mage::getModel('sales/order')->load($order_id);


        $payment = Mage::getModel('pagofacilchile/pagoFacilChile');

        $totalOrder = $payment->getAmount($order);
        $ct_monto = $request->getParam('x_amount');

        if ($ct_monto != $totalOrder)
            exit;


        $status = $request->getParam('x_result');

        if ($status == 'pending')
            exit;

        switch ($status){
            case 'completed':
                $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING)->setStatus($status);
                $order->getPayment()->setTransactionId($transaction_id);
                $order->getPayment()->registerCaptureNotification($ct_monto);
                $order->addStatusToHistory($order->getStatus(), __('Payment approved'));
                $order->save();
                break;
            case 'failed':
                $order->cancel();
                $order->addStatusToHistory($order->getStatus(), __('Payment declined'));
        }

        $order->save();
    }

    public function completeAction()
    {
        $request = $this->getRequest();
        $params = $request->getParams();

        if (empty($params))
            exit;

        Mage::helper('pagofacilchile')->log('Received notification', $params);

        $reference = $request->getParam('x_reference');
        $reference_ex = explode('_', $reference);
        $order_id = $reference_ex[0];
        $transaction_id = $request->getParam('x_gateway_reference');
        $order = Mage::getModel('sales/order')->load($order_id);

        $payment = Mage::getModel('pagofacilchile/pagoFacilChile');

        $totalOrder = $payment->getAmount($order);
        $ct_monto = $request->getParam('x_amount');


        if ($ct_monto != $totalOrder)
            $this->_redirect("checkout/onepage/failure");

        $pendingOrder = Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;
        $failedOrder = Mage_Sales_Model_Order::STATE_CANCELED;
        $aprovvedOrder =  Mage_Sales_Model_Order::STATE_PROCESSING;

        $pathRedirect = 'checkout/onepage/success';


        $statusTransaction = $request->getParam('x_result');

        if ($order->getState() == $pendingOrder && $statusTransaction == 'pending'){
            $pathRedirect = "pagofacilchile/payment/pending";
        }elseif ($order->getState() == $failedOrder && $statusTransaction == 'failed'){
            $pathRedirect = "checkout/onepage/failure";
        }elseif ($order->getState() == $pendingOrder && $statusTransaction == 'failed'){
            $order->cancel()->save();
            $order->addStatusToHistory($order->getStatus(), __('Payment declined'));
            $pathRedirect = "checkout/onepage/failure";
        }elseif ($order->getState() == $pendingOrder && $statusTransaction == 'completed'){
            $order->setState($aprovvedOrder)->setStatus($statusTransaction);
            $order->getPayment()->setTransactionId($transaction_id);
            $order->getPayment()->registerCaptureNotification($ct_monto);
            $order->addStatusToHistory($order->getStatus(), __('Payment approved'));
            $order->save();
        }

        $this->_redirect($pathRedirect);

    }

    public function pendingAction()
    {
        $this->loadLayout();
        $block = Mage::app()->getLayout()->createBlock('pagofacilchile/pending');
        $this->getLayout()->getBlock('content')->append($block);
        $this->_initLayoutMessages('core/session');
        $this->renderLayout();
    }

    public function generateTransaction()
    {

        $payment = Mage::getModel('pagofacilchile/pagoFacilChile');

        $orderIncrementId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
        $order->setState(\Mage_Sales_Model_Order::STATE_PENDING_PAYMENT);
        $order->save();

        $billing = $order->getBillingAddress();
        $shipping = $order->getShippingAddress();
        $country = empty($shipping->getCountryId())  ? $shipping->getCountryId() : $billing->getCountryId();

        $orderId = $order->getId();
        $reference = $orderId . "_" . time();

        $data = '';

        try{
            $pagoFacil = new PSTPagoFacil(Mage::helper('pagofacilchile')->getUserToken());
            $pagoFacil->sandbox_mode(Mage::helper('pagofacilchile')->getEnviroment());

            $transaction = array(
                'x_url_callback' => Mage::getUrl('pagofacilchile/payment/notify'),
                'x_url_cancel' => Mage::getUrl('checkout/onepage/failure'),
                'x_url_complete' => Mage::getUrl('pagofacilchile/payment/complete'),
                'x_customer_email' => $order->getCustomerEmail(),
                'x_reference' => $reference,
                'x_account_id' => Mage::helper('pagofacilchile')->accountId(),
                'x_amount' => $payment->getAmount($order),
                'x_currency' => $order->getOrderCurrencyCode(),
                'x_shop_country' => $country
            );

            $data = $pagoFacil->createPayTransaction($transaction);

        }catch (\Exception $exception){
            Mage::helper('pagofacilchile')->log('exception generate transaction', $exception->getMessage());
        }

        return $data;

    }
}