<?php
/*
 * @package     Inovarti_Pagarme
 * @copyright   Copyright (C) 2016 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class Inovarti_Pagarme_Transaction_CreditcardController
    extends Mage_Core_Controller_Front_Action
{

    public function postbackAction()
    {
        $pagarme = Mage::getModel('pagarme/api');
        $request = $this->getRequest();

        if ($request->isPost() && $pagarme->validateFingerprint($request->getPost('id'), $request->getPost('fingerprint'))) {

            $orderId = Mage::helper('pagarme')->getOrderIdByTransactionId($request->getPost('id'));
            $order = Mage::getModel('sales/order')->load($orderId);
<<<<<<< HEAD

            if ($order->getState() != Mage_Sales_Model_Order::STATE_NEW) {
                $order->setState(Mage_Sales_Model_Order::STATE_NEW)->save();
            }

=======
            
>>>>>>> marketplace
            $currentStatus = $request->getPost('current_status');

            if ($currentStatus === Inovarti_Pagarme_Model_Api::TRANSACTION_STATUS_PAID) {

                if (!$order->getId()) {
                    throw new Exception('Invalid order number.');
                }

                if (!$order->hasInvoices()) {
                    throw new Exception('Order does not have any invoices.');
                }

                foreach ($order->getInvoiceCollection() as $invoice) {
                    $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);
                    $invoice->capture();
                    Mage::getModel('core/resource_transaction')
                        ->addObject($invoice)
                        ->addObject($invoice->getOrder())
                        ->save();
                }

                $invoice->sendEmail();
                $order->addStatusHistoryComment($this->__('Approved by Pagarme via Creditcard postback.'))->save();
                return $this->getResponse()->setBody('ok');
            }

            $order->setState(Mage_Sales_Model_Order::STATE_CANCELED, true);
            $order->setStatus(Mage_Sales_Model_Order::STATE_CANCELED);

            $order->cancel();
            $order->save();

            $order->addStatusHistoryComment($this->__('Canceled by Pagarme via Creditcard postback.'))->save();

            return $this->getResponse()->setBody('ok');
        }

        $this->_forward('404');
    }
}
