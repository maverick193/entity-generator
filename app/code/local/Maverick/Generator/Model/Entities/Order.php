<?php
/**
 * Maverick_Generator Extension
 *
 * NOTICE OF LICENSE
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @version
 * @category    Maverick
 * @package     Maverick_Generator
 * @author      Mohammed NAHHAS <m.nahhas@live.fr>
 * @copyright   Copyright (c) 2013 Mohammed NAHHAS
 * @licence     OSL - Open Software Licence 3.0
 *
 */
 
/**
 * Sales Order Entity Generator Model
 */
class Maverick_Generator_Model_Entities_Order implements Maverick_Generator_Model_Entities_Interface
{

    /**
     * Generate Magento Orders
     *
     * @param $nbrOfEntities
     * @param array $additional
     * @return array
     */
    public function generateEntity($nbrOfEntities, $additional = array())
    {
        /**
         * Init objects
         */
        $customerResource    = Mage::getResourceModel('customer/customer');
        $customer            = Mage::getModel('customer/customer');
        $product             = Mage::getModel('catalog/product');
        $helper              = Mage::helper('maverick_generator');
        $fakerHelper         = Mage::helper('maverick_generator/faker');

        /**
         * Init data
         */
        $storeId             = Mage::getStoreConfig('generator/order/store_id');
        $shippingDescription = $helper->__('Shipping Method Used By Entity Generator');

        $result              = array(
            'entity_type' => $this->getEntityTypeCode(),
            'nbr'         => $nbrOfEntities
        );

        for ($i=0; $i<$nbrOfEntities; $i++) {
            $customerId = $this->_getRandomCustomerId($customerResource);

            // If no customer found in DB then throw an exception an log error message
            if (!$customerId) {
                $message = $helper->__('Unable to find a customer entity to create orders');
                $helper->log($message, Zend_Log::ERR);
                Mage::throwException($message);
            }
            // Load customer
            $customer->load($customerId);

            // Create and initialize quote
            $quote 	= Mage::getModel('sales/quote');
            $quote->assignCustomer($customer);
            $quote->setStoreId($storeId)
                  ->setIsActive(false)
                  ->setIsMultiShipping(false);

            // Products Ids to purchase
            $productsIds = $this->_getRandomProductIds($storeId);
            // If no product found in DB then throw an exception an log error message
            if (empty($productsIds)) {
                $message = $helper->__('Unable to find a product entity to create orders');
                $helper->log($message, Zend_Log::ERR);
                Mage::throwException($message);
            }

            /**
             * Adding products to cart
             */
            foreach ($productsIds as $productsId) {
                $product->load($productsId);

                // Calculate final price
                $finalPrice = $product->getFinalPrice();

                // Create quote item and assign it to the product
                $quoteItem 	= Mage::getModel('sales/quote_item');
                $quoteItem->setProduct($product);
                $quoteItem->setQuote($quote);
                $quoteItem->setQty(1);

                if ($message = $quoteItem->getMessage()) {
                    $message = $helper->__('An error encountered while adding product "%s" to the cart : %s',
                        $product->getName(), $message);
                    $helper->log($message, Zend_Log::ERR);
                    Mage::throwException($message);
                }

                $quoteItem->setPrice($finalPrice)
                          ->setBasePrice($finalPrice)
                          ->setOriginalPrice($finalPrice)
                          ->setCustomPrice($finalPrice)
                          ->setOriginalCustomPrice($finalPrice);

                $quoteItem->getProduct()->setIsSuperMode(true);

                $quote->addItem($quoteItem);
                $product->reset();
            }

            $billingAddress = Mage::getModel('customer/address');
            $billingAddress->load($customer->getDefaultBilling());
            if (!$billingAddress->getId()) {
                $billingAddress = Mage::getModel('customer/address');
                $billingAddress = $helper->createCustomerAddress($billingAddress, $customer, $fakerHelper);

            }
            $shippingAddress = $billingAddress;

            // Set billing and shipping addresses
            $quote->getBillingAddress()->addData($billingAddress->getData());
            $quote->getShippingAddress()->addData($shippingAddress->getData());

            // Set shipping method
            $shippingMethod = 'flatrate_flatrate';
            $quote->getShippingAddress()->setCollectShippingRates(true);
            $quote->getShippingAddress()->setShippingMethod($shippingMethod);
            $quote->getShippingAddress()->setShippingDescription($shippingDescription);
            $quote->getShippingAddress()->collectShippingRates();

            // Set payment method
            $paymentData = $this->_getRandomPaymentData();
            $quote->getPayment()->importData($paymentData);

            // Collect totals and submit all
            $quote->collectTotals()->save();
            $quote->collectTotals();

            /** @var $service Mage_Sales_Model_Service_Quote */
            $service = Mage::getModel('sales/service_quote', $quote);
            $service->submitAll();

            // Retrieve the order
            $order = $service->getOrder();

            // Log order information
            $helper->log(
                $helper->__('order #%s for customer "ID %s" successfully created : (Order ID %s)',
                    $order->getIncrementId(), $customer->getId(), $order->getId())
            );

            // Process configured action for order (create invoice, shipment, ...)
            $this->_processOrder($order);

            // Reinit objects
            $customer->reset();
        }

        return $result;
    }

