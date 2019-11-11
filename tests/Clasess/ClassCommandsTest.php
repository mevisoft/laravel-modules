<?php


namespace Nwidart\Modules\Tests\Clasess;


use Nwidart\Modules\Commands\MigrateCommand;
use Nwidart\Modules\Tests\BaseTestCase;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class ClassCommandsTest extends BaseTestCase
{
    /** @test */
    public function commandTest()
    {
        $this->assertTrue(is_subclass_of(MigrateCommand::class, SymfonyCommand::class), "yes");
    }
    /** @test */
    public function commandTestFalse()
    {
        $this->assertTrue(is_subclass_of(ClassCommandsTest::class, SymfonyCommand::class), "NO");
    }
}
