# nette-crunz
Implements the [crunzphp/crunz](https://github.com/crunzphp/crunz) into nette FW

Install a cron job once and for all, manage the rest from the code.

Crunz is a framework-agnostic package to schedule periodic tasks (cron jobs) in PHP using a fluent API.

Crunz is capable of executing any kind of executable command as well as PHP closures.

## Installation

To install it:

```bash
composer require dek-cz/nette-crunz
```

## Usage

### Creating a Simple Tasks

The task definition can look like this:

```neon
crunz:
    tasks:
        - 
            command: 'ls'
            parameters: [ '-la' , '-rt' ]
            runningWhen: 'everyMinute'
            on: ''
            at: ''
            in: ''
            from: ''
            to: ''
            preventOverlapping: true
            description: ''
        - 
            command: 'du'
            parameters: [ '-hS' : '' ]
            expression: '* * * * *'
            preventOverlapping: true
            description: ''
        - 
            command: [@testSer, 'run']
            expression: '* * * * *'
            preventOverlapping: false
            description: 'Tests\DekApps\TestService::run'
        - 
            command: [Tests\DekApps\TestService2(_,1), 'getPlus']
            expression: '* * * * *'
            preventOverlapping: false
            description: 'Tests\DekApps\TestService2::getPlus'
        - 
            command: [@testSer, 'run']
            expression: '* * * * *'
            preventOverlapping: false
            description: 'Tests\DekApps\TestService::run'
            events:
                skip: 
                    - [@testSer, 'skip']


services:
    testSer: Tests\DekApps\TestService

```


## Configuration

```neon
extensions:
    crunz: DekApps\Crunz\DI\CrunzExtension

```
There are a few configuration options provided by Crunz in YAML format. To modify the configuration settings, it is highly recommended to have your own copy of the configuration file, instead of modifying the original one. 

To create a copy of the configuration file, first we need to publish the configuration file:

```bash
/project/vendor/bin/crunz publish:config
The configuration file was generated successfully
```

As a result, a copy of the configuration file will be created within our project's root directory.

 The configuration file looks like this:

```yaml
# Crunz Configuration Settings

# This option defines where the task files and
# directories reside.
# The path is relative to the project's root directory,
# where the Crunz is installed (Trailing slashes will be ignored).
source: tasks

# The suffix is meant to target the task files inside the ":source" directory.
# Please note if you change this value, you need
# to make sure all the existing tasks files are renamed accordingly.
suffix: Tasks.php

# Timezone is used to calculate task run time
# This option is very important and not setting it is deprecated
# and will result in exception in 2.0 version.
timezone: ~

# This option define which timezone should be used for log files
# If false, system default timezone will be used
# If true, the timezone in config file that is used to calculate task run time will be used
timezone_log: false

# By default the errors are not logged by Crunz
# You may set the value to true for logging the errors
log_errors: false

# This is the absolute path to the errors' log file
# You need to make sure you have the required permission to write to this file though.
errors_log_file:

# By default the output is not logged as they are redirected to the
# null output.
# Set this to true if you want to keep the outputs
log_output: false

# This is the absolute path to the global output log file
# The events which have dedicated log files (defined with them), won't be
# logged to this file though.
output_log_file:

# By default line breaks in logs aren't allowed.
# Set the value to true to allow them.
log_allow_line_breaks: false

# By default empty context arrays are shown in the log.
# Set the value to true to remove them.
log_ignore_empty_context: false

# This option determines whether the output should be emailed or not.
email_output: false

# This option determines whether the error messages should be emailed or not.
email_errors: false

# Global Swift Mailer settings
#
mailer:
    # Possible values: smtp, mail, and sendmail
    transport: smtp
    recipients:
    sender_name:
    sender_email:


# SMTP settings
#
smtp:
    host:
    port:
    username:
    password:
    encryption:
```

## Task Files

Task files resemble crontab files. Just like crontab files they can contain one or more tasks.

Normally we create our task files in the `tasks/` directory within the project's root directory. 

> By default, Crunz assumes all the task files reside in the `tasks/` directory within the project's root directory.

There are two ways to specify the source directory: 1) Configuration file  2) As a parameter to the event runner command.
 
We can explicitly set the source path by passing it to the event runner as a parameter:

```bash
* * * * * cd /project && vendor/bin/crunz schedule:run [/path/to/tasks/directory]
```

## Other Useful Commands

We've already used a few of `crunz` commands like `schedule:run` and `publish:config`. 

To see all the valid options and arguments of `crunz`, we can run the following command:

```bash
vendor/bin/crunz --help
```

### Listing Tasks

One of these commands is `crunz schedule:list`, which lists the defined tasks (in collected `*.Tasks.php` files) in a tabular format.

```text
vendor/bin/crunz schedule:list

+---+---------------+-------------+--------------------+
| # | Task          | Expression  | Command to Run     |
+---+---------------+-------------+--------------------+
| 1 | Sample Task   | * * * * 1 * | command/to/execute |
+---+---------------+-------------+--------------------+
```

By default, list is in text format, but format can be changed by `--format` option.

List in `json` format, command:
```bash
vendor/bin/crunz schedule:list --format json
```

will output:

```json
[
    {
        "number": 1,
        "task": "Sample Task",
        "expression": "* * * * 1",
        "command": "command/to/execute"
    }
]
```

### Force run

While in development it may be useful to force run all tasks regardless of their actual run time,
which can be achieved by adding `--force` to `schedule:run`:

```bash
vendor/bin/crunz schedule:run --force
```

To force run a single task, use the schedule:list command above to determine the Task number and run as follows:

```bash
vendor/bin/crunz schedule:run --task 1 --force
```



