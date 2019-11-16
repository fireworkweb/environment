<?php

namespace Tests\Unit;

use App\Builder\Argument;
use App\Builder\DockerCompose;
use App\Builder\Unescaped;
use Tests\TestCase;

class DockerComposeCommandTest extends TestCase
{
    public function testDockerCompose()
    {
        $comm = new DockerCompose();

        $this->assertEquals('docker-compose -p fwd', (string) $comm);
    }

    public function testDockerComposeInnerCommand()
    {
        $comm = new DockerCompose(Unescaped::make('exec'));

        $comm->addArgument(new Argument('-e', 'FOO=bar', ' '));
        $comm->addArgument('mysql mysql');
        $comm->addArgument(new Argument('-e', 'SELECT 1', ' '));

        $this->assertEquals('docker-compose -p fwd exec -e \'FOO=bar\' mysql mysql -e \'SELECT 1\'', (string) $comm);
    }
}