    /**
     * Get Entity Type Code
     *
     * @return string
     */
    public function getEntityTypeCode()
    {
        $default = 'entities_order';
        if ($config = Mage::app()->getConfig()->getNode('generator/entities/order')) {
            if ($config->class) {
                return (string)$config->class;
            }
        }
        return $default;
    }

    /**
     * Get random customer_id
     *
     * @param $resource
     * @return bool
     */
    protected function _getRandomCustomerId($resource)
    {
        $adapter = $resource->getReadConnection();

        $select  = $adapter->select()
                           ->from($resource->getTable('customer/entity'), 'entity_id')
                           ->order('RAND()')
                           ->limit(1);

        $result = $adapter->fetchOne($select);
        if ($result) {
            return $result;
        }
        return false;
    }

    /**
     * Get Product Ids to purchase
     * Product must be simple, enabled, visible and in stock
     *
     * @param $storeId
     * @return array
     */
    protected function _getRandomProductIds($storeId)
    {
        $maxProducts = (Mage::getStoreConfig('generator/order/max_products')) ? Mage::getStoreConfig('generator/order/max_products') : 2;
        $collection = Mage::getResourceModel('catalog/product_collection')
                        ->addStoreFilter($storeId)
                        ->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
                        ->addAttributeToFilter('type_id', Mage_Catalog_Model_Product_Type::TYPE_SIMPLE)
                        ->addAttributeToFilter('visibility',array('in' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH))
                        ;

        Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($collection);

        $collection->getSelect()
                   ->limit(rand(1, $maxProducts))
                   ->order('RAND()');

        $ids = $collection->getConnection()->fetchCol($collection->getSelect());

        return $ids;
    }

    /**
     * Get random payment data from the available methods
     *
     * @return mixed
     */
    protected function _getRandomPaymentData()
    {
        $availablePaymentMethods = array(
                                      array('method' => 'checkmo'),
                                      array('method' => 'banktransfer')
                                   );

        return $availablePaymentMethods[array_rand($availablePaymentMethods, 1)];
    }

    /**
     * Do actions on order (create invoice, create shipment)
     * Actions are configured in the BackOffice
     *
     * @param Mage_Sales_Model_Order $order
     * @return array
     */
    protected function _processOrder(Mage_Sales_Model_Order $order)
    {
        $paymentMethod = $order->getPayment()->getMethod();
        $action = Mage::getStoreConfig('generator/order/' . $paymentMethod . '_order_status');
        $result = array();

        switch ($action) {
            case Maverick_Generator_Model_Source_Config_Order_Status::CREATE_ORDER_INVOICE_AND_SHIPMENT :
                $result[]  = $this->_createOrderInvoice($order);
                $result[]  = $this->_createOrderShipment($order);
                break;

            case Maverick_Generator_Model_Source_Config_Order_Status::CREATE_ORDER_INVOICE :
                $result[]  = $this->_createOrderInvoice($order);
                break;

            case Maverick_Generator_Model_Source_Config_Order_Status::CREATE_ORDER_SHIPMENT :
                $result[]  = $this->_createOrderShipment($order);
                break;
        }

        return $result;
    }

    /**
     * Create order invoice
     *
     * @param Mage_Sales_Model_Order $order
     * @return string
     */
    protected function _createOrderInvoice(Mage_Sales_Model_Order $order)
    {
        /**
         * Check invoice create availability
         */
        if (!$order->canInvoice()) {
            $message = Mage::helper('maverick_generator')->__('    Cannot create order invoice (order ID %s)', $order->getIncrementId());
            Mage::helper('maverick_generator')->log($message, Zend_Log::ERR);
        }

        /* @var $invoice Mage_Sales_Model_Order_Invoice */
        $invoice = $order->prepareInvoice();

        if ($invoice) {
            $invoice->register();
            $invoice->getOrder()->setIsInProcess(true);

            $transactionSave = Mage::getModel('core/resource_transaction')
                ->addObject($invoice)
                ->addObject($invoice->getOrder())
                ->save();

            // Log information about invoice
            $message = Mage::helper('maverick_generator')->__('    Invoice (%s) for order #%s was successfully created',
                            $invoice->getIncrementId(), $order->getIncrementId()
                       );
            Mage::helper('maverick_generator')->log($message);
        } else {
            $message = Mage::helper('maverick_generator')->__('    Cannot create order invoice (order ID %s)', $order->getIncrementId());
            Mage::helper('maverick_generator')->log($message, Zend_Log::ERR);
        }

        return $message;
    }

