<?php declare(strict_types=1);

namespace Tests\Kirameki\Cli;

use php_user_filter;

class Interceptor extends php_user_filter
{
    public static string $cache = '';
    public function filter(mixed $in, mixed $out, &$consumed, bool $closing): int
    {
        while ($bucket = stream_bucket_make_writeable($in)) {
            self::$cache .= $bucket->data;
            $consumed += $bucket->datalen;
            stream_bucket_append($out, $bucket);
        }
        return PSFS_PASS_ON;
    }
}
