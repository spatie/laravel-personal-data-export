<?php

namespace Spatie\PersonalDataDownload\Tests;

use Spatie\PersonalDataDownload\PersonalData;
use Spatie\TemporaryDirectory\TemporaryDirectory;

class PersonalDataTest extends TestCase
{
    /** @var \Spatie\TemporaryDirectory\TemporaryDirectory */
    protected $temporaryDirectory;

    /** @var \Spatie\PersonalDataDownload\PersonalData */
    protected $personalData;

    public function setUp(): void
    {
        parent::setUp();

        $this->temporaryDirectory = (new TemporaryDirectory())->create();

        $this->personalData = new PersonalData($this->temporaryDirectory);
    }

    /** @test */
    public function it_can_add_content()
    {
        $this->personalData->addContent('my-content.txt', 'this is my content');

        $this->assertFileContents($this->temporaryDirectory->path('my-content.txt'), 'this is my content');
    }
}