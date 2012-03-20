<?php

class Process
{
    private $commandline;
    private $cwd;
    private $env;
    private $stdin;
    private $timeout;
    private $options;
    private $exitcode;
    private $status;
    private $stdout;
    private $stderr;

    /**
     * Constructor.
     *
     * @param string  $commandline The command line to run
     * @param string  $cwd         The working directory
     * @param array   $env         The environment variables
     * @param string  $stdin       The STDIN content
     * @param integer $timeout     The timeout in seconds
     * @param array   $options     An array of options for proc_open
     *
     * @throws RuntimeException When proc_open is not installed
     *
     * @api
     */
    public function __construct($commandline, $cwd = null, array $env = null, $stdin = null, $timeout = 60, array $options = array())
    {
        if (!function_exists('proc_open')) {
            throw new RuntimeException('The Process class relies on proc_open, which is not available on your PHP installation.');
        }

        $this->commandline = $commandline;
        $this->cwd = null === $cwd ? getcwd() : $cwd;
        if (null !== $env) {
            $this->env = array();
            foreach ($env as $key => $value) {
                $this->env[(binary) $key] = (binary) $value;
            }
        } else {
            $this->env = null;
        }
        $this->stdin = $stdin;
        $this->timeout = $timeout;
        $this->options = array_merge(array('suppress_errors' => true, 'binary_pipes' => true, 'bypass_shell' => false), $options);
    }

    /**
     * Runs the process.
     *
     * The callback receives the type of output (out or err) and
     * some bytes from the output in real-time. It allows to have feedback
     * from the independent process during execution.
     *
     * The STDOUT and STDERR are also available after the process is finished
     * via the getOutput() and getErrorOutput() methods.
     *
     * @param Closure|string|array $callback A PHP callback to run whenever there is some
     *                                       output available on STDOUT or STDERR
     *
     * @return integer The exit status code
     *
     * @throws \RuntimeException When process can't be launch or is stopped
     *
     * @api
     */
    public function run()
    {
        $this->stdout = '';
        $this->stderr = '';

        $descriptors = array(array('pipe', 'r'), array('pipe', 'w'), array('pipe', 'w'));

        $process = proc_open($this->commandline, $descriptors, $pipes, $this->cwd, $this->env, $this->options);

        if (!is_resource($process)) {
            throw new Exception('Unable to launch a new process.');
        }

        fwrite($pipes[0], $this->stdin);
        $status = proc_get_status($process);

        while($status['running']) {
          $this->stdout .= stream_get_contents($pipes[1]);
          $this->stderr .= stream_get_contents($pipes[2]);
          $status = proc_get_status($process);
        }


        fclose($pipes[0]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        $this->exitcode = $status['exitcode'];

        return $this->exitcode;
    }

    /**
     * Returns the output of the process (STDOUT).
     *
     * This only returns the output if you have not supplied a callback
     * to the run() method.
     *
     * @return string The process output
     *
     * @api
     */
    public function getOutput()
    {
        return $this->stdout;
    }

    /**
     * Returns the error output of the process (STDERR).
     *
     * This only returns the error output if you have not supplied a callback
     * to the run() method.
     *
     * @return string The process error output
     *
     * @api
     */
    public function getErrorOutput()
    {
        return $this->stderr;
    }

    /**
     * Returns the exit code returned by the process.
     *
     * @return integer The exit status code
     *
     * @api
     */
    public function getExitCode()
    {
        return $this->exitcode;
    }

    /**
     * Checks if the process ended successfully.
     *
     * @return Boolean true if the process ended successfully, false otherwise
     *
     * @api
     */
    public function isSuccessful()
    {
        return 0 == $this->exitcode;
    }


    public function addOutput($line)
    {
        $this->stdout .= $line;
    }

    public function addErrorOutput($line)
    {
        $this->stderr .= $line;
    }

    public function getCommandLine()
    {
        return $this->commandline;
    }

    public function setCommandLine($commandline)
    {
        $this->commandline = $commandline;
    }

    public function getTimeout()
    {
        return $this->timeout;
    }

    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }

    public function getWorkingDirectory()
    {
        return $this->cwd;
    }

    public function setWorkingDirectory($cwd)
    {
        $this->cwd = $cwd;
    }

    public function getEnv()
    {
        return $this->env;
    }

    public function setEnv(array $env)
    {
        $this->env = $env;
    }

    public function getStdin()
    {
        return $this->stdin;
    }

    public function setStdin($stdin)
    {
        $this->stdin = $stdin;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function setOptions(array $options)
    {
        $this->options = $options;
    }
}
