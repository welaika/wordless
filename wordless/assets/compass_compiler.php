<?php

require_once 'process.php';
require_once 'process_builder.php';

class CompassCompiler
{
    private $compassPath;
    private $scss;

    // sass options
    private $unixNewlines;
    private $debugInfo;
    private $cacheLocation;
    private $noCache;

    // compass options
    private $force;
    private $style;
    private $quiet;
    private $noLineComments;
    private $imagesDir;
    private $javascriptsDir;

    // compass configuration file options
    private $plugins = array();
    private $loadPaths = array();
    private $httpPath;
    private $httpImagesPath;
    private $httpJavascriptsPath;

    public function __construct($compassPath, $cacheLocation)
    {
        $this->compassPath = $compassPath;
        $this->cacheLocation = $cacheLocation;
    }

    public function setScss($scss)
    {
        $this->scss = $scss;
    }

    // sass options setters
    public function setUnixNewlines($unixNewlines)
    {
        $this->unixNewlines = $unixNewlines;
    }

    public function setDebugInfo($debugInfo)
    {
        $this->debugInfo = $debugInfo;
    }

    public function setCacheLocation($cacheLocation)
    {
        $this->cacheLocation = $cacheLocation;
    }

    public function setNoCache($noCache)
    {
        $this->noCache = $noCache;
    }

    // compass options setters
    public function setForce($force)
    {
        $this->force = $force;
    }

    public function setStyle($style)
    {
        $this->style = $style;
    }

    public function setQuiet($quiet)
    {
        $this->quiet = $quiet;
    }

    public function setNoLineComments($noLineComments)
    {
        $this->noLineComments = $noLineComments;
    }

    public function setImagesDir($imagesDir)
    {
        $this->imagesDir = $imagesDir;
    }

    public function setJavascriptsDir($javascriptsDir)
    {
        $this->javascriptsDir = $javascriptsDir;
    }

    // compass configuration file options setters
    public function setPlugins(array $plugins)
    {
        $this->plugins = $plugins;
    }

    public function addPlugin($plugin)
    {
        $this->plugins[] = $plugin;
    }

    public function addLoadPath($loadPath)
    {
        $this->loadPaths[] = $loadPath;
    }

    public function setHttpPath($httpPath)
    {
        $this->httpPath = $httpPath;
    }

    public function setHttpImagesPath($httpImagesPath)
    {
        $this->httpImagesPath = $httpImagesPath;
    }

    public function setHttpJavascriptsPath($httpJavascriptsPath)
    {
        $this->httpJavascriptsPath = $httpJavascriptsPath;
    }

