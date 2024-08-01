<?php
declare(strict_types=1);
namespace crud\helper;

use exceptions\ApiException;

class Route extends Make
{
    protected string $name = 'route';

    /**
     * @return string
     */
    protected function setBaseDir(): string
    {
        return 'app' . DS . 'adminapi' . DS . 'route' . DS . 'crud';
    }

    /**
     * @param string $name
     * @param array $options
     * @return Route
     */
    public function handle(string $name, array $options = []): static
    {
        $path = $options['path'] ?? '';
        $route = $options['route'] ?? '';
        $controller = $options['controller'] ?? $name;
        $routePath = $options['routePath'] ?? '';
        $menus = $options['menus'] ?? '';
        if (!$route) {
            throw new ApiException(500045);
        }

        return $this->setRouteContent($route, $routePath, $controller, $menus)
            ->setRoute($name, $path);
    }

    /**
     * 设置路由模板内容
     * @param string $name
     * @param string $path
     * @return Route
     */
    protected function setRoute(string $name, string $path): static
    {
        $content = file_get_contents($this->getStub());

        $contentStr = str_replace($this->var, $this->value, $content);

        $filePath = $this->getFilePathName($path, strtolower($name));

        $this->setPathname($filePath);
        $this->setContent($contentStr);

        return $this;
    }

    /**
     * 设置路由页面内容
     * @param string $route
     * @param string $routePath
     * @param string $controller
     * @param string $menus
     * @return Route
     */
    protected function setRouteContent(string $route, string $routePath, string $controller, string $menus): static
    {
        $var = [
            '{%ROUTE%}',
            '{%CONTROLLER%}',
            '{%ROUTE_PATH%}',
            '{%MENUS%}',
        ];

        $value = [
            $route,
            $routePath,
            $controller ? ($routePath ? '.' : '') . Str::studly($controller) : '',
            $menus
        ];

        $routeContent = "";
        foreach (ActionEnum::ACTION_ALL as $item) {
            $routeContent .= file_get_contents($this->getStub($item)) . "\r\n";
        }

        $this->value['CONTENT_PHP'] = str_replace($var, $value, $routeContent);

        return $this;
    }

    /**
     * @param string $path
     * @param string $name
     * @return string
     */
    protected function getFilePathName(string $path, string $name): string
    {
        $path = ltrim(str_replace('\\', '/', $path), '/');

        return $this->getBasePath($path) . $name . '.' . $this->fileMime;
    }

    /**
     * 设置模板
     * @param string $type
     * @return string|array
     */
    protected function getStub(string $type = 'route'): string|array
    {
        $routePath = __DIR__ . DS . 'stubs' . DS . 'route' . DS;

        $stubs = [
            'index' => $routePath . 'index.stub',
            'create' => $routePath . 'create.stub',
            'save' => $routePath . 'save.stub',
            'edit' => $routePath . 'edit.stub',
            'update' => $routePath . 'update.stub',
            'status' => $routePath . 'status.stub',
            'delete' => $routePath . 'delete.stub',
            'route' => $routePath . 'route.stub',
            'read' => $routePath . 'read.stub',
        ];

        return $type ? $stubs[$type] : $stubs;
    }
}
