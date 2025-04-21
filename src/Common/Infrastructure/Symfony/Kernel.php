<?php

declare(strict_types=1);

namespace App\Common\Infrastructure\Symfony;

use App\Common\Infrastructure\DependencyInjection\DomainEventBusPass;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

final class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function getProjectDir(): string
    {
        return __DIR__ . '/../../../..';
    }

    protected function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new DomainEventBusPass());
    }
}
