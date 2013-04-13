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
 * Actions On Order Source Model
 */
class Maverick_Generator_Model_Source_Config_Order_Status
{
    const NO_ACTION                         = 0;
    const CREATE_ORDER_INVOICE              = 1;
    const CREATE_ORDER_SHIPMENT             = 2;
    const CREATE_ORDER_INVOICE_AND_SHIPMENT = 3;

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => self::NO_ACTION,             'label' => Mage::helper('maverick_generator')->__('No Action')),
            array('value' => self::CREATE_ORDER_INVOICE,  'label' => Mage::helper('maverick_generator')->__('Create Invoice')),
            array('value' => self::CREATE_ORDER_SHIPMENT, 'label' => Mage::helper('maverick_generator')->__('Create Shipment')),
            array('value' => self::CREATE_ORDER_INVOICE_AND_SHIPMENT, 'label' => Mage::helper('maverick_generator')->__('Create Invoice And Shipment'))
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            self::NO_ACTION             =>  Mage::helper('maverick_generator')->__('No Action'),
            self::CREATE_ORDER_INVOICE  =>  Mage::helper('maverick_generator')->__('Create Invoice'),
            self::CREATE_ORDER_SHIPMENT =>  Mage::helper('maverick_generator')->__('Create Shipment'),
            self::CREATE_ORDER_INVOICE_AND_SHIPMENT =>  Mage::helper('maverick_generator')->__('Create Invoice And Shipment'),
        );
    }
}