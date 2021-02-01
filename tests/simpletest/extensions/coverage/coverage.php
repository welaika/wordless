<?php

require_once __DIR__ . '/coverage_data_handler.php';

/**
 * SimpleTest - CodeCoverage
 */
class CodeCoverage
{
    public $log;
    public $root;
    public $includes;
    public $excludes;
    public $directoryDepth;
    public $maxDirectoryDepth = 20; // reasonable, otherwise arbitrary
    public $title             = 'Code Coverage';

    # NOTE: This assumes all code shares the same current working directory.
    public $settingsFile = './coverage-settings.json';

    public static $instance;

    public function writeUntouched()
    {
        $touched   = array_flip($this->getTouchedFiles());
        $untouched = [];
        $this->getUntouchedFiles($untouched, $touched, '.', '.');
        $this->includeUntouchedFiles($untouched);
    }

    public function getTouchedFiles()
    {
        $handler = new CoverageDataHandler($this->log);
        $touched = $handler->getFilenames();

        return $touched;
    }

    public function includeUntouchedFiles($untouched)
    {
        $handler = new CoverageDataHandler($this->log);
        foreach ($untouched as $file) {
            $handler->writeUntouchedFile($file);
        }
    }

    public function getUntouchedFiles(&$untouched, $touched, $parentPath, $rootPath, $directoryDepth = 1)
    {
        $parent = opendir($parentPath);
        while ($file = readdir($parent)) {
            $path = "$parentPath/$file";
            if (is_dir($path)) {
                if ($file != '.' && $file != '..') {
                    if ($this->isDirectoryIncluded($path, $directoryDepth)) {
                        $this->getUntouchedFiles($untouched, $touched, $path, $rootPath, $directoryDepth + 1);
                    }
                }
            } elseif ($this->isFileIncluded($path)) {
                $relativePath = CoverageDataHandler::ltrim($rootPath . '/', $path);
                if (!array_key_exists($relativePath, $touched)) {
                    $untouched[] = $relativePath;
                }
            }
        }
        closedir($parent);
    }

    public function resetLog()
    {
        $file = fopen($this->log, 'w');
        if (!$file) {
            throw new Exception('Could not create ' . $this->log);
        }
        fclose($file);
        if (!chmod($this->log, 0666)) {
            throw new Exception('Could not change ownership on file  ' . $this->log);
        }
        $handler = new CoverageDataHandler($this->log);
        $handler->createSchema();
    }

    public function startCoverage()
    {
        $this->root = getcwd();
        if (!extension_loaded('xdebug')) {
            throw new Exception('Could not load xdebug extension');
        }
        xdebug_start_code_coverage(XDEBUG_CC_UNUSED | XDEBUG_CC_DEAD_CODE);
    }

    public function stopCoverage()
    {
        $cov = xdebug_get_code_coverage();
        $this->filter($cov);
        $data = new CoverageDataHandler($this->log);
        chdir($this->root);
        $data->write($cov);
        unset($data); // release sqlite connection
        xdebug_stop_code_coverage();
        // make sure we wind up on same current working directory, otherwise
        // coverage handler writer doesn't know what directory to chop off
        chdir($this->root);
    }

    public function readSettings()
    {
        if (!file_exists($this->settingsFile)) {
            error_log('Could not find settings file ' . $this->settingsFile);
        }

        $this->setSettings(json_decode(file_get_contents($this->settingsFile), true));
    }

    public function writeSettings()
    {
        file_put_contents($this->settingsFile, json_encode($this->getSettings(), JSON_PRETTY_PRINT));
    }

    public function getSettings()
    {
        return [
            'log'      => realpath($this->log),
            'includes' => $this->includes,
            'excludes' => $this->excludes
        ];
    }

    public function setSettings($data)
    {
        $this->log      = $data['log'];
        $this->includes = $data['includes'];
        $this->excludes = $data['excludes'];
    }

    public function filter(&$coverage)
    {
        foreach ($coverage as $file => $line) {
            if (!$this->isFileIncluded($file)) {
                unset($coverage[$file]);
            }
        }
    }

    public function isFileIncluded($file)
    {
        if (!empty($this->excludes)) {
            foreach ($this->excludes as $path) {
                if (preg_match('|' . $path . '|', $file)) {
                    return false;
                }
            }
        }

        if (!empty($this->includes)) {
            foreach ($this->includes as $path) {
                if (preg_match('|' . $path . '|', $file)) {
                    return true;
                }
            }

            return false;
        }

        return true;
    }

    public function isDirectoryIncluded($dir, $directoryDepth)
    {
        if ($directoryDepth >= $this->maxDirectoryDepth) {
            return false;
        }
        if (isset($this->excludes)) {
            foreach ($this->excludes as $path) {
                if (preg_match('|' . $path . '|', $dir)) {
                    return false;
                }
            }
        }

        return true;
    }

    public static function isCoverageOn()
    {
        $coverage = self::getInstance();

        if (empty($coverage->log) || !file_exists($coverage->log)) {
            throw new Exception('Could not find the coverage log file.');
        }

        return true;
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
            self::$instance->readSettings();
        }

        return self::$instance;
    }
}
