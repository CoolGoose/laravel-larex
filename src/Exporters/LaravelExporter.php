<?php

namespace Lukasss93\Larex\Exporters;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Lukasss93\Larex\Console\LarexExportCommand;
use Lukasss93\Larex\Contracts\Exporter;
use Lukasss93\Larex\Support\CsvParser;
use Lukasss93\Larex\Support\Utils;

class LaravelExporter implements Exporter
{
    /**
     * @inheritDoc
     */
    public static function description(): string
    {
        return 'Export data from CSV to Laravel localization files';
    }

    /**
     * @inheritDoc
     */
    public function handle(LarexExportCommand $command, Collection $rows): int
    {
        $parser=new CsvParser($rows);
        $languages = $parser->parse();

        foreach ($parser->getWarnings() as $warning){
            $command->warn($warning);
        }

        $include = $command->option('include') !== null ? (explode(',', $command->option('include'))) : [];
        $exclude = $command->option('exclude') !== null ? explode(',', $command->option('exclude')) : [];

        //finally save the files
        $found = 0;
        foreach ($languages as $language => $groups) {
            if (count($include) > 0 && !in_array($language, $include, true)) {
                continue;
            }
            if (count($exclude) > 0 && in_array($language, $exclude, true)) {
                continue;
            }
            $found++;

            if (!File::exists(resource_path("lang/$language/"))) {
                File::makeDirectory(resource_path("lang/$language/"));
            }

            foreach ($groups as $group => $keys) {
                $write = fopen(resource_path("lang/$language/$group.php"), 'wb');
                fwrite($write, "<?php\n\nreturn [\n\n");

                foreach ($keys as $key => $value) {
                    Utils::writeKeyValue($key, $value, $write);
                }

                fwrite($write, "\n];\n");

                fclose($write);
                $command->info("resources/lang/$language/$group.php created successfully.");
            }
        }

        if ($found === 0) {
            $command->info('No entries exported.');
        }

        return 0;
    }
}
