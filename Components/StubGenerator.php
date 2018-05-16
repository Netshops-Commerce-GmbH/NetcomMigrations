<?php

namespace NetcomMigrations\Components;

use RuntimeException;

/**
 * Class StubGenerator
 */
class StubGenerator
{
    /**
     * Loads the contents of a stub file, replaces all given replacements and writes the file to the target path.
     *
     * @param string $stubPath
     * @param string $targetPath
     * @param array  $replacements
     *
     * @return string Path where the new file was created.
     *
     * @throws RuntimeException
     */
    public function generate(string $stubPath, string $targetPath, array $replacements) : string
    {
        if (\file_exists($targetPath)) {
            throw new RuntimeException(\sprintf('Cannot generate file. Target "%s" already exists.', $targetPath));
        }

        $contents = \file_get_contents($stubPath);

        foreach ($replacements as $tag => $replacement) {
            $contents = \str_replace($tag, $replacement, $contents);
        }

        $path = \pathinfo($targetPath, PATHINFO_DIRNAME);

        // Create target directory, if it doesn't exist
        if (!\file_exists($path) && !\mkdir($path, 0776, true) && !\is_dir($path)) {
            throw new RuntimeException(\sprintf('Cannot create directory "%s".', $path));
        }

        if (\file_put_contents($targetPath, $contents) === false) {
            throw new RuntimeException(\sprintf('Cannot generate file "%s".', $path));
        }

        return $targetPath;
    }
}
