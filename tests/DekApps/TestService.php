<?php

namespace Tests\DekApps;

final class TestService
{

    public int $runs = 0;

    public int $invokes = 0;
    
    public bool $skipped = false;

    public function run(): void
    {
        $this->runs++;
    }

    public function __invoke(): void
    {
        $this->invokes++;
    }

    public function skip() : bool
    {
        $this->skipped = true;
        return $this->skipped;
    }

}
