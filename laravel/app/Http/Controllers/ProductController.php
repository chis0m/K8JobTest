<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\User;
use App\Services\ProductService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class ProductController extends Controller
{
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Product::query()->take(100)->get();
    }

    /**
     * @throws Exception
     */
    public function throwError()
    {
        Log::info("Throwing exception now");
        throw new Exception("This is a test exception");
    }

    public function generateExcel(Request $request): JsonResponse
    {
        $users = User::all();
        $fileName = 'products.xlsx';
        $zipFileName = 'products.zip';
        $this->productService->size = $request->query('size');

        // zip and upload to s3
        $zipUrl = $this->productService->zipAndUploadToS3($fileName, $zipFileName);

        // Upload directly to s3
        $url = $this->productService->convertToExcelAndUploadToS3($fileName);

        // Send email with the URL
        $this->productService->sendEmail($users, $url, $zipUrl);

        return response()->json(['message' => 'Export generated and email sent successfully.']);
    }

    public function generateExcelJob(Request $request): JsonResponse
    {
        $userIds = User::query()->pluck('id')->toArray();
        $size = $request->query('size', 1000);
        $jobName = 'generate-excel-job-' . uniqid();
        $namespace = "excel";
        $image = "cl0ud/excel";
        $secret = "app-sec";
        $configMap = "app-cm";

        Log::info("The ids of the users to send email", $userIds);

        $jobYaml = [
            'apiVersion' => 'batch/v1',
            'kind' => 'Job',
            'metadata' => [
                'name' => $jobName,
                'namespace' => $namespace
            ],
            'spec' => [
                'template' => [
                    'spec' => [
                        'containers' => [
                            [
                                'name' => 'excel-generator',
                                'image' => $image,
                                'imagePullPolicy' => 'Always',
                                'command' => ['sh', '-c', "php /app/artisan generate:excel --size=$size"],
                                'env' => [
                                    ['name' => 'USER_IDS', 'value' => json_encode($userIds)],
                                    [
                                        'name' => 'DB_PASSWORD',
                                        'valueFrom' => [
                                            'secretKeyRef' => [
                                                'name' => 'moco-app-db',
                                                'key' => 'ADMIN_PASSWORD'
                                            ]
                                        ]
                                    ]
                                ],
                                'envFrom' => [
                                    ['configMapRef' => ['name' => $configMap]],
                                    ['secretRef' => ['name' => $secret]]
                                ]
                            ]
                        ],
                        'restartPolicy' => 'Never'
                    ]
                ],
                'backoffLimit' => 4
            ]
        ];

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->getKubernetesToken(),
        ])->withOptions([
            'verify' => '/var/run/secrets/kubernetes.io/serviceaccount/ca.crt',
        ])->post('https://kubernetes.default.svc/apis/batch/v1/namespaces/excel/jobs', $jobYaml);

        return response()->json(['status' => 'Job created', 'response' => $response->json()]);
    }

    private function getKubernetesToken(): false|string
    {
        return file_get_contents('/var/run/secrets/kubernetes.io/serviceaccount/token');
    }

}
