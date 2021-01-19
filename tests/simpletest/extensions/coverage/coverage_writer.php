<?php

/**
 * CoverageWriter class
 */
class CoverageWriter
{
    public function writeSummaryReport($file, $data)
    {
        $summaryTemplateContents = function($file, $data) {
            extract($data);
            $now = date('F j, Y, g:i a');
            ob_start();
            include __DIR__ . '/templates/index.php';
            return ob_get_clean();
        };

        $contents = $summaryTemplateContents($file, $data);

        file_put_contents($file, $contents);

        return $contents;
    }

    public function writeFileReport($file, $data)
    {
        $fileTemplateContents = function($file, $data) {
            extract($data);
            ob_start();
            include __DIR__ . '/templates/file.php';
            return ob_get_clean();
        };

        $contents = $fileTemplateContents($file, $data);

        file_put_contents($file, $contents);

        return $contents;
    }
}