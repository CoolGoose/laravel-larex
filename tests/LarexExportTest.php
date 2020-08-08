<?php

namespace Lukasss93\Larex\Tests;

use Illuminate\Support\Facades\File;

class LarexExportTest extends TestCase
{
    public function test_larex_command_without_entries(): void
    {
        $this->artisan('larex:init')->run();
        
        $this->artisan('larex:export')
            ->expectsOutput("Processing the '$this->file' file...")
            ->expectsOutput('No entries found.')
            ->run();
    }
    
    public function test_larex_command_fail_when_localization_file_not_exists(): void
    {
        $this->artisan('larex:export')
            ->expectsOutput("Processing the '$this->file' file...")
            ->expectsOutput("The '$this->file' does not exists.")
            ->expectsOutput('Please create it with: php artisan larex:init')
            ->run();
    }
    
    /** @dataProvider providerWarning
     * @param string $stub
     * @param string $output
     */
    public function test_larex_command_with_warning(string $stub, string $output): void
    {
        $this->initFromStub($stub);
        
        $this->artisan('larex:export')
            ->expectsOutput("Processing the '$this->file' file...")
            ->expectsOutput($output)
            ->expectsOutput('resources/lang/en/app.php created successfully.')
            ->run();
        
        self::assertFileExists(resource_path('lang/en/app.php'));
        
        self::assertEquals(
            $this->getTestStub('export/warning-output'),
            File::get(resource_path('lang/en/app.php'))
        );
    }
    
    public function test_larex_command(): void
    {
        $this->initFromStub('export/larex-input');
        
        $this->artisan('larex:export')
            ->expectsOutput("Processing the '$this->file' file...")
            ->expectsOutput("resources/lang/en/app.php created successfully.")
            ->expectsOutput("resources/lang/en/another.php created successfully.")
            ->run();
        
        self::assertFileExists(resource_path('lang/en/app.php'));
        self::assertFileExists(resource_path('lang/en/another.php'));
        
        self::assertEquals(
            $this->getTestStub('export/larex-output-app'),
            File::get(resource_path('lang/en/app.php'))
        );
        
        self::assertEquals(
            $this->getTestStub('export/larex-output-another'),
            File::get(resource_path('lang/en/another.php'))
        );
    }
    
    public function test_larex_watch(): void
    {
        $this->initFromStub('export/larex-input');
        
        $this->artisan('larex:export --watch')
            ->expectsOutput("Processing the '$this->file' file...")
            ->expectsOutput("resources/lang/en/app.php created successfully.")
            ->expectsOutput("resources/lang/en/another.php created successfully.")
            ->expectsOutput('Waiting for changes...')
            ->run();
        
        self::assertFileExists(resource_path('lang/en/app.php'));
        self::assertFileExists(resource_path('lang/en/another.php'));
        
        self::assertEquals(
            $this->getTestStub('export/larex-output-app'),
            File::get(resource_path('lang/en/app.php'))
        );
        
        self::assertEquals(
            $this->getTestStub('export/larex-output-another'),
            File::get(resource_path('lang/en/another.php'))
        );
    }
    
    public function providerWarning(): array
    {
        return [
            'blank line' => ['export/warning-input-1', 'Line 3 is not valid. It will be skipped.'],
            'missing key' => ['export/warning-input-2', 'Line 3 is not valid. It will be skipped.'],
            'missing column' => [
                'export/warning-input-3',
                '[app|second] on line 3, column 3 (en) is not valid. It will be skipped.'
            ],
        ];
    }
}