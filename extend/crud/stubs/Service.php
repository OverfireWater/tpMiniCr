<?php
declare(strict_types=1);
namespace crud\stubs;

use crud\enum\FormTypeEnum;
use crud\enum\ServiceActionEnum;
use think\helper\Str;

class Service extends Make
{
    protected string $name = "services";

    /**
     * @return string
     */
    protected function setBaseDir(): string
    {
        return 'app' . DS . 'adminapi' . DS . 'services' . DS . 'crud';
    }

    /**
     * @param string $name
     * @param array $options
     * @return Service
     */
    public function handle(string $name, array $options = []): static
    {
        $path = $options['path'] ?? '';
        $field = $options['field'] ?? [];
        $columnField = $options['columnField'] ?? [];
        $hasOneFields = $options['hasOneField'] ?? [];

        $this->value['USE_PHP'] = $this->getDaoClassName($name, $path);
        $this->value['NAME_CAMEL'] = Str::studly($name);
        $this->value['PATH'] = $this->getfolderPath($path);

        return $this->setServiceContent($field, $name, $columnField, $hasOneFields, $options)
            ->setService($name, $path);
    }

    /**
     * @param string $name
     * @param string $path
     * @return $this
     */
    protected function setService(string $name, string $path): static
    {
        //生成service
        [$className, $content] = $this->getStubContent($name, $this->name);
        $this->value['NAME'] = $className;

        $contentStr = str_replace($this->var, $this->value, $content);

        $filePath = $this->getFilePathName($path, $this->value['NAME_CAMEL']);
        $this->usePath = $this->baseDir . '\\' . $this->value['NAME_CAMEL'];

        $this->setContent($contentStr);
        $this->setPathname($filePath);

        return $this;
    }

    /**
     * 获取请求方法
     * @param string $name
     * @return string
     */
    protected function getActionContent(string $name): string
    {
        $contentAction = '';
        foreach (ServiceActionEnum::cases() as $item) {
            [, $stub] = $this->getStubContent($name, $item->value);
            $contentAction .= $stub . "\n";
        }

        return $contentAction;
    }

    /**
     * 获取列表展示字段
     * @param array $columnField
     * @param array $options
     * @return string
     */
    protected function getSelectFieldsContent(array $columnField, array $options): string
    {
        $select = [];
        foreach ($columnField as $item) {
            //处理查询字段
            if (in_array($item['type'], [
                FormTypeEnum::FRAME_IMAGES->value,
                FormTypeEnum::DATE_TIME_RANGE->value,
            ])) {
                $select[] = '`' . $item['field'] . '` as ' . $item['field'] . $this->attrPrefix;
            } else {
                $select[] = $item['field'];
            }
        }

        if (!empty($options['key'])) {
            $select[] = $options['key'];
        }
        return implode(',', $select);
    }

    /**
     * @param array $field
     * @param string $name
     * @param array $columnField
     * @param array $hasOneFields
     * @param array $options
     * @return Service
     */
    protected function setServiceContent(array $field, string $name, array $columnField, array $hasOneFields, array $options = []): static
    {
        //生成form表单
        $var = [
            '{%KEY%}',
            '{%ROUTE%}',
            '{%FORM_PHP%}',
            '{%FIELD%}',
            '{%WITH%}'
        ];

        $value = [
            $options['key'] ?? 'id',
            Str::snake($options['route'] ?? $name),
            $this->getFormContent($field),
            $this->getSelectFieldsContent($columnField, $options),
            $this->getWithFieldsContent($hasOneFields)
        ];

        //替换模板中的变量
        $this->value['CONTENT_PHP'] = str_replace($var, $value, $this->getActionContent($name));

        return $this;
    }

    /**
     * 获取表单创建内容
     * @param array $field
     * @return string
     */
    protected function getFormContent(array $field): string
    {
        $this->value['USE_PHP'] .= "\n" . 'use services\FormBuilder;';

        $from = [];
        foreach ($field as $item) {

            if ($item['type'] === FormTypeEnum::FRAME_IMAGES->value) {
                $fieldPre = $item['field'] . $this->attrPrefix;
            } else {
                $fieldPre = $item['field'];
            }

            //处理表单信息
            switch ($item['type']) {
                case FormTypeEnum::FRAME_IMAGE_ONE->value:
                    $from[] = $this->tab(2) . $this->getFrameImageOnePhpContent($item['field'], $item['name']) . ';';
                    break;
                case FormTypeEnum::FRAME_IMAGES->value:
                    $from[] = $this->tab(2) . $this->getFrameImagesPhpContent($item['field'], $item['name'], $fieldPre) . ';';
                    break;
                case FormTypeEnum::DATE_TIME_RANGE->value:
                    $tab = $this->tab(2);
                    $tab3 = $this->tab(3);
                    $from[] = <<<CONTENT
{$tab}if (isset(\$info['$fieldPre'])) {
{$tab3}\$time = is_array(\$info['$fieldPre']) ? \$info['$fieldPre'] : json_decode(\$info['$fieldPre'], true);
{$tab}} else {
{$tab3}\$time = ['', ''];
{$tab}}
{$tab}\$statTime = \$time[0] ?? '';
{$tab}\$endTime =  \$time[1] ?? '';
CONTENT;
                    $from[] = $this->tab(2) . '$rule[] = FormBuilder::' . FormTypeEnum::DATE_TIME_RANGE->value . '("' . $item['field'] . '", "' . $item['name'] . '", $statTime, $endTime);';
                    break;
                default:
                    $valueContent = "''";
                    $input = '$info["' . $item['field'] . '"] ?? ';
                    if (FormTypeEnum::SWITCH->value === $item['type']) {
                        $input = "isset(\$info['" . $item['field'] . "']) ? (string)\$info['" . $item['field'] . "'] : ''";
                    } else {
                        $input = $input . $valueContent;
                    }
                    $from[] = $this->tab(2) . '$rule[] = FormBuilder::' . $item['type'] . '("' . $item['field'] . '", "' . $item['name'] . '",  ' . $input . ')' . $this->getOptionContent(in_array($item['type'], ['radio', 'select', 'checkbox']), $item['option'] ?? []) . ';';
                    break;
            }
        }

        return $from ? implode("\n", $from) : '';
    }

