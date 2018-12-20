<?php

namespace DumpNamespace;

trait Files
{
    public function getPublicPathFiles()
    {
        return 'public/'.$this->getPathFiles();
    }

    public function removePublicFiles()
    {
        !\Storage::exists($this->getPublicPathFiles())?:\Storage::deleteDirectory($this->getPublicPathFiles());
    }

    public function getPathFiles()
    {
        return $this->getTable().'/'.$this->getKey();
    }

    public function removeFiles()
    {
        !\Storage::exists($this->getPathFiles())?:\Storage::deleteDirectory($this->getPathFiles());
    }

    public function getPublicPathWithFile(string $string)
    {
        return $this->getPublicPathFiles().'/'.$string;
    }
}
