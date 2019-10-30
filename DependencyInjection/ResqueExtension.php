<?php

/*
 * This file is part of the AllProgrammic ResqueBunde package.
 *
 * (c) AllProgrammic SAS <contact@allprogrammic.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AllProgrammic\Bundle\ResqueBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class ResqueExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = new Configuration();
        $config = $this->processConfiguration($config, $configs);
        $config = $container->resolveEnvPlaceholders($config, true);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        // Load config
        $container->setParameter('resque_worker_sleeping', $config['worker']['sleeping']);
        $container->setParameter('resque_redis_dsn', $config['redis']['dsn']);
        $container->setParameter('resque_redis_prefix', $config['redis']['prefix']);
        $container->setParameter('resque_alert_subject', $config['alert']['subject']);
        $container->setParameter('resque_alert_from', $config['alert']['from']);
        $container->setParameter('resque_alert_to', $config['alert']['to']);
    }
}
