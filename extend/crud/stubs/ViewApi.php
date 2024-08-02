<?php
declare(strict_types=1);
namespace crud\stubs;

use crud\enum\ActionEnum;
use think\helper\Str;
use exceptions\ApiException;
use think\App;

class ViewApi extends Make
{
    protected string $name = 'api';

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
        return 'api' . DS . 'crud';
    }

    /**
     * @param string $name
     * @param array $options
     * @return ViewApi
     */
    public function handle(string $name, array $options = []): static
    {
        $path = $options['path'] ?? '';
        $route = $options['route'] ?? '';
        if (!$route) {
            throw new ApiException(500045);
        }

        return $this->setJsContent($name, $route)
            ->setApi($name, $path);
    }

    /**
     * 设置页面JS内容
     * @param string $name
     * @param string $route
     * @return $this
     */
    protected function setJsContent(string $name, string $route): static
    {
        $contentJs = '';

        foreach (ActionEnum::cases() as $item) {
            $contentJs .= file_get_contents($this->getStub($item->value)) . "\n";
        }

        $var = [
            '{%ROUTE%}',
            '{%NAME_CAMEL%}',
            '{%NAME_STUDLY%}',
        ];

        $value = [
            $route,
            Str::camel($name),
            Str::studly($name),
        ];

        $contentJs = str_replace($var, $value, $contentJs);


        $this->value['CONTENT_JS'] = $contentJs;

        return $this;
    }

    /**
     * 设置页面api内容
     * @param string $name
     * @param string $path
     * @return $this
     */
    protected function setApi(string $name, string $path): static
    {
        //生成api
        [, $content] = $this->getStubContent($name, $this->name);

        $contentStr = str_replace($this->var, $this->value, $content);
        $filePath = $this->getFilePathName($path, Str::camel($name));

        $this->setPathname($filePath);
        $this->setContent($contentStr);

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
     * 模板文件配置
     * @param string $type
     * @return string|array
     */
    protected function getStub(string $type = 'api'): string|array
    {
        $servicePath = __DIR__ . DS . 'stubs' . DS . 'view' . DS . 'api' . DS;

        $stubs = [
            'index' => $servicePath . 'getCrudListApi.stub',
            'create' => $servicePath . 'getCrudCreateApi.stub',
            'save' => $servicePath . 'crudSaveApi.stub',
            'status' => $servicePath . 'crudStatusApi.stub',
            'edit' => $servicePath . 'getCrudEditApi.stub',
            'read' => $servicePath . 'getCrudReadApi.stub',
            'delete' => $servicePath . 'crudDeleteApi.stub',
            'update' => $servicePath . 'crudUpdateApi.stub',
            'api' => $servicePath . 'crud.stub',
        ];

        return $type ? $stubs[$type] : $stubs;
    }

}
