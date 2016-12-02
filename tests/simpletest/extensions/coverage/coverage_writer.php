<?php
/**
 * @package        SimpleTest
 * @subpackage     Extensions
 */
/**
 * @package        SimpleTest
 * @subpackage     Extensions
 */
interface CoverageWriter
{
    public function writeSummary($out, $variables);

    public function writeByFile($out, $variables);
}
