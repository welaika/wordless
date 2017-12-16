<?php

namespace Pug\Engine;

class Renderer extends Options
{
    /**
     * Render using the PHP engine.
     *
     * @param string $input    pug input or file
     * @param array  $vars     to pass to the view
     * @param string $filename optional file path
     *
     * @throws \Exception
     *
     * @return string
     */
    public function renderWithPhp($input, array $vars, $filename = null)
    {
        return parent::render($input, $vars, $filename);
    }

    /**
     * Render using the PHP engine.
     *
     * @param string $path pug input or file
     * @param array  $vars to pass to the view
     *
     * @throws \Exception
     *
     * @return string
     */
    public function renderFileWithPhp($path, array $vars)
    {
        return parent::renderFile($path, $vars);
    }

    /**
     * Render HTML code from a Pug input string.
     *
     * @param string $input    pug input string
     * @param array  $vars     to pass to the view
     * @param string $filename optional file path
     *
     * @throws \Exception
     *
     * @return string
     */
    public function renderString($input, array $vars = [], $filename = null)
    {
        $fallback = function () use ($input, $vars, $filename) {
            return $this->renderWithPhp($input, $vars, $filename);
        };

        if ($this->getDefaultOption('pugjs')) {
            return $this->renderWithJs($input, $filename, $vars, $fallback);
        }

        return call_user_func($fallback);
    }

    /**
     * Render HTML code from a Pug input or a Pug file.
     *
     * @param string $input    pug input or file
     * @param array  $vars     to pass to the view
     * @param string $filename optional file path
     *
     * @throws \Exception
     *
     * @return string
     */
    public function render($input, array $vars = [], $filename = null)
    {
        if (!$this->getOption('strict') && strpos($input, "\n") === false && file_exists($input) && !is_dir($input) && is_readable($input)) {
            $extension = pathinfo($input, PATHINFO_EXTENSION);
            $extension = $extension === '' ? '' : '.' . $extension;
            if (in_array($extension, $this->getOption('extensions'))) {
                return $this->renderFile($input, $vars);
            }
        }

        return $this->renderString($input, $vars, $filename);
    }

    /**
     * Render HTML code from a Pug input or a Pug file.
     *
     * @param string $input pug file
     * @param array  $vars  to pass to the view
     *
     * @throws \Exception
     *
     * @return string
     */
    public function renderFile($path, array $vars = [])
    {
        $fallback = function () use ($path, $vars) {
            return $this->renderFileWithPhp($path, $vars);
        };

        if ($this->getDefaultOption('pugjs')) {
            return $this->renderFileWithJs($path, $vars, $fallback);
        }

        return call_user_func($fallback);
    }

    /**
     * Display HTML code from a Pug input string.
     *
     * @param string $input    pug input string
     * @param array  $vars     to pass to the view
     * @param string $filename optional file path
     *
     * @throws \Exception
     *
     * @return string
     */
    public function displayString($input, array $vars = [], $filename = null)
    {
        if ($this->getDefaultOption('pugjs')) {
            echo $this->renderString($input, $vars, $filename);

            return;
        }

        parent::display($input, $vars, $filename);
    }

    /**
     * Display HTML code from a Pug input or a Pug file.
     *
     * @param string $input    pug input or file
     * @param array  $vars     to pass to the view
     * @param string $filename optional file path
     *
     * @throws \Exception
     *
     * @return string
     */
    public function display($input, array $vars = [], $filename = null)
    {
        if (!$this->getOption('strict') && strpos($input, "\n") === false && file_exists($input) && !is_dir($input) && is_readable($input)) {
            $extension = pathinfo($input, PATHINFO_EXTENSION);
            $extension = $extension === '' ? '' : '.' . $extension;
            if (in_array($extension, $this->getOption('extensions'))) {
                echo $this->renderFile($input, $vars);

                return;
            }
        }

        $this->displayString($input, $vars, $filename);
    }

    /**
     * Display HTML code from a Pug input or a Pug file.
     *
     * @param string $input pug file
     * @param array  $vars  to pass to the view
     *
     * @throws \Exception
     *
     * @return string
     */
    public function displayFile($input, array $vars = [])
    {
        if ($this->getDefaultOption('pugjs')) {
            echo $this->renderFile($input, $vars);

            return;
        }

        parent::displayFile($input, $vars);
    }
}
