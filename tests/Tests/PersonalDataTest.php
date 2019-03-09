<?php

namespace Spatie\PersonalDataDownload\Tests\Tests;

use Illuminate\Support\Facades\Storage;
use Spatie\PersonalDataDownload\Exceptions\CouldNotAddToPersonalData;
use Spatie\PersonalDataDownload\PersonalData;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use Spatie\PersonalDataDownload\Tests\TestCase;


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
    public function it_can_copy_a_local_file_to_the_personal_data()
    {
        $avatarPath = $this->getStubPath('avatar.png');

        $this->personalData->addFile($avatarPath);

        $this->assertFileExists($this->temporaryDirectory->path('avatar.png'));
        $this->assertFileExists($avatarPath);
    }

    /** @test */
    public function it_can_copy_a_file_from_a_disk_to_the_personal_data_temporary_directory()
    {
        $disk = Storage::fake('test-disk');

        $disk->put('my-file.txt', 'my content');

        $this->personalData->addFile('my-file.txt', 'test-disk');

        $this->assertFileContents($this->temporaryDirectory->path('my-file.txt'), 'my content');
    }

    /** @test */
    public function it_will_not_allow_an_entry_in_the_personal_data_to_be_overwritten()
    {
        $this->personalData->addContent('test.txt', 'test content');

        $this->expectException(CouldNotAddToPersonalData::class);

        $this->personalData->addContent('test.txt', 'test content');
    }
}
