#!/usr/bin/env php
<?php

use Symfony\Component\Console\Application;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\DialogHelper;
use Mage_Core_Model_Factory as MageFactory;

require_once (dirname(__FILE__) . '/../../../autoload.php');

class Generator extends Command
{
    /**
     * Magento Root path
     *
     * @var string
     */
    protected $_rootPath;

    /**
     * Initialize application with code (store, website code)
     *
     * @var string
     */
    protected $_appCode     = 'admin';

    /**
     * Initialize application code type (store, website, store_group)
     *
     * @var string
     */
    protected $_appType     = 'store';

    /**
     * Input arguments
     *
     * @var array
     */
    protected $_args        = array();

    /**
     * Factory instance
     *
     * @var Mage_Core_Model_Factory
     */
    protected $_factory;

    /**
     * Progress Bar
     * @var null
     */
    protected $_progress = null;

    /**
     * Initialize application and parse input parameters
     */
    public function __construct()
    {
        parent::__construct();

        require_once $this->_getRootPath() . 'app' . DIRECTORY_SEPARATOR . 'Mage.php';
        Mage::app($this->_appCode, $this->_appType);

        $this->_applyPhpVariables();
        $this->_validate();
    }

    /**
     * Interacts with the user.
     *
     * @param InputInterface $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     * @param bool $displayWelcome
     */
    public function interact(InputInterface $input, OutputInterface $output, $displayWelcome = true)
    {
        $helper = $this->_getHelper();
        if ($displayWelcome) {
            $welcome = '
    ______      __  _ __           ______                           __
   / ____/___  / /_(_) /___  __   / ____/__  ____  ___  _________ _/ /_____  _____
  / __/ / __ \/ __/ / __/ / / /  / / __/ _ \/ __ \/ _ \/ ___/ __ `/ __/ __ \/ ___/
 / /___/ / / / /_/ / /_/ /_/ /  / /_/ /  __/ / / /  __/ /  / /_/ / /_/ /_/ / /
/_____/_/ /_/\__/_/\__/\__, /   \____/\___/_/ /_/\___/_/   \__,_/\__/\____/_/
                      /____/
        ';
            $output->writeln('<info>' . $welcome . '</info>');
        }

        if (!$input->getOption('entity-type')) {
            $dialog             = $this->getHelperSet()->get('dialog');

            // Get the entity type
            $entityTypeCommands = $this->_getCommands('type');
            $entityType         = $dialog->select($output,
                '<fg=green;options=bold>Please, enter the entity type you want to generate</fg=green;options=bold>',
                $entityTypeCommands,
                0
            );

            //If user choose exit
            if ($entityType == 'exit') {
                $output->writeln($helper->__('Oki Bye'));
                return;
            }

            //We'll store in $data all the information needed to generate entities
            $data = array();

            //If category is selected we need more information : parent_id
            if ($entityType == 'category') {
                $parentId = $dialog->select($output,
                    '<fg=green;options=bold>The ID of the category parent is required</fg=green;options=bold>',
                    array(
                        'random' => $helper->__('Whatever, do it randomly'),
                        'config' => $helper->__('Use the one I\'ve configured in System -> Configuration -> Entity Generator'),
                        'fixed'  => $helper->__('I want to enter an existing category ID manually')
                    ),
                    0
                );

                if ($parentId == 'fixed') {
                    $parentId = $dialog->askAndValidate( $output,
                        '<fg=green;options=bold>Enter the ID of an existing category : </fg=green;options=bold>',
                        function ($categoryId) {
                            $category = Mage::getModel('catalog/category')->load($categoryId);
                            if (!$category->getId()) {
                                throw new \RunTimeException(
                                    $this->_getHelper()->__('Category with ID %s does not exist', $categoryId)
                                );
                            }
                            return $categoryId;
                        },
                        false
                    );
                }

                $data['parent_id'] = $parentId;
            }

            if ($entityType != 'order') {
                // Get the desired locale
                $localeCommands = $this->_getCommands('locale');
                $locale         = $dialog->select($output,
                    '<fg=green;options=bold>Please, enter the desired language</fg=green;options=bold>',
                    $localeCommands,
                    0
                );

                //If user choose exit
                if ($locale == 'exit') {
                    $output->writeln($helper->__('Oki Bye'));
                    return;
                }
                /* Set Faker locale in the register */
                Mage::register('faker_locale', $locale);
            }

            // Get the number of entities to generate
            $nbrOfEntities = $dialog->askAndValidate( $output,
                '<fg=green;options=bold>Please, enter the number of entities you want to generate : </fg=green;options=bold>',
                function ($nbr) {
                    $nbr = (int) $nbr;
                    if ($nbr <= 0) {
                        throw new \RunTimeException(
                            $this->_getHelper()->__('This parameter should be a valid number greater than 0.')
                        );
                    }
                    return $nbr;
                },
                false,
                1
            );

            //Now we start to generate entities
            $creatorInstance = Mage::getModel('maverick_generator/' . $this->_getEntityClass($entityType));
            if (!is_object($creatorInstance)) {
                throw new \RunTimeException(
                    $this->_getHelper()->__('Unable to find entity type %s', $entityType)
                );
            }

            $output->writeln($helper->__('<fg=cyan>Generating %s %s entities</fg=cyan>', $nbrOfEntities, $entityType));
            $result = $this->_generateEntities($creatorInstance, $output, $nbrOfEntities, $data);

            if (is_array($result) && !empty($result)) {
                if ($dialog->askConfirmation($output,
                    '<question>' . $helper->__('Do you want to display results (y/n) : ') . '</question>',
                    true
                )) {
                    foreach ($result as $message) {
                        $output->writeln($helper->__('<fg=cyan>' . $message . '</fg=cyan>'));
                    }
                }
            }


        }
    }

    protected function _generateEntities($creatorInstance, $output, $nbrOfEntities, $data)
    {
        $progress   = $this->_initProgressBar();
        $result     = array();

        $progress->start($output, $nbrOfEntities);
        $progress->display();

        for ($i=0; $i<$nbrOfEntities; $i++) {
            $messages   = $creatorInstance->createOneEntity($data);
            $result     = array_merge($result, $messages);
            $progress->advance();
        }

        $progress->finish();

        return $result;
    }

    /**
     * Get commands
     *
     * @param $class
     * @return array
     */
    protected function _getCommands($class)
    {
        $commands           = Mage::getSingleton('maverick_generator/source_entity_' . $class)->optionsForShell();
        $commands['exit']   = $this->_getHelper()->__('Exit');

        return $commands;
    }

    protected function _getEntityClass($entityType)
    {
        return (string)Mage::app()->getConfig()->getNode('generator/entities/' . $entityType . '/class');
    }

    /**
     * Get Faker helper
     *
     * @return Maverick_Generator_Helper_Faker
     */
    protected function _getHelper()
    {
        return Mage::helper('maverick_generator/faker');
    }

    /**
     * Get Magento Root path (with last directory separator)
     *
     * @return string
     */
    protected function _getRootPath()
    {
        if (is_null($this->_rootPath)) {
            $this->_rootPath = '..' . DIRECTORY_SEPARATOR;
        }
        return $this->_rootPath;
    }

    /**
     * Parse .htaccess file and apply php settings to shell script
     *
     */
    protected function _applyPhpVariables()
    {
        $htaccess = $this->_getRootPath() . '.htaccess';
        if (file_exists($htaccess)) {
            // parse htaccess file
            $data = file_get_contents($htaccess);
            $matches = array();
            preg_match_all('#^\s+?php_value\s+([a-z_]+)\s+(.+)$#siUm', $data, $matches, PREG_SET_ORDER);
            if ($matches) {
                foreach ($matches as $match) {
                    @ini_set($match[1], str_replace("\r", '', $match[2]));
                }
            }
            preg_match_all('#^\s+?php_flag\s+([a-z_]+)\s+(.+)$#siUm', $data, $matches, PREG_SET_ORDER);
            if ($matches) {
                foreach ($matches as $match) {
                    @ini_set($match[1], str_replace("\r", '', $match[2]));
                }
            }
        }
    }

    /**
     * Validate arguments
     */
    protected function _validate()
    {
        if (isset($_SERVER['REQUEST_METHOD'])) {
            die('This script cannot be run from Browser. This is a shell script.');
        }
    }

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('generate')
            ->setDescription('Generate Magento Entities')
            ->addOption('entity-type', null, InputOption::VALUE_REQUIRED, 'Entity Type')
            ->addOption('nbr', null, InputOption::VALUE_REQUIRED, 'Number of entities to create')
            ->setHelp(<<<EOT
The <info>Entity Generator Command</info> helps you genrate Magento entities with fake data.

By default, the command interacts with the user and asks about the entity type :
<comment>category</comment>, <comment>customer</comment>, etc ...

If you want to disable any user interaction, use <comment>--no-interaction</comment>
but don't forget to pass all needed options:

<info>php generator.php generate --entity-type customer --nbr 11</info>
OR
<info>php generator.php generate --entity-type order --nbr 5</info>

EOT
            );
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return null|integer null or 0 if everything went fine, or an error code
     *
     * @throws \LogicException When this abstract method is not implemented
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($entityType = $input->getOption('entity-type')) {

        }
    }

    /**
     * Init progress bar
     *
     * @return null|\Symfony\Component\Console\Helper\HelperInterface
     */
    protected function _initProgressBar()
    {
        if (is_null($this->_progress)) {
            $progress = $this->getHelperSet()->get('progress');
            $progress->setFormat(
                \Symfony\Component\Console\Helper\ProgressHelper::FORMAT_VERBOSE
            );
            $progress->setBarCharacter('<comment>=</comment>');
            $progress->setEmptyBarCharacter('<fg=red>-</fg=red>');
            $progress->setProgressCharacter('>');
            $progress->setBarWidth(50);
            $this->_progress = $progress;
        }

        return $this->_progress;
    }
}

$application = new Application();
$application->add(new Generator);
$application->run();