<?php

declare(strict_types=1);

namespace idcheckio\Unit;

use DateTime;
use DateTimeInterface;
use Generator;
use idcheckio\ObjectSerializer;
use idcheckio\model\Control;
use idcheckio\model\ControlGroup;
use idcheckio\model\ImageRequest;
use idcheckio\model\ResultResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ObjectSerializerTest extends TestCase
{
    public function testSerializesWireNamesAndPreservesFalseWhileOmittingNull(): void
    {
        $image = new ImageRequest([
            'api_version' => '0',
            'front_image' => 'fixture-image',
            'face_image_cropped' => false,
            'signature_image_cropped' => true,
        ]);

        self::assertSame([
            'apiVersion' => '0',
            'frontImage' => 'fixture-image',
            'rectoImageCropped' => false,
            'faceImageCropped' => false,
            'signatureImageCropped' => true,
        ], (array) ObjectSerializer::sanitizeForSerialization($image));
    }

    public function testDeserializesNestedModelsAndRoundTripsTheWirePayload(): void
    {
        $json = '{"analysisRefUid":"fixture-ref","controls":[{"identifier":"group-1","control":[{"identifier":"check-1","result":"OK"}]}]}';
        $result = ObjectSerializer::deserialize(json_decode($json, flags: JSON_THROW_ON_ERROR), ResultResponse::class);

        self::assertInstanceOf(ResultResponse::class, $result);
        self::assertSame('fixture-ref', $result->getAnalysisRefUid());
        self::assertNull($result->getMrz());
        $groups = $result->getControls();
        self::assertCount(1, $groups);
        self::assertInstanceOf(ControlGroup::class, $groups[0]);
        $controls = $groups[0]->getControl();
        self::assertCount(1, $controls);
        self::assertInstanceOf(Control::class, $controls[0]);
        self::assertSame('OK', $controls[0]->getResult());
        $roundTrip = json_encode(ObjectSerializer::sanitizeForSerialization($result), JSON_THROW_ON_ERROR);
        self::assertJsonStringEqualsJsonString($json, $roundTrip);
    }

    public static function deserializationCases(): Generator
    {
        yield 'null model' => [null, ResultResponse::class, null];
        yield 'integer' => ['0', 'int', 0];
        yield 'list of strings' => [[10, '20'], 'string[]', ['10', '20']];
        yield 'map of integers' => [(object) ['zero' => '0', 'one' => '1'], 'map[string,int]', ['zero' => 0, 'one' => 1]];
        yield 'empty date' => ['', '\\DateTime', null];
    }

    #[DataProvider('deserializationCases')]
    public function testDeserializesPrimitiveAndCollectionTypes(mixed $input, string $type, mixed $expected): void
    {
        self::assertSame($expected, ObjectSerializer::deserialize($input, $type));
    }

    public function testDateAndPathParameterFormatting(): void
    {
        $timestamp = '2026-11-23T10:15:00+01:00';
        $date = ObjectSerializer::deserialize($timestamp, '\\DateTime');
        $serializer = new ObjectSerializer();

        self::assertInstanceOf(DateTime::class, $date);
        self::assertSame($timestamp, $date->format(DateTimeInterface::ATOM));
        self::assertSame($timestamp, ObjectSerializer::sanitizeForSerialization($date));
        self::assertSame($timestamp, $serializer->toHeaderValue($date));
        self::assertSame('reference%2Fwith%20space%3F', $serializer->toPathValue('reference/with space?'));
    }

    public static function collectionFormats(): Generator
    {
        yield 'csv' => [['a', 'b'], 'csv', false, 'a,b'];
        yield 'pipes' => [['a', 'b'], 'pipes', false, 'a|b'];
        yield 'spaces' => [['a', 'b'], 'ssv', false, 'a b'];
        yield 'tabs' => [['a', 'b'], 'tsv', false, "a\tb"];
        yield 'multi' => [['tag' => ['a b', 'c+d']], 'multi', true, 'tag=a+b&tag=c%2Bd'];
        yield 'multi disabled' => [['a', 'b'], 'multi', false, 'a,b'];
    }

    #[DataProvider('collectionFormats')]
    public function testSerializesCollections(array $items, string $format, bool $allowMulti, string $expected): void
    {
        self::assertSame($expected, (new ObjectSerializer())->serializeCollection($items, $format, $allowMulti));
    }
}
