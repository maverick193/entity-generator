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

require_once Mage::getBaseDir() . '/vendor/autoload.php';

class Maverick_Generator_Helper_Faker extends Mage_Core_Helper_Abstract
{
    protected $_faker;
    protected $_country_id;
    protected $_regionResource;

    /**
     * Init Faker Generator and Providers
     */
    public function __construct()
    {
        $faker = new Faker\Generator();
        $faker->addProvider(new Faker\Provider\fr_FR\Person($faker));
        $faker->addProvider(new Faker\Provider\fr_FR\Internet($faker));
        $faker->addProvider(new Faker\Provider\fr_FR\Address($faker));
        $faker->addProvider(new Faker\Provider\fr_FR\PhoneNumber($faker));
        $faker->addProvider(new Faker\Provider\fr_FR\Company($faker));
        $faker->addProvider(new Faker\Provider\Lorem($faker));

        $this->_country_id      = 'FR';
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
            'country_id'            => 'FR',
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

        $data = $adapter->fetchRow($select, array('country_id' => $this->_country_id));
        if (empty($data)) {
            return array(
                'region_id'     => $this->_faker->region,
                'default_name'  => 224
            );
        }

        return $data;
    }
}