<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic Contributors. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\InstallBundle\Configurator\Step;

use Mautic\InstallBundle\Configurator\Form\EmailStepType;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Email Step.
 */
class EmailStep implements StepInterface
{

    /**
     * From name for email sent from Mautic
     *
     * @var string
     * @Assert\NotBlank(message = "mautic.install.notblank")
     */
    var $mailer_from_name;

    /**
     * From email sent from Mautic
     *
     * @var string
     * @Assert\NotBlank(message = "mautic.install.notblank")
     * @Assert\Email(message = "mautic.install.invalidemail")
     */
    var $mailer_from_email;

    /**
     * Mail transport
     *
     * @var string
     */
    var $mailer_transport = 'mail';

    /**
     * SMTP host
     *
     * @var string
     */
    var $mailer_host;

    /**
     * SMTP port
     *
     * @var string
     */
    var $mailer_port;

    /**
     * SMTP username
     *
     * @var string
     */
    var $mailer_user;

    /**
     * SMTP password
     *
     * @var string
     */
    var $mailer_password;

    /**
     * SMTP encryption
     *
     * @var string
     */
    var $mailer_encryption; // null|tls|ssl

    /**
     * SMTP auth mode
     *
     * @var string
     */
    var $mailer_auth_mode; //  null|plain|login|cram-md5

    /**
     * Spool mode
     *
     * @var string
     */
    var $mailer_spool_type = 'memory'; // file|memory

    /**
     * Spool path
     *
     * @var string
     */
    var $mailer_spool_path = "%kernel.root_dir%/spool";

    /**
     * {@inheritdoc}
     */
    public function getFormType()
    {
        return new EmailStepType();
    }

    /**
     * {@inheritdoc}
     */
    public function checkRequirements()
    {
        return array();
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
    public function getTemplate()
    {
        return 'MauticInstallBundle:Install:email.html.php';
    }

    /**
     * {@inheritdoc}
     */
    public function update(StepInterface $data)
    {
        $parameters = array();

        foreach ($data as $key => $value) {
            $parameters[$key] = $value;
        }

        return $parameters;
    }
}