    /**
     * 获取关联查询内容
     * @param array $hasOneFields
     * @return string
     */
    protected function getWithFieldsContent(array $hasOneFields): string
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

        return $with ? '[' . implode(',', $with) . ']' : '[]';
    }

    /**
     * 获取选项内容
     * @param bool $isOption
     * @param array $option
     * @return string
     */
    protected function getOptionContent(bool $isOption, array $option = []): string
    {
        if (!$isOption) {
            return '';
        }

        $php = '';
        if ($option) {
            $attOption = [];
            foreach ($option as $item) {
                $value = (int)$item['value'];
                $attOption[] = $this->tab(3) . "['value'=>{$value}, 'label'=>'{$item['label']}'],";
            }

            $strOption = implode("\n", $attOption);
            $php = "->options([\n" . $strOption . "\n" . $this->tab(2) . "])";
        }

        return $php;
    }

    /**
     * 单图获取formphp内容
     * @param string $field
     * @param string $name
     * @param bool $required
     * @param string $icon
     * @param string $width
     * @param string $height
     * @return string
     */
    protected function getFrameImageOnePhpContent(string $field, string $name, bool $required = false, string $icon = 'el-icon-picture-outline', string $width = '950px', string $height = '560px'): string
    {
        $name = addslashes($name);
        $requiredText = $required ? '->required()' : '';
        return <<<CONTENT
\$rule[] = FormBuilder::frameImage('$field', '$name', url(config('app.admin_prefix', 'admin') . '/widget.images/index', ['fodder' => '$field']), \$info['$field'] ?? '')->icon('$icon')->width('$width')->height('$height')->Props(['footer' => false])$requiredText
CONTENT;
    }

    /**
     * 多图获取formphp内容
     * @param string $field
     * @param string $name
     * @param bool $required
     * @param string $icon
     * @param int $maxLength
     * @param string $width
     * @param string $height
     * @return string
     */
    protected function getFrameImagesPhpContent(string $field, string $name, bool $required = false, string $icon = 'el-icon-picture-outline', int $maxLength = 10, string $width = '950px', string $height = '560px'): string
    {
        $name = addslashes($name);
        $requiredText = $required ? '->required()' : '';
        $tab = $this->tab(2);
        $tab3 = $this->tab(3);
        return <<<CONTENT
if (isset(\$info['$field'])) {
{$tab3}\$pics = is_array(\$info['$field']) ? \$info['$field'] : json_decode(\$info['$field'], true);
{$tab}} else {
{$tab3}\$pics = [];
{$tab}}
{$tab}\$pics = is_array(\$pics) ? \$pics : [];
{$tab}\$rule[] = FormBuilder::frameImages('$field', '$name', url(config('app.admin_prefix', 'admin') . '/widget.images/index', ['fodder' => '$field', 'type' => 'many', 'maxLength' => $maxLength]), \$pics)->maxLength($maxLength)->icon('$icon')->width('$width')->height('$height')->Props(['footer' => false])$requiredText
CONTENT;
    }

    /**
     * @param string $name
     * @param string $path
     * @return string
     */
    protected function getDaoClassName(string $name, string $path): string
    {
        $path = str_replace(['app\\services', 'app/services'], '', $path);
        $path = ltrim(str_replace('\\', '/', $path), '/');
        return 'use app\dao\crud\\' . ($path ? $path . '\\' : '') . Str::studly($name) . 'Dao;';
    }


    /**
     * @param string $type
     * @return string|string[]
     */
    protected function getStub(string $type = 'services'): string|array
    {
        $servicePath = __DIR__ . DS . 'stubs' . DS . 'service' . DS;

        $stubs = [
            'index' => $servicePath . 'crudListIndex.stub',
            'form' => $servicePath . 'getCrudForm.stub',
            'save' => $servicePath . 'crudSave.stub',
            'update' => $servicePath . 'crudUpdate.stub',
            'services' => $servicePath . 'crudService.stub',
        ];

        return $type ? $stubs[$type] : $stubs;
    }
}
