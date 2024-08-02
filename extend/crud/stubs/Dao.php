<?php
declare(strict_types=1);
namespace crud\stubs;


use crud\enum\SearchEnum;

class Dao extends Make
{
    protected string $name = "dao";

    /**
     * @return string
     */
    protected function setBaseDir(): string
    {
        return 'app' . DS . 'adminapi' . DS . 'dao' . DS . 'crud';
    }

    /**
     * 执行替换
     * @param string $name
     * @param array $options
     * @return Dao
     */
    public function handle(string $name, array $options = []): static
    {
        $this->setSearchDaoPhpContent($options['searchField'] ?? []);
        return parent::handle($name, $options);
    }

    /**
     * 获取搜索dao的php代码
     * @param array $fields
     * @return Dao
     */
    protected function setSearchDaoPhpContent(array $fields): static
    {
        $templateContent = file_get_contents($this->getStub('search'));
        $contentSearchPhp = '';
        foreach ($fields as $item) {
            $tab2 = $this->tab(2);
            $contentStr = <<<CONTENT
->when(!empty(\$where['$item[field]']), function(\$query) use (\$where) {
$tab2    \$query->{%WHERE%}('$item[field]', '{%SEARCH%}', \$where['$item[field]']);
$tab2})
CONTENT;
            if (isset($item['search']) && $item['search']) {

                switch ($item['search']) {
                    case SearchEnum::SEARCH_TYPE_EQ->value:
                    case SearchEnum::SEARCH_TYPE_GTEQ->value:
                    case SearchEnum::SEARCH_TYPE_LTEQ->value:
                    case SearchEnum::SEARCH_TYPE_NEQ->value:
                        $contentSearchPhp .= str_replace([
                            '{%WHERE%}',
                            '{%SEARCH%}'
                        ], [
                            'where',
                            $item['search']
                        ], $contentStr);
                        break;
                    case SearchEnum::SEARCH_TYPE_LIKE->value:
                        $contentSearchPhp .= <<<CONTENT
->when(!empty(\$where['$item[field]']), function(\$query) use (\$where) {
$tab2    \$query->whereLike('$item[field]', '%'.\$where['$item[field]'].'%');
$tab2})
CONTENT;
                        break;
                    case SearchEnum::SEARCH_TYPE_BETWEEN->value:
                        $contentSearchPhp .= <<<CONTENT
->when(!empty(\$where['$item[field]']), function(\$query) use (\$where) {
$tab2    \$query->whereBetween('$item[field]', \$where['$item[field]']);
$tab2})
CONTENT;
                        break;
                }
            }
        }

        $this->value['CONTENT_PHP'] = str_replace(['{%CONTENT_SEARCH_PHP%}'], [$contentSearchPhp . ';'], $templateContent);

        return $this;
    }

    /**
     * 模板文件
     * @param string $type
     * @return string
     */
    protected function getStub(string $type = ''): string
    {
        $daoPath = __DIR__ . DS . 'stubs' . DS . 'dao' . DS;

        $stubs = [
            'dao' => $daoPath . 'crudDao.stub',
            'search' => $daoPath . 'search.stub',
        ];

        return $type ? $stubs[$type] : $stubs['dao'];
    }
}
