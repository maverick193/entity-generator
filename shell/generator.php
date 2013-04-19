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

require_once 'abstract.php';

/**
 * Entity Generator Shell Script
 *
 * @category    Maverick
 * @package     Maverick_Generator
 * @author      Mohammed NAHHAS <m.nahhas@live.fr>
 */
class Mage_Shell_Generator extends Mage_Shell_Abstract
{
    /**
     * Get available entities through configuration
     *
     * @param bool $class
     * @return array
     */
    protected function _getAvailableEntities($class = false)
    {
        return Mage::getSingleton('maverick_generator/source_entity_type')->optionsForShell($class);
    }

    /**
     * Run script
     *
     */
    public function run()
    {
        if ($this->getArg('info')) {
            $entities = $this->_getAvailableEntities();
            foreach ($entities as $entityCode => $entityLabel) {
                echo sprintf('%-30s', $entityCode);
                echo $entityLabel . "\n";
            }
        } else if ($this->getArg('create') && $this->getArg('type')) {
            try {
                $nbr = ($this->getArg('nbr')) ? $this->getArg('nbr') : 10;
                $entitiesClasses = $this->_getAvailableEntities(true);

                if (!isset($entitiesClasses[$this->getArg('type')])) {
                    Mage::throwException(Mage::helper('maverick_generator')->__(
                        'Cannot find the entity type "%s"',
                        $this->getArg('type')
                    ));
                }

                $creatorInstance = Mage::getModel('maverick_generator/' . $entitiesClasses[$this->getArg('type')]);

                if (!is_object($creatorInstance)) {
                    Mage::throwException(Mage::helper('maverick_generator')->__(
                        'Cannot instantiate the entity type object : please check your entity type "%s"',
                        $this->getArg('type')
                    ));
                }

                for ($i=0; $i<$nbr; $i++) {
                    $messages = $creatorInstance->createOneEntity();
                    if (is_array($messages) && !empty($messages)) {
                        foreach ($messages as $message) {
                            echo $message . "\n";
                        }
                    }
                }
            } catch (Mage_Core_Exception $e) {
                echo $e->getMessage() . "\n";
            } catch (Exception $e) {
                echo Mage::helper('maverick_generator')->__('Entity generator unknown error:\n');
                echo $e . "\n";
            }

        } else {
            echo $this->usageHelp();
        }
    }

    /**
     * Retrieve Usage Help Message
     *
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f generator.php info
        php -f generator.php create --type <entity_type> --nbr <a_number>

[options] :
===========
  create                        Create an entity
  --type <entity_type>          Entity type (required)
  --nbr  <number>               Number of entities to create, default is 10
  info                          Show available entity types that can be created
  help                          This help

example : php generator.php create --type customer --nbr 100


USAGE;
    }
}

$shell = new Mage_Shell_Generator();
$shell->run();
