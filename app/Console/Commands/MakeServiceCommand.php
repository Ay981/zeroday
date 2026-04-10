<?php

namespace App\Console\Commands;

use Illuminate\Console\GeneratorCommand;

class MakeServiceCommand extends GeneratorCommand
{
    // The command the user types
    protected $signature = 'make:service {name}';

    // The description in the artisan list
    protected $description = 'Create a new service class for business logic';

    // The type of class being generated
    protected $type = 'Service';

    // Where the file should be placed
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Services';
    }

    // You need to create a simple "Stub" (template) file
    protected function getStub()
    {
        // We will create this file next
        return base_path('stubs/service.stub');
    }
}
