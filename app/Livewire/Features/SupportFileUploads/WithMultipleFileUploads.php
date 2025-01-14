<?php

namespace App\Livewire\Features\SupportFileUploads;

use Illuminate\Support\Arr;
use Livewire\Features\SupportFileUploads\FileUploadConfiguration;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Features\SupportFileUploads\WithFileUploads;

trait WithMultipleFileUploads
{
    use WithFileUploads;

    /**
     * Overrides the default Livewire file upload method to allow appending multiple file
     * uploads.
     */
    function _finishUpload($name, $tmpPath, $isMultiple)
    {
        if (FileUploadConfiguration::shouldCleanupOldUploads()) {
            $this->cleanupOldUploads();
        }

        if ($isMultiple) {
            $file = collect($tmpPath)->map(function ($i) {
                return TemporaryUploadedFile::createFromLivewire($i);
            })->toArray();
            $this->dispatch('upload:finished', name: $name, tmpFilenames: collect($file)->map->getFilename()->toArray())->self();
        } else {
            $file = TemporaryUploadedFile::createFromLivewire($tmpPath[0]);
            $this->dispatch('upload:finished', name: $name, tmpFilenames: [$file->getFilename()])->self();
        }

        // If the property is an array
        // then APPEND the upload to the array, rather than replacing it.
        if (is_array($value = $this->getPropertyValue($name))) {
            $file = array_merge($value, Arr::wrap($file));
        }

        app('livewire')->updateProperty($this, $name, $file);
    }
}
