<?php

require_once 'process.php';

class ProcessBuilder {

	private static $isWindows; //@codingStandardsIgnoreLine

	private $arguments;
	private $cwd;
	private $env;
	private $stdin;
	private $timeout    = 60;
	private $options    = array();
	private $inheritEnv = false; //@codingStandardsIgnoreLine

	public function __construct( array $arguments = array() ) {
		$this->arguments = $arguments;

		if ( null === self::$isWindows ) { //@codingStandardsIgnoreLine
			self::$isWindows = defined( 'PHP_WINDOWS_VERSION_MAJOR' ); //@codingStandardsIgnoreLine
		}
	}

	/**
	 * Adds an unescaped argument to the command string.
	 *
	 * @param string $argument A command argument
	 */
	public function add( $argument ) {
		$this->arguments[] = $argument;

		return $this;
	}

	public function setWorkingDirectory( $cwd ) { //@codingStandardsIgnoreLine
		$this->cwd = $cwd;

		return $this;
	}

	public function inheritEnvironmentVariables( $inheritEnv = true ) { //@codingStandardsIgnoreLine
		$this->inheritEnv = $inheritEnv; //@codingStandardsIgnoreLine

		return $this;
	}

	public function setEnv( $name, $value ) { //@codingStandardsIgnoreLine
		if ( null === $this->env ) {
			$this->env = array();
		}

		$this->env[ $name ] = $value;

		return $this;
	}

	public function setInput( $stdin ) { //@codingStandardsIgnoreLine
		$this->stdin = $stdin;

		return $this;
	}

	public function setTimeout( $timeout ) { //@codingStandardsIgnoreLine
		$this->timeout = $timeout;

		return $this;
	}

	public function setOption( $name, $value ) { //@codingStandardsIgnoreLine
		$this->options[ $name ] = $value;

		return $this;
	}

	public function getProcess() { //@codingStandardsIgnoreLine
		if ( ! count( $this->arguments ) ) {
			throw new LogicException( 'You must add() command arguments before calling getProcess().' );
		}

		$options = $this->options;

		if ( self::$isWindows ) { //@codingStandardsIgnoreLine
			$options += array( 'bypass_shell' => true );

			$args = $this->arguments;
			$cmd  = array_shift( $args );

			$script = '"' . $cmd . '"';
			if ( $args ) {
				$script .= ' ' . implode( ' ', array_map( 'escapeshellarg', $args ) );
			}

			$script = 'cmd /V:ON /E:ON /C "' . $script . '"';
		} else {
			$script = implode( ' ', array_map( 'escapeshellarg', $this->arguments ) );
		}
		$env = $this->inheritEnv && $_ENV ? ( $this->env ? $this->env : array() ) + $_ENV : $this->env; //@codingStandardsIgnoreLine

		return new Process( $script, $this->cwd, $env, $this->stdin, $this->timeout, $options );
	}
}

