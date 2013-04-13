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
 * Container Grid Block
 */
class Maverick_Generator_Block_Adminhtml_Container_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('maverick_entity_generator_history_grid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    /**
     * Prepare Grid Columns
     * @see Mage_Adminhtml_Block_Widget_Grid::_prepareColumns
     */
    protected function _prepareColumns()
    {
        $this->addColumn('entity_id', array(
            'header'	=> Mage::helper('maverick_generator')->__('ID'),
            'width' 	=> '80px',
            'type'  	=> 'text',
            'align' 	=> 'center',
            'index' 	=> 'entity_id',
        ));

        $this->addColumn('started_at', array(
            'header' 	=> Mage::helper('maverick_generator')->__('Started at'),
            'index'  	=> 'started_at',
            'type'   	=> 'datetime',
        ));

        $this->addColumn('finished_at', array(
            'header' 	=> Mage::helper('maverick_generator')->__('Finished at'),
            'index'  	=> 'finished_at',
            'type'   	=> 'datetime',
        ));

        $this->addColumn('entity_type', array(
            'header' 	=> Mage::helper('maverick_generator')->__('Entity Type'),
            'index'  	=> 'entity_type',
            'type'   	=> 'options',
            'options' 	=> Mage::getSingleton('maverick_generator/source_entity_type')->optionsForForm(),
            'align'  	=> 'center',
        ));

        $this->addColumn('nbr', array(
            'header' 	=> Mage::helper('maverick_generator')->__('Number Of Entities Created'),
            'index'  	=> 'nbr',
            'type'   	=> 'text',
            'align'  	=> 'center',
        ));

        $this->addColumn('user', array(
            'header' 	=> Mage::helper('maverick_generator')->__('User'),
            'index'  	=> 'user',
            'type'   	=> 'text',
            'align'  	=> 'center',
        ));

        $this->addColumn('remote_ip', array(
            'header' 	=> Mage::helper('maverick_generator')->__('Ip'),
            'index'  	=> 'remote_ip',
            'type'   	=> 'text',
            'align'  	=> 'center',
        ));

        $this->addExportType('*/*/exportCsv', Mage::helper('maverick_generator')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('maverick_generator')->__('Excel XML'));

        return parent::_prepareColumns();
    }

    /**
     * Prepare history collection
     *
     * @access protected
     * @see Mage_Adminhtml_Block_Widget_Grid::_prepareCollection()
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('maverick_generator/history_collection');

        $this->setCollection($collection);
        parent::_prepareCollection();
    }

    /**
     * Return Grid URL for AJAX query
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    /**
     * Prepare Massaction Toolbar
     *
     * @return Maverick_Generator_Block_Adminhtml_Container_Grid
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('history');

        $this->getMassactionBlock()->addItem('delete',
            array(
                'label'    => Mage::helper('maverick_generator')->__('Delete History'),
                'url'      => $this->getUrl('*/*/massDelete'),
                'confirm'  => Mage::helper('maverick_generator')->__('Are you sure?')
            ));

        return $this;
    }
}