<?php

namespace Tests;

use App\CommandExecutor;
use App\Environment;
use LaravelZero\Framework\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected $asUser;

    protected function setAsUser($user)
    {
        $this->asUser = $user;

        return $this;
    }

    protected function asFwdUser()
    {
        return $this->setAsUser(env('FWD_ASUSER'));
    }

    protected function assertDocker(...$command)
    {
        $this->assertCommandRun([
            env('FWD_DOCKER_BIN', 'docker'),
            $this->buildCommand($command),
        ]);
    }

    protected function assertDockerCompose(...$command)
    {
        $this->assertCommandRun([
            env('FWD_DOCKER_COMPOSE_BIN', 'docker-compose'),
            sprintf('-p %s', basename(getcwd())),
            $this->buildCommand($command),
        ]);
    }

    protected function assertDockerComposeExec(...$command)
    {
        $params = [
            env('FWD_DOCKER_COMPOSE_BIN', 'docker-compose'),
            sprintf('-p %s exec', basename(getcwd())),
        ];

        if (! empty($this->asUser)) {
            $params[] = '--user';
            $params[] = $this->asUser;
        }

        $params[] = env('FWD_COMPOSE_EXEC_FLAGS');
        $params[] = $this->buildCommand($command);

        $this->assertCommandRun($params);
    }

    protected function assertDockerRun(...$command)
    {
        $this->assertCommandRun([
            env('FWD_DOCKER_BIN', 'docker'),
            'run',
            sprintf('-e ASUSER=%s', env('FWD_ASUSER')),
            '-it --init --rm -w \'/app\'',
            sprintf('-v \'%s:/app:cached\'', env('FWD_CONTEXT_PATH')),
            sprintf('-v \'%s:/home/developer/.ssh/id_rsa:cached\'', env('FWD_SSH_KEY_PATH')),
            $this->buildCommand($command),
        ]);
    }

    protected function assertCommandRun(array $command)
    {
        $command = $this->buildCommand($command);

        $hasCommand = app(CommandExecutor::class)->hasCommand($command);

        if (! $hasCommand) {
            dump(app(CommandExecutor::class)->commands(), $command);
        }

        static::assertTrue($hasCommand, 'Failed asserting that this command was called: ' . $command);
    }

    protected function mockCommandExecutor()
    {
        $this->mock(CommandExecutor::class, function ($mock) {
            $mock->shouldAllowMockingProtectedMethods()
                ->shouldReceive('execute')
                ->andReturn(0);
        })->makePartial();
    }

    protected function buildCommand(array $command)
    {
        return trim(implode(' ', array_filter($command)));
    }

    protected function makeDockerComposeExecString(string $args = ''): string
    {
        $flags = env('FWD_COMPOSE_EXEC_FLAGS') ? ' ' . env('FWD_COMPOSE_EXEC_FLAGS') : '';

        return trim($this->dockerComposeExecString() . $flags . ' ' . $args);
    }

    protected function makeDockerComposeExecUserString($user = null, string $args = ''): string
    {
        $flags = env('FWD_COMPOSE_EXEC_FLAGS') ? ' ' . env('FWD_COMPOSE_EXEC_FLAGS') : '';

        return trim($this->dockerComposeExecString() . $flags . ' --user ' . $user . ' ' . $args);
    }

    protected function dockerComposeExecString(): string
    {
        return "{$this->dockerComposeString()} exec";
    }

    protected function makeDockerComposeRunString(string $args = '', string $envs = ''): string
    {
        $flags = env('FWD_COMPOSE_RUN_FLAGS') ? ' ' . env('FWD_COMPOSE_RUN_FLAGS') : '';
        $extraArgs = $envs ? " -e {$envs} " : ' ';

        return trim($this->dockerComposeRunString($extraArgs) . $flags . ' ' . $args);
    }

    protected function makeDockerComposeRunUserString($user = null, string $args = ''): string
    {
        $flags = env('FWD_COMPOSE_RUN_FLAGS') ? ' ' . env('FWD_COMPOSE_RUN_FLAGS') : '';
        $extraArgs = " --user {$user} ";

        return trim($this->dockerComposeRunString($extraArgs) . $flags . ' ' . $args);
    }

    protected function dockerComposeRunString(string $extraArgs = ' '): string
    {
        return "{$this->dockerComposeString()} run" . $extraArgs . '--rm';
    }

    protected function dockerComposeString(): string
    {
        $project = env('FWD_NAME');

        return "docker-compose -p {$project}";
    }

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        // resets some env
        app(Environment::class)
            ->overloadEnv('.fwd')
            ->overloadEnv('.fwd.testing');

        parent::setup();

        $this->mockCommandExecutor();

        // resets intended execution user
        $this->setAsUser(null);
    }
}
