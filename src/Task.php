<?php
declare(strict_types = 1);

namespace DekApps\Crunz;

use Crunz\Event;
use Crunz\Schedule;

final class Task
{

    private Schedule $service;

    private Event $task;

    private string $command = '';

    private ?array $parameters = [];

    private ?string $runningWhen = null;

    private ?string $on = null;

    private ?string $at = null;

    private ?string $in = null;

    private bool $preventOverlapping = true;

    private ?string $expression = null;

    private ?string $description = null;

    public function __construct(Schedule $service, string $command, bool $preventOverlapping, ?array $parameters = [], ?string $expression = null)
    {
        $this->service = $service;
        $this->command = $command;
        $this->parameters = $parameters;
        $this->expression = $expression;
        $this->preventOverlapping = $preventOverlapping;

        $keys = array_keys($parameters);
        if ($keys !== range(0, count($parameters) - 1)) {
            $mapparams = join(' ', array_map(function ($key) use ($parameters) {
                    if ($parameters[$key] !== null && $parameters[$key] !== '') {
                        return $key . ' ' . $parameters[$key];
                    }
                    return $key;
                }, array_keys($parameters)));
            $task = $service->run(sprintf('%s %s', $command, $mapparams));
        } else {
            $task = $service->run($command, $parameters);
        }

        $this->task = $task;
    }

    public function run()
    {
        $service = $this->service;
        $command = $this->command;
        $parameters = $this->parameters;
        $expression = $this->expression;
        $runningWhen = $this->runningWhen;
        $preventOverlapping = $this->preventOverlapping;
        $on = $this->on;
        $at = $this->at;
        $in = $this->in;
        $task = $this->task;

//        print system($task->getCommand());

        if ($expression !== null && $expression !== '') {
            $task->cron($expression);
        } else if ($runningWhen !== null && $runningWhen !== '') {
            $task->{$runningWhen}();
        }
        if ($on !== null && $on !== '') {
            $task->on($on);
        }
        if ($in !== null && $in !== '') {
            $task->in($in);
        }
        if ($at !== null && $at !== '') {
            $task->at($at);
        }
        if ($preventOverlapping) {
            $task->preventOverlapping();
        }
    }

    public function setService(Schedule $service)
    {
        $this->service = $service;
        return $this;
    }

    public function setCommand(string $command)
    {
        $this->command = $command;
        return $this;
    }

    public function setParameters(?array $parameters)
    {
        $this->parameters = $parameters;
        return $this;
    }

    public function setRunningWhen(?string $runningWhen)
    {
        $this->runningWhen = $runningWhen;
        return $this;
    }

    public function setOn(?string $on)
    {
        $this->on = $on;
        return $this;
    }

    public function setAt(?string $at)
    {
        $this->at = $at;
        return $this;
    }

    public function setIn(?string $in)
    {
        $this->in = $in;
        return $this;
    }

    public function setPreventOverlapping(bool $preventOverlapping)
    {
        $this->preventOverlapping = $preventOverlapping;
        return $this;
    }

    public function setExpression(?string $expression)
    {
        $this->expression = $expression;
        return $this;
    }

    public function setDescription(?string $description)
    {
        $this->description = $description;
        return $this;
    }

    public function getService(): Schedule
    {
        return $this->service;
    }

    public function getCommand(): string
    {
        return $this->command;
    }

    public function getParameters(): ?array
    {
        return $this->parameters;
    }

    public function getRunningWhen(): ?string
    {
        return $this->runningWhen;
    }

    public function getOn(): ?string
    {
        return $this->on;
    }

    public function getAt(): ?string
    {
        return $this->at;
    }

    public function getIn(): ?string
    {
        return $this->in;
    }

    public function getPreventOverlapping(): bool
    {
        return $this->preventOverlapping;
    }

    public function getExpression(): ?string
    {
        return $this->expression;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getTask(): Event
    {
        return $this->task;
    }
    
    
    public function skip(\Closure $callback): self
    {
        $this->getTask()->skip($callback);
        return $this;
    }
    
    public function when(\Closure $callback): self
    {
        $this->getTask()->when($callback);
        return $this;
    }

}
