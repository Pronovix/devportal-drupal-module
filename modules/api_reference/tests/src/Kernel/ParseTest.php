<?php

namespace Drupal\Tests\devportal_api_reference\Kernel;

use DirectoryIterator;
use Drupal\Core\Cache\NullBackend;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\devportal_api_reference\Plugin\Reference\OpenApi3;
use Drupal\devportal_api_reference\Plugin\Reference\Swagger;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests the parsing and validating edge-case swagger/openapi files.
 */
final class ParseTest extends KernelTestBase {

  /**
   * Mock logger that does nothing.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  private $mockLogger;

  /**
   * Mock cache that does nothing.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  private $mockCache;

  /**
   * Creates a Swagger plugin.
   *
   * @return \Drupal\devportal_api_reference\Plugin\Reference\Swagger
   *   Instance.
   */
  private function createSwaggerPlugin(): Swagger {
    return new Swagger([], '', [], $this->mockCache, $this->mockLogger);
  }

  /**
   * Creates an OpenApi3 plugin.
   *
   * @return \Drupal\devportal_api_reference\Plugin\Reference\OpenApi3
   *   Instance.
   */
  private function createOpenApi3Plugin(): OpenApi3 {
    return new OpenApi3([], '', [], $this->mockCache, $this->mockLogger);
  }

  /**
   * Lists the files in a subdirectory of fixtures/specials.
   *
   * @param string $subdir
   *   Subdirectory name.
   *
   * @return array
   *   Directory contents in a format that is accepted by PhpUnit's data
   *   provider. The key is the file name, the value is an array with a single
   *   element that is the full path of the file.
   */
  private function listFiles(string $subdir): array {
    $dir = realpath(__DIR__ . "/../../fixtures/specials/$subdir");

    $files = [];

    foreach (new DirectoryIterator($dir) as $fileinfo) {
      if ($fileinfo->isFile()) {
        $files[$fileinfo->getFilename()] = [$fileinfo->getRealPath()];
      }
    }

    return $files;
  }

  /**
   * Provides a list of special swagger files.
   *
   * @return array
   *   List of files.
   *
   * @see \Drupal\Tests\devportal_api_reference\Kernel\ParseTest::listFiles()
   */
  public function swaggerFiles(): array {
    return $this->listFiles('swagger');
  }

  /**
   * Provides a list of special OpenApi3 files.
   *
   * @return array
   *   List of files.
   *
   * @see \Drupal\Tests\devportal_api_reference\Kernel\ParseTest::listFiles()
   */
  public function openApi3Files(): array {
    return $this->listFiles('openapi3');
  }

  /**
   * Tests a Swagger file.
   *
   * @param string $filepath
   *   Full path to the test file.
   *
   * @dataProvider swaggerFiles
   */
  public function testSwaggerFile(string $filepath): void {
    self::assertNotNull($this->createSwaggerPlugin()->parse($filepath));
  }

  /**
   * Tests an OpenApi3 file.
   *
   * @param string $filepath
   *   Full path to the test file.
   *
   * @dataProvider openApi3Files
   */
  public function testOpenApi3File(string $filepath): void {
    self::assertNotNull($this->createOpenApi3Plugin()->parse($filepath));
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->mockCache = new NullBackend('');
    $this->mockLogger = $this->createMock(LoggerChannelInterface::class);
  }

}
