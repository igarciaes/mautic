<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic Contributors. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Mautic Application Kernel
 */
class AppKernel extends Kernel
{

    /**
     * Major version number
     *
     * @const integer
     */
    const MAJOR_VERSION = 1;

    /**
     * Minor version number
     *
     * @const integer
     */
    const MINOR_VERSION = 0;

    /**
     * Patch version number
     *
     * @const integer
     */
    const PATCH_VERSION = 0;

    /**
     * Extra version identifier
     *
     * This constant is used to define additional version segments such as development
     * or beta status.
     *
     * @const string
     */
    const EXTRA_VERSION = '-beta3';

    /**
     * @var array
     */
    private $addonBundles  = array();

    /**
     * {@inheritdoc}
     */
    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        if (strpos($request->getRequestUri(), 'installer') !== false || !$this->isInstalled()) {
            define('MAUTIC_INSTALLER', 1);
        } else {
            //set the table prefix before boot
            $localParams = $this->getLocalParams();
            $prefix      = isset($localParams['db_table_prefix']) ? $localParams['db_table_prefix'] : '';
            define('MAUTIC_TABLE_PREFIX', $prefix);
        }

        if (false === $this->booted) {
            $this->boot();
        }
        //the context is not populated at this point so have to do it manually
        $router = $this->getContainer()->get('router');
        $requestContext = new \Symfony\Component\Routing\RequestContext();
        $requestContext->fromRequest($request);
        $router->setContext($requestContext);

        if (strpos($request->getRequestUri(), 'installer') === false && !$this->isInstalled()) {
            //the context is not populated at this point so have to do it manually
            $router = $this->getContainer()->get('router');
            $requestContext = new \Symfony\Component\Routing\RequestContext();
            $requestContext->fromRequest($request);
            $router->setContext($requestContext);

            $base  = $requestContext->getBaseUrl();
            //check to see if the .htaccess file exists or if not running under apache
            if ((strpos(strtolower($_SERVER["SERVER_SOFTWARE"]), 'apache') === false || !file_exists(__DIR__ .'../.htaccess') && strpos($base, 'index') === false)) {
                $base .= '/index.php';
            }

            //return new RedirectResponse();
            return new RedirectResponse($base . '/installer');
        }

