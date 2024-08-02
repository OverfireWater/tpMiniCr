<?php
declare(strict_types=1);
namespace crud\stubs;

use crud\enum\ActionEnum;
use crud\enum\FormTypeEnum;
use think\helper\Str;

class Controller extends Make
{

    protected string $name = 'controller';

    /**
     * @return string
     */
    protected function setBaseDir(): string
    {
        return 'app' . DS . 'adminapi' . DS . 'controller' . DS . 'crud';
    }

    /**
     * @param string $name
     * @param array $options
     * @return Controller
     */
    public function handle(string $name, array $options = []): static
    {
        $this->options = $options;

        $path = $options['path'] ?? '';
        $field = $options['field'] ?? [];
        $hasOneFields = $options['hasOneField'] ?? [];

        $this->value['NAME_CAMEL'] = Str::studly($name);
        $this->value['PATH'] = $this->getfolderPath($path);

        return $this->setUseContent()
            ->setControllerContent($field, $options['searchField'] ?? [], $name, $options['columnField'] ?? [], $hasOneFields)
            ->setController($name, $path);
    }

    /**
     * 设置控制器内容
     * @param string $name
     * @param string $path
     * @return Controller
     */
    protected function setController(string $name, string $path): static
    {
        [$className, $contentController] = $this->getStubContent($name, 'controller');

        $this->value['NAME'] = $className;

        $contentStr = str_replace($this->var, $this->value, $contentController);
        $filePath = $this->getFilePathName($path, $this->value['NAME_CAMEL']);
        $this->usePath = $this->value['PATH'];

        $this->setPathname($filePath);
        $this->setContent($contentStr);

        return $this;
    }

    /**
     * 设置use内容
     * @return $this
     */
    protected function setUseContent(): static
    {
        $this->value['USE_PHP'] = "use " . str_replace('/', '\\', $this->options['usePath']) . "Services;\n";
        return $this;
    }

    /**
     * 设置控制器内容
     * @param array $field
     * @param array $searchField
     * @param string $name
     * @param array $columnField
     * @param array $hasOneFields
     * @return $this
     */
    protected function setControllerContent(array $field, array $searchField, string $name, array $columnField, array $hasOneFields): static
    {
        $var = [
            '{%VALIDATE_NAME%}',
            '{%FIELD_PHP%}',
            '{%FIELD%}',
            '{%WITH%}',
            '{%OTHER_PHP%}',
            '{%FIELD_ALL_PHP%}',
            '{%FIELD_SEARCH_PHP%}'
        ];

        $replace = [
            $this->options['validateName'] ?? '',
            $this->getSearchFieldContent($field),
            $this->getSearchListFieldContent($columnField),
            $this->getSearchListWithContent($hasOneFields),
            $this->getSearchListOtherContent($columnField),
            $this->getStatusUpdateContent($columnField),
            $this->getSearchPhpContent($searchField)
        ];

        $this->value['CONTENT_PHP'] = str_replace($var, $replace, $this->getStubControllerContent($name));

        return $this;
    }

    /**
     * 获取搜索字段内容
     * @param array $field
     * @return string
     */
    protected function getSearchFieldContent(array $field): string
    {
        $fieldStr = '';
        foreach ($field as $k) {
            $fieldStr .= $this->tab(3) . "['$k', ''],\n";
        }

        return $fieldStr;
    }

    /**
     * 提取控制器模板内容
     * @param string $name
     * @return string
     */
    protected function getStubControllerContent(string $name): string
    {
        $contentPhp = '';
        foreach (ActionEnum::cases() as $item) {
            [, $stub] = $this->getStubContent($name, $item->value);
            $contentPhp .= $stub . "\r\n";
        }

        return $contentPhp;
    }

