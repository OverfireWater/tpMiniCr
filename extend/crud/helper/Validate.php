<?php
declare(strict_types=1);
namespace crud\helper;

class Validate extends Make
{
    protected string $name = 'validate';

    /**
     * @return string
     */
    protected function setBaseDir(): string
    {
        return 'app' . DS . 'adminapi' . DS . 'validate' . DS . 'crud';
    }

    /**
     * @param string $name
     * @param array $options
     * @return Validate
     */
    public function handle(string $name, array $options = []): static
    {
        $this->value['MODEl_NAME'] = $options['modelName'] ?? $name;

        $this->setRuleContent($options['field']);

        return parent::handle($name, $options);
    }

    /**
     * 设置规则内容
     * @param array $field
     * @return Validate
     */
    protected function setRuleContent(array $field): static
    {
        $content = [];
        $message = [];
        foreach ($field as $item) {
            $item['name'] = addslashes($item['name']);
            if ($item['required']) {
                $content[] = $this->tab(2) . '\'' . $item['field'] . '\'=> \'require\',';
                $message[] = $this->tab(2) . '\'' . $item['field'] . '.require\'=> \'' . $item['name'] . '必须填写\',';
            }
        }

        $this->value['RULE_PHP'] = implode("\n", $content);
        $this->value['MESSAGE_PHP'] = implode("\n", $message);
        return $this;
    }

    /**
     * 模板文件配置
     * @param string $type
     * @return string
     */
    protected function getStub(string $type = ''): string
    {
        return __DIR__ . DS . 'stubs' . DS . 'validate' . DS . 'crudValidate.stub';
    }
}
