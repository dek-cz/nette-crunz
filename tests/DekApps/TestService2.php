<?php

namespace Tests\DekApps;

final class TestService2
{
    private int $plus = 0;
    
    private TestService $a;
    
    public function __construct(TestService $a, int $plus)
    {
        $this->plus = $plus;
        $this->a = $a;
    }

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
    
    public function getPlus(): int
    {
        
        return $this->plus;
    }



}
