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

    /**
     * Get options in "key-value" format
     *
     * @param bool $empty
     * @return array
     */
    public function optionsForForm($empty = false)
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

            if ($empty) {
                array_unshift($options, array('label' => Mage::helper('maverick_generator')->__('**-- Select Type --**'), 'value' => ''));
            }

            $this->_options = $options;
        }

        return $this->_options;
    }
}