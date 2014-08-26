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
                case 'config' :
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
        $result         = array();

        /** @var $category Mage_Catalog_Model_Category */
        $category = Mage::getModel('catalog/category')->setStoreId($this->_getStoreId());

        $category->addData(array('path' => implode('/', $parentCategory->getPathIds())));
        $category->setAttributeSetId($category->getDefaultAttributeSetId());

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
        $result[] = $helper->__('Category "%s" was successfully created : (ID %s) [Child of "%s"]',
            $category->getName(), $category->getId(), $parentCategory->getName()
        );

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
}