    private function realFilter($path, $tempDir)
    {

        // compass does not seems to handle symlink, so we use realpath()

        $pb = new ProcessBuilder(array(
          $this->compassPath,
          'compile',
          $tempDir,
        ));

        $pb->inheritEnvironmentVariables();

        if ($this->force) {
            $pb->add('--force');
        }

        if ($this->style) {
            $pb->add('--output-style')->add($this->style);
        }

        if ($this->quiet) {
            $pb->add('--quiet');
        }

        if ($this->noLineComments) {
            $pb->add('--no-line-comments');
        }

        // these two options are not passed into the config file
        // because like this, compass adapts this to be xxx_dir or xxx_path
        // whether it's an absolute path or not
        if ($this->imagesDir) {
            $pb->add('--images-dir')->add($this->imagesDir);
        }

        if ($this->javascriptsDir) {
            $pb->add('--javascripts-dir')->add($this->javascriptsDir);
        }

        // options in config file
        $optionsConfig = array();

        if (!empty($this->loadPaths)) {
            $optionsConfig['additional_import_paths'] = $this->loadPaths;
        }

        if ($this->unixNewlines) {
            $optionsConfig['sass_options']['unix_newlines'] = true;
        }

        if ($this->debugInfo) {
            $optionsConfig['sass_options']['debug_info'] = true;
        }

        if ($this->cacheLocation) {
            $optionsConfig['sass_options']['cache_location'] = $this->cacheLocation;
        }

        if ($this->noCache) {
            $optionsConfig['sass_options']['no_cache'] = true;
        }

        if ($this->httpPath) {
            $optionsConfig['http_path'] = $this->httpPath;
        }

        if ($this->httpImagesPath) {
            $optionsConfig['http_images_path'] = $this->httpImagesPath;
        }

        if ($this->httpJavascriptsPath) {
            $optionsConfig['http_javascripts_path'] = $this->httpJavascriptsPath;
        }

        // options in configuration file
        if (false && count($optionsConfig)) {
            $config = array();
            foreach ($this->plugins as $plugin) {
                $config[] = sprintf("require '%s'", addcslashes($plugin, '\\'));
            }
            foreach ($optionsConfig as $name => $value) {
                if (!is_array($value)) {
                    $config[] = sprintf('%s = "%s"', $name, addcslashes($value, '\\'));
                } elseif (!empty($value)) {
                    $config[] = sprintf('%s = %s', $name, $this->formatArrayToRuby($value));
                }
            }

            $configFile = tempnam($tempDir, 'compass_config');
            file_put_contents($configFile, implode("\n", $config)."\n");
            $pb->add('--config')->add($configFile);
        }

        // compass choose the type (sass or scss from the filename)
        if (null !== $this->scss) {
            $type = $this->scss ? 'scss' : 'sass';
        } elseif ($path) {
            // FIXME: what if the extension is something else?
            $type = pathinfo($path, PATHINFO_EXTENSION);
        } else {
            $type = 'scss';
        }

        $pb->add("--sass-dir")->add(dirname($path));
        $pb->add("--css-dir")->add($tempDir);

        // output
        $output = $tempDir . "/" . basename($path, ".$type") . '.css';

        $proc = $pb->getProcess();
        $start = time();
        $code = $proc->run();
        $end = time();

        if (0 < $code) {
          throw new Exception($proc->getErrorOutput());
        }

        $output = file_get_contents($output);

        if (isset($configFile)) {
            unlink($configFile);
        }

        return sprintf("/* compile time: ~%d secs /*\n", $end - $start) . $output;
    }

    public function filter($input_path, $cache_path) {
      $base_path = dirname($input_path);
      $files = $this->folderTree("*.sass", 0, dirname($base_path));
      sort($files);
      $modification_times = array();
      foreach ($files as $file) {
        $modification_times[] = file_get_contents($file);
      }
      $hash = "compass-".md5(join($modification_times));
      $cached_path = Wordless::join_paths($cache_path, $hash);

      if (file_exists($cached_path)) {
        return "/** cached version **/\n" . file_get_contents($cached_path);
      } else {
        $output = $this->realFilter($input_path, $cache_path);
        file_put_contents($cached_path, $output);
        return $output;
      }
    }

    private function formatArrayToRuby($array)
    {
        $output = array();

        // does we have an associative array ?
        if (count(array_filter(array_keys($array), "is_numeric")) != count($array)) {
            foreach($array as $name => $value) {
                $output[] = sprintf('    :%s => "%s"', $name, addcslashes($value, '\\'));
            }
            $output = "{\n".implode(",\n", $output)."\n}";
        } else {
            foreach($array as $name => $value) {
                $output[] = sprintf('    "%s"', addcslashes($value, '\\'));
            }
            $output = "[\n".implode(",\n", $output)."\n]";
        }

        return $output;
    }

    /* to use:
    pattern = glob pattern to match
    flags = glob flags
    path = path to search
    depth = how deep to travel, -1 for unlimited, 0 for only current directory
    */

    private function folderTree($pattern = '*', $flags = 0, $path = false, $depth = -1, $level = 0) {
      $files = glob($path.$pattern, $flags);
      if (!is_array($files)) {
        $files = array();
      }
      $paths = glob($path.'*', GLOB_ONLYDIR|GLOB_NOSORT);

      if (!empty($paths) && ($level < $depth || $depth == -1)) {
        $level++;
        foreach ($paths as $sub_path) {
          $subfiles = $this->folderTree($pattern, $flags, $sub_path.DIRECTORY_SEPARATOR, $depth, $level);
          if (is_array($subfiles))
            $files = array_merge($files, $subfiles);
        }
      }

      return $files;
    }
}
