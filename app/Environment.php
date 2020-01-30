<?php

namespace App;

use Dotenv\Dotenv;
use Dotenv\Environment\DotenvFactory;
use Dotenv\Environment\DotenvVariables;
use Dotenv\Exception\InvalidFileException;
use Dotenv\Exception\InvalidPathException;
use Illuminate\Support\Arr;

class Environment
{
    protected static $keys = [
        'FWD_IMAGE_APP',
        'FWD_IMAGE_HTTP',
        'FWD_IMAGE_NODE',
        'FWD_IMAGE_CHROMEDRIVER',
        'FWD_IMAGE_CACHE',
        'FWD_IMAGE_DATABASE',
        'FWD_IMAGE_NODE_QA',
        'FWD_IMAGE_PHP_QA',
        'FWD_DEBUG',
        'FWD_VERBOSE',
        'FWD_ATTEMPTS_DELAY',
        'FWD_NAME',
        'FWD_HTTP_PORT',
        'FWD_DATABASE_PORT',
        'FWD_ASUSER',
        'FWD_DOCKER_BIN',
        'FWD_DOCKER_COMPOSE_BIN',
        'FWD_COMPOSE_EXEC_FLAGS',
        'FWD_DOCKER_RUN_FLAGS',
        'FWD_SSH_KEY_PATH',
        'FWD_CONTEXT_PATH',
        'FWD_CUSTOM_PATH',
        'FWD_START_DEFAULT_SERVICES',
        'DB_DATABASE',
        'DB_USERNAME',
        'DB_PASSWORD',
        'COMPOSE_API_VERSION',
        'FWD_COMPOSE_VERSION',
        'FWD_NETWORK',
    ];

    /** @var DotenvVariables $envVariables */
    protected $envVariables;

    public function getKeys(): array
    {
        return static::$keys;
    }

    public function getValues(): array
    {
        return Arr::only(getenv(), $this->getKeys());
    }

    public function load(): void
    {
        $this->loadEnv($this->getContextEnv('.env'))
            ->loadEnv($this->getContextEnv('.fwd'))
            ->loadEnv($this->getHomeFwd())
            ->loadEnv($this->getDefaultFwd())
            ->fixVariables();
    }

    public function getDefaultPath(): string
    {
        return base_path();
    }

    public function getContextPath(): string
    {
        return getcwd();
    }

    public function getDefaultDockerCompose(string $version): string
    {
        return sprintf('%s/docker-compose-v%s.yml', $this->getDefaultPath(), $version);
    }

    public function getContextDockerCompose(): string
    {
        return $this->getContextFile('docker-compose.yml');
    }

    public function getDefaultFwd(): string
    {
        return sprintf('%s/.fwd', $this->getDefaultPath());
    }

    public function getHomeFwd(): string
    {
        return sprintf('%s/.fwd', $_SERVER['HOME']);
    }

    public function getContextEnv(string $env = '.env'): string
    {
        return $this->getContextFile($env);
    }

    public function getContextFile(string $file): string
    {
        return sprintf('%s/%s', $this->getContextPath(), $file);
    }

    public function safeLoadEnv(string $envFile): self
    {
        return $this->loadEnv($envFile)->fixVariables();
    }

    public function overloadEnv(string $envFile): self
    {
        return $this->loadEnv($envFile, true)->fixVariables();
    }

    protected function loadEnv(string $envFile, bool $overload = false): self
    {
        try {
            $method = $overload ? 'overload' : 'safeLoad';

            Dotenv::create(
                pathinfo($envFile, PATHINFO_DIRNAME),
                pathinfo($envFile, PATHINFO_BASENAME)
            )->{$method}();
        } catch (InvalidPathException $e) {
            // do nothing
        } catch (InvalidFileException $e) {
            echo 'The environment file is invalid: ' . $e->getMessage();
            die(1);
        }

        return $this;
    }

    protected function fixVariables(): self
    {
        $envVariables = app(DotenvFactory::class)->create();

        if (empty(env('FWD_NAME'))) {
            $envVariables->set(
                'FWD_NAME',
                basename(getcwd())
            );
        }

        if (empty(env('FWD_NETWORK'))) {
            // defines default network name
            $envVariables->set(
                'FWD_NETWORK',
                'fwd_global'
            );
        }

        $envVariables->set(
            'FWD_SSH_KEY_PATH',
            str_replace('$HOME', $_SERVER['HOME'], env('FWD_SSH_KEY_PATH'))
        );

        $envVariables->set(
            'FWD_CONTEXT_PATH',
            str_replace('$PWD', getcwd(), env('FWD_CONTEXT_PATH'))
        );

        $envVariables->set(
            'FWD_CUSTOM_PATH',
            str_replace('$PWD', getcwd(), env('FWD_CUSTOM_PATH'))
        );

        $envVariables->set(
            'FWD_ASUSER',
            str_replace('$UID', posix_geteuid(), env('FWD_ASUSER'))
        );

        return $this;
    }
}
