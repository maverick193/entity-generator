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
 * Adminhtml Main Controller
 */
set_time_limit(0);

class Maverick_Generator_Adminhtml_GeneratorController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Check ACL permissions
     * Check current user permission on resource and privilege
     *
     * @return  boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('maverick/generator');
    }

    /**
     * History grid page
     */
    public function indexAction()
    {
        $this->_title($this->__('CampToCamp'))->_title($this->__('Entity Generator'));
        $this->loadLayout()
            ->_addBreadcrumb(Mage::helper('maverick_generator')->__('CampToCamp'), Mage::helper('maverick_generator')->__('CampToCamp'))
            ->_addBreadcrumb(Mage::helper('maverick_generator')->__('Entity Generator'), Mage::helper('maverick_generator')->__('Entity Generator'))
            ->_setActiveMenu('maverick/generator');
        $this->renderLayout();
    }

    /**
     * Grid action for ajax requests
     */
    public function gridAction()
    {
        $this->loadLayout(false);
        $this->renderLayout();
    }

    /**
     * Generate entities
     */
    public function generateAction()
    {
        try {
            if ($data = $this->_initParams()) {
                $startDateTime = Mage::getModel('core/date')->date();
                $creatorInstance = Mage::getModel('maverick_generator/' . $data['synchro_type']);

                if (!is_object($creatorInstance)) {
                    Mage::throwException(Mage::helper('maverick_generator')->__('Cannot instanciate the entity type object'));
                }

                $result  = $creatorInstance->generateEntity($data['nbr']);
                //$comment = (isset($data['comment'])) ? $data['comment'] : '';
                $endDateTime = Mage::getModel('core/date')->date();

                $history = Mage::getModel('maverick_generator/history')->setData($result);
                $history->setStartedAt($startDateTime)
                        ->setFinishedAt($endDateTime)
                        ->save();

                $this->_getSession()->addSuccess(
                    Mage::helper('maverick_generator')->__('%d "%s" was successfully created', $history->getNbr(), $history->getEntityType())
                );
            } else {
                Mage::throwException(Mage::helper('maverick_generator')->__('An error as been encountered while retrieving data'));
            }
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage())->setProductData($data);
        } catch (Exception $e) {
            Mage::helper('maverick_generator')->log($e->getMessage(), Zend_Log::ERR);
            $this->_getSession()->addError($e->getMessage());
        }

        $this->_redirect('*/*/index');
        return;
    }

    /**
     * Initialize and prepare POST data
     *
     * @return mixeb array||bool
     */
    protected function _initParams()
    {
        if ($data = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()->getParam('params'))) {
            if (!isset($data['synchro_type'])) {
                Mage::helper('adminhtml')->log('Cannot retrieve entity type, aborting the action');
                return false;
            }

            $nbrOfEntities  = (isset($data['nbr'])) ? (int)$data['nbr'] : 10;
            $data['nbr']    = $nbrOfEntities;

            return $data;
        }

        return false;
    }

    /**
     * Mass delete records
     */
    public function massDeleteAction()
    {
        $data = $this->getRequest()->getPost('history');
        if ($data) {
            $deleted = array();
            $model = Mage::getModel('maverick_generator/history');
            foreach ((array)$data as $id) {
                $model->setId($id)->delete();
                $deleted[] = $id;
            }
            $this->_getSession()->addSuccess(
                $this->__('Histoty records with IDs %s was successfully deleted', implode(',', $deleted))
            );
        }

        $this->_redirect('*/*');
    }
}