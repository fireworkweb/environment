<?php

namespace App\Commands;

use App\Commands\Traits\ArtisanCall;
use App\Commands\Traits\HasDynamicArgs;
use LaravelZero\Framework\Commands\Command;
use App\Process;

class Mysql extends Command
{
    use ArtisanCall, HasDynamicArgs;

    /**
     * The name of the command.
     *
     * @var string
     */
    protected $name = 'mysql';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Run Mysql client inside the container.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(Process $process)
    {
        $process->dockerCompose(
            'exec -e MYSQL_PWD='.env('DB_PASSWORD').' mysql mysql -u root',
            env('DB_DATABASE'),
            $this->getArgs()
        );
    }
}
