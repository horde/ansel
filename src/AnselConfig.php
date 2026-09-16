<?php

declare(strict_types=1);
/**
 * Ansel configuration class
 *
 * Provides access to the Ansel configuration settings.
 *
 * Old pattern: globals $conf; $somethingDetail = $conf['something']['detail']; *
 * New pattern: $config = $injector->get(AnselConfig::class); $somethingDetail = $config->get('something.detail');
 *
 * Prefer DI over instantiating $config in your code.
 */

namespace Horde\Ansel;

use Horde\Core\Config\State;
use Horde\Injector\Attribute\Factory;

#[Factory(factory: AnselConfigFactory::class, method: 'create')]
class AnselConfig extends State {}