    /**
     * 设置搜索字段展示
     * @param array $columnField
     * @return string
     */
    protected function getSearchListFieldContent(array $columnField): string
    {
        $select = [];
        foreach ($columnField as $item) {
            //处理查询字段
            if (in_array($item['type'], [
                FormTypeEnum::DATE_TIME_RANGE->value,
                FormTypeEnum::FRAME_IMAGES->value
            ])) {
                $select[] = '`' . $item['field'] . '` as ' . $item['field'] . $this->attrPrefix;
            }
        }
        unset($item);

        return $select ? '\'*\',\'' . implode('\',\'', $select) . '\'' : '\'*\'';
    }

    /**
     * 设置搜索关联内容
     * @param array $hasOneFields
     * @return string
     */
    protected function getSearchListWithContent(array $hasOneFields): string
    {
        $with = [];
        foreach ($hasOneFields as $item) {
            if (isset($item['hasOne'])) {
                [$modelName,] = is_array($item['hasOne']) ? $item['hasOne'] : [$item['hasOne'], 'id'];
                $modelName = Model::getHasOneNamespace($modelName);
                if (!$modelName) {
                    continue;
                }
                $with[] = "'" . Str::camel($item['field']) . 'HasOne' . "'";
            }
        }
        unset($item);

        return $with ? implode(',', $with) : '[]';
    }

    /**
     * 获取可以修改的字段内容
     * @param array $columnField
     * @return string
     */
    public function getStatusUpdateContent(array $columnField): string
    {
        $fieldAll = [];
        foreach ($columnField as $item) {
            if ($item['type'] == FormTypeEnum::SWITCH->value) {
                $fieldAll[] = $item['field'];
            }
        }
        return $fieldAll ? "['" . implode("','", $fieldAll) . "']" : '[]';
    }

    /**
     * 设置搜索其他内容
     * @param array $columnField
     * @return string
     */
    protected function getSearchListOtherContent(array $columnField): string
    {
        $otherContent = '';

        foreach ($columnField as $item) {
            //处理查询字段
            if (in_array($item['type'], [FormTypeEnum::FRAME_IMAGES->value, FormTypeEnum::DATE_TIME_RANGE->value])) {
                if (!$otherContent) {
                    $otherContent .= "\n";
                }
                $otherContent .= $this->tab(2) . '$data[\'' . $item['field'] . '\'] = json_encode($data[\'' . $item['field'] . "']);\n";
            }
        }

        return $otherContent;
    }

    /**
     * 获取控制器中搜索内容
     * @param array $fields
     * @return string
     */
    protected function getSearchPhpContent(array $fields): string
    {
        $fieldStr = '';
        foreach ($fields as $i => $item) {
            if (!empty($item['search'])) {
                $fieldStr .= $this->tab(3) . "['$item[field]', '']," . ((count($fields) - 1) == $i ? "" : "\n");
            }
        }

        return $fieldStr;
    }

    /**
     * 返回模板路径
     * @param string $type
     * @return string|string[]
     */
    protected function getStub(string $type = 'controller'): string|array
    {
        $controllerPath = __DIR__ . DS . 'stubs' . DS . 'controller' . DS;

        $stubs = [
            'index' => $controllerPath . 'index.stub',
            'create' => $controllerPath . 'create.stub',
            'save' => $controllerPath . 'save.stub',
            'status' => $controllerPath . 'status.stub',
            'edit' => $controllerPath . 'edit.stub',
            'update' => $controllerPath . 'update.stub',
            'delete' => $controllerPath . 'delete.stub',
            'read' => $controllerPath . 'read.stub',
            'controller' => $controllerPath . 'crudController.stub',
        ];

        return $type ? $stubs[$type] : $stubs;
    }

    /**
     * @param string $path
     * @param string $name
     * @return string
     */
    protected function getFilePathName(string $path, string $name): string
    {
        $path = str_replace(['app\\', 'app/'], '', $path);

        $path = ltrim(str_replace('\\', '/', $path), '/');

        return $this->getBasePath($path) . $name . '.' . $this->fileMime;
    }
}