        return parent::handle($request, $type, $catch);
    }

    /**
     * {@inheritdoc}
     */
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(),
            new Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new Knp\Bundle\MenuBundle\KnpMenuBundle(),
            new FOS\OAuthServerBundle\FOSOAuthServerBundle(),
            new Bazinga\OAuthServerBundle\BazingaOAuthServerBundle(),
            new FOS\RestBundle\FOSRestBundle(),
            new JMS\SerializerBundle\JMSSerializerBundle(),
        );

        //dynamically register Mautic Bundles
        $searchPath = __DIR__ . '/bundles';
        $finder     = new \Symfony\Component\Finder\Finder();
        $finder->files()
            ->in($searchPath)
            ->depth('1')
            ->name('*Bundle.php');

        foreach ($finder as $file) {
            $path      = substr($file->getRealPath(), strlen($searchPath) + 1, -4);
            $parts     = explode(DIRECTORY_SEPARATOR, $path);
            $class     = array_pop($parts);
            $namespace = "Mautic\\" . implode('\\', $parts);
            $class     = $namespace . '\\' . $class;
            if (class_exists($class)) {
                $bundleInstance = new $class();
                if (method_exists($bundleInstance, 'isEnabled')) {
                    if ($bundleInstance->isEnabled()) {
                        $bundles[] = $bundleInstance;
                    }
                } else {
                    $bundles[] = $bundleInstance;
                }
            }
        }

        //dynamically register Mautic Addon Bundles
        $searchPath = dirname(__DIR__) . '/addons';
        $finder     = new \Symfony\Component\Finder\Finder();
        $finder->files()
            ->depth('1')
            ->in($searchPath)
            ->name('*Bundle.php');

        foreach ($finder as $file) {
            $path      = substr($file->getRealPath(), strlen($searchPath) + 1, -4);
            $parts     = explode(DIRECTORY_SEPARATOR, $path);
            $class     = array_pop($parts);
            $namespace = "MauticAddon\\" . implode('\\', $parts);
            $class     = $namespace . '\\' . $class;
            if (class_exists($class)) {
                $bundles[] = new $class();
            }
        }

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Symfony\Bundle\TwigBundle\TwigBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
            $bundles[] = new Nelmio\ApiDocBundle\NelmioApiDocBundle();
            $bundles[] = new Webfactory\Bundle\ExceptionsBundle\WebfactoryExceptionsBundle();
        }

        if (in_array($this->getEnvironment(), array('test'))) {
            $bundles[] = new Liip\FunctionalTestBundle\LiipFunctionalTestBundle();
        }

        return $bundles;
    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        if (true === $this->booted) {
            return;
        }

        if ($this->loadClassCache) {
            $this->doLoadClassCache($this->loadClassCache[0], $this->loadClassCache[1]);
        }

        // init bundles
        $this->initializeBundles();

        // init container
        $this->initializeContainer();

        $registeredAddonBundles = $this->container->getParameter('mautic.addon.bundles');

        $addonBundles = array();
        foreach ($this->getBundles() as $name => $bundle) {
            if ($bundle instanceof \Mautic\AddonBundle\Bundle\AddonBundleBase) {
                //boot after it's been check to see if it's enabled
                $addonBundles[$name] = $bundle;

                //set the container for the addon helper
                $bundle->setContainer($this->container);
            } else {
                $bundle->setContainer($this->container);
                $bundle->boot();
            }
        }

        $factory = $this->container->get('mautic.factory');

        $dispatcher = $factory->getDispatcher();
        $listeners  = $dispatcher->getListeners();

        // addon listeners have to be removed if disabled so loop to find MauticAddon listeners
        $addonListeners = array();

        foreach ($listeners as $event => $subscribers) {
            foreach ($subscribers as $subscriber) {
                if (is_array($subscriber)) {
                    $name = is_object($subscriber[0]) ? get_class($subscriber[0]) : $subscriber[0];
                } else {
                    $name = $subscriber;
                }

                if (strpos($name, 'MauticAddon') !== false) {
                    //get the name of the bundle
                    $parts                         = explode('\\', $name);
                    $bundlePath                    = $parts[0] . '\\' . $parts[1];
                    $addonListeners[$bundlePath][] = array($event, $subscriber);
                }
            }
        }

        // It's only after we've booted that we have access to the container, so here is where we will check if addon bundles are enabled then deal with them accordingly
        foreach ($addonBundles as $name => $bundle) {
            if (!$bundle->isEnabled()) {
                unset($this->bundles[$name]);
                unset($this->bundleMap[$name]);
                unset($registeredAddonBundles[$name]);

                // remove listeners as well
                $bundleClass = get_class($bundle);

                $parts      = explode('\\', $bundleClass);
                $bundlePath = $parts[0] . '\\' . $parts[1];

                if (isset($addonListeners[$bundlePath])) {
                    foreach ($addonListeners[$bundlePath] as $listener) {
                        $dispatcher->removeListener($listener[0], $listener[1]);
                    }
                }
            } else {
                // boot the bundle
                $bundle->boot();
            }
        }

        $this->addonBundles = $registeredAddonBundles;

        $this->booted = true;
    }

    /**
     * Returns a list of addon bundles that are enabled
     *
     * @return array
     */
    public function getAddonBundles()
    {
        return $this->addonBundles;
    }

    /**
     * {@inheritdoc}
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__ . '/config/config_' . $this->getEnvironment() . '.php');
    }

    /**
     * Retrieves the application's version number
     *
     * @return string
     */
    public function getVersion()
    {
        return self::MAJOR_VERSION . '.' . self::MINOR_VERSION . '.' . self::PATCH_VERSION . self::EXTRA_VERSION;
    }

    /**
     * Checks if the application has been installed
     *
     * @return bool
     */
    private function isInstalled()
    {
        $params = $this->getLocalParams();
        if (!empty($params)) {
            // Check the DB Driver, Name, and User
            if ((isset($params['db_driver']) && $params['db_driver'])
                && (isset($params['db_user']) && $params['db_user'])
                && (isset($params['db_name']) && $params['db_name'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     *
     * @api
     */
    public function getCacheDir()
    {
        $parameters = $this->getLocalParams();
        if (isset($parameters['cache_path'])) {
            $envFolder = (strpos($parameters['cache_path'], -1) != '/') ? '/' . $this->environment : $this->environment;
            return str_replace('%kernel.root_dir%', $this->getRootDir(), $parameters['cache_path'] . $envFolder);
        } else {
            return parent::getCacheDir();
        }
    }

    /**
     * {@inheritdoc}
     *
     * @api
     */
    public function getLogDir()
    {
        $parameters = $this->getLocalParams();
        if (isset($parameters['log_path'])) {
            return str_replace('%kernel.root_dir%', $this->getRootDir(), $parameters['log_path']);
        } else {
            return parent::getLogDir();
        }
    }

    /**
     * Get Mautic's local configuration file
     *
     * @return array
     */
    private function getLocalParams()
    {
        static $localParameters;

        if (!is_array($localParameters)) {
            /** @var $paths */
            $root = $this->getRootDir();
            include $root . '/config/paths.php';

            if ($configFile = $this->getLocalConfigFile()) {
                /** @var $parameters */
                include $configFile;
                $localParameters = $parameters;
            } else {
                $localParameters = array();
            }

            //check for parameter overrides
            if (file_exists($root . '/config/parameters_local.php')) {
                /** @var $parameters */
                include $root . '/config/parameters_local.php';
                $localParameters = array_merge($localParameters, $parameters);
            }
        }

        return $localParameters;
    }

    /**
     * Get local config file
     *
     * @param $checkExists If true, then return false if the file doesn't exist
     *
     * @return bool
     */
    public function getLocalConfigFile($checkExists = true)
    {
        /** @var $paths */
        $root = $this->getRootDir();
        include $root . '/config/paths.php';

        if (isset($paths['local_config'])) {
            $paths['local_config'] = str_replace('%kernel.root_dir%', $root, $paths['local_config']);
            if (!$checkExists || file_exists($paths['local_config'])) {
                return $paths['local_config'];
            }
        }

        return false;
    }
}
