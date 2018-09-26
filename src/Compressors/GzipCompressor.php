<?php

namespace Spatie\DbDumper\Compressors;

class GzipCompressor implements Compressor
{
    public function getCommand()
    {
        return 'gzip';
    }

    public function getExtension()
    {
        return '.gz';
    }
}
