<?php

declare(strict_types=1);

namespace Shaarli\Front\Controller;

use PHPUnit\Framework\TestCase;
use Shaarli\Bookmark\BookmarkFilter;
use Shaarli\Bookmark\BookmarkServiceInterface;
use Shaarli\Config\ConfigManager;
use Shaarli\Container\ShaarliContainer;
use Shaarli\Plugin\PluginManager;
use Shaarli\Render\PageBuilder;
use Shaarli\Security\LoginManager;
use Shaarli\Security\SessionManager;
use Slim\Http\Request;
use Slim\Http\Response;

class TagCloudControllerTest extends TestCase
{
    /** @var ShaarliContainer */
    protected $container;

    /** @var TagCloudController */
    protected $controller;

    public function setUp(): void
    {
        $this->container = $this->createMock(ShaarliContainer::class);
        $this->controller = new TagCloudController($this->container);
    }

    public function testValidCloudControllerInvokeDefault(): void
    {
        $this->createValidContainerMockSet();

        $allTags = [
            'ghi' => 1,
            'abc' => 3,
            'def' => 12,
        ];
        $expectedOrder = ['abc', 'def', 'ghi'];

        $request = $this->createMock(Request::class);
        $request->expects(static::once())->method('getQueryParam')->with('searchtags')->willReturn(null);
        $response = new Response();

        // Save RainTPL assigned variables
        $assignedVariables = [];
        $this->assignTemplateVars($assignedVariables);

        $this->container->bookmarkService
            ->expects(static::once())
            ->method('bookmarksCountPerTag')
            ->with([], null)
            ->willReturnCallback(function () use ($allTags): array {
                return $allTags;
            })
        ;

        // Make sure that PluginManager hook is triggered
        $this->container->pluginManager
            ->expects(static::at(0))
            ->method('executeHooks')
            ->willReturnCallback(function (string $hook, array $data, array $param): array {
                static::assertSame('render_tagcloud', $hook);
                static::assertSame('', $data['search_tags']);
                static::assertCount(3, $data['tags']);

                static::assertArrayHasKey('loggedin', $param);

                return $data;
            })
        ;

        $result = $this->controller->index($request, $response);

        static::assertSame(200, $result->getStatusCode());
        static::assertSame('tag.cloud', (string) $result->getBody());
        static::assertSame('Tag cloud - Shaarli', $assignedVariables['pagetitle']);

        static::assertSame('', $assignedVariables['search_tags']);
        static::assertCount(3, $assignedVariables['tags']);
        static::assertSame($expectedOrder, array_keys($assignedVariables['tags']));

        foreach ($allTags as $tag => $count) {
            static::assertArrayHasKey($tag, $assignedVariables['tags']);
            static::assertSame($count, $assignedVariables['tags'][$tag]['count']);
            static::assertGreaterThan(0, $assignedVariables['tags'][$tag]['size']);
            static::assertLessThan(5, $assignedVariables['tags'][$tag]['size']);
        }
    }

