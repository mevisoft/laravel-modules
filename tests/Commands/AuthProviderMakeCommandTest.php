<?php

namespace Nwidart\Modules\Tests\Commands;

use Nwidart\Modules\Contracts\RepositoryInterface;
use Nwidart\Modules\Tests\BaseTestCase;
use Spatie\Snapshots\MatchesSnapshots;

class AuthProviderMakeCommandTest extends BaseTestCase
{
    use MatchesSnapshots;
    /**
     * @var \Illuminate\Filesystem\Filesystem
     */
    private $finder;
    /**
     * @var string
     */
    private $modulePath;

    public function setUp(): void
    {
        parent::setUp();
        $this->modulePath = base_path('modules/Blog');
        $this->finder = $this->app['files'];
        $this->artisan('module:make', ['name' => ['Blog']]);
    }

    public function tearDown(): void
    {
        $this->app[RepositoryInterface::class]->delete('Blog');
        parent::tearDown();
    }

    /** @test */
    public function it_generates_a_new_service_provider_class()
    {
        $this->artisan('module:auth-provider', ['module' => 'Blog']);

        $this->assertTrue(is_file($this->modulePath . '/Providers/AuthServiceProvider.php'));
    }

    /** @test */
    public function it_generated_correct_file_with_content()
    {
        $this->artisan('module:auth-provider', ['module' => 'Blog']);

        $file = $this->finder->get($this->modulePath . '/Providers/AuthServiceProvider.php');

        $this->assertMatchesSnapshot($file);
    }

    /** @test */
    public function it_can_change_the_default_namespace()
    {
        $this->app['config']->set('modules.paths.generator.provider.path', 'SuperProviders');

        $this->artisan('module:auth-provider', ['module' => 'Blog']);

        $file = $this->finder->get($this->modulePath . '/SuperProviders/AuthServiceProvider.php');

        $this->assertMatchesSnapshot($file);
    }

    /** @test */
    public function it_can_change_the_default_namespace_specific()
    {
        $this->app['config']->set('modules.paths.generator.provider.namespace', 'SuperProviders');

        $this->artisan('module:auth-provider', ['module' => 'Blog']);

        $file = $this->finder->get($this->modulePath . '/Providers/AuthServiceProvider.php');

        $this->assertMatchesSnapshot($file);
    }

    /** @test */
    public function it_can_overwrite_auth_file_names()
    {
        $this->app['config']->set('modules.stubs.files.auths/web', 'SuperAuths/web.php');
        $this->app['config']->set('modules.stubs.files.auths/api', 'SuperAuths/api.php');

        $this->artisan('module:auth-provider', ['module' => 'Blog', '--force' => true]);

        $file = $this->finder->get($this->modulePath . '/Providers/AuthServiceProvider.php');

        $this->assertMatchesSnapshot($file);
    }

    /** @test */
    public function it_can_overwrite_file(): void
    {
        $this->artisan('module:auth-provider', ['module' => 'Blog']);
        $this->app['config']->set('modules.stubs.files.auths/web', 'SuperAuths/web.php');

        $this->artisan('module:auth-provider', ['module' => 'Blog', '--force' => true]);
        $file = $this->finder->get($this->modulePath . '/Providers/AuthServiceProvider.php');

        $this->assertMatchesSnapshot($file);
    }

    /** @test */
    public function it_can_change_the_custom_controller_namespace(): void
    {
        $this->app['config']->set('modules.paths.generator.controller.path', 'Base/Http/Controllers');
        $this->app['config']->set('modules.paths.generator.provider.path', 'Base/Providers');

        $this->artisan('module:auth-provider', ['module' => 'Blog']);
        $file = $this->finder->get($this->modulePath . '/Base/Providers/AuthServiceProvider.php');

        $this->assertMatchesSnapshot($file);
    }
}
