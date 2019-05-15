<?php

namespace App\Commands;

use App\Process;
use App\Commands\Traits\RunTask;
use App\Commands\Traits\HasDynamicArgs;
use LaravelZero\Framework\Commands\Command;

class Down extends Command
{
    use HasDynamicArgs, RunTask;

    /**
     * The name of the command.
     *
     * @var string
     */
    protected $name = 'down';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Get down all containers and destroy them.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(Process $process)
    {
        return $this->runTask('Shutting down', function () use ($process) {
            return $process->dockerComposeExecNoOutput('down', $this->getArgs());
        });
    }
}