    /**
     * create order shipment
     *
     * @param Mage_Sales_Model_Order $order
     * @return string
     */
    protected function _createOrderShipment(Mage_Sales_Model_Order $order)
    {
        /**
         * Check shipment create availability
         */
        if (!$order->canShip()) {
            $message = Mage::helper('maverick_generator')->__('    Cannot create order shipment (order ID %s)', $order->getIncrementId());
            Mage::helper('maverick_generator')->log($message, Zend_Log::ERR);
        }

        /* @var $shipment Mage_Sales_Model_Order_Shipment */
        $shipment = $order->prepareShipment();

        if ($shipment) {
            $shipment->register();
            $shipment->getOrder()->setIsInProcess(true);

            $transactionSave = Mage::getModel('core/resource_transaction')
                ->addObject($shipment)
                ->addObject($shipment->getOrder())
                ->save();

            // Log information about invoice
            $message = Mage::helper('maverick_generator')->__('    Shipment (%s) for order #%s was successfully created',
                            $shipment->getIncrementId(), $order->getIncrementId()
                       );
            Mage::helper('maverick_generator')->log($message);
        } else {
            $message = Mage::helper('maverick_generator')->__('    Cannot create order shipment (order ID %s)', $order->getIncrementId());
            Mage::helper('maverick_generator')->log($message, Zend_Log::ERR);
        }

        return $message;
    }

    /**
     * Generate one Magento Entity For Shell Script
     *
     * @param array $data
     * @return array
     */
    public function createOneEntity($data = array())
    {
        /**
         * Init objects
         */
        $customerResource    = Mage::getResourceModel('customer/customer');
        $customer            = Mage::getModel('customer/customer');
        $product             = Mage::getModel('catalog/product');
        $helper              = Mage::helper('maverick_generator');
        $fakerHelper         = Mage::helper('maverick_generator/faker');
        $result              = array();

        /**
         * Init data
         */
        $storeId             = Mage::getStoreConfig('generator/order/store_id');
        $shippingDescription = $helper->__('Shipping Method Used By Entity Generator');

        //for ($i=0; $i<$nbrOfEntities; $i++) {
        $customerId = $this->_getRandomCustomerId($customerResource);

        // If no customer found in DB then throw an exception an log error message
        if (!$customerId) {
            $message = $helper->__('Unable to find a customer entity to create orders');
            Mage::throwException($message);
        }

        // Load customer
        $customer->load($customerId);

        // Create and initialize quote
        $quote 	= Mage::getModel('sales/quote');
        $quote->assignCustomer($customer);
        $quote->setStoreId($storeId)
            ->setIsActive(false)
            ->setIsMultiShipping(false);

        // Products Ids to purchase
        $productsIds = $this->_getRandomProductIds($storeId);
        // If no product found in DB then throw an exception an log error message
        if (empty($productsIds)) {
            $message = $helper->__('Unable to find a product entity to create orders');
            Mage::throwException($message);
        }

        /**
         * Adding products to cart
         */
        foreach ($productsIds as $productsId) {
            $product->load($productsId);

            // Calculate final price
            $finalPrice = $product->getFinalPrice();

            // Create quote item and assign it to the product
            $quoteItem 	= Mage::getModel('sales/quote_item');
            $quoteItem->setProduct($product);
            $quoteItem->setQuote($quote);
            $quoteItem->setQty(1);

            if ($message = $quoteItem->getMessage()) {
                $message = $helper->__('An error encountered while adding product "%s" to the cart : %s',
                    $product->getName(), $message);
                Mage::throwException($message);
            }

            $quoteItem->setPrice($finalPrice)
                ->setBasePrice($finalPrice)
                ->setOriginalPrice($finalPrice)
                ->setCustomPrice($finalPrice)
                ->setOriginalCustomPrice($finalPrice);

            $quoteItem->getProduct()->setIsSuperMode(true);

            $quote->addItem($quoteItem);
            $product->reset();
        }

        $billingAddress = Mage::getModel('customer/address');
        $billingAddress->load($customer->getDefaultBilling());
        if (!$billingAddress->getId()) {
            $billingAddress = Mage::getModel('customer/address');
            $billingAddress = $helper->createCustomerAddress($billingAddress, $customer, $fakerHelper);

        }
        $shippingAddress = $billingAddress;

        // Set billing and shipping addresses
        $quote->getBillingAddress()->addData($billingAddress->getData());
        $quote->getShippingAddress()->addData($shippingAddress->getData());

        // Set shipping method
        $shippingMethod = 'flatrate_flatrate';
        $quote->getShippingAddress()->setCollectShippingRates(true);
        $quote->getShippingAddress()->setShippingMethod($shippingMethod);
        $quote->getShippingAddress()->setShippingDescription($shippingDescription);
        $quote->getShippingAddress()->collectShippingRates();

        // Set payment method
        $paymentData = $this->_getRandomPaymentData();
        $quote->getPayment()->importData($paymentData);

        // Collect totals and submit all
        $quote->collectTotals()->save();
        $quote->collectTotals();

        /** @var $service Mage_Sales_Model_Service_Quote */
        $service = Mage::getModel('sales/service_quote', $quote);
        $service->submitAll();

        // Retrieve the order
        $order = $service->getOrder();

        // Log order information
        $result[] = $helper->__('order #%s for customer "ID %s" successfully created : (Order ID %s)',
                        $order->getIncrementId(), $customer->getId(), $order->getId()
                    );

        // Process configured action for order (create invoice, shipment, ...)
        $processMessages = $this->_processOrder($order);
        if (!empty($processMessages)) {
            $result = array_merge($result, $processMessages);
        }
        return $result;
    }
}