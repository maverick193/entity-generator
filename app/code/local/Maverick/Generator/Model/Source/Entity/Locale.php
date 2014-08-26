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
 * @copyright   Copyright (c) 2014 Mohammed NAHHAS
 * @licence     OSL - Open Software Licence 3.0
 *
 */

/**
 * Entity Types Source Model
 */
class Maverick_Generator_Model_Source_Entity_Locale extends Mage_Core_Model_Abstract
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
            $options    = array();
            $locale     = Mage::app()->getConfig()->getNode('generator/locale');

            foreach ($locale->children() as $code => $lan) {
                if (!$lan->label || !$lan->country_id) {
                    continue;
                }
                $options[$code] = Mage::helper('maverick_generator')->__((string)$lan->label);
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
            'label' => Mage::helper('maverick_generator')->__('-- Please Choose an language --'),
            'value' => ''
        ));

        return $result;
    }

    /**
     * Get available available locale for shell commands
     *
     * @return array
     */
    public function optionsForShell()
    {
        if (!$this->_shell_options) {
            $options    = array();
            $helper     = Mage::helper('maverick_generator');
            $entities   = Mage::app()->getConfig()->getNode('generator/locale');

            foreach ($entities->children() as $code => $entity) {
                if (!$entity->label) {
                    continue;
                }
                $options[(string)$code] = $helper->__((string)$entity->label);
            }

            $this->_shell_options = $options;
        }

        return $this->_shell_options;
    }
}