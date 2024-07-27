<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\ProductService;
use Illuminate\Console\Command;

class JobGenerateExcel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'job:generate-excel {--size=1000}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Excel file and send via email';

    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        parent::__construct();
        $this->productService = $productService;
    }

    public function handle(): void
    {
        $userIds = json_decode(env('USER_IDS'), true);

        $users = User::query()->findMany($userIds);

        $size = $this->option('size');

        $fileName = 'products.xlsx';

        $zipFileName = 'products.zip';

        $this->productService->size = $size;

        // zip and upload to s3
        $zipUrl = $this->productService->zipAndUploadToS3($fileName, $zipFileName);

        // Upload directly to s3
        $url = $this->productService->convertToExcelAndUploadToS3($fileName);

        $this->productService->sendEmail($users, $url, $zipUrl);

        $this->info('Export generated and email sent successfully.');
    }
}
