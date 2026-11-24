<?php

declare(strict_types=1);

namespace idcheckio\Unit;

use ArrayAccess;
use Generator;
use idcheckio\model\HealthResponse;
use idcheckio\model\ImageRequest;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ModelTest extends TestCase
{
    public static function modelClasses(): Generator
    {
        foreach (glob(__DIR__ . '/../../lib/model/*.php') as $file) {
            $model = basename($file, '.php');
            yield $model => ['idcheckio\\model\\' . $model];
        }
    }

    #[DataProvider('modelClasses')]
    public function testModelAcceptsOmittedAndExplicitNullData(string $modelClass): void
    {
        $default = new $modelClass();
        self::assertInstanceOf(ArrayAccess::class, $default);
        self::assertEquals($default, new $modelClass(null));
        self::assertEquals($default, new $modelClass([]));
    }

    public function testArrayAccessPreservesFalseAndDistinguishesItFromMissingData(): void
    {
        $model = new ImageRequest(['face_image_cropped' => false]);

        self::assertTrue(isset($model['face_image_cropped']));
        self::assertFalse($model['face_image_cropped']);
        self::assertFalse($model->getFaceImageCropped());

        $model['face_image_cropped'] = true;
        self::assertTrue($model->getFaceImageCropped());

        unset($model['face_image_cropped']);
        self::assertFalse(isset($model['face_image_cropped']));
        self::assertNull($model['face_image_cropped']);
    }

    public function testHealthStatusValidation(): void
    {
        $health = new HealthResponse();
        self::assertFalse($health->valid());
        self::assertSame($health, $health->setStatus(HealthResponse::STATUS_OK));
        self::assertTrue($health->valid());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid value for 'status'");
        $health->setStatus('UNKNOWN');
    }
}
