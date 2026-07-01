<?php

declare(strict_types=1);

namespace Horde\Ansel\Test;

use Horde\Compress\CompressFactory;
use Horde\Compress\Driver\Zip;
use PHPUnit\Framework\TestCase;

/**
 * Tests the ZIP compress/decompress contract as used by Ansel.
 *
 * Ansel uses two patterns:
 * 1. Compress: collect files → compressFiles() → raw ZIP string for download
 * 2. Decompress: ZIP_LIST for listing, ZIP_DATA for raw file content passed to addImage()
 * @coversNothing
 */
class AnselCompressTest extends TestCase
{
    /**
     * Ansel.php pattern: compress an array of ['data','name'] into a ZIP string.
     * The caller sends this string directly as a download body with strlen().
     */
    public function testCompressReturnsNonEmptyString(): void
    {
        $zip = (new CompressFactory())->create('zip');

        $files = [
            ['data' => 'image-binary-data-1', 'name' => 'photo1.jpg'],
            ['data' => 'image-binary-data-2', 'name' => 'photo2.jpg'],
        ];

        $body = $zip->compressFiles($files);

        $this->assertIsString($body);
        $this->assertNotEmpty($body);
        $this->assertGreaterThan(0, strlen($body));
    }

    /**
     * View/Upload.php pattern: decompress ZIP_LIST returns array of entries
     * with 'name' key that the caller uses to filter meta files.
     */
    public function testDecompressListReturnsEntriesWithNames(): void
    {
        $zip = (new CompressFactory())->create('zip');

        $files = [
            ['data' => 'img-a', 'name' => 'gallery/photo_a.png'],
            ['data' => 'img-b', 'name' => 'gallery/photo_b.png'],
            ['data' => 'meta', 'name' => '__MACOSX/.DS_Store'],
        ];

        $archive = $zip->compressFiles($files);
        $listing = $zip->decompress($archive, [
            'action' => Zip::ZIP_LIST,
        ]);

        $this->assertIsArray($listing);
        $this->assertCount(3, $listing);
        $this->assertEquals('gallery/photo_a.png', $listing[0]['name']);
        $this->assertEquals('gallery/photo_b.png', $listing[1]['name']);
        $this->assertEquals('__MACOSX/.DS_Store', $listing[2]['name']);
    }

    /**
     * View/Upload.php pattern: decompress ZIP_DATA returns file content.
     * The PSR-4 API returns ['data' => string], so the caller unwraps it.
     * The addImage() call receives a raw string either way.
     */
    public function testDecompressDataReturnsContent(): void
    {
        $zip = (new CompressFactory())->create('zip');

        $originalContent = random_bytes(256);
        $files = [
            ['data' => $originalContent, 'name' => 'test_image.jpg'],
        ];

        $archive = $zip->compressFiles($files);
        $listing = $zip->decompress($archive, [
            'action' => Zip::ZIP_LIST,
        ]);

        $result = $zip->decompress($archive, [
            'action' => Zip::ZIP_DATA,
            'info' => $listing,
            'key' => 0,
        ]);

        // PSR-4 returns ['data' => string]; caller unwraps with is_array check.
        $zdata = is_array($result) ? $result['data'] : $result;
        $this->assertIsString($zdata);
        $this->assertEquals($originalContent, $zdata);
    }

    /**
     * Round-trip: compress multiple files then extract each one individually.
     * Proves the full cycle matches what Ansel does in Upload.php.
     */
    public function testRoundTripMultipleFiles(): void
    {
        $zip = (new CompressFactory())->create('zip');

        $inputFiles = [
            ['data' => 'content-alpha', 'name' => 'alpha.dat'],
            ['data' => 'content-beta', 'name' => 'beta.dat'],
            ['data' => 'content-gamma', 'name' => 'gamma.dat'],
        ];

        $archive = $zip->compressFiles($inputFiles);
        $listing = $zip->decompress($archive, [
            'action' => Zip::ZIP_LIST,
        ]);

        $this->assertCount(3, $listing);

        foreach ($inputFiles as $key => $input) {
            $result = $zip->decompress($archive, [
                'action' => Zip::ZIP_DATA,
                'info' => $listing,
                'key' => $key,
            ]);
            $extracted = is_array($result) ? $result['data'] : $result;
            $this->assertEquals($input['data'], $extracted, "File at key $key mismatch");
        }
    }
}
