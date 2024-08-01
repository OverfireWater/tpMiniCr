<?php
declare(strict_types=1);
namespace crud\helper;

use think\App;
use think\helper\Str;

/**
 * 创建crud基类
 */
abstract class Make
{

    /**
     * 名称
     */
    protected string $name = '';

    /**
     * 文件类型
     */
    protected string $fileMime = 'php';

    /**
     * 文件全部路径
     */
    protected string $filePathName = '';

    /**
     * 文件基本路径
     */
    protected string $fileBasePath;

    /**
     * 文件内容
     */
    protected string $content = '';

    /**
     * 实际文件存放
     */
    protected string $pathname = '';

    /**
     * 命名空间路径
     */
    protected string $usePath = '';

    /**
     * 变量名称
     */
    protected array $var = [];

    /**
     * 内容
     */
    protected array $value = [];

    /**
     * 参数配置项
     */
    protected array $options = [];

    /**
     * 后台前端模板根路径
     */
    protected string $adminTemplatePath;

    /**
     * 默认保存路径
     */
    protected string $basePath;

    /**
     * 默认文件夹
     */
    protected string $baseDir;

    public function __construct(
       protected App $app
    )
    {
        $this->adminTemplatePath = self::adminTemplatePath();
        $this->basePath = $this->app->getRootPath();
        $this->baseDir = $this->setBaseDir();
        $this->var = $this->authDrawVar();
        $this->value = $this->drawValueKeys();
        $this->setDefaultValue();
    }

    /**
     * 设置默认路径
     * @param string $basePath
     * @return $this
     */
    public function setBasePath(string $basePath): static
    {
        if ($basePath) {
            $this->basePath = $basePath;
        }
        return $this;
    }

    /**
     * @return string
     */
    public static function adminTemplatePath(): string
    {
        return config('app.admin_template_path');
    }

    /**
     * 设置默认保存目录
     */
    protected function setBaseDir(): string
    {
        return 'crud';
    }

    /**
     * 获取保存文件的目录
     * @param string $path
     * @return string
     */
    protected function getBasePath(string $path = ''): string
    {
        //替换成本地路径格式
        $path = str_replace('/', DS, $path);
        $pathAttr = explode(DS, $path);
        $basePathAttr = explode(DS, $this->baseDir);
        //替换掉和基础目录相同的
        if (count($pathAttr) > 1) {
            $newsPath = array_merge(array_diff($basePathAttr, $pathAttr))[0] ?? '';
            if ($newsPath !== 'crud') {
                $path = $newsPath;
            } else {
                $this->baseDir = '';
            }
        }
        //多个斜杠的替换成一个
        $this->fileBasePath = str_replace(DS . DS, DS, $this->basePath . ($this->baseDir ? $this->baseDir . DS : '') . ($path ? $path . DS : ''));

        return $this->fileBasePath;
    }

    /**
     * 设置文件保存就路径名称
     * @param string $filePathName
     * @return $this
     */
    public function setFilePathName(string $filePathName = ''): static
    {
        if ($filePathName) $this->filePathName = $filePathName;
        return $this;
    }

    /**
     * 生成tab
     * @param int $num
     * @return string
     */
    public function tab(int $num = 1): string
    {
        return str_pad('', 4 * $num);
    }

    /**
     * 执行创建
     * @param string $name
     * @param array $options
     * @return Make
     */
    public function handle(string $name, array $options = []): static
    {
        $path = $options['path'] ?? '';
        [$nameData, $content] = $this->getStubContent($name);

        $this->value['NAME'] = $nameData;
        if (isset($this->value['NAME_CAMEL']) && !$this->value['NAME_CAMEL']) {
            $this->value['NAME_CAMEL'] = Str::studly($name);
        }
        if (isset($this->value['PATH'])) {
            $this->value['PATH'] = $this->getfolderPath($path);
        }
        if (isset($this->value['USE_PHP']) && !empty($options['usePath'])) {
            $this->value['USE_PHP'] = "use " . str_replace('/', '\\', $options['usePath']) . ";\n";
        }
        if (isset($this->value['MODEL_NAME']) && !$this->value['MODEL_NAME'] && !empty($options['modelName'])) {
            $this->value['MODEL_NAME'] = $options['modelName'];
        }

        $contentStr = str_replace($this->var, $this->value, $content);
        $filePath = $this->getFilePathName($path, $this->value['NAME_CAMEL']);

        $this->usePath = $this->baseDir . '\\' . $this->value['NAME_CAMEL'];
        $this->setPathname($filePath);
        $this->setContent($contentStr);

        return $this;
    }

