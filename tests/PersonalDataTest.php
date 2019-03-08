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
    public function it_can_add_a_string_as_content()
    {
        $this->personalData->addContent('my-content.txt', 'this is my content');

        $this->assertFileContents($this->temporaryDirectory->path('my-content.txt'), 'this is my content');
    }

    /** @test */
    public function it_can_add_an_array_as_content()
    {
        $this->personalData->addContent('my-content.txt', ['key' => 'value']);

        $this->assertFileContents($this->temporaryDirectory->path('my-content.txt'), json_encode(['key' => 'value']));
    }

    /** @test */
    public function it_can_copy_a_file_to_the_personal_data()
    {
        $avatarPath = $this->getStubPath('avatar.png');

        $this->personalData->addFile($avatarPath);

        $this->assertFileExists($this->temporaryDirectory->path('avatar.png'));
        $this->assertFileExists($avatarPath);
    }


}
