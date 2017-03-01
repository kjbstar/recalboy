<?php
if ( ! function_exists('public_path'))
{
    function public_path($path = '')
    {
        return ($path ? '/' . $path : $path);
    }
}

if ( ! function_exists('config_path'))
{
    /**
     * Get the configuration path.
     *
     * @param  string $path
     * @return string
     */
    function config_path($path = '')
    {
        return app()->basePath() . '/config' . ($path ? '/' . $path : $path);
    }
}