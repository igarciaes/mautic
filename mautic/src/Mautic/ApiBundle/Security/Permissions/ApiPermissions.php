<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\ApiBundle\Security\Permissions;

use Symfony\Component\Form\FormBuilderInterface;
use Mautic\CoreBundle\Security\Permissions\CommonPermissions;
use Symfony\Component\DependencyInjection\Container;
use Doctrine\ORM\EntityManager;

/**
 * Class ApiPermissions
 *
 * @package Mautic\UserBundle\Security\Permissions
 */
class ApiPermissions extends CommonPermissions
{

    /**
     * {@inheritdoc}
     *
     * @param Container     $container
     * @param EntityManager $em
     */
    public function __construct(Container $container, EntityManager $em)
    {
        parent::__construct($container, $em);
            $this->permissions = array(
                'access' => array(
                    'prohibit' => 1,
                    'full'     => 1024
                )
            );
    }

    /**
     * {@inheritdoc}
     *
     * @return string|void
     */
    public function getName() {
        return 'api';
    }

    /**
     * {@inheritdoc}
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface &$builder, array $options)
    {
        //convert the permission bits from the db into readable names
        $data = $this->convertBitsToPermissionNames($options['permissions']);


        $builder->add('api:access', 'choice', array(
            'choices'  => array(
                'prohibit' => 'mautic.api.permissions.prohibited', //basically no permission will be saved
                'full'     => 'mautic.api.permissions.granted',
            ),
            'label'    => 'mautic.api.permissions.apiaccess',
            'expanded' => true,
            'multiple' => true,
            'attr'     => array(
                'onclick' => 'Mautic.toggleFullPermissions(this, event)'
            ),
            'data'     => (!empty($data['access']) ? $data['access'] : array())
        ));
    }

    /**
     * {inheritdoc}
     *
     * @param $name
     * @param $perm
     */
    public function getValue($name, $perm)
    {
        //ensure api is enabled system wide
        if (!$this->container->getParameter('mautic.api_enabled')) {
            return 0;
        } else {
            return parent::getValue($name, $perm);
        }
    }

    /**
     * {inheritdoc}
     *
     * @return bool|mixed
     */
    public function isEnabled() {
        return $this->container->getParameter('mautic.api_enabled', 0);
    }
}