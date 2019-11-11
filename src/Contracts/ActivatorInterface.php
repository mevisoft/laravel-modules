<?php

namespace Nwidart\Modules\Contracts;

use Nwidart\Modules\Module;

interface ActivatorInterface
{
    /**
     * Enables a module
     *
     * @param Module $module
     */
    public function enable(Module $module): void;

    /**
     * Disables a module
     *
     * @param Module $module
     */
    public function disable(Module $module): void;

    /**
     * @param Module $module
     */
    public function install(Module $module): void;

    /**
     * @param Module $module
     */
    public function uninstall(Module $module): void;

    /**
     * Determine whether the given status same with a module status.
     *
     * @param Module $module
     * @param bool $status
     *
     * @return bool
     */
    public function hasStatus(Module $module, bool $status): bool;

    /**
     * Determine whether the given status same with a module status.
     *
     * @param Module $module
     * @param bool $status
     *
     * @return bool
     */
    public function hasInstall(Module $module, bool $status): bool;

    /**
     * Set active state for a module.
     *
     * @param Module $module
     * @param bool $active
     */
    public function setActive(Module $module, bool $active): void;

    /**
     * Set active state for a module.
     *
     * @param Module $module
     * @param bool $active
     */
    public function setInstall(Module $module, bool $active): void;

    /**
     * Sets a module status by its name
     *
     * @param string $name
     * @param bool $active
     */
    public function setActiveByName(string $name, bool $active, bool $install): void;

    /**
     * Sets a module status by its name
     *
     * @param string $name
     * @param bool $active
     */
    public function setInstallByName(string $name, bool $active): void;

    /**
     * Deletes a module activation status
     *
     * @param Module $module
     */
    public function delete(Module $module): void;

    /**
     * Deletes any module activation statuses created by this class.
     */
    public function reset(): void;

}
