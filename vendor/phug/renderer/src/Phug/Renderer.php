<?php

namespace Phug;

use Phug\Renderer\AdapterInterface;
use Phug\Renderer\CacheInterface;
use Phug\Renderer\Partial\AdapterTrait;
use Phug\Renderer\Partial\Debug\DebuggerTrait;
use Phug\Renderer\Partial\RendererOptionsTrait;
use Phug\Renderer\Partial\SharedVariablesTrait;
use Phug\Util\ModuleContainerInterface;
use Phug\Util\Partial\ModuleContainerTrait;
use Phug\Util\SandBox;

class Renderer implements ModuleContainerInterface
{
    use ModuleContainerTrait;
    use DebuggerTrait;
    use RendererOptionsTrait;
    use SharedVariablesTrait;
    use AdapterTrait;

    /**
     * @var Compiler
     */
    private $compiler;

    /**
     * Renderer constructor.
     *
     * @param array|\ArrayAccess|null $options
     *
     * @throws RendererException
     */
    public function __construct($options = null)
    {
        $this->setOptionsDefaults($options ?: [], $this->getDefaultOptions($options));

        $this->initCompiler();

        $this->initDebugOptions($this);

        $adapterClassName = $this->getOption('adapter_class_name');

        if (!is_a($adapterClassName, AdapterInterface::class, true)) {
            throw new RendererException(
                "Passed adapter class $adapterClassName is ".
                'not a valid '.AdapterInterface::class
            );
        }
        $this->adapter = new $adapterClassName($this, $this->getOptions());

        $this->enableModules();
    }

    /**
     * Get the current compiler in use. The compiler class name can be changed with compiler_class_name option and
     * is Phug\Compiler by default.
     *
     * @return CompilerInterface
     */
    public function getCompiler()
    {
        return $this->compiler;
    }

    /**
     * Compile a pug template string into a PHP string.
     *
     * @param string $string   pug input string
     * @param string $filename optional file path of the given template
     *
     * @return string
     */
    public function compile($string, $filename = null)
    {
        $compiler = $this->getCompiler();

        $this->setDebugString($string);
        $this->setDebugFile($filename);
        $this->setDebugFormatter($compiler->getFormatter());

        return $compiler->compile($string, $filename);
    }

    /**
     * Compile a pug template file into a PHP string.
     *
     * @param string $path pug input file
     *
     * @return string
     */
    public function compileFile($path)
    {
        $compiler = $this->getCompiler();

        $this->setDebugFile($path);
        $this->setDebugFormatter($compiler->getFormatter());

        return $compiler->compileFile($path);
    }

    /**
     * Render a pug template string into a HTML/XML string (or any tag templates if you use a custom format).
     *
     * @param string $string     pug input string
     * @param array  $parameters parameters (values for variables used in the template)
     * @param string $filename   optional file path of the given template
     *
     * @throws RendererException
     *
     * @return string
     */
    public function render($string, array $parameters = [], $filename = null)
    {
        return $this->callAdapter(
            'render',
            null,
            $string,
            function ($path, $input) use ($filename) {
                return $this->compile($input, $filename);
            },
            $parameters
        );
    }

    /**
     * Render a pug template file into a HTML/XML string (or any tag templates if you use a custom format).
     *
     * @param string       $path       pug input file
     * @param string|array $parameters parameters (values for variables used in the template)
     *
     * @throws RendererException
     *
     * @return string
     */
    public function renderFile($path, array $parameters = [])
    {
        return $this->callAdapter(
            'render',
            $path,
            null,
            function ($path) {
                return $this->compileFile($path);
            },
            $parameters
        );
    }

    /**
     * Render all pug template files in an input directory and output in an other or the same directory.
     * Return an array with success count and error count.
     *
     * @param string       $path        pug input directory containing pug files
     * @param string       $destination pug output directory (optional)
     * @param string       $extension   file extension (optional, .html by default)
     * @param string|array $parameters  parameters (values for variables used in the template) (optional)
     *
     * @return array
     */
    public function renderDirectory($path, $destination = null, $extension = '.html', array $parameters = [])
    {
        if (is_array($destination)) {
            $parameters = $destination;
            $destination = null;
        } elseif (is_array($extension)) {
            $parameters = $extension;
            $extension = '.html';
        }
        if (!$destination) {
            $destination = $path;
        }
        $path = realpath($path);
        $destination = realpath($destination);

        $success = 0;
        $errors = 0;
        if ($path && $destination) {
            $path = rtrim($path, '/\\');
            $destination = rtrim($destination, '/\\');
            $length = mb_strlen($path);
            foreach ($this->scanDirectory($path) as $file) {
                $relativeDirectory = trim(mb_substr(dirname($file), $length), '//\\');
                $filename = pathinfo($file, PATHINFO_FILENAME);
                $outputDirectory = $destination.DIRECTORY_SEPARATOR.$relativeDirectory;
                $counter = null;
                if (!file_exists($outputDirectory)) {
                    if (!@mkdir($outputDirectory, 0777, true)) {
                        $counter = 'errors';
                    }
                }
                if (!$counter) {
                    $outputFile = $outputDirectory.DIRECTORY_SEPARATOR.$filename.$extension;
                    $sandBox = new SandBox(function () use ($outputFile, $file, $parameters) {
                        return file_put_contents($outputFile, $this->renderFile($file, $parameters));
                    });
                    $counter = $sandBox->getResult() ? 'success' : 'errors';
                }
                $$counter++;
            }
        }

        return [$success, $errors];
    }

    /**
     * Display a pug template string into a HTML/XML string (or any tag templates if you use a custom format).
     *
     * @param string $string     pug input string
     * @param array  $parameters parameters or file name
     * @param string $filename
     *
     * @throws RendererException|\Throwable
     */
    public function display($string, array $parameters = [], $filename = null)
    {
        return $this->callAdapter(
            'display',
            null,
            $string,
            function ($path, $input) use ($filename) {
                return $this->compile($input, $filename);
            },
            $parameters
        );
    }

    /**
     * Display a pug template file into a HTML/XML string (or any tag templates if you use a custom format).
     *
     * @param string $path       pug input file
     * @param array  $parameters parameters (values for variables used in the template)
     *
     * @throws RendererException|\Throwable
     */
    public function displayFile($path, array $parameters = [])
    {
        return $this->callAdapter(
            'display',
            $path,
            null,
            function ($path) {
                return $this->compileFile($path);
            },
            $parameters
        );
    }

    /**
     * Cache all templates in a directory in the cache directory you specified with the cache_dir option.
     * You should call after deploying your application in production to avoid a slower page loading for the first
     * user.
     *
     * @param $directory
     *
     * @throws RendererException
     *
     * @return array
     */
    public function cacheDirectory($directory)
    {
        $adapter = $this->getAdapter();
        if (!($adapter instanceof CacheInterface)) {
            throw new RendererException(
                'You cannot cache a directory with '.get_class($adapter).
                ' because this adapter does not implement '.CacheInterface::class
            );
        }

        return $adapter->cacheDirectory($directory);
    }
}
