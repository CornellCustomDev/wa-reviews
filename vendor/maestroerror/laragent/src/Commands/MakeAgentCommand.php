<?php

namespace LarAgent\Commands;

use Illuminate\Console\Command;

class MakeAgentCommand extends Command
{
    protected $signature = 'make:agent {name : The name of the agent}';

    protected $description = 'Create a new LarAgent agent class';

    public function handle()
    {
        $name = $this->argument('name');

        $path = app_path('AiAgents/'.$name.'.php');

        if (file_exists($path)) {
            $this->error('Agent already exists: '.$name);

            return 1;
        }

        $stub = file_get_contents(__DIR__.'/stubs/agent.stub');

        $stub = str_replace('{{ class }}', $name, $stub);

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        file_put_contents($path, $stub);

        $this->info('Agent created successfully: '.$name);
        $this->line('Location: '.$path);

        return 0;
    }
}
