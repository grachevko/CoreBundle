<?php

namespace Preemiere\CoreBundle\DependencyInjection;

use Doctrine\DBAL\Types\Type;
use Preemiere\CoreBundle\Doctrine\EnumType;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class PreemiereCoreExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        if ($container->hasParameter('doctrine.entity_managers')) {
            if (!Type::hasType(EnumType::ENUM)) {
                Type::addType(EnumType::ENUM, EnumType::class);
            }

            $entityManagerNameList = $container->getParameter('doctrine.entity_managers');
            foreach ($entityManagerNameList as $entityManagerName) {
                $em = $container->get($entityManagerName);
                if (!$em->getConnection()->getDatabasePlatform()->hasDoctrineTypeMappingFor(EnumType::ENUM)) {
                    $em->getConnection()->getDatabasePlatform()->registerDoctrineTypeMapping(EnumType::ENUM, EnumType::ENUM);
                }
            }
        }
    }
}
