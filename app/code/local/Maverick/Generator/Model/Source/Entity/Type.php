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
 * Entity Types Source Model
 */
class Maverick_Generator_Model_Source_Entity_Type extends Mage_Core_Model_Abstract
{
    protected $_options;

    protected $_shell_options;

    /**
     * Get options in "key-value" format for grid
     *
     * @return array
     */
    public function optionForGrid()
    {
        if (!$this->_options) {
            $options = array();

            $entities   = Mage::app()->getConfig()->getNode('generator/entities');

            foreach ($entities->children() as $entity) {
                if (!$entity->label || !$entity->class) {
                    continue;
                }

                $options[(string)$entity->class] = Mage::helper('maverick_generator')->__((string)$entity->label);
            }

            $this->_options = $options;
        }

        return $this->_options;
    }

    /**
     * Get options in "value", "label" format for form
     *
     * @return array
     */
    public function optionsForForm()
    {
        $options    = $this->optionForGrid();
        $result     = array();

        foreach ($options as $value => $label) {
            $result[] = array('label' => $label, 'value' => $value);
        }

        array_unshift($result, array(
            'label' => Mage::helper('maverick_generator')->__('-- Please Choose an Entity Type --'),
            'value' => ''
        ));

        return $result;
    }

    /**
     * Get available entity types for shell commands
     *
     * @param bool $class
     * @return array
     */
    public function optionsForShell($class = false)
    {
        if (!$this->_shell_options) {
            $options    = array();
            $helper     = Mage::helper('maverick_generator');
            $entities   = Mage::app()->getConfig()->getNode('generator/entities');

            foreach ($entities->children() as $entityCode => $entity) {
                if (!$entity->label || !$entity->class) {
                    continue;
                }

                if ($class) {
                    $value = (string)$entity->class;
                } else {
                    $value = $helper->__('Create %s Entities', (string)$entity->label);
                }

                $options[(string)$entityCode] = $value;
            }

            $this->_shell_options = $options;
        }

        return $this->_shell_options;
    }
}