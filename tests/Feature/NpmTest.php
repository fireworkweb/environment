<?php

namespace Tests\Feature;

use Tests\TestCase;

class NpmTest extends TestCase
{
    public function testNpm()
    {
        $this->artisan('npm')->assertExitCode(0);

        $this->assertDockerRun('fireworkweb/node:12 npm -v');
    }

    public function testNpmCustom()
    {
        $this->artisan('npm install')->assertExitCode(0);

        $this->assertDockerRun('fireworkweb/node:12 npm install');
    }
}
