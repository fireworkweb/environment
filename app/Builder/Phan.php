<?php

namespace App\Builder;

class Phan extends Command
{
    public function getProgramName() : string
    {
        return 'phan';
    }

    public function makeWrapper() : ?Command
    {
        return PhpQa::make();
    }

    public function getPhpQa(): PhpQa
    {
        return $this->wrapper;
    }

    public function getDefaultArgs(): array
    {
        return ['--color -p -l app -iy 5'];
    }
}