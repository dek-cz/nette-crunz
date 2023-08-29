<?php
declare(strict_types = 1);

namespace DekApps\Crunz;

use Closure;
use Crunz\Event;
use Crunz\Schedule;

final class Task
{

    private Schedule $service;

    private Event $task;

    private string|array|Closure $command = '';

    private ?array $parameters = [];

    private ?string $runningWhen = null;

    private ?string $on = null;

    private ?string $at = null;

    private ?string $in = null;

    private bool $preventOverlapping = true;

    private ?string $expression = null;

    private ?string $description = null;

    private ?string $from = null;

    private ?string $to = null;

    public function __construct(Schedule $service, string|array|Closure $command, bool $preventOverlapping, ?array $parameters = [], ?string $expression = null)
    {
        $this->service = $service;
        if (is_array($command)) {
            $command = Closure::fromCallable($command);
        }
        $this->command = $command;
        $this->parameters = $parameters;
        $this->expression = $expression;
        $this->preventOverlapping = $preventOverlapping;

        $keys = array_keys($parameters ?? []);
        if (!empty($keys) && $keys !== range(0, count($parameters) - 1)) {
            $mapparams = join(' ', array_map(function ($key) use ($parameters) {
                    if ($parameters[$key] !== null && $parameters[$key] !== '') {
                        return $key . ' ' . $parameters[$key];
                    }
                    return $key;
                }, array_keys($parameters)));
            $task = $service->run(sprintf('%s %s', $command, $mapparams));
        } else {
            $task = $service->run($command, $parameters ?? []);
        }

        $this->task = $task;
    }

    public function run(): void
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
        $from = $this->from;
        $to = $this->to;

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
        if ($from !== null && $from !== '') {
            $task->from($from);
        }
        if ($to !== null && $to !== '') {
            $task->from($to);
        }
        if ($preventOverlapping) {
            $task->preventOverlapping();
        }
    }

    public function setService(Schedule $service): self
    {
        $this->service = $service;
        return $this;
    }

    public function setCommand(string|array|Closure $command): self
    {
        $this->command = $command;
        return $this;
    }

    public function setParameters(?array $parameters): self
    {
        $this->parameters = $parameters;
        return $this;
    }

    public function setRunningWhen(?string $runningWhen): self
    {
        $this->runningWhen = $runningWhen;
        return $this;
    }

    public function setOn(?string $on): self
    {
        $this->on = $on;
        return $this;
    }

    public function setAt(?string $at): self
    {
        $this->at = $at;
        return $this;
    }

    public function setIn(?string $in): self
    {
        $this->in = $in;
        return $this;
    }

    public function setPreventOverlapping(bool $preventOverlapping): self
    {
        $this->preventOverlapping = $preventOverlapping;
        return $this;
    }

    public function setExpression(?string $expression): self
    {
        $this->expression = $expression;
        return $this;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function setFrom(?string $from): self
    {
        $this->from = $from;
        return $this;
    }

    public function setTo(?string $to): self
    {
        $this->to = $to;
        return $this;
    }

    public function getService(): Schedule
    {
        return $this->service;
    }

    public function getCommand(): string|array|Closure
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

    public function getFrom(): ?string
    {
        return $this->from;
    }

    public function getTo(): ?string
    {
        return $this->to;
    }

}
