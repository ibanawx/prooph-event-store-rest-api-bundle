<?php
namespace Ibanawx\Bundle\Prooph\EventStore\RestApiBundle\Tests\Functional\Symfony;

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{

    public function registerBundles()
    {
        return [
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Prooph\Bundle\EventStore\ProophEventStoreBundle(),
            new \Ibanawx\Bundle\Prooph\EventStore\RestApiBundle\ProophEventStoreRestApiBundle()
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__ . '/config/config.yml');
    }

}