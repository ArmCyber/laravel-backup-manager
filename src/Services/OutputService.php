<?php

namespace ArmCyber\BackupManager\Services;

use ArmCyber\BackupManager\Exceptions\BackupManagerRuntimeException;
use Illuminate\Support\Facades\App;
use Symfony\Component\Console\Output\ConsoleOutput;

class OutputService
{
    private ?ConsoleOutput $consoleOutput = null;

    public function __construct()
    {
        if (App::runningInConsole()) {
            $this->consoleOutput = App::make(ConsoleOutput::class);
        }
    }

    public function write($text, $type = null)
    {
        if (isset($this->consoleOutput)) {
            $output = $text;
            if ($type) {
                $output = "<{$type}>{$text}</{$type}>";
            }
            $this->consoleOutput->writeln($output);
        }
    }

    public function throw(BackupManagerRuntimeException $exception)
    {
        if (isset($this->consoleOutput)) {
            $this->write($exception->getMessage(), 'error');
            return;
        }
        throw $exception;
    }
}
