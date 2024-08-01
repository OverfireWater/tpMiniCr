<?php
declare(strict_types=1);
namespace crud\helper;

class Model extends Make
{
    protected string $name = "model";

    /**
     * @return string
     */
    protected function setBaseDir(): string
    {
        return 'app' . DS . 'model' . DS . 'crud';
    }

    /**
     * @param string $name
     * @param array $options
     * @return Model
     */
    public function handle(string $name, array $options = []): static
    {
        $this->options = $options;

        $field = $options['fromField'] ?? [];
        $hasOneFields = $options['hasOneField'] ?? [];

        $this->value['KEY'] = $options['key'] ?? 'id';
        $this->value['MODEL_NAME'] = $options['modelName'] ?? $name;

        $this->setUseDeleteContent()
            ->setAttrFnContent($field)
            ->setHasOneContent($hasOneFields);

        return parent::handle($name, $options);
    }

    /**
     * 设置命令空间
     * @return $this
     */
    protected function setUseDeleteContent(): static
    {
        if (isset($this->options['softDelete']) && $this->options['softDelete']) {
            $this->value['USE_PHP'] = "use think\model\concern\SoftDelete;\n";
            $this->value['CONTENT_PHP'] = $this->tab() . "use SoftDelete;\n";
        }

        return $this;
    }

    /**
     * 设置获取字段方法内容
     * @param array $field
     * @return $this
     */
    protected function setAttrFnContent(array $field): static
    {
        $attrFnContent = [];
        foreach ($field as $item) {
            if (in_array($item['type'], [FormTypeEnum::RADIO, FormTypeEnum::SELECT]) && !empty($item['option'])) {
                $attrFnContent[] = $this->getAttrFnContent($item['field'], $item['name'], $item['option']);
            } else if (FormTypeEnum::CHECKBOX == $item['type']) {
                $attrFnContent[] = $this->getAttrFnCheckboxContent($item['field'], $item['name'], $item['option']);
            } else if (in_array($item['type'], [FormTypeEnum::FRAME_IMAGES, FormTypeEnum::DATE_TIME_RANGE])) {
                $attrFnContent[] = $this->getAttrJoinFnContent($item['field'], $item['name']);
            }
        }
        if ($attrFnContent) {
            $this->value['ATTR_PHP'] = "\n" . implode("\n", $attrFnContent);
        }
        return $this;
    }

    /**
     * 设置hasOne方法内容
     * @param array $hasOneFields
     * @return $this
     */
    protected function setHasOneContent(array $hasOneFields): static
    {
        $hasOneContent = $this->getHasPhpContent($hasOneFields);
        if ($hasOneContent) {
            $this->value['ATTR_PHP'] .= "\n" . $hasOneContent;
        }

        return $this;
    }

    /**
     * 转JSON数据获取器
     * @param string $key
     * @param string $name
     * @return array|false|string|string[]
     */
    public function getAttrJoinFnContent(string $key, string $name): array|bool|string
    {
        $attrFnStub = file_get_contents($this->getStub('attr'));

        $var = [
            '{%NAME%}',
            '{%CONTENT_PHP%}'
        ];

        $tab = $this->tab(2);
        $content = <<<CONTENT
$tab\$value = \$value ? json_decode(\$value, true) : [];
{$tab}return \$value;
CONTENT;

        $value = [
            $name,
            $content
        ];

        return str_replace($var, $value, $attrFnStub);
    }

    /**
     * Checkbox代码获取
     * @param string $key
     * @param string $comment
     * @param array $options
     * @return array|false|string|string[]
     */
    protected function getAttrFnCheckboxContent(string $key, string $comment, array $options): array|bool|string
    {
        $optionsStr = '';
        $tab2 = $this->tab(2);
        $tab3 = $this->tab(3);
        $tab4 = $this->tab(4);
        foreach ($options as $i => $option) {
            if (0 == $i) {
                $n = '';
            } else {
                $n = "\n";
            }
            $optionsStr .= <<<CONTENT
{$n}{$tab3}[
$tab4'value' => '$option[value]',
$tab4'label' => '$option[label]',
{$tab3}],
CONTENT;
        }
        $content = <<<CONTENT
{$tab2}\$options = [
$optionsStr
{$tab2}];

{$tab2}\$var = [];
{$tab2}\$value = \$value ? json_decode(\$value, true) : [];
{$tab2}foreach(\$options as \$item) {
{$tab2}   if (is_array(\$value) && in_array(\$item['value'], \$value)) {
{$tab2}     \$var[] = \$item['label'];
{$tab2}   }   
{$tab2}}

{$tab2}return implode(',', \$var);
CONTENT;


        $var = [
            '{%FIELD%}',
            '{%DATE%}',
            '{%NAME%}',
            '{%CONTENT_PHP%}'
        ];

        $value = [
            Str::studly($key . $this->attrPrefix),
            date('Y-m-d'),
            $comment,
            $content
        ];

        $attrFnStub = file_get_contents($this->getStub('attr'));
        return str_replace($var, $value, $attrFnStub);
    }

