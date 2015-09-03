<?php

namespace MacFJA\Tracy;

use Doctrine\DBAL\Logging\DebugStack;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use Tracy\Debugger;
use Tracy\IBarPanel;

/**
 * Class DoctrineSql
 *
 * @author  MacFJA
 * @license MIT
 * @package MacFJA\Tracy
 */
class DoctrineSql implements IBarPanel
{
    /** @var Configuration The doctrine configuration */
    private $doctrineConfiguration;
    /** @var string The name of the panel (Useful if you watch multiple Doctrine instance) */
    private $name = '';

    /**
     * Initialize the panel (set a SQL logger)
     *
     * @param Configuration $doctrineConfiguration The doctrine configuration
     * @param string        $name                  The name of the panel (Useful if you watch multiple Doctrine instance)
     */
    public function __construct(Configuration $doctrineConfiguration, $name = '')
    {
        $doctrineConfiguration->setSQLLogger(new DebugStack());
        $this->doctrineConfiguration = $doctrineConfiguration;
        $this->name = $name;
    }


    /**
     * {@inheritDoc}
     */
    public function getTab()
    {
        ob_start();
        $data = $this->doctrineConfiguration->getSQLLogger()->queries;
        require __DIR__ . '/assets/doctrine-sql.tab.phtml';
        return ob_get_clean();
    }


    /**
     * {@inheritDoc}
     */
    public function getPanel()
    {
        ob_start();
        $data = $this->doctrineConfiguration->getSQLLogger()->queries;
        require __DIR__ . '/assets/doctrine-sql.panel.phtml';
        return ob_get_clean();
    }

    /**
     * Create and initialize a new Doctrine Sql tab/panel.
     * The panel will be attach to the Tracy Debugger Bar.
     *
     * @param EntityManagerInterface $entityManager The doctrine manager to watch
     */
    public static function init(EntityManagerInterface $entityManager, $name = '') {
        Debugger::getBar()->addPanel(new static($entityManager->getConfiguration(), $name));
    }

    protected function formatArrayData($data) {
        return preg_replace(
            '#^\s{4}#m', '', // Remove 1rst "tab" of the JSON result
            substr(
                json_encode($data, JSON_PRETTY_PRINT|JSON_NUMERIC_CHECK),
                2, // Remove "[\n"
                -2 // Remove "\n]"
            )
        );
    }
    protected function transformNumericType($data) {
        $search = array(
            '#\b101\b#', // Array of int
            '#\b102\b#', // Array of string

        );
        $replace = array(
            'integer[]', // Array of int
            'string[]', // Array of string
        );

        return preg_replace($search, $replace, $data);
    }
}