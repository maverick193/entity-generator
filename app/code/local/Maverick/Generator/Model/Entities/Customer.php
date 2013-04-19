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
 * Customer Entity Generator Model
 */
class Maverick_Generator_Model_Entities_Customer implements Maverick_Generator_Model_Entities_Interface
{
    /**
     * Generate Magento customers
     *
     * @param $nbrOfEntities
     * @return array
     */
    public function generateEntity($nbrOfEntities)
    {
        $customer       = Mage::getModel('customer/customer');
        $address        = Mage::getModel('customer/address');
        $helper         = Mage::helper('maverick_generator');
        $fakerHelper    = Mage::helper('maverick_generator/faker');

        $result         = array(
                            'entity_type' => $this->getEntityTypeLabel(),
                            'nbr'         => $nbrOfEntities
                          );

        for ($i=0; $i<$nbrOfEntities; $i++) {
            $customerData = $fakerHelper->generateCustomerData();
            while ($this->emailExists($customer, $customerData['email'])) {
                $customerData['email'] .= $i + 1;
            }

            $customer->setData($customerData)->save();

            // Log customer information
            $helper->log(
                $helper->__('Customer "%s %s" successfully created : (ID %s)',
                    $customer->getFirstname(), $customer->getLastname(), $customer->getId())
            );

            if (Mage::getStoreConfig('generator/customer/create_address') === '1') {

                $address = $helper->createCustomerAddress($address, $customer, $fakerHelper);

                // Log address information
                $helper->log(
                    $helper->__('        Address for customer "ID %s" successfully created : (Address ID %s)',
                        $customer->getId(), $address->getId())
                );

                $address->unsetData();
                $address->unsetOldData();
            }
            $customer->reset();
        }

        return $result;
    }

    /**
     * Get Entity Type Label
     *
     * @return string
     */
    public function getEntityTypeLabel()
    {
        return Mage::helper('maverick_generator')->__('Customer');
    }

    /**
     * Check if customer email already exists
     *
     * @param $customer
     * @param $email
     * @return bool
     */
    public function emailExists($customer, $email)
    {
        $customerResource = $customer->getResource();

        $adapter = $customerResource->getReadConnection();
        $bind    = array('email' => $email);
        $select  = $adapter->select()
                           ->from($customerResource->getTable('customer/entity'), 'email')
                           ->where('email = :email')
                           ->limit(1);

        $result = $adapter->fetchOne($select, $bind);
        if ($result) {
            return true;
        }
        return false;
    }

    /**
     * Generate one Magento Entity For Shell Script
     *
     * @return array
     */
    public function createOneEntity()
    {
        $customer       = Mage::getModel('customer/customer');
        $address        = Mage::getModel('customer/address');
        $helper         = Mage::helper('maverick_generator');
        $fakerHelper    = Mage::helper('maverick_generator/faker');

        $customerData = $fakerHelper->generateCustomerData();
        $i = 0;
        $result = array();

        while ($this->emailExists($customer, $customerData['email'])) {
            $customerData['email'] .= $i + 1;
        }

        $customer->setData($customerData)->save();

        // Log customer information
        $result[] = $helper->__('* Customer "%s %s" was successfully created : (ID %s)',
                        $customer->getFirstname(), $customer->getLastname(), $customer->getId()
                    );


        if (Mage::getStoreConfig('generator/customer/create_address') === '1') {

            $address = $helper->createCustomerAddress($address, $customer, $fakerHelper);

            // Log address information
            $result[] = $helper->__('   - Address for this customer "%s" was successfully created : (Address ID %s)',
                            $customer->getName(), $address->getId()
                        );
        }

        return $result;
    }
}