    /**
     * Additional parameters:
     *   - logged in
     *   - visibility private
     *   - search tags: `ghi` and `def` (note that filtered tags are not displayed anymore)
     */
    public function testValidCloudControllerInvokeWithParameters(): void
    {
        $this->createValidContainerMockSet();

        $allTags = [
            'ghi' => 1,
            'abc' => 3,
            'def' => 12,
        ];

        $request = $this->createMock(Request::class);
        $request
            ->expects(static::once())
            ->method('getQueryParam')
            ->with('searchtags')
            ->willReturn('ghi def')
        ;
        $response = new Response();

        // Save RainTPL assigned variables
        $assignedVariables = [];
        $this->assignTemplateVars($assignedVariables);

        $this->container->loginManager->method('isLoggedin')->willReturn(true);
        $this->container->sessionManager->expects(static::once())->method('getSessionParameter')->willReturn('private');

        $this->container->bookmarkService
            ->expects(static::once())
            ->method('bookmarksCountPerTag')
            ->with(['ghi', 'def'], BookmarkFilter::$PRIVATE)
            ->willReturnCallback(function () use ($allTags): array {
                return $allTags;
            })
        ;

        // Make sure that PluginManager hook is triggered
        $this->container->pluginManager
            ->expects(static::at(0))
            ->method('executeHooks')
            ->willReturnCallback(function (string $hook, array $data, array $param): array {
                static::assertSame('render_tagcloud', $hook);
                static::assertSame('ghi def', $data['search_tags']);
                static::assertCount(1, $data['tags']);

                static::assertArrayHasKey('loggedin', $param);

                return $data;
            })
        ;

        $result = $this->controller->index($request, $response);

        static::assertSame(200, $result->getStatusCode());
        static::assertSame('tag.cloud', (string) $result->getBody());
        static::assertSame('ghi def - Tag cloud - Shaarli', $assignedVariables['pagetitle']);

        static::assertSame('ghi def', $assignedVariables['search_tags']);
        static::assertCount(1, $assignedVariables['tags']);

        static::assertArrayHasKey('abc', $assignedVariables['tags']);
        static::assertSame(3, $assignedVariables['tags']['abc']['count']);
        static::assertGreaterThan(0, $assignedVariables['tags']['abc']['size']);
        static::assertLessThan(5, $assignedVariables['tags']['abc']['size']);
    }

    public function testEmptyCloud(): void
    {
        $this->createValidContainerMockSet();

        $request = $this->createMock(Request::class);
        $request->expects(static::once())->method('getQueryParam')->with('searchtags')->willReturn(null);
        $response = new Response();

        // Save RainTPL assigned variables
        $assignedVariables = [];
        $this->assignTemplateVars($assignedVariables);

        $this->container->bookmarkService
            ->expects(static::once())
            ->method('bookmarksCountPerTag')
            ->with([], null)
            ->willReturnCallback(function (array $parameters, ?string $visibility): array {
                return [];
            })
        ;

        // Make sure that PluginManager hook is triggered
        $this->container->pluginManager
            ->expects(static::at(0))
            ->method('executeHooks')
            ->willReturnCallback(function (string $hook, array $data, array $param): array {
                static::assertSame('render_tagcloud', $hook);
                static::assertSame('', $data['search_tags']);
                static::assertCount(0, $data['tags']);

                static::assertArrayHasKey('loggedin', $param);

                return $data;
            })
        ;

        $result = $this->controller->index($request, $response);

        static::assertSame(200, $result->getStatusCode());
        static::assertSame('tag.cloud', (string) $result->getBody());
        static::assertSame('Tag cloud - Shaarli', $assignedVariables['pagetitle']);

        static::assertSame('', $assignedVariables['search_tags']);
        static::assertCount(0, $assignedVariables['tags']);
    }

    protected function createValidContainerMockSet(): void
    {
        $loginManager = $this->createMock(LoginManager::class);
        $this->container->loginManager = $loginManager;

        $sessionManager = $this->createMock(SessionManager::class);
        $this->container->sessionManager = $sessionManager;

        // Config
        $conf = $this->createMock(ConfigManager::class);
        $this->container->conf = $conf;

        $this->container->conf->method('get')->willReturnCallback(function (string $parameter, $default) {
            return $default;
        });

        // PageBuilder
        $pageBuilder = $this->createMock(PageBuilder::class);
        $pageBuilder
            ->method('render')
            ->willReturnCallback(function (string $template): string {
                return $template;
            })
        ;
        $this->container->pageBuilder = $pageBuilder;

        // Plugin Manager
        $pluginManager = $this->createMock(PluginManager::class);
        $this->container->pluginManager = $pluginManager;

        // BookmarkService
        $bookmarkService = $this->createMock(BookmarkServiceInterface::class);
        $this->container->bookmarkService = $bookmarkService;
    }

    protected function assignTemplateVars(array &$variables): void
    {
        $this->container->pageBuilder
            ->expects(static::atLeastOnce())
            ->method('assign')
            ->willReturnCallback(function ($key, $value) use (&$variables) {
                $variables[$key] = $value;

                return $this;
            })
        ;
    }
}