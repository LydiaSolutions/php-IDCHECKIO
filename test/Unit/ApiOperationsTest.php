<?php

declare(strict_types=1);

namespace idcheckio\Unit;

use Generator;
use idcheckio\ApiClient;
use idcheckio\ApiException;
use idcheckio\Configuration;
use idcheckio\api\AdministrationApi;
use idcheckio\api\AnalysisApi;
use idcheckio\api\SandboxApi;
use idcheckio\model\ErrorResponse;
use idcheckio\model\HealthResponse;
use idcheckio\model\ImageListResponse;
use idcheckio\model\ImageRequest;
use idcheckio\model\MrzListResponse;
use idcheckio\model\MrzRequest;
use idcheckio\model\MrzResponse;
use idcheckio\model\ReportResponse;
use idcheckio\model\ResultResponse;
use idcheckio\model\TaskResponse;
use idcheckio\model\UserResponse;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ApiOperationsTest extends TestCase
{
    private const JSON_HEADERS = ['Accept' => 'application/json', 'Content-Type' => 'application/json'];

    public static function getRequests(): Generator
    {
        yield 'health without authentication' => [
            AdministrationApi::class, 'getHealth', [],
            '/v0/admin/health', '/v0/admin/health', [], self::JSON_HEADERS,
            HealthResponse::class, ['status' => 'OK'],
        ];
        yield 'user with authentication and language' => [
            AdministrationApi::class, 'getUser', ['fr-FR'],
            '/v0/admin/user', '/v0/admin/user', [],
            self::JSON_HEADERS + ['Accept-Language' => 'fr-FR', 'Authorization' => 'Basic ' . base64_encode('fixture-user:fixture-pass')],
            UserResponse::class, ['remainingCredits' => 12],
        ];
        yield 'report with an encoded reference' => [
            AnalysisApi::class, 'getReport', ['fixture/report'],
            '/v0/pdfreport/fixture%2Freport', '/v0/pdfreport/{analysisRefUid}', [],
            self::JSON_HEADERS + ['Authorization' => 'Basic ' . base64_encode('fixture-user:fixture-pass')],
            ReportResponse::class, ['analysisRefUid' => 'fixture/report', 'report' => 'fixture-pdf'],
        ];
        yield 'task with a zero wait' => [
            AnalysisApi::class, 'getTask', ['fixture-task', null, 0],
            '/v0/task/fixture-task', '/v0/task/{analysisRefUid}', ['wait' => 0],
            self::JSON_HEADERS + ['Authorization' => 'Basic ' . base64_encode('fixture-user:fixture-pass')],
            TaskResponse::class, ['uid' => 'fixture-task', 'isWithProgress' => false],
        ];
        yield 'sandbox image list' => [
            SandboxApi::class, 'getImageList', [],
            '/v0/sandbox/imagelist', '/v0/sandbox/imagelist', [], self::JSON_HEADERS,
            '\\' . ImageListResponse::class, ['images' => []],
        ];
        yield 'sandbox MRZ with an encoded reference' => [
            SandboxApi::class, 'getMrz', ['fixture/mrz'],
            '/v0/sandbox/mrz/fixture%2Fmrz', '/v0/sandbox/mrz/{mrzUid}', [], self::JSON_HEADERS,
            '\\' . MrzResponse::class, ['mrz' => (object) ['line1' => 'FIXTURE']],
        ];
        yield 'sandbox MRZ list' => [
            SandboxApi::class, 'getMrzList', [],
            '/v0/sandbox/mrzlist', '/v0/sandbox/mrzlist', [], self::JSON_HEADERS,
            '\\' . MrzListResponse::class, ['mrz' => ['fixture-mrz']],
        ];
    }

    #[DataProvider('getRequests')]
    public function testGetOperationsReturnOnlyTheTypedResponse(
        string $apiClass,
        string $method,
        array $arguments,
        string $path,
        string $endpoint,
        array $query,
        array $headers,
        string $responseType,
        array $payload,
    ): void {
        $configuration = (new Configuration())->setUsername('fixture-user')->setPassword('fixture-pass');
        $client = $this->mockTransport($configuration);
        $client->expects($this->once())->method('callApi')
            ->with($path, 'GET', $this->identicalTo($query), '', $headers, $responseType, $endpoint)
            ->willReturn([(object) $payload, 200, ['X-Request-Id' => 'fixture-id']]);

        $response = (new $apiClass($client))->$method(...$arguments);

        self::assertInstanceOf($responseType, $response);
        self::assertEquals($payload, (array) $client->getSerializer()->sanitizeForSerialization($response));
    }

    public function testGetResultEncodesThePathAndKeepsFalseQueryOptions(): void
    {
        $client = $this->mockTransport();
        $responseHeaders = ['X-Request-Id' => 'fixture-id'];
        $client->expects($this->once())->method('callApi')->with(
            '/v0/result/fixture%2Fwith%20space',
            'GET',
            $this->identicalTo(['rectoImageCropped' => false, 'signatureImageCropped' => true]),
            '',
            self::JSON_HEADERS + ['Accept-Language' => 'en'],
            ResultResponse::class,
            '/v0/result/{analysisRefUid}',
        )->willReturn([(object) ['analysisRefUid' => 'fixture/with space'], 200, $responseHeaders]);

        [$result, $status, $headers] = (new AnalysisApi($client))
            ->getResultWithHttpInfo('fixture/with space', 'en', false, null, true);

        self::assertInstanceOf(ResultResponse::class, $result);
        self::assertSame('fixture/with space', $result->getAnalysisRefUid());
        self::assertSame(200, $status);
        self::assertSame($responseHeaders, $headers);
    }

    public static function submissions(): Generator
    {
        yield 'image' => ['postImage', '/v0/task/image', ImageRequest::class, ['front_image' => 'fixture']];
        yield 'MRZ' => ['postMrz', '/v0/task/mrz', MrzRequest::class, ['line1' => 'FIXTURE']];
    }

    #[DataProvider('submissions')]
    public function testSubmissionsPassTheModelAndAsyncOptionToTheTransport(
        string $method,
        string $path,
        string $requestType,
        array $requestData,
    ): void {
        $request = new $requestType($requestData);
        $client = $this->mockTransport();
        $client->expects($this->once())->method('callApi')->with(
            $path,
            'POST',
            $this->identicalTo(['asyncMode' => false]),
            $this->identicalTo($request),
            self::JSON_HEADERS,
            ResultResponse::class,
            $path,
        )->willReturn([(object) ['uid' => 'fixture-result'], 200, []]);

        $response = (new AnalysisApi($client))->$method($request, false);
        self::assertInstanceOf(ResultResponse::class, $response);
        self::assertSame('fixture-result', $response->getUid());
    }

    public function testSandboxImagePassesQueryOptionsAndReturnsAList(): void
    {
        $client = $this->mockTransport();
        $client->expects($this->once())->method('callApi')->with(
            '/v0/sandbox/image/fixture%2Ffront',
            'GET',
            $this->identicalTo(['rawType' => 'PNG', 'light' => 'UV']),
            '',
            ['Accept' => '*/*', 'Content-Type' => 'application/json'],
            'string[]',
            '/v0/sandbox/image/{imageUid}',
        )->willReturn([['fixture-image'], 200, []]);

        self::assertSame(['fixture-image'], (new SandboxApi($client))->getImage('fixture/front', 'PNG', null, 'UV'));
    }

    public static function requiredParameters(): Generator
    {
        yield 'report reference' => [AnalysisApi::class, 'getReport', 'analysis_ref_uid'];
        yield 'result reference' => [AnalysisApi::class, 'getResult', 'analysis_ref_uid'];
        yield 'task reference' => [AnalysisApi::class, 'getTask', 'analysis_ref_uid'];
        yield 'image request' => [AnalysisApi::class, 'postImage', 'body'];
        yield 'MRZ request' => [AnalysisApi::class, 'postMrz', 'body'];
        yield 'sandbox image ID' => [SandboxApi::class, 'getImage', 'image_uid'];
        yield 'sandbox MRZ ID' => [SandboxApi::class, 'getMrz', 'mrz_uid'];
    }

    #[DataProvider('requiredParameters')]
    public function testRejectsNullRequiredParametersBeforeCallingTransport(
        string $apiClass,
        string $method,
        string $parameter,
    ): void {
        $client = $this->mockTransport();
        $client->expects($this->never())->method('callApi');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing the required parameter $' . $parameter . ' when calling ' . $method);
        (new $apiClass($client))->$method(null);
    }

    public static function taskErrors(): Generator
    {
        yield 'redirect includes the task' => [303, ['uid' => 'fixture-task', 'isWithProgress' => true], TaskResponse::class];
        yield 'known error includes a typed body' => [503, ['errorMessage' => 'fixture unavailable'], ErrorResponse::class];
        yield 'unknown status leaves response object unset' => [418, ['errorMessage' => 'unexpected status'], null];
    }

    #[DataProvider('taskErrors')]
    public function testTaskPreservesTheTransportExceptionAndDecodesKnownBodies(
        int $status,
        array $payload,
        ?string $responseType,
    ): void {
        $headers = ['X-Request-Id' => 'fixture-id'];
        $body = (object) $payload;
        $failure = new ApiException('fixture failure', $status, $headers, $body);
        $client = $this->mockTransport();
        $client->expects($this->once())->method('callApi')->willThrowException($failure);

        try {
            (new AnalysisApi($client))->getTask('fixture-task');
            self::fail('Expected the SDK to rethrow the transport exception.');
        } catch (ApiException $caught) {
            self::assertSame($failure, $caught);
            self::assertSame($status, $caught->getCode());
            self::assertSame($headers, $caught->getResponseHeaders());
            self::assertSame($body, $caught->getResponseBody());

            $response = $caught->getResponseObject();
            if ($responseType === null) {
                self::assertNull($response);
            } else {
                self::assertInstanceOf($responseType, $response);
                self::assertSame($payload, (array) $client->getSerializer()->sanitizeForSerialization($response));
            }
        }
    }

    private function mockTransport(?Configuration $configuration = null): ApiClient&MockObject
    {
        return $this->getMockBuilder(ApiClient::class)
            ->setConstructorArgs([$configuration ?? new Configuration()])
            ->onlyMethods(['callApi'])
            ->getMock();
    }
}
