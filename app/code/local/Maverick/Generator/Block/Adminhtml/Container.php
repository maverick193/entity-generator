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
 * Block Container
 */
class Maverick_Generator_Block_Adminhtml_Container extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Initialize container block settings
     */
    public function __construct() {

        /* $this->_blockGroup . '/' . $this->_controller . '_grid' */
        $this->_blockGroup = 'maverick_generator';
        $this->_controller = 'adminhtml_container';

        $this->_headerText = $this->__('Maverick Entity Generator');

        parent::_construct();

        $this->addButton('generate', array(
            'label'     => Mage::helper('maverick_generator')->__('Generate Entity'),
            'onclick'   => 'generate()'
        ));
    }

    /**
     * Get GenerateAction Url
     *
     * @return string
     */
    public function getGenerateActionUrl() {
        return $this->getUrl('*/*/generate');
    }
}