    /**
     * 模板文件配置
     * @param string $type
     * @return mixed
     */
    abstract protected function getStub(string $type = ''): mixed;

    /**
     * 自动获取模板变量
     */
    protected function authDrawVar(): array
    {
        $content = file_get_contents($this->getStub());
        $pattern = '/\{%+[a-zA-Z0-9_-]+%}/';
        preg_match_all($pattern, $content, $var);
        $varData = $var[0] ?? [];
        return array_unique($varData);
    }

    /**
     * 提取value key
     * @return array
     */
    protected function drawValueKeys(): array
    {
        $data = [];
        foreach ($this->var as $value) {
            $data[str_replace(['{%', '%}'], '', $value)] = '';
        }
        return $data;
    }

    /**
     * 设置默认值
     * @return void
     */
    protected function setDefaultValue(): void
    {
        if (isset($this->value['YEAR'])) {
            $this->value['YEAR'] = date('Y');
        }
        if (isset($this->value['TIME'])) {
            $this->value['TIME'] = date('Y/m/d H:i:s');
        }
        if (isset($this->value['DATE'])) {
            $this->value['DATE'] = date('Y/m/d');
        }
    }

    /**
     * 提取模板文件
     * @param string $name
     * @param string $type
     * @return array
     */
    protected function getStubContent(string $name, string $type = ''): array
    {
        $stub = file_get_contents($this->getStub($type));

        $namespace = trim(implode('\\', array_slice(explode('\\', $name), 0, -1)), '\\');

        $class = str_replace($namespace . '\\', '', $name);

        return [$class, $stub];
    }

    /**
     * 获取文件路径
     * @param string $path
     * @param string $name
     * @return string
     */
    protected function getFilePathName(string $path, string $name): string
    {
        $path = ltrim(str_replace('\\', '/', $path), '/');

        return $this->getBasePath($path) . $name . ucwords($this->name) . '.' . $this->fileMime;
    }

    /**
     * @param string $path
     * @return string
     */
    protected function getFolderPath(string $path): string
    {
        $path = $path ?: $this->filePathName;
        $path = str_replace([$this->basePath, $this->baseDir], '', $path);
        $path = ltrim(str_replace('\\', '/', $path), '/');
        $pathArr = explode('/', $path);
        array_pop($pathArr);
        if ($pathArr) {
            return '\\' . implode('\\', $pathArr);
        } else {
            return '';
        }
    }

    /**
     * 获取保存文件路径
     * @param string $name
     * @return string
     */
    protected function getPathName(string $name): string
    {
        $name = str_replace('app\\', '', $name);

        return $this->app->getBasePath() . ltrim(str_replace('\\', '/', $name), '/') . '.php';
    }

    /**
     * 获取类名
     * @param string $name
     * @return string
     */
    protected function getClassName(string $name): string
    {
        if (str_contains($name, '\\')) {
            return $name;
        }

        if (strpos($name, '@')) {
            [$app, $name] = explode('@', $name);
        } else {
            $app = '';
        }

        if (str_contains($name, '/')) {
            $name = str_replace('/', '\\', $name);
        }

        return $this->getNamespace($app) . '\\' . $name;
    }

    /**
     * 获取命名空间名
     * @param string $app
     * @return string
     */
    protected function getNamespace(string $app): string
    {
        return 'app' . ($app ? '\\' . $app : '');
    }

    /**
     * 设置内容
     * @param string $content
     * @return array|string|string[]
     */
    protected function setContent(string $content)
    {
        $this->content = str_replace(' ', '', $content);
        return $this->content;
    }


    /**
     * @param string $pathname
     * @return $this
     */
    protected function setPathname(string $pathname): static
    {
        $this->pathname = $this->filePathName ?: $pathname;
        return $this;
    }

    /**
     * 获取值
     * @param string $key
     * @return mixed
     */
    public function getValue(string $key): mixed
    {
        return $this->value[$key] ?? '';
    }

    /**
     * 获取命名空间路径
     * @return string
     */
    public function getUsePath(): string
    {
        return $this->usePath;
    }

    /**
     * 获取内容
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->pathname;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'path' => $this->pathname,
            'content' => $this->content,
            'value' => $this->value,
            'var' => $this->var,
            'usePath' => $this->usePath,
        ];
    }

    public function __destruct()
    {
        $this->content = '';
        $this->pathname = '';
        $this->usePath = '';
    }
}
