<?php

use Jade\Jade;

class JadeTest extends Jade
{
    protected $compilationsCount = 0;

    public function getCompilationsCount()
    {
        return $this->compilationsCount;
    }

    public function compile($input, $filename = null)
    {
        $this->compilationsCount++;
        return parent::compile($input, $filename);
    }
}

class JadeCacheTest extends PHPUnit_Framework_TestCase
{
    protected function emptyDirectory($dir)
    {
        if (!is_dir($dir)) {
            return;
        }
        foreach (scandir($dir) as $file) {
            if ($file !== '.' && $file !== '..') {
                $path = $dir . '/' . $file;
                if (is_dir($path)) {
                    $this->emptyDirectory($path);
                } else {
                    unlink($path);
                }
            }
        }
    }

    /**
     * @expectedException \ErrorException
     * @expectedExceptionCode 5
     */
    public function testMissingDirectory()
    {
        $jade = new Jade(array(
            'singleQuote' => false,
            'cache' => 'does/not/exists'
        ));
        $jade->render(__DIR__ . '/../templates/attrs.jade');
    }

    /**
     * Cache from string input
     */
    public function testStringInputCache()
    {
        $dir = sys_get_temp_dir() . '/jade';
        if (file_exists($dir)) {
            if (is_file($dir)) {
                unlink($dir);
                mkdir($dir);
            } else {
                $this->emptyDirectory($dir);
            }
        } else {
            mkdir($dir);
        }
        $jade = new JadeTest(array(
            'cache' => $dir
        ));
        $this->assertSame(0, $jade->getCompilationsCount(), 'Should have done always 2 compilations because the code changed');
        $this->assertSame(0, $jade->getCompilationsCount(), 'Should have done no compilations yet');
        $jade->render("header\n  h1#foo Hello World!\nfooter");
        $this->assertSame(1, $jade->getCompilationsCount(), 'Should have done 1 compilation');
        $jade->render("header\n  h1#foo Hello World!\nfooter");
        $this->assertSame(1, $jade->getCompilationsCount(), 'Should have done always 1 compilation because the code is cached');
        $jade->render("header\n  h1#foo Hello World?\nfooter");
        $this->assertSame(2, $jade->getCompilationsCount(), 'Should have done always 2 compilations because the code changed');
        $this->emptyDirectory($dir);
    }

    /**
     * @expectedException \ErrorException
     * @expectedExceptionCode 6
     */
    public function testReadOnlyDirectory()
    {
        $dir = __DIR__;
        while (is_writeable($dir)) {
            $parent = realpath($dir . '/..');
            if ($parent === $dir) {
                $dir = 'C:';
                if (!file_exists($dir) || is_writable($dir)) {
                    throw new \ErrorException('No read-only directory found to do the test', 6);
                }
                break;
            }
            $dir = $parent;
        }
        $jade = new Jade(array(
            'singleQuote' => false,
            'cache' => $dir,
        ));
        $jade->cache(__DIR__ . '/../templates/attrs.jade');
    }

    private function cacheSystem($keepBaseName)
    {
        $cacheDirectory = sys_get_temp_dir() . '/pug-test';
        $this->emptyDirectory($cacheDirectory);
        if (!is_dir($cacheDirectory)) {
            mkdir($cacheDirectory, 0777, true);
        }
        $file = tempnam(sys_get_temp_dir(), 'jade-test-');
        $jade = new Jade(array(
            'singleQuote' => false,
            'keepBaseName' => $keepBaseName,
            'cache' => $cacheDirectory,
        ));
        copy(__DIR__ . '/../templates/attrs.jade', $file);
        $name = basename($file);
        $stream = $jade->cache($file);
        $phpFiles = array_values(array_map(function ($file) use ($cacheDirectory) {
            return $cacheDirectory . DIRECTORY_SEPARATOR . $file;
        }, array_filter(scandir($cacheDirectory), function ($file) {
            return substr($file, -4) === '.php';
        })));
        $start = 'jade.stream://data;';
        $this->assertTrue(strpos($stream, $start) === 0, 'Fresh content should be a stream.');
        $this->assertSame(1, count($phpFiles), 'The cached file should now exist.');
        $cachedFile = realpath($phpFiles[0]);
        $this->assertFalse(!$cachedFile, 'The cached file should now exist.');
        $this->assertSame($stream, $jade->stream($jade->compile($file)), 'Should return the stream of attrs.jade.');
        $this->assertStringEqualsFile($cachedFile, substr($stream, strlen($start)), 'The cached file should contains the same contents.');
        touch($file, time() - 3600);
        $path = $jade->cache($file);
        $this->assertSame(realpath($path), $cachedFile, 'The cached file should be used instead if untouched.');
        copy(__DIR__ . '/../templates/mixins.jade', $file);
        touch($file, time() + 3600);
        $stream = $jade->cache($file);
        $this->assertSame($stream, $jade->stream($jade->compile(__DIR__ . '/../templates/mixins.jade')), 'The cached file should be the stream of mixins.jade.');
        unlink($file);
    }

    /**
     * Normal function
     */
    public function testCache()
    {
        $this->cacheSystem(false);
    }

    /**
     * Test option keepBaseName
     */
    public function testCacheWithKeepBaseName()
    {
        $this->cacheSystem(true);
    }

    /**
     * Test cacheDirectory method
     */
    public function testCacheDirectory()
    {
        $cacheDirectory = sys_get_temp_dir() . '/pug-test';
        $this->emptyDirectory($cacheDirectory);
        if (!is_dir($cacheDirectory)) {
            mkdir($cacheDirectory, 0777, true);
        }
        $templatesDirectory = __DIR__ . '/../templates';
        $jade = new Jade(array(
            'basedir' => $templatesDirectory,
            'cache' => $cacheDirectory,
        ));
        list($success, $errors) = $jade->cacheDirectory($templatesDirectory);
        $filesCount = count(array_filter(scandir($cacheDirectory), function ($file) {
            return $file !== '.' && $file !== '..';
        }));
        $expectedCount = count(array_filter(array_merge(
            scandir($templatesDirectory),
            scandir($templatesDirectory . '/auxiliary'),
            scandir($templatesDirectory . '/auxiliary/subdirectory/subsubdirectory')
        ), function ($file) {
            return in_array(pathinfo($file, PATHINFO_EXTENSION), array('pug', 'jade'));
        }));
        $this->emptyDirectory($cacheDirectory);
        $templatesDirectory = __DIR__ . '/../templates/subdirectory/subsubdirectory';
        $jade = new Jade(array(
            'basedir' => $templatesDirectory,
            'cache' => $cacheDirectory,
        ));
        $this->emptyDirectory($cacheDirectory);
        rmdir($cacheDirectory);

        $this->assertSame($expectedCount, $success + $errors, 'Each .jade file in the directory to cache should generate a success or an error.');
        $this->assertSame($success, $filesCount, 'Each file successfully cached should be in the cache directory.');
    }
}
