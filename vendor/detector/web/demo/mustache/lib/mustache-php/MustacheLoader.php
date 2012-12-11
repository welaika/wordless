<?php

/**
 * A Mustache Partial filesystem loader.
 *
 * @author Justin Hileman {@link http://justinhileman.com}
 */
class MustacheLoader implements ArrayAccess {

	protected $baseDir;
	protected $partialsCache = array();
	protected $extension;

	/**
	 * MustacheLoader constructor.
	 *
	 * @access public
	 * @param  string $baseDir   Base template directory.
	 * @param  string $extension File extension for Mustache files (default: 'mustache')
	 * @return void
	 */
	public function __construct($baseDir, $extension = 'mustache', $defaultDir = 'base') {
		
		if (!is_dir($baseDir)) {
			throw new InvalidArgumentException('$baseDir must be a valid directory, ' . $baseDir . ' given.');
		}
		if (!is_dir($defaultDir)) {
			throw new InvalidArgumentException('$defaultDir must be a valid directory, ' . $defaultDir . ' given.');
		}
		
		$this->baseDir    = $baseDir;
		$this->extension  = $extension;
		$this->defaultDir = $defaultDir;
		$this->finalPath  = "";
	}

	/**
	 * @param  string $offset Name of partial
	 * @return boolean
	 */
	public function offsetExists($offset,$test = true) {
		if ($test) {
			$this->finalPath = $this->pathName($offset);
		}
		return (isset($this->partialsCache[$offset]) || file_exists($this->finalPath));
	}
	
	/**
	 * @throws InvalidArgumentException if the given partial doesn't exist
	 * @param  string $offset Name of partial
	 * @return string Partial template contents
	 */
	public function offsetGet($offset) {
		
		if (!$this->offsetExists($offset, false)) {
			throw new InvalidArgumentException('Partial does not exist: ' . $offset);
		}

		if (!isset($this->partialsCache[$offset])) {
			$file = @file_get_contents($this->finalPath);
			$this->partialsCache[$offset] = $file;
		}

		return $this->partialsCache[$offset];
	}
	
	/**
	 * MustacheLoader is an immutable filesystem loader. offsetSet throws a LogicException if called.
	 *
	 * @throws LogicException
	 * @return void
	 */
	public function offsetSet($offset, $value) {
		throw new LogicException('Unable to set offset: MustacheLoader is an immutable ArrayAccess object.');
	}
	
	/**
	 * MustacheLoader is an immutable filesystem loader. offsetUnset throws a LogicException if called.
	 *
	 * @throws LogicException
	 * @return void
	 */
	public function offsetUnset($offset) {
		throw new LogicException('Unable to unset offset: MustacheLoader is an immutable ArrayAccess object.');
	}

	/**
	 * An internal helper for generating path names.
	 * 
	 * @param  string $file Partial name
	 * @return string File path
	 */
	protected function pathName($file) {
		if (Detector::$splitFamily) {
			$matches = explode("-",$this->baseDir);
			if (count($matches) > 1) {
				$dirs = array();
				$dir = "";
				$k = 0;			
				while ($k < count($matches)) {
					$dir .= ($k == 0) ? $matches[$k] : "-".$matches[$k];
					$dirs[] = $dir;
					$k++;
				}
				$dirs = array_reverse($dirs);
				foreach($dirs as $dir) {
					if (file_exists($dir . '/' . $file . '.' . $this->extension)) {
						return $dir . '/' . $file . '.' . $this->extension;
					}
				}
			}
		}
		if (file_exists($this->baseDir . '/' . $file . '.' . $this->extension)) {
			return $this->baseDir . '/' . $file . '.' . $this->extension;
		} 
		if (file_exists($this->defaultDir . '/' . $file . '.' . $this->extension)) {
			return $this->defaultDir . '/' . $file . '.' . $this->extension;
		}
	}
}
