<?php

namespace Tests;

use Closure;
use Crunz\Schedule;
use DekApps\Crunz\Crunz;
use PHPUnit\Framework\TestCase;
use Tests\DekApps\DiIncubator;

class CrunzExtensionTest extends TestCase
{

    private string $rootDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->rootDir = dirname(__DIR__, 1);
    }

    public function testMinimal(): void
    {
        $configurator = new DiIncubator($this->rootDir);
        $configurator->setForceReloadContainer();
        $configurator->addConfig(__DIR__ . '/crunz.neon');

        $container = $configurator->createContainer();

        $crunz = $container->getService('crunz.crunz');
        self::assertInstanceOf(Crunz::class, $crunz);
        self::assertSame($crunz, $container->getByType(Crunz::class));
        
        self::assertSame(count($crunz->getTasks()), 4);
        $crunz->run();
        self::assertSame($crunz->getTasks()[2]->getTask()->isClosure(), true);
        self::assertSame($crunz->getTasks()[0]->getTask()->getExpression(), '* * * * *');
        self::assertSame($crunz->getTasks()[1]->getTask()->getExpression(), '* * * * *');
        self::assertSame($crunz->getTasks()[3]->getTask()->getCommand()->__invoke(), 1);
        
        /**
         * @todo: ;-)
         */
        
    }

}
