<?php

namespace App\Console\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Illuminate\Console\GeneratorCommand;

class MakeController extends GeneratorCommand
{
    protected $name = 'make:controller';
    protected $description = 'Create a new controller class';
    protected $type = 'Controller';

    public function fire()
    {
        $module = $this->argument('module');
        $name = $this->argument('name');

        $modulePath = "{$this->laravel['path']}/Modules/{$module}/";

        if ($this->files->exists($modulePath.'Controllers/'.$name.'.php')) {
            $this->error("Controller already exists.");

            return false;
        }

        $this->files->makeDirectory($modulePath.'Controllers', 0777, true);

        $stub = $this->files->get($this->getStub('controller'));
        $stub = str_replace('DummyModule', $module, $stub);
        $stub = str_replace('DummyFile', $name, $stub);

        $this->files->put($modulePath.'Controllers/'.$name.'.php', $stub);

        if (!$this->files->exists($modulePath.'/routes.php')) {
            $stub = $this->files->get($this->getStub('route'));
            $stub = str_replace('DummyModule', $module, $stub);
            $stub = str_replace('DummyPrefix', strtolower($module), $stub);
            $stub = str_replace('DummyName', $name, $stub);

            $this->files->put($modulePath.'routes.php', $stub);
        }


        $this->info("Controller created successfully.");
    }

    protected function getStub($type = 'controller')
    {
        switch ($type) {
            case 'controller':
                return __DIR__.'/stubs/controller.stub';
            case 'route':
                return __DIR__.'/stubs/route.stub';
        }
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace;
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['resource', null, InputOption::VALUE_NONE, 'Generate a resource controller class.'],
        ];
    }

    protected function getArguments()
    {
        return [
            ['module', InputArgument::REQUIRED, 'The module for the class to go into'],
            ['name', InputArgument::REQUIRED, 'The name of the class']
        ];
    }
}
