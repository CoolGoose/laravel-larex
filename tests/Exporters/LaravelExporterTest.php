<?php

namespace Lukasss93\Larex\Tests\Exporters;

use Illuminate\Support\Facades\File;
use Lukasss93\Larex\Tests\TestCase;

class LaravelExporterTest extends TestCase
{
    public function test_exporter(): void
    {
        $this->initFromStub('exporters/laravel/base/input');

        $result = $this->artisan('larex:export')
            ->expectsOutput("Processing the '$this->file' file...")
            ->expectsOutput("resources/lang/en/app.php created successfully.")
            ->expectsOutput("resources/lang/en/special.php created successfully.")
            ->expectsOutput("resources/lang/it/app.php created successfully.")
            ->expectsOutput("resources/lang/it/special.php created successfully.")
            ->run();

        self::assertFileExists(resource_path('lang/en/app.php'));
        self::assertFileExists(resource_path('lang/en/special.php'));
        self::assertFileExists(resource_path('lang/it/app.php'));
        self::assertFileExists(resource_path('lang/it/special.php'));

        self::assertEquals(
            $this->getTestStub('exporters/laravel/base/output-en-app'),
            File::get(resource_path('lang/en/app.php'))
        );

        self::assertEquals(
            $this->getTestStub('exporters/laravel/base/output-en-special'),
            File::get(resource_path('lang/en/special.php'))
        );

        self::assertEquals(
            $this->getTestStub('exporters/laravel/base/output-it-app'),
            File::get(resource_path('lang/it/app.php'))
        );

        self::assertEquals(
            $this->getTestStub('exporters/laravel/base/output-it-special'),
            File::get(resource_path('lang/it/special.php'))
        );

        self::assertEquals(0, $result);
    }

    public function test_exporter_with_watch(): void
    {
        $this->initFromStub('exporters/laravel/base/input');

        $result = $this->artisan('larex:export --watch')
            ->expectsOutput("Processing the '$this->file' file...")
            ->expectsOutput("resources/lang/en/app.php created successfully.")
            ->expectsOutput("resources/lang/en/special.php created successfully.")
            ->expectsOutput("resources/lang/it/app.php created successfully.")
            ->expectsOutput("resources/lang/it/special.php created successfully.")
            ->expectsOutput('Waiting for changes...')
            ->run();

        self::assertFileExists(resource_path('lang/en/app.php'));
        self::assertFileExists(resource_path('lang/en/special.php'));
        self::assertFileExists(resource_path('lang/it/app.php'));
        self::assertFileExists(resource_path('lang/it/special.php'));

        self::assertEquals(
            $this->getTestStub('exporters/laravel/base/output-en-app'),
            File::get(resource_path('lang/en/app.php'))
        );

        self::assertEquals(
            $this->getTestStub('exporters/laravel/base/output-en-special'),
            File::get(resource_path('lang/en/special.php'))
        );

        self::assertEquals(
            $this->getTestStub('exporters/laravel/base/output-it-app'),
            File::get(resource_path('lang/it/app.php'))
        );

        self::assertEquals(
            $this->getTestStub('exporters/laravel/base/output-it-special'),
            File::get(resource_path('lang/it/special.php'))
        );

        self::assertEquals(0, $result);
    }

    public function test_exporter_with_include(): void
    {
        $this->initFromStub('exporters/laravel/include-exclude/input');

        $result = $this->artisan('larex:export --include=en')
            ->expectsOutput("Processing the '$this->file' file...")
            ->expectsOutput("resources/lang/en/app.php created successfully.")
            ->expectsOutput("resources/lang/en/another.php created successfully.")
            ->run();

        self::assertFileExists(resource_path('lang/en/app.php'));
        self::assertFileExists(resource_path('lang/en/another.php'));
        self::assertFileDoesNotExist(resource_path('lang/it/app.php'));
        self::assertFileDoesNotExist(resource_path('lang/it/another.php'));

        self::assertEquals(
            $this->getTestStub('exporters/laravel/include-exclude/output-en-app'),
            File::get(resource_path('lang/en/app.php'))
        );

        self::assertEquals(
            $this->getTestStub('exporters/laravel/include-exclude/output-en-another'),
            File::get(resource_path('lang/en/another.php'))
        );

        self::assertEquals(0, $result);
    }

    public function test_exporter_with_exclude(): void
    {
        $this->initFromStub('exporters/laravel/include-exclude/input');

        $result = $this->artisan('larex:export --exclude=en')
            ->expectsOutput("Processing the '$this->file' file...")
            ->expectsOutput("resources/lang/it/app.php created successfully.")
            ->expectsOutput("resources/lang/it/another.php created successfully.")
            ->run();

        self::assertFileDoesNotExist(resource_path('lang/en/app.php'));
        self::assertFileDoesNotExist(resource_path('lang/en/another.php'));
        self::assertFileExists(resource_path('lang/it/app.php'));
        self::assertFileExists(resource_path('lang/it/another.php'));

        self::assertEquals(
            $this->getTestStub('exporters/laravel/include-exclude/output-it-app'),
            File::get(resource_path('lang/it/app.php'))
        );

        self::assertEquals(
            $this->getTestStub('exporters/laravel/include-exclude/output-it-another'),
            File::get(resource_path('lang/it/another.php'))
        );

        self::assertEquals(0, $result);
    }
    
    public function test_exporter_with_warning(): void
    {
        $this->initFromStub('exporters/laravel/warnings/input');

        $result = $this->artisan('larex:export -v')
            ->expectsOutput("Processing the '$this->file' file...")
            ->expectsOutput('Invalid row at line 3. The row will be skipped.')
            ->expectsOutput('Missing key name at line 4. The row will be skipped.')
            ->expectsOutput('app.second at line 5, column 3 (en) is missing. It will be skipped.')
            ->expectsOutput('resources/lang/en/app.php created successfully.')
            ->expectsOutput('resources/lang/it/app.php created successfully.')
            ->run();

        self::assertFileExists(resource_path('lang/en/app.php'));
        self::assertFileExists(resource_path('lang/it/app.php'));

        self::assertEquals(
            $this->getTestStub('exporters/laravel/warnings/output-it'),
            File::get(resource_path('lang/it/app.php'))
        );

        self::assertEquals(0, $result);
    }

    public function test_exporter_with_no_entries(): void
    {
        $this->artisan('larex:init')->run();

        $result = $this->artisan('larex:export')
            ->expectsOutput("Processing the '$this->file' file...")
            ->expectsOutput('No entries exported.')
            ->run();

        self::assertEquals(0, $result);
    }
}