<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\InvalidMetadataException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;

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
    public function throwErrors(): void
    {
        Log::emergency("This is an emergency log.");
        Log::alert("This is an alert log.");
        Log::critical("This is a critical log.");
        Log::error("This is an error log.");
        Log::warning("This is a warning log.");
        Log::notice("This is a notice log.");
        Log::info("This is an info log.");
        Log::debug("This is a debug log.");

        // Throw an exception at the end to simulate an error
        $this->throwExceptions();
    }

    /**
     * @throws Exception
     */
    public function throwExceptions()
    {
        // Model not found
        $rand = rand(0, 10);
        logger("rand $rand");
        if ($rand == 0) {
            throw new ModelNotFoundException("Model not found");
        }

        // Authentication error
        if ($rand == 1) {
            throw new AuthenticationException("Unauthenticated");
        }

        // Validation error
        if ($rand == 2) {
            $validator = Validator::make([], []);
            throw new ValidationException($validator, "Validation failed");
        }

        // Not found HTTP exception
        if ($rand == 3) {
            throw new AccessDeniedHttpException("Not Found");
        }

        // Unauthorized HTTP exception
        if ($rand == 4) {
            throw new UnauthorizedHttpException("Bearer", "Unauthorized");
        }

        // Bad request HTTP exception
        if ($rand == 5) {
            throw new BadRequestHttpException("Bad Request");
        }

        // Unsupported Media exception
        if ($rand == 6) {
            throw new UnsupportedMediaTypeHttpException("Unsupported Media type");
        }

        // Invalid Metadata exception
        if ($rand == 7) {
            throw new InvalidMetadataException("Invalid Metadata");
        }

        // Method not allowed HTTP exception
        if ($rand == 8) {
            throw new MethodNotAllowedHttpException([], "Method Not Allowed");
        }

        // Service unavailable HTTP exception
        if ($rand == 9) {
            throw new ServiceUnavailableHttpException(null, "Service Unavailable");
        }

        // Conflict HTTP exception
        if ($rand == 10) {
            throw new ConflictHttpException("Conflict");
        }

        throw new Exception("This is a test to generate exceptions");
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
                                'command' => ['sh', '-c', "php /app/artisan job:generate-excel --size=$size"],
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
                'backoffLimit' => 1
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
