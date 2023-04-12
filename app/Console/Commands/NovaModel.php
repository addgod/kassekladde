<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class NovaModel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nova:model
        {class : The name of the class}
        {--a|all : Generates controller, factory, policy, migration and nova resource for model}
        {--c|controller : Create a new controller for the model }
        {--f|factory : Create a new factory for the model}
        {--p|policy : Create a new policy for the model}
        {--m|migration : Create a new migration for the model}
        {--api : Generates the controller as a api controller, and generates a corresponding resource}
        {--model= : The eloquent model that should be used}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'A command to make all scaffold everything for nova';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $class = $this->argument('class');
        $arguments = collect($this->options());

        $model = $arguments->get('model') ?: $class;

        $this->call('make:model', [
            'name'        => $model,
            '--migration' => $arguments->get('all') || $arguments->get('migration'),
            '--factory'   => $arguments->get('all') || $arguments->get('factory'),
        ]);

        $this->call('nova:resource', [
            'name'    => $class,
            '--model' => $model,
        ]);

        if ($arguments->get('all') || $arguments->get('policy')) {
            $this->call('make:policy', [
                'name'    => $class . 'Policy',
                '--model' => $model,
            ]);
        }

        if ($arguments->get('all') || $arguments->get('controller')) {
            $this->call('make:controller', [
                'name'  => $class . 'Controller',
                '--api' => $arguments->get('api'),
            ]);

            $this->call('make:request', [
                'name' => $class . 'Request',
            ]);

            if ($arguments->get('all') || $arguments->get('api')) {
                $this->call('make:resource', [
                    'name' => $class . 'Resource',
                ]);
            }
        }
    }
}
