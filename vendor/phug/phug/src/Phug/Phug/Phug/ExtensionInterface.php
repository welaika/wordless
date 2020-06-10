<?php

namespace Phug;

interface ExtensionInterface
{
    public function getOptions();

    public function getEvents();

    public function getIncludes();

    public function getScanners();

    public function getFilters();

    public function getKeywords();

    public function getTokenHandlers();

    public function getElementHandlers();

    public function getPhpTokenHandlers();

    public function getCompilers();

    public function getFormats();

    public function getAssignmentHandlers();

    public function getPatterns();
}
