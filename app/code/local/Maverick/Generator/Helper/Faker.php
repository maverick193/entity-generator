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
 * Faker Helper
 * This module uses the PHP library Faker that generates fake data
 * @see : http://github.com/fzaninotto/Faker
 */

require_once(dirname(__FILE__) . '/../../../../../../../../autoload.php');

class Maverick_Generator_Helper_Faker extends Mage_Core_Helper_Abstract
{
    protected $_faker;
    protected $_country_id;
    protected $_regionResource;
    protected $_locale;

    /**
     * Init Faker Generator and Providers
     */
    public function __construct()
    {
        $this->_locale = Mage::registry('faker_locale') ?
            Mage::registry('faker_locale') :
            Maverick_Generator_Helper_Data::DEFAULT_LOCALE_FAKER;

        $faker      = new Faker\Generator();
        $providers  = array('Person', 'Address', 'PhoneNumber', 'Company');

        foreach ($providers as $provider) {
            $class = 'Faker\Provider\\' . $this->_locale . '\\' . $provider;
            $faker->addProvider(new $class($faker));
        }

        /* Internet provider */
        $class = 'Faker\Provider\\' . $this->_locale . '\Internet';
        if (class_exists($class, false)) {
            $faker->addProvider(new $class($faker));
        } else {
            $faker->addProvider(new Faker\Provider\Internet($faker));
        }

        /* Lorem Ipsum provider */
        $faker->addProvider(new Faker\Provider\Lorem($faker));

        $this->_regionResource  = Mage::getResourceModel('directory/region');
        $this->_faker           = $faker;
    }

    /**
     * Generate data needed to create customer entity
     *
     * @return array
     */
    public function generateCustomerData()
    {
        $data = array(
            'password'   => Mage::getStoreConfig('generator/customer/password'),
            'website_id' => Mage::getStoreConfig('generator/customer/website_id'),
            'store_id'   => Mage::getStoreConfig('generator/customer/store_id'),
            'group_id'   => Mage::getStoreConfig('generator/customer/group_id'),
        );

        $data['firstname']  = $this->_faker->firstname;
        $data['lastname']   = $this->_faker->lastname;
        $data['email']      = $this->_faker->safeEmail;

        return $data;
    }

    /**
     * Generate data needed to create customer address entity
     *
     * @return array
     */
    public function generateAddressData()
    {
        $regionData = $this->_getRandomRegionData();

        return array(
            'city'                  => $this->_faker->city,
            'country_id'            => $this->_getCountryId(),
            'postcode'              => str_replace(' ', '', $this->_faker->postcode),
            'region'                => $regionData['default_name'],
            'region_id'             => $regionData['region_id'],
            'street'                => array($this->_faker->streetName),
            'telephone'             => $this->_faker->phoneNumber,
            'lastname'              => $this->_faker->firstname,
            'firstname'             => $this->_faker->lastname,
            'company'               => $this->_faker->company,
            'is_default_billing'    => true,
            'is_default_shipping'   => true
        );
    }

    /**
     * Generate data needed to create catalog category entity
     *
     * @return array
     */
    public function generateCategoryData()
    {
        $data = array(
            'name'              => ucwords($this->_faker->words(rand(1, 2), true)),
            'is_active'         => 1,
            'available_sort_by' => 'position',
            'default_sort_by'   => 'position',
            'description'       => $this->_faker->text(255),
            'is_anchor'         => 0,
            'include_in_menu'   => 1,
        );

        return $data;
    }

    /**
     * Set Faker locale
     *
     * @param $locale
     */
    public function setLocale($locale)
    {
        $this->_locale = $locale;
    }

    /**
     * Add catalog category providers
     *
     * @return Maverick_Generator_Helper_Faker
     */
    public function addCategoryProviders()
    {
        $categoryProviders = array('ProductCategory');

        foreach ($categoryProviders as $provider) {
            $class = 'Faker\Provider\\' . $this->_locale . '\\' . $provider;
            if (class_exists($class, false)) {
                $this->_faker->addProvider(new $class($this->_faker));
            } else {
                $this->_faker->addProvider(new Faker\Provider\ProductCategory($this->_faker));
            }
        }

        return $this;
    }

    /**
     * Fetch a random region_id and region_name value
     *
     * @return array
     */
    protected function _getRandomRegionData()
    {
        $adapter        = $this->_regionResource->getReadConnection();
        $select         = $adapter->select()
                                  ->from($this->_regionResource->getTable('directory/country_region'))
                                  ->where('country_id = :country_id')
                                  ->order('RAND()')
                                  ->limit(1);

        $data = $adapter->fetchRow($select, array('country_id' => $this->_getCountryId()));
        if (empty($data)) {
            return array(
                'region_id'     => $this->_faker->region,
                'default_name'  => 224
            );
        }

        return $data;
    }

    /**
     * Return configured country ID
     *
     * @return Mage_Core_Model_Config_Element
     */
    protected function _getCountryId()
    {
        if (!$this->_country_id) {
            $countryId = Mage::app()->getConfig()->getNode('generator/locale/' . $this->_locale . '/country_id');

            $this->_country_id =  $countryId ? (string)$countryId : Maverick_Generator_Helper_Data::DEFAULT_COUNTRY_ID;
        }

        return $this->_country_id;
    }
}