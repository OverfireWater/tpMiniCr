<?php
declare(strict_types=1);
namespace crud\stubs;

use exceptions\ApiException;
use think\App;
use think\helper\Str;

class ViewRouter extends Make
{
    protected string $name = 'router';

    protected string $fileMime = 'js';

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->basePath = $this->adminTemplatePath;
    }

    /**
     * @return string
     */
    protected function setBaseDir(): string
    {
        return 'router' . DS . 'modules' . DS . 'crud';
    }

    /**
     * @param string $name
     * @param array $options
     * @return ViewRouter
     */
    public function handle(string $name, array $options = []): static
    {
        $path = $options['path'] ?? '';
        [$nameData, $content] = $this->getStubContent($name);

        $menus = $options['menuName'] ?? $name;
        $route = $options['route'] ?? Str::snake($name);
        $pagePath = $options['pagePath'] ?? Str::camel($name);
        if (!$route) {
            throw new ApiException(500045);
        }

        $this->value['MENUS'] = $menus;
        $this->value['NAME'] = $nameData;
        $this->value['ROUTE'] = $route;
        $this->value['PAGE_PATH'] = $pagePath;
        if (isset($this->value['PATH'])) {
            $this->value['PATH'] = $this->getfolderPath($path);
        }

        $contentStr = str_replace($this->var, $this->value, $content);

        $filePath = $this->getFilePathName($path, Str::camel($name));

        $this->setContent($contentStr);
        $this->setPathname($filePath);
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
     * @param string $type
     * @return string
     */
    protected function getStub(string $type = ''): string
    {
        return __DIR__ . DS . 'stubs' . DS . 'view' . DS . 'router' . DS . 'modules' . DS . 'crud.stub';
    }
}
