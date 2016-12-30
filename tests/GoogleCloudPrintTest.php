<?php
use Bnb\GoogleCloudPrint\GoogleCloudPrint;

class GoogleCloudPrintTest extends PHPUnit_Framework_TestCase
{

    protected $configMock;


    public function setUp()
    {
        $this->configMock = Mockery::mock('Illuminate\Contracts\Config\Repository')->shouldDeferMissing();
    }


    /**
     * @return GoogleCloudPrint
     */
    private function getService()
    {
        $this->configMock->shouldReceive('get')
            ->with('credentials')
            ->once()
            ->andReturn(__DIR__ . '/' . getenv('GCP_TEST_CREDENTIALS'));

        return new GoogleCloudPrint($this->configMock);
    }


    private function getPrinterId()
    {
        return $printerId = getenv('GCP_TEST_PRINTER_ID');
    }


    /** @test */
    public function it_authenticates()
    {
        $gcp = $this->getService();

        $this->assertNotEmpty($gcp->getAccessToken());
    }


    /** @test */
    public function it_prints_text()
    {
        $gcp = $this->getService();
        $printer = $this->getPrinterId();

        $printJob = $gcp
            ->asText()
            ->content(<<<TEXT
                      ____
                 ____ \__ \
                 \__ \__/ / __
                 __/ ____ \ \ \    ____
                / __ \__ \ \/ / __ \__ \
           ____ \ \ \__/ / __ \/ / __/ / __
      ____ \__ \ \/ ____ \/ / __/ / __ \ \ \
      \__ \__/ / __ \__ \__/ / __ \ \ \ \/
      __/ ____ \ \ \__/ ____ \ \ \ \/ / __
     / __ \__ \ \/ ____ \__ \ \/ / __ \/ /
     \ \ \__/ / __ \__ \__/ / __ \ \ \__/
      \/ ____ \/ / __/ ____ \ \ \ \/ ____
         \__ \__/ / __ \__ \ \/ / __ \__ \
         __/ ____ \ \ \__/ / __ \/ / __/ / __
        / __ \__ \ \/ ____ \/ / __/ / __ \/ /
        \/ / __/ / __ \__ \__/ / __ \/ / __/
        __/ / __ \ \ \__/ ____ \ \ \__/ / __
       / __ \ \ \ \/ ____ \__ \ \/ ____ \/ /
       \ \ \ \/ / __ \__ \__/ / __ \__ \__/
        \/ / __ \/ / __/ ____ \ \ \__/
           \ \ \__/ / __ \__ \ \/
            \/      \ \ \__/ / __
                     \/ ____ \/ /
                        \__ \__/
                        __/

TEXT
            )
            ->printer($printer)
            ->send();

        $this->assertNotNull($printJob);
        $this->assertEquals('IN_PROGRESS', $printJob->status);
    }


    /** @test */
    public function it_prints_pdf_from_file()
    {
        $gcp = $this->getService();
        $printer = $this->getPrinterId();

        $printJob = $gcp
            ->asPdf()
            ->file(__DIR__ . '/test.pdf')
            ->printer($printer)
            ->send();

        $this->assertNotNull($printJob);
        $this->assertEquals('IN_PROGRESS', $printJob->status);
    }


    /** @test */
    public function it_prints_html_from_url_with_range()
    {
        $gcp = $this->getService();
        $printer = $this->getPrinterId();

        $printJob = $gcp
            ->asHtml()
            ->url('https://opensource.org/licenses/MIT')
            ->range(1, 1)
            ->marginsInMillimeters(5, 5, 5, 5)
            ->printer($printer)
            ->send();

        $this->assertNotNull($printJob);
        $this->assertEquals('IN_PROGRESS', $printJob->status);
    }

}