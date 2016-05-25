<?php
/**
 * Cache utilities
 */

/**
 * Purges all cached pages
 *
 * @param string $pageCacheDir   page cache directory
 * @param string $filePattern    Pattern matching files to delete, e.g: '*.cache'.
 * @param string $excludePattern Keep files matching pattern in $pageCacheDir.
 *
 * @return mixed an error string if the directory is missing
 */
function purgeCachedPages($pageCacheDir, $filePattern, $excludePattern = '')
{
    if (! is_dir($pageCacheDir)) {
        $error = 'Cannot purge '.$pageCacheDir.': no directory';
        error_log($error);
        return $error;
    }

    $files = array_filter(
       glob($pageCacheDir .'/'. $filePattern),
       function($file) use ($excludePattern) {
           return strpos($file, $excludePattern) === false;
       }
    );
    array_map('unlink', $files);
}

/**
 * Invalidates caches when the database is changed or the user logs out.
 *
 * @param string $pageCacheDir      page cache directory
 * @param string $templateCacheDir  Template cache directory.
 * @param string $thumbnailCacheDir Thumbnails cache directory.
 */
function invalidateCaches($pageCacheDir, $templateCacheDir, $thumbnailCacheDir)
{
    // Purge page cache shared by sessions.
    purgeCachedPages($pageCacheDir, '*.cache');

    // Purge RainTPL cache.
    purgeCachedPages($templateCacheDir, '*.rtpl.php');

    // Purge thumbnail cache.
    purgeCachedPages($thumbnailCacheDir, '*', '.htaccess');
}
