<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic Contributors. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\InstallBundle\Configurator\Step;

use Mautic\InstallBundle\Configurator\Form\DoctrineStepType;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Doctrine Step.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class DoctrineStep implements StepInterface
{
    /**
     * Database driver
     *
     * @Assert\Choice(callback="getDriverKeys")
     */
    public $driver = 'mysqli';

    /**
     * Database host
     *
     * @Assert\NotBlank(message = "mautic.install.notblank")
     */
    public $host = 'localhost';

    /**
     * Database table prefix
     *
     * @var string
     */
    public $table_prefix;

    /**
     * Database connection port
     *
     * @Assert\Range(min = "0")
     */
    public $port = 3306;

    /**
     * Database name
     *
     * @Assert\NotBlank(message = "mautic.install.notblank")
     */
    public $name;

    /**
     * Database user
     * @Assert\NotBlank(message = "mautic.install.notblank")
     */
    public $user;

    /**
     * Database user's password
     *
     * @var string
     */
    public $password;

    /**
     * Path to database
     *
     * @var string
     */
    public $path;

    /**
     * Backup tables if they exist; otherwise drop them
     *
     * @var bool
     */
    public $backup_tables = true;

    /**
     * Prefix for backup tables
     *
     * @var string
     */
    public $backup_prefix = 'bak_';

    /**
     * Constructor
     *
     * @param array $parameters
     */
    public function __construct(array $parameters)
    {
        foreach ($parameters as $key => $value) {
            if (0 === strpos($key, 'db_')) {
                $parameters[substr($key, 3)] = $value;
                $key = substr($key, 3);
                $this->$key = $value;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getFormType()
    {
        return new DoctrineStepType();
    }

    /**
     * {@inheritdoc}
     */
    public function checkRequirements()
    {
        $messages = array();

        if (!class_exists('\PDO')) {
            $messages[] = 'mautic.install.pdo.mandatory';
        } else {
            $drivers = \PDO::getAvailableDrivers();
            if (0 == count($drivers)) {
                $messages[] = 'mautic.install.pdo.drivers';
            }
        }

        return $messages;
    }

    /**
     * {@inheritdoc}
     */
    public function checkOptionalSettings()
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function update(StepInterface $data)
    {
        $parameters = array();

        foreach ($data as $key => $value) {
            // Exclude backup params from the config
            if (substr($key, 0, 6) != 'backup') {
                $parameters['db_' . $key] = $value;
            }
        }

        return $parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplate()
    {
        return 'MauticInstallBundle:Install:doctrine.html.php';
    }

    /**
     * Return the key values of the available driver array
     *
     * @return array
     */
    public static function getDriverKeys()
    {
        return array_keys(static::getDrivers());
    }

    /**
     * Fetches the available database drivers for the environment
     *
     * @return array
     */
    public static function getDrivers()
    {
        $supported = array(
            'pdo_mysql'  => 'MySQL (PDO)',
            'pdo_sqlite' => 'SQLite (PDO)',
            'pdo_pgsql'  => 'PosgreSQL (PDO)',
            'pdo_oci'    => 'Oracle (PDO)',
            'pdo_ibm'    => 'IBM DB2 (PDO)',
            'pdo_sqlsrv' => 'SQLServer (PDO)',
            'oci8'       => 'Oracle (native)',
            'ibm_db2'    => 'IBM DB2 (native)',
            'mysqli'     => 'MySQLi',
        );

        $available = array();

        // Add PDO drivers if they're supported
        foreach (\PDO::getAvailableDrivers() as $driver) {
            if (array_key_exists('pdo_' . $driver, $supported)) {
                $available['pdo_' . $driver] = $supported['pdo_' . $driver];
            }
        }

        // Add MySQLi if available
        if (function_exists('mysqli_connect')) {
            $available['mysqli'] = 'MySQLi';
        }

        return $available;
    }
}
