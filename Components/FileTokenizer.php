<?php

namespace NetcomMigrations\Components;

/**
 * Class FileTokenizer
 */
class FileTokenizer
{
    /**
     * @param string $filePath
     *
     * @return string
     */
    public function getClassFromFile($filePath): string
    {
        $fileContent = \file_get_contents($filePath);
        $namespace = '';
        $class = '';
        $readNamespace = false;
        $readClassName = false;

        foreach (\token_get_all($fileContent) as $token) {
            if (\is_array($token) && $token[0] === T_NAMESPACE) {
                $readNamespace = true;
            }

            if (\is_array($token) && $token[0] === T_CLASS) {
                $readClassName = true;
            }

            if ($readNamespace === true) {
                if (\is_array($token) && \in_array($token[0], [T_STRING, T_NS_SEPARATOR], true)) {
                    $namespace .= $token[1];
                } else {
                    if ($token === ';') {
                        $readNamespace = false;
                    }
                }
            }

            if ($readClassName === true && \is_array($token) && $token[0] === T_STRING) {
                $class = $token[1];
                break;
            }
        }

        return $namespace ? $namespace . '\\' . $class : $class;
    }
}
