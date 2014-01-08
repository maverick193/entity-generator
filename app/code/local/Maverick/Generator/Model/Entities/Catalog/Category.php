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
 * Catalog Category Entity Generator Model
 */
class Maverick_Generator_Model_Entities_Catalog_Category implements Maverick_Generator_Model_Entities_Interface
{

    /**
     * Generate Magento Entity
     *
     * @param $nbrOfEntities
     * @param array $additional
     * @return array
     */
    public function generateEntity($nbrOfEntities, $additional = array())
    {
        $helper = Mage::helper('maverick_generator');
        if (empty($additional) || !isset($additional['category_parent'])) {
            Mage::throwException(
                $helper->__('An error occurred while processing your request')
            );
        }

        if (($additional['category_parent'] === 'fixed') && !isset($additional['category_parent_id'])) {
            Mage::throwException(
                $helper->__('You need to specify an existing category ID')
            );
        }

        $configuredParent = Mage::getStoreConfig('generator/catalog_category/default_parent');
        if (($additional['category_parent'] === 'configured') && !$configuredParent) {
            Mage::throwException(
                $helper->__('You need to configure a default parent ID <a href="%s" target="_blank">here</a>, be sure that your configuration is saved',
                    Mage::helper('adminhtml')->getUrl('adminhtml/system_config/edit', array('section' => 'generator'))
                )
            );
        }

        switch ($additional['category_parent']) {
            case 'random' :
                $parentId = $this->_getRandomParentId();
                break;
            case 'configured' :
                $parentId = $configuredParent;
                break;
            case 'fixed' :
                $parentId = $additional['category_parent_id'];
                break;
        }

        if (!$parentId) {
            Mage::throwException(
                $helper->__('Request aborted, cannot retrieve a parent category ID')
            );
        }

        $parentCategory = $this->_initCategory($parentId);
        $fakerHelper    = Mage::helper('maverick_generator/faker');

        $result         = array(
            'entity_type' => $this->getEntityTypeCode(),
            'nbr'         => $nbrOfEntities
        );

        for ($i=0; $i<$nbrOfEntities; $i++) {
            /** @var $category Mage_Catalog_Model_Category */
            $category = Mage::getModel('catalog/category')->setStoreId($this->_getStoreId());

            $category->addData(array('path' => implode('/', $parentCategory->getPathIds())));
            $category->setAttributeSetId($category->getDefaultAttributeSetId());

            $categoryData   = $fakerHelper->generateCategoryData();

            if(isset($additional['category_products'])) {
                $productIds = $this->_getRandomProductIds($this->_getStoreId());
                if (!empty($productIds)) {
                    $category->setPostedProducts($productIds);
                }
                else{
                    $message = $helper->__('Unable to find a product entity to assign');
                    $helper->log($message, Zend_Log::ERR);
                    Mage::throwException($message);
                }
            }
            
            foreach ($categoryData as $attribute => $value) {
                $category->setData($attribute, $value);
            }
            $category->setParentId($parentCategory->getId());

            $validate = $category->validate();
            if ($validate !== true) {
                foreach ($validate as $code => $error) {
                    if ($error === true) {
                        Mage::throwException(Mage::helper('catalog')->__('Attribute "%s" is required.', $code));
                    } else {
                        Mage::throwException($error);
                    }
                }
            }

            $category->save();

            // Log category information
            $helper->log(
                $helper->__('Category "%s" successfully created : (ID %s)',
                    $category->getName(), $category->getId())
            );

            $category->unsetData();
            $category->unsetOldData();
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
        $default = 'entities_catalog_category';
        if ($config = Mage::app()->getConfig()->getNode('generator/entities/category')) {
            if ($config->class) {
                return (string)$config->class;
            }
        }
        return $default;
    }

    /**
     * Generate one Magento Entity For Shell Script
     *
     * @param array $data
     * @return array
     */
    public function createOneEntity($data = array())
    {
        $helper   = Mage::helper('maverick_generator');
        if (!empty($data) && isset($data['parent_id'])) {
            switch ($data['parent_id']) {
                case 'random' :
                    $parentId = $this->_getRandomParentId();
                    break;
                case 'configuration' :
                    $parentId = Mage::getStoreConfig('generator/catalog_category/default_parent');
                    break;
                default :
                    $parentId = $data['parent_id'];
                    break;
            }

        } else {
            $parentId = Mage::getStoreConfig('generator/catalog_category/default_parent');
        }

        if (!$parentId) {
            Mage::throwException(
                $helper->__('You need to specify a category parent ID or configure it in System -> Configuration -> Entity Generator -> Catalog Category Generator Configuration')
            );
        }

        $parentCategory = $this->_initCategory($parentId);

        $fakerHelper    = Mage::helper('maverick_generator/faker');
        $categoryData   = $fakerHelper->generateCategoryData();
        $categoryData = $this->getAtrributesMerging($data, $categoryData);
        $result         = array();

        /** @var $category Mage_Catalog_Model_Category */
        $category = Mage::getModel('catalog/category')->setStoreId($this->_getStoreId());

        $category->addData(array('path' => implode('/', $parentCategory->getPathIds())));
        $category->setAttributeSetId($category->getDefaultAttributeSetId());

        if(isset($categoryData['category_products'])) {
            $products = array();
            parse_str($categoryData['category_products'], $products);
            $category->setPostedProducts($products);
            unset($categoryData['category_products']);
        }

        foreach ($categoryData as $attribute => $value) {
            $category->setData($attribute, $value);
        }
        $category->setParentId($parentCategory->getId());

        $validate = $category->validate();
        if ($validate !== true) {
            foreach ($validate as $code => $error) {
                if ($error === true) {
                    Mage::throwException(Mage::helper('catalog')->__('Attribute "%s" is required.', $code));
                } else {
                    Mage::throwException($error);
                }
            }
        }

        $category->save();

        // Log category information
        $result[] = $helper->__('* Category "%s" was successfully created : (ID %s) [Child of "%s"]',
            $category->getName(), $category->getId(), $parentCategory->getName()
        )
        .((isset($products))? $helper->__('- %d products has been assigned',count($products)):'');

        return $result;
    }

    /**
     * Load and check category
     *
     * @param $id
     * @param null $store
     * @return Mage_Catalog_Model_Category
     */
    protected function _initCategory($id, $store = null)
    {
        $category = Mage::getModel('catalog/category')
                            ->setStoreId($this->_getStoreId($store))
                            ->load($id);

        if (!$category->getId()) {
            Mage::throwException(
                Mage::helper('maverick_generator')->__('Cannot retrieve category parent with ID "%s"', $id)
            );
        }

        return $category;
    }

    /**
     * Retrives store id from store code, if no store id specified,
     *
     * @param string|int $store
     * @return int
     */
    protected function _getStoreId($store = null)
    {
        if (is_null($store)) {
            $store = 0;
        }

        try {
            $storeId = Mage::app()->getStore($store)->getId();
        } catch (Mage_Core_Model_Store_Exception $e) {
            Mage::throwException(
                Mage::helper('maverick_generator')->__('Store does not exists')
            );
        }

        return $storeId;
    }

    /**
     * Get a random category ID with level > 1
     *
     * @return string
     */
    protected function _getRandomParentId()
    {
        $ids = Mage::getResourceModel('catalog/category_collection')
                    ->addAttributeToFilter('level', array('gteq' => '1'))
                    ->getAllIds();

        if (empty($ids)) {
            Mage::throwException(
                Mage::helper('maverick_generator')->__('Cannot retrieve a random category parent')
            );
        }

        return $ids[array_rand($ids, 1)];
    }

    /**
     * Merge Arguments Data and Fake Data generated to empty skeletor Data
     *
     * @return array
     */
    public function getAtrributesMerging($args = array(), $data = array())
    {
        $skeletorData = array(
            'name'              => '',
            'description'       => '',
            'is_active'         => '',
            'available_sort_by' => '',
            'default_sort_by'   => '',
            'is_anchor'         => 0,
            'include_in_menu'   => 1,
            'page_layout'       => '',
            'custom_use_parent_settings'    => 0,
            'custom_apply_to_products'      => 0,
            'custom_design'     => '',
            'use_config'        => '',
        );

        //fill empty entries with argument value
        if(!empty($args)){
            if (!array_walk($skeletorData, array($this, 'merge'), $args)) {
                Mage::throwException(Mage::helper('catalog')->__('Error while attribute merging, args treatment'));
            }
        }

        //fill empty entries with fake value
        if(!empty($data)){
            if (!array_walk($skeletorData, array($this, 'merge'), $data)) {
                Mage::throwException(Mage::helper('catalog')->__('Error while attribute merging, fakedata treatment'));
            }
        }
        
        if(isset($args['available_sort_by_use_config'])) {
            $skeletorData['use_config'][] =  'available_sort_by';
        }
        
        if(isset($args['default_sort_by_use_config'])) {
            $skeletorData['use_config'][] =  'default_sort_by';
        }

        if(isset($args['filter_price_range'])) {
            $skeletorData['use_config'][] =  'filter_price_range';
        }

        /*
        * if assign_random_product exists,
        * random products will be assigned to the category
        */
        $helper = Mage::helper('maverick_generator');
        $storeId = Mage::getStoreConfig('generator/order/store_id');

        if(isset($args['assign_random_products'])){
            $productIds = $this->_getRandomProductIds($storeId);
            if (!empty($productIds)) {
                $skeletorData['category_products'] = implode('=&', $productIds);
            }
            else{
                $message = $helper->__('Unable to find a product entity to assign');
                $helper->log($message, Zend_Log::ERR);
                Mage::throwException($message);
            }
        }
        return $skeletorData;
    }

    public function merge(&$data, $index, $fakedata)
    {
        if(strlen($data) == 0 && isset($fakedata[$index]))
            $data = $fakedata[$index];
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
        $collection = Mage::getResourceModel('catalog/product_collection')
            ->addStoreFilter($storeId)
            ->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
            ->addAttributeToFilter('type_id', Mage_Catalog_Model_Product_Type::TYPE_SIMPLE)
            ->addAttributeToFilter('visibility',array('in' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH));

        Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($collection);

        $collection->getSelect()
            ->limit(rand(1, 100))
            ->order('RAND()');

        $ids = $collection->getConnection()->fetchCol($collection->getSelect());

        return $ids;
    }
}