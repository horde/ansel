<?php

declare(strict_types=1);
/**
 * Ansel configuration class factory
 *
 * Creates instances of the AnselConfig class.
 *
 * Old pattern: globals $conf; $somethingDetail = $conf['something']['detail']; *
 * New pattern: $config = $injector->get(AnselConfig::class); $somethingDetail = $config->get('something.detail');
 *
 * Prefer DI over instantiating $config in your code.
 */

namespace Horde\Ansel;

use Horde\Core\Config\ConfigLoader;
use Horde\Injector\Injector;

class AnselConfigFactory
{
    public function __construct(private Injector $injector) {}

    public function create(): AnselConfig
    {
        $state = $this->injector->get(ConfigLoader::class)->load('ansel');
        return new AnselConfig($state->toArray());
    }
}
