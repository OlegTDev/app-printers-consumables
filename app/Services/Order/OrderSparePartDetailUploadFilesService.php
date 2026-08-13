<?php
namespace App\Services\Order;


class OrderSparePartDetailUploadFilesService
{

    public function __construct(private array $files, private string $uploadPath = 'orders')
    {
    }

    public function upload(): array
    {
        $uploadedPaths = [];

        if ($this->files) {
            foreach ((array) $this->files as $file) {
                $uploadedPaths[] = $file->store($this->uploadPath);
            }
        }

        return $uploadedPaths;
    }
}
