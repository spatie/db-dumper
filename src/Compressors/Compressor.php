<?php

namespace Spatie\DbDumper\Compressors;

interface Compressor
{
    public function useCommand(): string;
}
