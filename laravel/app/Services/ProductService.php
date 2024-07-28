<?php

namespace App\Services;

use ZipArchive;
use App\Exports\ProductExport;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ProductExportNotification;

class ProductService
{
    public $size = 1000;

    public function convertToExcelAndUploadToS3($fileName)
    {
        $filePath = 'test_folder/' . $fileName;
        Excel::store(new ProductExport($this->size), $filePath, 's3');
        return Storage::disk('s3')->url($filePath);
    }

    public function zipAndUploadToS3($fileName, $zipFileName)
    {
        // Convert to Excel and store locally
        $this->convertToExcel($fileName);

        // Zip the Excel file
        $this->zipFile($fileName, $zipFileName);

        // Upload the zip file to S3
        return $this->uploadToS3($zipFileName);
    }

    private function convertToExcel($fileName)
    {
        Excel::store(new ProductExport($this->size), $fileName, 'local');
    }

    private function zipFile($fileName, $zipFileName)
    {
        $zip = new ZipArchive;
        $fileFullPath = storage_path('app/' . $fileName);
        $zipFullPath = storage_path('app/' . $zipFileName);

        if ($zip->open($zipFullPath, ZipArchive::CREATE) === TRUE) {
            $zip->addFile($fileFullPath, $fileName);
            $zip->close();
        } else {
            logger('Failed to create zip file');
        }
    }

    private function uploadToS3($zipFileName)
    {
        $filePath = storage_path('app/' . $zipFileName);
        $s3Path = 'test_folder/' . $zipFileName;

        // Check if the file exists before uploading
        if (file_exists($filePath)) {
            Storage::disk('s3')->put($s3Path, file_get_contents($filePath), 'private');
            return Storage::disk('s3')->url($s3Path);
        } else {
            logger("File does not exist: " . $filePath);
            return null;
        }
    }

    public function sendEmail(Collection $users, $url, $zipUrl): void
    {
        Notification::send($users, new ProductExportNotification($url, $zipUrl));
    }
}
