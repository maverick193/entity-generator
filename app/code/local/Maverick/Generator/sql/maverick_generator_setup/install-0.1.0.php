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

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/**
 * drop table 'maverick_generator/history' if it exists
 */
$installer->getConnection()->dropTable($installer->getTable('maverick_generator/history'));

/**
 * Create table 'maverick_generator/history'
 */

$table = $installer->getConnection()
    ->newTable($installer->getTable('maverick_generator/history'))
   	->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'History Id')
    ->addColumn('nbr', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => 0
    ), 'Number Of Entities Created')
    ->addColumn('entity_type', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Synchro Entity_type')   
    ->addColumn('started_at', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
    	'nullable'	=> false,
        ), 'Started At')
    ->addColumn('finished_at', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
        'nullable'	=> false,
    	), 'Finished At')    
   	->addColumn('user', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
   		'nullable'	=> false,
   		), 'Admin Username')    
   	->addColumn('remote_ip', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
   		'nullable'	=> false,
   		), 'User Ip')
    ->setComment('Generated Entities History');
    
$installer->getConnection()->createTable($table);

$installer->endSetup();