<?php
declare(strict_types = 1);

namespace DekApps\Crunz;

use Crunz\Event;
use Crunz\Schedule;

final class Crunz
{

    private Schedule $service;

    /**
     * @param Event[] $tasks
     */
    private array $tasks = [];

    public function __construct(Schedule $service, array $tasks)
    {
        $this->service = $service;
        $this->tasks = $tasks;
    }

    public function run()
    {
        foreach ($this->tasks as $task) {
            $task->run();
        }
    }

    /**
     * @return Event[]
     */
    public function getTasks(): array
    {
        return $this->tasks;
    }

    public function getService(): Schedule
    {
        return $this->service;
    }

    public function before(\Closure $callback): self
    {
        $this->getService()->before($callback);
        return $this;
    }

    public function then(\Closure $callback): self
    {
        $this->getService()->then($callback);
        return $this;
    }

    public function onError(\Closure $callback): self
    {
        $this->getService()->onError($callback);
        return $this;
    }

}
