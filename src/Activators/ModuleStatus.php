<?php


namespace Nwidart\Modules\Activators;


class ModuleStatus
{
    public $name;
    public $enable = false;
    public $install = false;

    /**
     * ModuleStatus constructor.
     * @param $name
     * @param bool $enable
     * @param bool $install
     */
    public function __construct($name, bool $enable, bool $install)
    {
        $this->name = $name;
        $this->enable = $enable;
        $this->install = $install;
    }

}
