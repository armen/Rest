<?php

/*
 * This file is part of the Flintstones RestExtension.
 *
 * (c) Igor Wiedler <igor@wiedler.ch>
 *
 * For the full copyright and license information, please view the LICENSE
  * file that was distributed with this source code.
 */

namespace Flintstones\Rest\Tests;

use Flintstones\Rest\Extension as RestExtension;

use Silex\Application;

use Symfony\Component\HttpFoundation\Request;

/**
 * RestExtension test cases.
 *
 * @author Igor Wiedler <igor@wiedler.ch>
 */
class ExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        if (!is_file(__DIR__.'/../vendor/FOS/RestBundle/FOSRestBundle.php')) {
            $this->markTestSkipped('FOS\RestBundle submodule was not installed.');
        }
    }

    public function testRegister()
    {
        $app = new Application();

        $app->register(new RestExtension(), array(
            'rest.fos.class_path'                  => __DIR__.'/../vendor',
            'rest.serializer.class_path'           => __DIR__.'/../vendor',
            'rest.dependency_injection.class_path' => __DIR__.'/../vendor',
        ));

        $this->assertInstanceOf('Symfony\Component\Serializer\Serializer', $app['rest.serializer']);

        return $app;
    }

    /**
     * @depends testRegister
     */
    public function testDecodingOfRequestBody(Application $app)
    {
        $app->put('/api/user/{id}', function ($id) use ($app) {
            return $app['request']->get('name');
        });

        $request = Request::create('/api/user/1', 'put', array(), array(), array(), array(), '{"name":"igor"}');
        $request->headers->set('Content-Type', 'application/json');
        $response = $app->handle($request);

        $this->assertEquals('igor', $response->getContent());
    }

    /**
     * @depends testRegister
     */
    public function testFormatDetection(Application $app)
    {
        $app->get('/api/user/{id}', function ($id) use ($app) {
            return $app['request']->getRequestFormat();
        });

        $request = Request::create('/api/user/1');
        $request->headers->set('Accept', 'application/json');
        $response = $app->handle($request);

        $this->assertEquals('json', $response->getContent());
    }
}
