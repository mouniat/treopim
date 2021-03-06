<?php
/**
 * This file is part of EspoCRM and/or TreoPIM.
 *
 * EspoCRM - Open Source CRM application.
 * Copyright (C) 2014-2019 Yuri Kuznetsov, Taras Machyshyn, Oleksiy Avramenko
 * Website: http://www.espocrm.com
 *
 * TreoPIM is EspoCRM-based Open Source Product Information Management application.
 * Copyright (C) 2017-2019 TreoLabs GmbH
 * Website: http://www.treopim.com
 *
 * TreoPIM as well as EspoCRM is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * TreoPIM as well as EspoCRM is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with EspoCRM. If not, see http://www.gnu.org/licenses/.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "EspoCRM" word
 * and "TreoPIM" word.
 */

declare(strict_types=1);

namespace Treo\Console;

/**
 * Events console
 *
 * @author r.ratsun@zinitsolutions.com
 */
class Events extends AbstractConsole
{
    /**
     * Get console command description
     *
     * @return string
     */
    public static function getDescription(): string
    {
        return 'Show all triggered events.';
    }

    /**
     * Run action
     *
     * @param array $data
     */
    public function run(array $data): void
    {
        if (!empty($files = $this->getDirFiles('application/Espo'))) {
            $classes = [];
            $tmp = [];
            foreach ($files as $file) {
                $parsed = $this->parseFile($file);
                if (!empty($parsed)) {
                    // prepare classname
                    $className = str_replace(
                        [CORE_PATH . '/application/', '/', '.php'],
                        ['', '\\', ''],
                        $file
                    );

                    $classes[] = $className;

                    foreach ($parsed as $row) {
                        $tmp[$className] = [
                            $className,
                            $row['target'],
                            $row['action'],
                            $row['data']
                        ];
                    }
                }
            }

            // sorting
            natsort($classes);

            // prepare result
            foreach ($classes as $class) {
                $result[] = $tmp[$class];
            }
        }

        // render
        self::show('Triggered events:', self::INFO);
        echo self::arrayToTable($result, ['CLASS NAME', 'TARGET', 'ACTION', 'DATA']);
    }

    /**
     * Parse file
     *
     * @param string $fileName
     *
     * @return array
     */
    protected function parseFile(string $fileName): array
    {
        // prepare result
        $result = [];

        if (file_exists($fileName)) {
            $content = file_get_contents($fileName);
            if (!empty($content)) {
                $content = str_replace(array("\r", "\n"), "", $content);
                if (preg_match_all("/->triggered\((.*?),(.*?),(.*?)\)/", $content, $matches)) {
                    if (!empty($matches[1]) && !empty($matches[2]) && !empty($matches[3])) {
                        foreach ($matches[1] as $k => $row) {
                            $result[] = [
                                'target' => str_replace(' ', '', $matches[1][$k]),
                                'action' => str_replace(' ', '', $matches[2][$k]),
                                'data'   => str_replace(' ', '', $matches[3][$k])
                            ];
                        }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Get dir files
     *
     * @param string $dir
     * @param array  $results
     *
     * @return array
     */
    protected function getDirFiles(string $dir, &$results = []): array
    {
        if (is_dir($dir)) {
            $files = scandir($dir);
            foreach ($files as $key => $value) {
                $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
                if (!is_dir($path) && preg_match('/^.*.php$/', $path)) {
                    $results[] = $path;
                } else {
                    if ($value != "." && $value != "..") {
                        $this->getDirFiles($path, $results);
                    }
                }
            }
        }

        return $results;
    }
}
