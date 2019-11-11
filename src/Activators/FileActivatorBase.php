<?php

namespace Nwidart\Modules\Activators;

use Illuminate\Cache\CacheManager;
use Illuminate\Config\Repository as Config;
use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;
use Nwidart\Modules\Contracts\ActivatorInterface;
use Nwidart\Modules\Module;

abstract class FileActivatorBase implements ActivatorInterface
{
    /**
     * Laravel cache instance
     *
     * @var CacheManager
     */
    protected $cache;

    /**
     * Laravel Filesystem instance
     *
     * @var Filesystem
     */
    protected $files;

    /**
     * Laravel config instance
     *
     * @var Config
     */
    protected $config;

    /**
     * @var string
     */
    protected $cacheKey;

    /**
     * @var string
     */
    protected $cacheLifetime;

    /**
     * Array of modules activation statuses
     *
     * @var array|ModuleStatus[]
     */
    protected $modulesStatuses;

    /**
     * File used to store activation statuses
     *
     * @var string
     */
    protected $statusesFile;

    public function __construct(Container $app)
    {
        $this->cache = $app['cache'];
        $this->files = $app['files'];
        $this->config = $app['config'];
        $this->statusesFile = $this->config('statuses-file');
        $this->cacheKey = $this->config('cache-key');
        $this->cacheLifetime = $this->config('cache-lifetime');
        $this->modulesStatuses = $this->getModulesStatuses();
    }

    /**
     * Array of modules activation statuses
     *
     * @return  array|ModuleStatus[]
     */
    abstract protected function getModulesStatuses(): array;

    /**
     * Get the path of the file where statuses are stored
     *
     * @return string
     */
    public function getStatusesFilePath(): string
    {
        return $this->statusesFile;
    }

    /**
     * @param $module
     * @return mixed|ModuleStatus|null
     */
    protected function getConfigModule($module)
    {
        if (!isset($this->modulesStatuses[$module])) {
            return null;
        }
        $m = $this->modulesStatuses[$module];
        if ($m instanceof ModuleStatus) {
            return $m;
        }
        $m = optional($this->modulesStatuses[$module]);

        return new ModuleStatus($module, (bool)$m->enable, (bool)$m->install);
    }

    /**
     * Enables a module
     *
     * @param Module $module
     */
    public function enable(Module $module): void
    {
        $this->setActiveByName($module->getName(), true, $module->isInstall());
    }

    /**
     * Disables a module
     *
     * @param Module $module
     */
    public function disable(Module $module): void
    {
        $this->setActiveByName($module->getName(), false, $module->isInstall());
    }

    /**
     * @param Module $module
     */
    public function install(Module $module): void
    {
        $this->setInstallByName($module->getName(), true);
    }

    /**
     * @param Module $module
     */
    public function uninstall(Module $module): void
    {
        $this->setInstallByName($module->getName(), false);
    }

    /**
     * Determine whether the given status same with a module status.
     *
     * @param Module $module
     * @param bool $status
     *
     * @return bool
     */
    public function hasStatus(Module $module, bool $status): bool
    {
        if (($config = $this->getConfigModule($module->getName())) != null) {
            return $config->enable === $status;
        }

        return $status === false;
    }

    /**
     * Determine whether the given status same with a module status.
     *
     * @param Module $module
     * @param bool $status
     *
     * @return bool
     */
    public function hasInstall(Module $module, bool $status): bool
    {
        if (($config = $this->getConfigModule($module->getName())) != null) {
            return $config->install === $status;
        }

        return $status === false;
    }

    /**
     * Set active state for a module.
     *
     * @param Module $module
     * @param bool $active
     */
    public function setActive(Module $module, bool $active): void
    {
        $this->setActiveByName($module->getName(), $active, $this->hasInstall($module, true));
    }

    /**
     * Set active state for a module.
     *
     * @param Module $module
     * @param bool $active
     */
    public function setInstall(Module $module, bool $active): void
    {
        $this->setInstallByName($module->getName(), $active);
    }

    /**
     * Sets a module status by its name
     *
     * @param string $name
     * @param bool $active
     * @param bool $install
     */
    public function setActiveByName(string $name, bool $active, bool $install): void
    {
        $this->modulesStatuses[$name] = new ModuleStatus($name, ($active ? $install == $active : false), $install);
        $this->flushCache();
    }

    /**
     * Sets a module status by its name
     *
     * @param string $name
     * @param bool $active
     */
    public function setInstallByName(string $name, bool $active): void
    {
        $this->modulesStatuses[$name] = new ModuleStatus($name, $active, $active);
        $this->flushCache();
    }

    /**
     * Deletes a module activation status
     *
     * @param Module $module
     */
    public function delete(Module $module): void
    {
        if (!isset($this->modulesStatuses[$module->getName()])) {
            return;
        }
        unset($this->modulesStatuses[$module->getName()]);
        $this->flushCache();
    }

    /**
     * Deletes any module activation statuses created by this class.
     */
    public function reset(): void
    {
        if ($this->files->exists($this->statusesFile)) {
            $this->files->delete($this->statusesFile);
        }
        $this->modulesStatuses = [];
        $this->flushCache();
    }

    /**
     * Reads a config parameter under the 'activators.file' key
     *
     * @param string $key
     * @param  $default
     * @return mixed
     */
    private function config(string $key, $default = null)
    {
        $active = $this->config->get('modules.activator');

        return $this->config->get('modules.activators.' . $active . '.' . $key, $default);
    }

    /**
     * Flushes the modules activation statuses cache
     */
    protected function flushCache(): void
    {
        $this->cache->forget($this->cacheKey);
    }
}
