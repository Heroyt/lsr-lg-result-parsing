<?php
declare(strict_types=1);

if (!function_exists('lang')) {
    /**
     * @param string|null $msg
     * @param string|null $plural
     * @param int $num
     * @param string|null $context
     * @param string|null $domain
     * @param array<int, mixed> $format
     * @return string
     */
    function lang(
        ?string $msg = null,
        ?string $plural = null,
        int     $num = 1,
        ?string $context = null,
        ?string $domain = null,
        array   $format = []
    ): string
    {
        // Fallback implementation
        return $msg ?? '';
    }
}