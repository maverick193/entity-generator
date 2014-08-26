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
 * Form Block
 */
class Maverick_Generator_Block_Adminhtml_Container_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $actionUrl = $this->getUrl('*/*/*');
        $form = new Varien_Data_Form(
            array('id' => 'generator_form', 'action' => $actionUrl, 'method' => 'get')
        );

        $form->setHtmlIdPrefix('maverick_entity_generator');

        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('maverick_generator')->__('Generate Entity')));

        /** Numbre Of Entities **/
        $fieldset->addField('nbr', 'text', array(
            'name'      => 'nbr',
            'required'  => false,
            'label'     => Mage::helper('maverick_generator')->__('Number Of Entities'),
            'title'     => Mage::helper('maverick_generator')->__('Number Of Entities'),
            'class' 	=> 'validate-greater-than-zero',
            'note'      => Mage::helper('maverick_generator')->__('Number of entities to create, default is 10')
        ));

        /** Entity Type Field **/
        $synchroType = $fieldset->addField('synchro_type', 'select', array(
            'name'      => 'synchro_type',
            'values'    => Mage::getSingleton('maverick_generator/source_entity_type')->optionsForForm(),
            'required'  => true,
            'label'     => Mage::helper('maverick_generator')->__('Entity Type'),
            'title'     => Mage::helper('maverick_generator')->__('Entity Type'),
        ));

        /** @var  $parentCategory */
        /** Category Parent Type */
        $parentCategory = $fieldset->addField('category_parent', 'select', array(
            'name'      => 'category_parent',
            'required'  => true,
            'label'     => Mage::helper('maverick_generator')->__('Category Parent'),
            'title'     => Mage::helper('maverick_generator')->__('Category Parent'),
            'values'    => array(
                array('label' => Mage::helper('maverick_generator')->__('Random'),           'value' => 'random'),
                array('label' => Mage::helper('maverick_generator')->__('Configured Value'), 'value' => 'configured'),
                array('label' => Mage::helper('maverick_generator')->__('Specific Value'),   'value' => 'fixed'),
            ),
        ));

        /** @var  $parentId */
        /** Specific Category Parent ID */
        $parentId = $fieldset->addField('category_parent_id', 'text', array(
            'name'      => 'category_parent_id',
            'required'  => true,
            'label'     => Mage::helper('maverick_generator')->__('Category Parent ID'),
            'title'     => Mage::helper('maverick_generator')->__('Category Parent ID'),
            'note'      => Mage::helper('maverick_generator')->__('The created categories will be children of this parent.  If no ID is specified, categories will be under the root category')
        ));

        /** @var  $assignProduct */
        /** Assign random product to the category */
        $assignProduct = $fieldset->addField('assign_random_products', 'checkbox', array(
            'name'      => 'assign_random_products',
            'required'  => false,
            'checked' => false,
            'value'  => '1',
            'label'     => Mage::helper('maverick_generator')->__('Assign random product'),
            'title'     => Mage::helper('maverick_generator')->__('Assign random product'),
            'note'      => Mage::helper('maverick_generator')->__('Random product will be assigned to the category')
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        /** Field Dependency Management */
        $this->setChild('form_after', $this->getLayout()->createBlock('adminhtml/widget_form_element_dependence')
                ->addFieldMap($synchroType->getHtmlId(), $synchroType->getName())
                ->addFieldMap($parentCategory->getHtmlId(), $parentCategory->getName())
                ->addFieldMap($parentId->getHtmlId(), $parentId->getName())
                ->addFieldMap($assignProduct->getHtmlId(), $assignProduct->getName())
                ->addFieldDependence($parentCategory->getName(), $synchroType->getName(), 'entities_catalog_category')
                ->addFieldDependence($assignProduct->getName(), $synchroType->getName(), 'entities_catalog_category')
                ->addFieldDependence($parentId->getName(), $parentCategory->getName(), 'fixed')
        );

        return parent::_prepareForm();
    }
}