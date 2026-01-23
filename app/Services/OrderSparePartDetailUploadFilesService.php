<?php
namespace App\Services;

use Illuminate\Http\UploadedFile;

class OrderSparePartDetailUploadFilesService
{

    public function __construct(private $files, private string $uploadPath = 'orders') 
    {}

    /**
     * @param UploadedFile[] $files
     * @return void
     */
    public function upload(): array
    {
        $uploadedPaths = [];

        if ($this->files) {            
            foreach ((array)$this->files as $file) {
                $uploadedPaths[] = $file->store($this->uploadPath);
            }
        }

        return $uploadedPaths;
    }
}