<?php

namespace Ensi\LaravelOpenApiTesting;

use Illuminate\Support\Facades\ParallelTesting;
use Osteel\OpenApi\Testing\ValidatorBuilder;
use Osteel\OpenApi\Testing\ValidatorInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Throwable;

class CachedValidator
{
    protected static array $map = []; // shared across all test "oas doc path" => "validator"

    public static function fromYaml(string $path): ValidatorInterface
    {
        return self::getFromCache($path, fn (string $p) => ValidatorBuilder::fromYaml($p)->getValidator());
    }

    public static function fromJson(string $path): ValidatorInterface
    {
        return self::getFromCache($path, fn (string $p) => ValidatorBuilder::fromJson($p)->getValidator());
    }

    public static function getCacheFilePath(string $path): string
    {
        return sys_get_temp_dir() .
            DIRECTORY_SEPARATOR .
            'ensi_api_validator_cache_' .
            ParallelTesting::token() .
            str_replace(DIRECTORY_SEPARATOR, '_', $path);
    }

    protected static function getFromCache(string $path, callable $fn): ValidatorInterface
    {
        if (!isset(self::$map[$path])) {
            $validator = null;

            try {
                $hash = static::getMd5DirHash(pathinfo($path, PATHINFO_DIRNAME));
                $cacheFile = self::getCacheFilePath($path);

                if (file_exists($cacheFile)) {
                    $info = unserialize(file_get_contents($cacheFile));
                    if ($info['hash'] == $hash) {
                        $validator = $info['validator'];
                    }
                }

                if (!$validator) {
                    $validator = $fn($path);
                    file_put_contents($cacheFile, serialize([
                        'hash' => $hash,
                        'validator' => $validator,
                    ]));
                }
            } catch (Throwable) {
                $validator = $fn($path);
            }

            self::$map[$path] = $validator;
        }

        return self::$map[$path];
    }

    protected static function getMd5DirHash(string $dir): string
    {
        $array = [];
        $dir = realpath($dir);
        $fileSPLObjects =  new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir), RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($fileSPLObjects as $fullFileName => $fileSPLObject) {
            if ($fileSPLObject->isFile()) {
                $array[] = $fullFileName;
            }
        }
        $md5 = array_map('md5_file', $array);

        return md5(implode('', $md5));
    }
}
