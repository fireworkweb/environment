<?php

use App\Builder\Generic;
use App\Commands\Command;

class Custom extends Command
{
    /**
     * The name of the command.
     *
     * @var string
     */
    protected $name = 'custom';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'custom command';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        return $this->commandExecutor->run(
            Generic::make('echo custom')
        );
    }
}