    /**
     * 获取获取器的方法内容
     * @param string $key
     * @param string $comment
     * @param array $options
     * @return array|false|string|string[]
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/5/11
     */
    protected function getAttrFnContent(string $key, string $comment, array $options): array|bool|string
    {
        $attrFnStub = file_get_contents($this->getStub('attr'));

        $var = [
            '{%FIELD%}',
            '{%DATE%}',
            '{%NAME%}',
            '{%CONTENT_PHP%}'
        ];

        $value = [
            Str::studly($key . $this->attrPrefix),
            date('Y-m-d'),
            $comment,
            $this->getSwitchAndSelectPhpContent($options)
        ];

        return str_replace($var, $value, $attrFnStub);
    }

    /**
     * 获取开关和下拉框获取器内容
     * @param array $options
     * @return string
     * @author 等风来
     * @email 136327134@qq.com
     * @date 2023/5/11
     */
    protected function getSwitchAndSelectPhpContent(array $options): string
    {
        if (!$options) {
            return '';
        }
        $case = [];
        foreach ($options as $option) {
            $case[] = $this->tab(3) . "case " . $option['value'] . ":\n" . $this->tab(4) . "\$attr = '$option[label]';\n" . $this->tab(4) . "break;";
        }
        $caseContent = implode("\n", $case);
        $tab2 = $this->tab(2);
        return <<<CONTENT
{$tab2}\$attr = '';
{$tab2}switch ((int)\$value){
{$caseContent}
{$tab2}}
{$tab2}return \$attr;
CONTENT;
    }

    /**
     * 获取关联数据模板
     * @param array $fields
     * @return string
     */
    protected function getHasPhpContent(array $fields): string
    {
        $hasOneStub = file_get_contents($this->getStub('hasOne'));
        $date = date('Y/m/d');

        $content = '';
        foreach ($fields as $item) {
            if (isset($item['hasOne']) && $item['hasOne']) {
                [$modelName, $foreignKey] = is_array($item['hasOne']) ? $item['hasOne'] : [$item['hasOne'], 'id'];
                $modelName = self::getHasOneNamespace($modelName);
                if (!$modelName) {
                    continue;
                }
                $content .= "\n" . str_replace(
                        [
                            '{%NAME%}',
                            '{%DATE%}',
                            '{%FIELD%}',
                            '{%CLASS%}',
                            '{%FOREIGN_KEY%}',
                            '{%LOCAL_KEY%}'
                        ],
                        [
                            $item['name'],
                            $date,
                            Str::camel($item['field']),
                            $modelName,
                            $foreignKey,
                            $item['field']
                        ],
                        $hasOneStub
                    );
            }
        }

        return $content;
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
     * 模板文件
     * @param string $type
     * @return string
     */
    protected function getStub(string $type = 'model'): string
    {
        $routePath = __DIR__ . DS . 'stubs' . DS . 'model' . DS;

        $stubs = [
            'model' => $routePath . 'crudModel.stub',
            'attr' => $routePath . 'getattr.stub',
            'hasOne' => $routePath . 'hasOne.stub',
            'hasMany' => $routePath . 'hasMany.stub',
        ];

        return $type ? $stubs[$type] : $stubs['model'];
    }

    /**
     * 获取模型命令空间
     * @param string $modelName
     * @return string
     */
    public static function getHasOneNamespace(string $modelName): string
    {
        $dir = root_path('app' . DS . 'model');
        $res = self::searchFiles($dir, '$name = \'' . $modelName . '\'');
        $namespace = '';
        foreach ($res as $item) {
            $namespace = self::getFileNamespace($item);
        }

        return $namespace ? "\\" . $namespace . '\\' . Str::studly($modelName) . "::class" : '';
    }

    /**
     * 搜索文件内容包含某个字符串，返回包含的文件路径
     * @param string $dir
     * @param string $searchString
     * @return array
     */
    public static function searchFiles(string $dir, string $searchString): array
    {
        $foundFiles = [];

        $files = scandir($dir);

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $path = $dir . '/' . $file;

            if (is_dir($path)) {
                $foundFiles = array_merge($foundFiles, self::searchFiles($path, $searchString));
            } else {
                $content = file_get_contents($path);
                if (str_contains($content, $searchString)) {
                    $foundFiles[] = $path;
                }
            }
        }

        return $foundFiles;
    }

    /**
     * 获取文件的命名空间
     * @param string $filePath
     * @return string
     */
    public static function getFileNamespace(string $filePath): string
    {
        $content = file_get_contents($filePath);
        $tokens = token_get_all($content);
        $namespace = '';

        foreach ($tokens as $token) {
            if ($token[0] === T_NAMESPACE) {
                $namespace = '';
            } elseif ($namespace !== null && in_array($token[0], [T_STRING, T_NS_SEPARATOR])) {
                $namespace .= $token[1];
            } elseif ($token === ';') {
                break;
            }
        }

        return $namespace;
    }
}
