<?php

namespace Spatie\DbDumper\Compressors;

class LzmaCompressor implements Compressor
{
    public function getCommand()
    {
        return 'lzma -ze';
    }

    public function getExtension()
    {
        return '.lzma';
    }
}
