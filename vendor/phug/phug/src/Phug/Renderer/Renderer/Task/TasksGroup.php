<?php

namespace Phug\Renderer\Task;

use Phug\Renderer;

class TasksGroup
{
    protected $renderer;
    protected $errors = 0;
    protected $success = 0;
    protected $errorDetails = [];

    public function __construct(Renderer $renderer = null)
    {
        if ($renderer) {
            $this->renderer = $renderer;
        }
    }

    public function record($successful, $details = null)
    {
        $this->{$successful ? 'success' : 'errors'}++;

        if (!$successful && $details) {
            $this->errorDetails[] = $details;
        }
    }

    public function runInSandBox(callable $task, $details)
    {
        $sandBox = $this->renderer->getNewSandBox($task);
        $error = $sandBox->getThrowable();

        if ($error) {
            $details['error'] = $error;
        }

        $this->record(!$error && $sandBox->getResult(), $details);
    }

    public function getResult()
    {
        return [$this->success, $this->errors, $this->errorDetails];
    }
}
