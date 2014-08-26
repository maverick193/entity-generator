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
 * Helper Data
 */

class Maverick_Generator_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Log filename
     *
     * @var string
     */
    protected $_log_file_name = 'maverick_generator.log';

    /**
     * Default ignored attribute codes
     *
     * @var array
     */
    protected $_ignoredAttributeCodes = array('entity_id', 'attribute_set_id', 'entity_type_id');

    /**
     * Default ignored attribute types
     *
     * @var array
     */
    protected $_ignoredAttributeTypes = array();

    /**
     * Default locale
     */
    const DEFAULT_LOCALE_FAKER = 'fr_FR';

    /**
     * Default country ID
     */
    const DEFAULT_COUNTRY_ID = 'FR';

    /**
     * Log messages in custom log file
     *
     * @param $message
     * @param null $level
     */
    public function log($message, $level = null)
    {
        Mage::log($message, $level, $this->_log_file_name, true);
    }

    /**
     * Create customer address using fake data
     *
     * @param $address
     * @param $customer
     * @param $fakerHelper
     * @return Mage_Customer_Model_Address
     */
    public function createCustomerAddress($address, $customer, $fakerHelper)
    {
        $addressData = $fakerHelper->generateAddressData();

        foreach ($this->getAllowedAttributes($address) as $attributeCode=>$attribute) {
            if (isset($addressData[$attributeCode])) {
                $address->setData($attributeCode, $addressData[$attributeCode]);
            }
        }

        if (isset($addressData['is_default_billing'])) {
            $address->setIsDefaultBilling('1');
        }

        if (isset($addressData['is_default_shipping'])) {
            $address->setIsDefaultShipping('1');
        }

        $address->setCustomerId($customer->getId());

        $valid = $address->validate();

        if (is_array($valid)) {
            $message = $this->__('Invalid Address Data : ') . implode("\n", $valid);
            $this->log(
                $this->__('An error encoutered while creating address for customer (ID %s) - %s',
                    $customer->getId(), $message)
            );
            Mage::throwException($message);
        }

        $address->save();

        return $address;
    }

    /**
     * Return list of allowed attributes
     *
     * @param Mage_Eav_Model_Entity_Abstract $entity
     * @param array $filter
     * @return array
     */
    public function getAllowedAttributes($entity, array $filter = null)
    {
        $attributes = $entity->getResource()
            ->loadAllAttributes($entity)
            ->getAttributesByCode();
        $result = array();
        foreach ($attributes as $attribute) {
            if ($this->_isAllowedAttribute($attribute, $filter)) {
                $result[$attribute->getAttributeCode()] = $attribute;
            }
        }

        return $result;
    }

    /**
     * Check is attribute allowed
     *
     * @param Mage_Eav_Model_Entity_Attribute_Abstract $attribute
     * @param array $filter
     * @internal param array $attributes
     * @return boolean
     */
    protected function _isAllowedAttribute($attribute, array $filter = null)
    {
        if (!is_null($filter)
            && !( in_array($attribute->getAttributeCode(), $filter)
                || in_array($attribute->getAttributeId(), $filter))) {
            return false;
        }

        return !in_array($attribute->getFrontendInput(), $this->_ignoredAttributeTypes)
            && !in_array($attribute->getAttributeCode(), $this->_ignoredAttributeCodes);
    }
}