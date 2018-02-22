<?php

namespace Phug\Renderer\Partial;

use ErrorException;
use Phug\Renderer\Adapter\FileAdapter;
use Phug\Renderer\AdapterInterface;
use Phug\Renderer\CacheInterface;
use Phug\Renderer\Event\HtmlEvent;
use Phug\Renderer\Event\RenderEvent;
use Phug\RendererException;
use Phug\Util\SandBox;

/**
 * Trait AdapterTrait: require ModuleContainerInterface to be implemented.
 */
trait AdapterTrait
{
    /**
     * @var AdapterInterface
     */
    private $adapter;

    /**
     * Fallback to FileAdapter if the current adapter (typically an adapter) is not a cache adapter
     * (implement CacheInterface).
     */
    private function expectCacheAdapter()
    {
        if (!($this->adapter instanceof CacheInterface)) {
            $this->setAdapterClassName(FileAdapter::class);
        }
    }

    /**
     * Create/reset if needed the adapter.
     *
     * @throws RendererException
     *
     * @return $this
     */
    public function initAdapter()
    {
        $adapterClassName = $this->getOption('adapter_class_name');

        if (!$this->adapter || !is_a($this->adapter, $adapterClassName)) {
            if (!is_a($adapterClassName, AdapterInterface::class, true)) {
                throw new RendererException(
                    "Passed adapter class $adapterClassName is ".
                    'not a valid '.AdapterInterface::class
                );
            }
            $this->adapter = new $adapterClassName($this, $this->getOptions());
        }

        return $this;
    }

    /**
     * Get the current adapter used (file, stream, eval or custom adapter provided).
     *
     * @return AdapterInterface
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * Set the current adapter engine (file, stream, eval or custom adapter provided).
     *
     * @return $this
     */
    public function setAdapterClassName($adapterClassName)
    {
        return $this->setOption('adapter_class_name', $adapterClassName)->initAdapter();
    }

    /**
     * Return a sandbox with renderer settings for a given callable action.
     *
     * @param callable $action
     *
     * @return SandBox
     */
    public function getNewSandBox(callable $action)
    {
        $errorHandler = $this->getOption('error_reporting');
        if ($errorHandler !== null && !is_callable($errorHandler)) {
            $errorReporting = $errorHandler;
            $errorHandler = function ($number, $message, $file, $line) use ($errorReporting) {
                if ($errorReporting & $number) {
                    throw new ErrorException($message, 0, $number, $file, $line);
                }

                return true;
            };
        }

        return new SandBox($action, $errorHandler);
    }

    /**
     * Call an adapter method inside a sandbox and return the SandBox result.
     *
     * @param string   $source
     * @param string   $method
     * @param string   $path
     * @param string   $input
     * @param callable $getSource
     * @param array    $parameters
     *
     * @return SandBox
     */
    private function getSandboxCall(&$source, $method, $path, $input, callable $getSource, array $parameters)
    {
        return $this->getNewSandBox(function () use (&$source, $method, $path, $input, $getSource, $parameters) {
            $adapter = $this->getAdapter();
            $cacheEnabled = (
                $adapter->hasOption('cache_dir') && $adapter->getOption('cache_dir') ||
                $this->hasOption('cache_dir') && $this->getOption('cache_dir')
            );
            if ($cacheEnabled) {
                $this->expectCacheAdapter();
                $adapter = $this->getAdapter();
                $display = function () use ($adapter, $path, $input, $getSource, $parameters) {
                    /* @var CacheInterface $adapter */
                    $adapter->displayCached($path, $input, $getSource, $parameters);
                };

                return in_array($method, ['display', 'displayFile'])
                    ? $display()
                    : $adapter->captureBuffer($display);
            }

            $source = $getSource($path, $input);

            return $adapter->$method(
                $source,
                $parameters
            );
        });
    }

    /**
     * Handle an html event and accordingly to it, display, returns or throw the result/error.
     *
     * @param HtmlEvent $htmlEvent
     * @param array     $parameters
     * @param callable  $getSource
     *
     * @throws RendererException|\Throwable
     *
     * @return mixed
     */
    private function handleHtmlEvent(HtmlEvent $htmlEvent, array $parameters, $path, callable $getSource)
    {
        if ($error = $htmlEvent->getError()) {
            $this->handleError($error, 1, $path, $getSource(), $parameters, [
                'debug'               => $this->getOption('debug'),
                'error_handler'       => $this->getOption('error_handler'),
                'html_error'          => $this->getOption('html_error'),
                'error_context_lines' => $this->getOption('error_context_lines'),
                'color_support'       => $this->getOption('color_support'),
            ]);
        }

        if ($buffer = $htmlEvent->getBuffer()) {
            echo $buffer;
        }

        return $htmlEvent->getResult();
    }

    /**
     * Call a method on the adapter (render, renderFile, display, displayFile, more methods can be available depending
     * on the adapter user).
     *
     * @param string   $method
     * @param string   $path
     * @param string   $input
     * @param callable $getSource
     * @param array    $parameters
     *
     * @throws RendererException|\Throwable
     *
     * @return bool|string|null
     */
    public function callAdapter($method, $path, $input, callable $getSource, array $parameters)
    {
        $source = '';

        $renderEvent = new RenderEvent($input, $path, $method, $parameters);
        $this->trigger($renderEvent);
        $input = $renderEvent->getInput();
        $path = $renderEvent->getPath();
        $method = $renderEvent->getMethod();
        $parameters = $this->mergeWithSharedVariables($renderEvent->getParameters());
        if ($self = $this->getOption('self')) {
            $self = $self === true ? 'self' : strval($self);
            $parameters = [
                $self => $parameters,
            ];
        }

        $sandBox = $this->getSandboxCall($source, $method, $path, $input, $getSource, $parameters);

        $htmlEvent = new HtmlEvent(
            $renderEvent,
            $sandBox->getResult(),
            $sandBox->getBuffer(),
            $sandBox->getThrowable()
        );
        $this->trigger($htmlEvent);
        $sourceOnDemand = function () use ($source, $getSource, $path, $input) {
            return $source ?: $getSource($path, $input);
        };

        return $this->handleHtmlEvent($htmlEvent, $parameters, $path, $sourceOnDemand);
    }
}
