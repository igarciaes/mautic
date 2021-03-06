<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic Contributors. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\CoreBundle\Loader;

use Mautic\CoreBundle\Factory\MauticFactory;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\Loader\LoaderInterface;
use Symfony\Component\Translation\MessageCatalogue;

/**
 * Class TranslationLoader
 */
class TranslationLoader extends ArrayLoader implements LoaderInterface
{

    /**
     * @var MauticFactory
     */
    protected $factory;

    /**
     * @param MauticFactory $factory
     */
    public function __construct(MauticFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function load($resource, $locale, $domain = 'messages')
    {
        $bundles   = $this->factory->getParameter('bundles');
        $addons    = $this->factory->getEnabledAddons();
        $catalogue = new MessageCatalogue($locale);

        foreach ($bundles as $name => $bundle) {
            //load translations
            $translations = $bundle['directory'] . '/Translations/' . $locale;
            if (file_exists($translations)) {

                $iniFiles = new Finder();
                $iniFiles->files()->in($translations)->name('*.ini');

                foreach ($iniFiles as $file) {
                    $iniFile = $file->getRealpath();
                    $messages = parse_ini_file($iniFile, true);
                    $domain  = substr($file->getFilename(), 0, -4);

                    $thisCatalogue = parent::load($messages, $locale, $domain);
                    $catalogue->addCatalogue($thisCatalogue);
                }
            }
        }

        foreach ($addons as $name => $bundle) {
            //load translations
            $translations = $bundle['directory'] . '/Translations/' . $locale;
            if (file_exists($translations)) {

                $iniFiles = new Finder();
                $iniFiles->files()->in($translations)->name('*.ini');

                foreach ($iniFiles as $file) {
                    $iniFile = $file->getRealpath();
                    $messages = parse_ini_file($iniFile, true);
                    $domain  = substr($file->getFilename(), 0, -4);

                    $thisCatalogue = parent::load($messages, $locale, $domain);
                    $catalogue->addCatalogue($thisCatalogue);
                }
            }
        }

        //get some values for translation loading
        $themeDir = $this->factory->getSystemPath('currentTheme', true);
        if (file_exists($override = $themeDir . '/translations/' . $locale)) {
            $iniFiles = new Finder();
            $iniFiles->files()->in($override)->name('*.ini');
            foreach ($iniFiles as $file) {
                $iniFile  = $file->getRealPath();
                $messages = parse_ini_file($iniFile, true);
                $domain   = substr($file->getFilename(), 0, -4);

                $thisCatalogue = parent::load($messages, $locale, $domain);
                $catalogue->addCatalogue($thisCatalogue);
            }
        }

        return $catalogue;
    }
}
