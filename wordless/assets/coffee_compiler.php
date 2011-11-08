<?php

require_once 'process.php';
require_once 'process_builder.php';

class CoffeeCompiler
{
    private $coffeePath;
    private $nodePath;

    // coffee options
    private $bare;

    public function __construct($coffeePath = '/usr/bin/coffee', $nodePath = '/usr/bin/node')
    {
        $this->coffeePath = $coffeePath;
        $this->nodePath = $nodePath;
    }

    public function setBare($bare)
    {
        $this->bare = $bare;
    }

    public function filter($input_path, $cache_path)
    {

        $input_content = file_get_contents($input_path);
        $cached_path = Wordless::join_paths($cache_path, "coffee-".md5($input_content));

        if (file_exists($cached_path)) {

          return "/** cached version! **/\n".file_get_contents($cached_path);

        } else {

          $pb = new ProcessBuilder(array(
              $this->nodePath,
              $this->coffeePath,
              '-cp',
          ));

          if ($this->bare) {
              $pb->add('--bare');
          }

          $pb->add($input_path);
          $proc = $pb->getProcess();
          $code = $proc->run();

          if (0 < $code) {
              throw new Exception($proc->getErrorOutput());
          }

          $output = $proc->getOutput();
          file_put_contents($cached_path, $output);

          return $output;
        }
    }
}
