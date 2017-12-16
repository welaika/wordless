<?php

namespace Phug\Test;

use JsTransformer\JsTransformer;
use NodejsPhpFallback\NodejsPhpFallback;
use Phug\JsTransformerExtension;
use Phug\JsTransformerFilter;
use Phug\Phug;

/**
 * @coversDefaultClass \Phug\JsTransformerExtension
 */
class JsTransformerExtensionTest extends \PHPUnit_Framework_TestCase
{
    protected static function removeDirectory($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object !== '.' && $object !== '..') {
                    if (is_dir($dir.'/'.$object)) {
                        static::removeDirectory($dir.'/'.$object);
                        continue;
                    }
                    // move before delete to avoid Windows too long name error
                    try {
                        @rename($dir.'/'.$object, sys_get_temp_dir().'/to-delete');
                        @unlink(sys_get_temp_dir().'/to-delete');
                    } catch (\Exception $e) {
                    }
                }
            }
            @rmdir($dir);
        }
        if (is_file($dir)) {
            @unlink($dir);
        }
    }

    /**
     * @covers ::getTransformer
     */
    public function testGetTransformer()
    {
        $extension = new JsTransformerExtension();
        $get1 = $extension->getTransformer();
        $get2 = $extension->getTransformer();

        self::assertInstanceOf(JsTransformer::class, $get1);
        self::assertSame($get1, $get2);
    }

    /**
     * @covers ::getOptions
     * @covers \Phug\JsTransformerFilter::<public>
     */
    public function testGetOptions()
    {
        $dir = __DIR__.'/../../vendor/nodejs-php-fallback/nodejs-php-fallback/node_modules';
        static::removeDirectory("$dir/jstransformer-less");

        $extension = new JsTransformerExtension();

        $resolver = $extension->getOptions()['filter_resolvers']['jsTransformer'];

        self::assertSame(null, $resolver('less'));

        NodejsPhpFallback::installPackages(['jstransformer-less']);

        $filter = $resolver('less');

        self::assertInstanceOf(JsTransformerFilter::class, $filter);

        self::assertSame("a{color:red}\n", $filter("a {\n  color: red;\n}\n", ['compress' => true]));

        Phug::addExtension(JsTransformerExtension::class);

        self::assertSame(
            '<style>a{color:red}</style>',
            preg_replace('/\s/', '', Phug::render("style: :less(compress=true)\n  a {\n    color: red;\n  }\n"))
        );
        static::removeDirectory("$dir/jstransformer-less");
    }
}
