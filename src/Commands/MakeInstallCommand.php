<?php

namespace Nwidart\Modules\Commands;

use Illuminate\Console\Command;
use Nwidart\Modules\Module;
use Symfony\Component\Console\Input\InputArgument;

class MakeInstallCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'module:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the specified module.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        /** @var Module $module */
        $module = $this->laravel['modules']->findOrFail($this->argument('module'));

        if (!$module->isInstall()) {
            $module->install();

            $this->info("Module [{$module}] install successful.");
        } else {
            $this->comment("Module [{$module}] has already installed.");
        }
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['module', InputArgument::REQUIRED, 'Module name.'],
        ];
    }
}
