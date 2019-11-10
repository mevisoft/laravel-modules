<?php

namespace Nwidart\Modules\Tests\Commands;

use Nwidart\Modules\Contracts\RepositoryInterface;
use Nwidart\Modules\Module;
use Nwidart\Modules\Tests\BaseTestCase;

class EnableCommandTest extends BaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('module:make', ['name' => ['Blog']]);
    }

    public function tearDown(): void
    {
        $this->app[RepositoryInterface::class]->delete('Blog');
        parent::tearDown();
    }

    /** @test */
    public function it_enables_a_module()
    {
        /** @var Module $blogModule */
        $blogModule = $this->app[RepositoryInterface::class]->find('Blog');
        $blogModule->disable();

        $this->artisan('module:enable', ['module' => 'Blog']);

        $this->assertTrue($blogModule->isEnabled());
    }
}
