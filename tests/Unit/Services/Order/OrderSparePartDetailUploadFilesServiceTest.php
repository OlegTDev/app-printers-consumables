<?php

namespace Tests\Unit\Services\Order;

use App\Services\Order\OrderSparePartDetailUploadFilesService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OrderSparePartDetailUploadFilesServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake();
    }

    public function test_it_upload_a_single_file_correctly(): void
    {
        $file = UploadedFile::fake()->create('some-file.pdf', 500);

        $service = new OrderSparePartDetailUploadFilesService([$file]);
        $paths = $service->upload();

        $this->assertCount(1, $paths);
        Storage::disk()->assertExists($paths);
    }

    public function test_it_upload_multiple_files_correctly(): void
    {
        $files = [
            UploadedFile::fake()->create('some-file_1.pdf', 500),
            UploadedFile::fake()->create('some-file_2.png', 500),
            UploadedFile::fake()->create('some-file_3.jpd', 500),
            UploadedFile::fake()->create('some-file_4.some', 500),
        ];

        $service = new OrderSparePartDetailUploadFilesService($files, 'uploads_dir');
        $paths = $service->upload();

        $this->assertCount(4, $paths);
        foreach ($paths as $path) {
            $this->assertStringStartsWith('uploads_dir/', $path);
        }
        Storage::disk()->assertExists($paths);
    }



}
