<?php

declare(strict_types=1);

namespace Shaarli\Front\Controller;

use PHPUnit\Framework\TestCase;
use Shaarli\Container\ShaarliContainer;
use Slim\Http\Request;
use Slim\Http\Response;

class TagControllerTest extends TestCase
{
    /** @var ShaarliContainer */
    protected $container;

    /** @var TagController */
    protected $controller;

    public function setUp(): void
    {
        $this->container = $this->createMock(ShaarliContainer::class);
        $this->controller = new TagController($this->container);
    }

    public function testAddTagWithoutReferer(): void
    {
        $request = $this->createMock(Request::class);
        $response = new Response();

        $tag = 'hiThere';

        $result = $this->controller->addTag($request, $response, ['newTag' => $tag]);

        static::assertInstanceOf(Response::class, $result);
        static::assertSame(302, $result->getStatusCode());

        static::assertSame('./?searchtags=hiThere', $result->getHeader('Location')[0]);
    }

    public function testAddTagSimpleAddTag(): void
    {
        $request = $this->createMock(Request::class);
        $response = new Response();

        $this->container->environment = ['HTTP_REFERER' => 'https://shaarli.tld/tag-cloud'];

        $tag = 'hiThere';

        $result = $this->controller->addTag($request, $response, ['newTag' => $tag]);

        static::assertInstanceOf(Response::class, $result);
        static::assertSame(302, $result->getStatusCode());

        static::assertSame('/tag-cloud?searchtags=hiThere', $result->getHeader('Location')[0]);
    }

    public function testAddTagAddTagSubDir(): void
    {
        $request = $this->createMock(Request::class);
        $response = new Response();

        $this->container->environment = ['HTTP_REFERER' => 'https://shaarli.tld/other/path/tag-cloud'];

        $tag = 'hiThere';

        $result = $this->controller->addTag($request, $response, ['newTag' => $tag]);

        static::assertInstanceOf(Response::class, $result);
        static::assertSame(302, $result->getStatusCode());

        static::assertSame('/other/path/tag-cloud?searchtags=hiThere', $result->getHeader('Location')[0]);
    }

    public function testAddTagAddExistingSearch(): void
    {
        $request = $this->createMock(Request::class);
        $response = new Response();

        $this->container->environment = [
            'HTTP_REFERER' => 'https://shaarli.tld/?searchtags=abc+def'
        ];

        $tag = 'hiThere';

        $result = $this->controller->addTag($request, $response, ['newTag' => $tag]);

        static::assertInstanceOf(Response::class, $result);
        static::assertSame(302, $result->getStatusCode());

        static::assertSame('/?searchtags=abc+def+hiThere', $result->getHeader('Location')[0]);
    }

    public function testAddTagAddExistingSearchWithTerms(): void
    {
        $request = $this->createMock(Request::class);
        $response = new Response();

        $this->container->environment = [
            'HTTP_REFERER' => 'https://shaarli.tld/subddir/tag-cloud?searchtags=abc+def&searchterm=test'
        ];

        $tag = 'hiThere';

        $result = $this->controller->addTag($request, $response, ['newTag' => $tag]);

        static::assertInstanceOf(Response::class, $result);
        static::assertSame(302, $result->getStatusCode());

        static::assertSame(
            '/subddir/tag-cloud?searchtags=abc+def+hiThere&searchterm=test',
            $result->getHeader('Location')[0]
        );
    }

    public function testAddTagAddEmpty(): void
    {
        $request = $this->createMock(Request::class);
        $response = new Response();

        $this->container->environment = [
            'HTTP_REFERER' => 'https://shaarli.tld/subddir/tag-cloud'
        ];

        $result = $this->controller->addTag($request, $response, []);

        static::assertInstanceOf(Response::class, $result);
        static::assertSame(302, $result->getStatusCode());

        static::assertSame(
            '/subddir/tag-cloud',
            $result->getHeader('Location')[0]
        );
    }

    public function testAddTagDuplicate(): void
    {
        $request = $this->createMock(Request::class);
        $response = new Response();

        $tag = 'hiThere';

        $this->container->environment = [
            'HTTP_REFERER' => 'https://shaarli.tld/subddir/tag-cloud?searchtags='. $tag
        ];

        $result = $this->controller->addTag($request, $response, ['newTag' => $tag]);

        static::assertInstanceOf(Response::class, $result);
        static::assertSame(302, $result->getStatusCode());

        static::assertSame(
            '/subddir/tag-cloud?searchtags=hiThere',
            $result->getHeader('Location')[0]
        );
    }

    public function testAddTagNotAnURLReferer(): void
    {
        $request = $this->createMock(Request::class);
        $response = new Response();

        $this->container->environment = [
            'HTTP_REFERER' => 'http://nope'
        ];

        $tag = 'hiThere';
        $result = $this->controller->addTag($request, $response, ['newTag' => $tag]);

        static::assertInstanceOf(Response::class, $result);
        static::assertSame(302, $result->getStatusCode());

        static::assertSame(
            '/?searchtags=hiThere',
            $result->getHeader('Location')[0]
        );
    }

    public function testAddTagWithPagination(): void
    {
        $request = $this->createMock(Request::class);
        $response = new Response();

        $this->container->environment = [
            'HTTP_REFERER' => 'http://shaarli.tld?page=4'
        ];

        $tag = 'hiThere';
        $result = $this->controller->addTag($request, $response, ['newTag' => $tag]);

        static::assertInstanceOf(Response::class, $result);
        static::assertSame(302, $result->getStatusCode());

        static::assertSame(
            '/?searchtags=hiThere',
            $result->getHeader('Location')[0]
        );
    }
}
