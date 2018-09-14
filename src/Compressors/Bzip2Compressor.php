<?php

namespace Spatie\DbDumper\Compressors;

class Bzip2Compressor implements Compressor
{
    public function getCommand()
    {
        return 'bzip2 -f9';
    }

    public function getExtension()
    {
        return '.bz2';
    }
}
