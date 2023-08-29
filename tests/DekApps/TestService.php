<?php

namespace Tests\DekApps;

final class TestService
{

    public int $runs = 0;

    public int $invokes = 0;

    public function run(): void
    {
        $this->runs++;
    }

    public function __invoke(): void
    {
        $this->invokes++;
    }

}
