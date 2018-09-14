<?php

namespace Spatie\DbDumper\Compressors;

interface Compressor
{
    /** @return string */
    public function getCommand();

    /** @return string */
    public function getExtension();
}
