<?php
declare(strict_types = 1);

namespace DekApps\Crunz\DI;

use Closure;
use Cron\CronExpression;
use Crunz\Schedule;
use DekApps\Crunz\Crunz;
use DekApps\Crunz\Task;
use Nette\DI\CompilerExtension;
use Nette\DI\ContainerBuilder;
use Nette\DI\Definitions\ServiceDefinition;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use stdClass;

final class CrunzExtension extends CompilerExtension
{

    public function getConfigSchema(): Schema
    {
        return Expect::structure([
                'tasks' => Expect::arrayOf(
                    Expect::structure([
                        'expression' => Expect::string()
                            ->assert(
                                static fn(string $value): bool => $value === '' || CronExpression::isValidExpression($value),
                                'Valid cron expression',
                            ),
                        'command' => Expect::anyOf(Expect::string(), Expect::array()->min(2)->max(2), Expect::type(Closure::class)),
                        'parameters' => Expect::array()->default(null),
                        'preventOverlapping' => Expect::bool()->default(true),
                        'runningWhen' => Expect::anyOf(
                            'hourly', 'daily', 'weekly', 'monthly', 'quarterly', 'yearly',
                            'mondays', 'tuesdays', 'wednesdays', 'thursdays', 'fridays', 'saturdays', 'sundays', 'weekdays',
                            'everyMinute', 'everyTwoMinutes', 'everyThreeMinutes', 'everyFourMinutes', 'everyFiveMinutes', 'everyTenMinutes', 'everyFifteenMinutes', 'everyThirtyMinutes',
                            'everyTwoHours', 'everyThreeHours', 'everyFourHours', 'everySixHours')->default(null),
                        'on' => Expect::string()->default(null),
                        'in' => Expect::string()->default(null),
                        'at' => Expect::string()->default(null),
                        'from' => Expect::string()->default(null),
                        'to' => Expect::string()->default(null),
                        'beetween' => Expect::array()->min(2)->max(2)->default(null),
                        'description' => Expect::string()->default(null),
                        'events' => Expect::structure([
                            'skip' => Expect::listOf(
                                Expect::anyOf(
                                    Expect::string(),
                                    /* @infection-ignore-all */
                                    Expect::array()->min(2)->max(2),
                                    Expect::type(Statement::class),
                                ),
                            ),
                            'when' => Expect::listOf(
                                Expect::anyOf(
                                    Expect::string(),
                                    /* @infection-ignore-all */
                                    Expect::array()->min(2)->max(2),
                                    Expect::type(Statement::class),
                                ),
                            ),
                        ])
                    ])),
                'events' => Expect::structure([
                    'before' => Expect::listOf(
                        Expect::anyOf(
                            Expect::string(),
                            /* @infection-ignore-all */
                            Expect::array()->min(2)->max(2),
                            Expect::type(Statement::class),
                        ),
                    ),
                    'after' => Expect::listOf(
                        Expect::anyOf(
                            Expect::string(),
                            /* @infection-ignore-all */
                            Expect::array()->min(2)->max(2),
                            Expect::type(Statement::class),
                        ),
                    ),
                    'onError' => Expect::listOf(
                        Expect::anyOf(
                            Expect::string(),
                            /* @infection-ignore-all */
                            Expect::array()->min(2)->max(2),
                            Expect::type(Statement::class),
                        ),
                    ),
                ]),
        ]);
    }

    public function loadConfiguration(): void
    {
        $builder = $this->getContainerBuilder();
        $config = $this->config;

        $definition = $this->registerCrunz($builder, $config);
        $this->compiler->addExportedType(Crunz::class);
    }

    private function registerCrunz(ContainerBuilder $builder, stdClass $config): ServiceDefinition
    {
        $scheduler = $builder->addDefinition($this->prefix('crunz.scheduler'))->setFactory(Schedule::class);
        $tasks = [];
        foreach ($config->tasks as $id => $t) {
            $taskDefName = 'crunz.task.' . $id;
            $task = $builder->addDefinition($taskDefName)
                ->setFactory(Task::class, [
                'service' => $scheduler,
                'command' => $t->command,
                'preventOverlapping' => $t->preventOverlapping ?? true,
                'parameters' => $t->parameters ?? null,
                'expression' => $t->expression ?? null,
            ])->addSetup('setRunningWhen', [$t->runningWhen ?? null])
            ->addSetup('setOn', [$t->on ?? null])
            ->addSetup('setAt', [$t->at ?? null])
            ->addSetup('setIn', [$t->in ?? null]) 
            ->addSetup('setFrom', [$t->from ?? null])
            ->addSetup('setTo', [$t->to ?? null])  
            ->addSetup('setDescription', [$t->description ?? null])  
                ;

            $evs = $t->events;
            foreach ($evs->skip as $ev) {
                $task->addSetup(
                    'skip',
                    [
                        new Statement([
                            Closure::class,
                            'fromCallable',
                            ], [
                            $ev,
                            ]),
                    ],
                );
            }
            foreach ($evs->when as $ev) {
                $task->addSetup(
                    'when',
                    [
                        new Statement([
                            Closure::class,
                            'fromCallable',
                            ], [
                            $ev,
                            ]),
                    ],
                );
            }

            $tasks[] = $task;
        }


        $definition = $builder->addDefinition($this->prefix('crunz'))
            ->setFactory(Crunz::class, ['tasks' => $tasks]);

        $events = $config->events;

        foreach ($events->before as $event) {
            $definition->addSetup(
                'before',
                [
                    new Statement([
                        Closure::class,
                        'fromCallable',
                        ], [
                        $event,
                        ]),
                ],
            );
        }

        foreach ($events->after as $event) {
            $definition->addSetup(
                'then',
                [
                    new Statement([
                        Closure::class,
                        'fromCallable',
                        ], [
                        $event,
                        ]),
                ],
            );
        }
        foreach ($events->onError as $event) {
            $definition->addSetup(
                'onError',
                [
                    new Statement([
                        Closure::class,
                        'fromCallable',
                        ], [
                        $event,
                        ]),
                ],
            );
        }

        return $definition;
    }

}
