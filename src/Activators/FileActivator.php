<?php

namespace Nwidart\Modules\Activators;

use Illuminate\Cache\CacheManager;
use Illuminate\Config\Repository as Config;
use Illuminate\Container\Container;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Nwidart\Modules\Contracts\ActivatorInterface;
use Nwidart\Modules\Module;

class FileActivator implements ActivatorInterface
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
     * Get the path of the file where statuses are stored
     *
     * @return string
     */
    public function getStatusesFilePath(): string
    {
        return $this->statusesFile;
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
        if (!isset($this->modulesStatuses[$module->getName()])) {
            return $status === false;
        }

        return optional($this->modulesStatuses[$module->getName()])->enable === $status;
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
        if (!isset($this->modulesStatuses[$module->getName()])) {
            return $status === false;
        }

        return optional($this->modulesStatuses[$module->getName()])->install === $status;
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
        $this->modulesStatuses[$name] = new ModuleStatus($name, $install == $active, $install);
        $this->writeJson();
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
        $this->writeJson();
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
        $this->writeJson();
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
     * Writes the activation statuses in a file, as json
     */
    private function writeJson(): void
    {
        $this->files->put($this->statusesFile, serialize($this->modulesStatuses));
    }

    /**
     * Reads the json file that contains the activation statuses.
     * @return array
     * @throws FileNotFoundException
     */
    private function readJson(): array
    {
        if (!$this->files->exists($dir = dirname($this->statusesFile))) {
            $this->files->makeDirectory($dir);
        }
        if (!$this->files->exists($this->statusesFile)) {
            return [];
        }
        var_dump(realpath($this->statusesFile));

        return unserialize($this->files->get($this->statusesFile));
    }

    /**
     * Get modules statuses, either from the cache or from
     * the json statuses file if the cache is disabled.
     * @return array
     * @throws FileNotFoundException
     */
    private function getModulesStatuses(): array
    {
        if (!$this->config->get('modules.cache.enabled')) {
            return $this->readJson();
        }

        return $this->cache->remember($this->cacheKey, $this->cacheLifetime, function () {
            return $this->readJson();
        });
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
    private function flushCache(): void
    {
        $this->cache->forget($this->cacheKey);
    }
}
