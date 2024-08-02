<?php
declare(strict_types=1);

namespace services;

use crud\stubs\Make;

class FileService
{
    /**
     * 批量写入文件
     * @param array $make
     * @return bool
     */
    public static function batchMakeFiles(array $make): bool
    {

        $files = [];
        $dirNames = [];
        foreach ($make as $item) {
            if ($item instanceof Make) {
                $files[] = $item = $item->toArray();
            }
            try {
                $dirNames[] = $dirname = dirname($item['path']);
                if (!is_dir($dirname)) {
                    mkdir($dirname, 0755, true);
                }
            } catch (\Throwable $e) {
                if ($dirNames) {
                    foreach ($dirNames as $dirname) {
                        if (str_contains($dirname, app()->getRootPath() . 'backup')) {
                            rmdir($dirname);
                        }
                    }
                }
                throw new \RuntimeException($e->getMessage());
            }
        }
        $res = true;
        foreach ($files as $item) {
            $res = $res && file_put_contents($item['path'], $item['content'], LOCK_EX);
        }

        return $res;
    }
}
