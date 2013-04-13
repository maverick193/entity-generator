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
        $fieldset->addField('synchro_type', 'select', array(
            'name'      => 'synchro_type',
            'values'    => Mage::getSingleton('maverick_generator/source_entity_type')->optionsForForm(true),
            'required'  => true,
            'label'     => Mage::helper('maverick_generator')->__('Synchronization Type'),
            'title'     => Mage::helper('maverick_generator')->__('Synchronization Type'),
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}