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

namespace Treo\Services;

use Espo\Core\CronManager;
use Treo\Core\UpgradeManager;
use Treo\Core\Utils\Mover;
use Treo\Core\Migration\Migration;

/**
 * Service TreoUpgrade
 *
 * @author r.ratsun r.ratsun@zinitsolutions.com
 */
class TreoUpgrade extends AbstractService
{
    /**
     * @var string
     */
    private $packagesPath = "data/upload/upgrades";

    /**
     * @var null|array
     */
    private $versions = null;

    /**
     * Get available versions
     *
     * @return array
     */
    public function getVersions(): array
    {
        if (is_null($this->versions)) {
            // prepare path
            $path = $this->getDomain() . "/api/v1/Packages/" . $this->getCurrentVersion();
            if ($this->isDevelopMod()) {
                $path .= '?dev=1';
            }

            $this->versions = $this->readJsonData($path);
        }

        return $this->versions;
    }

    /**
     * Create upgrade job
     *
     * @param string|null $to
     *
     * @return bool
     * @throws \Espo\Core\Exceptions\Error
     */
    public function createUpgradeJob(string $to = null): bool
    {
        // prepare available versions
        $versions = array_column($this->getVersions(), 'link', 'version');

        $from = $this->getCurrentVersion();

        // prepare version to
        if (is_null($to)) {
            $to = end(array_keys($versions));
        }

        if (!isset($versions[$to]) || empty($package = $this->downloadPackage($versions[$to]))) {
            return false;
        }

        // create job
        $jobEntity = $this->getEntityManager()->getEntity('Job');
        $jobEntity->set(
            [
                'name'        => 'run-treo-update',
                'status'      => CronManager::PENDING,
                'executeTime' => (new \DateTime())->format('Y-m-d H:i:s'),
                'serviceName' => 'TreoUpgrade',
                'method'      => 'runUpgradeJob',
                'data'        => [
                    'versionFrom' => $from,
                    'versionTo'   => $to,
                    'fileName'    => $package,
                    'createdById' => $this->getUser()->get('id')
                ]
            ]
        );
        $this->getEntityManager()->saveEntity($jobEntity);

        return true;
    }

    /**
     * Run upgrade core job
     *
     * @param array $data
     */
    public function runUpgradeJob(array $data): void
    {
        if (!empty($versionFrom = $data['versionFrom'])
            && !empty($versionTo = $data['versionTo'])
            && !empty($fileName = $data['fileName'])) {
            // upgrade treocore
            $upgradeManager = new UpgradeManager($this->getContainer());
            $upgradeManager->install(['id' => $fileName]);

            // call migration
            $this
                ->getContainer()
                ->get('migration')
                ->run(Migration::CORE_NAME, $data['versionFrom'], $data['versionTo']);
        }
    }

    /**
     * Download package
     *
     * @param string $link
     *
     * @return null|string
     */
    public function downloadPackage(string $link): ?string
    {
        // parse link
        $matches = explode("/", $link);

        // prepare name
        $name = str_replace(".zip", "", end($matches));

        // clearing cache
        if (file_exists($this->packagesPath)) {
            Mover::deleteDir($this->packagesPath);
        }

        // create upgrade dir
        mkdir($this->packagesPath, 0777, true);

        // prepare extract dir
        $extractDir = $this->packagesPath . "/{$name}";

        // prepare zip name
        $zipName = $this->packagesPath . "/{$name}.zip";

        // download
        file_put_contents($zipName, fopen($link, 'r'));

        // create extract dir
        mkdir($extractDir, 0777, true);

        $zip = new \ZipArchive();
        $res = $zip->open($zipName);
        if ($res === true) {
            $zip->extractTo($extractDir);
            $zip->close();

            // delete archive
            unlink($zipName);
        }

        return $name;
    }

    /**
     * Send notification about new version of core
     */
    public function notify(): void
    {
        // max version
        $version = array_pop(array_column($this->getVersions(), 'version'));

        if ($version != $this->getConfig()->get('notifiedVersion')) {
            // create notification(s)
            $this->notifyAboutNewVersion($version);

            // update config
            $this->getConfig()->set('notifiedVersion', $version);
            $this->getConfig()->save();
        }
    }

    /**
     * @return string
     */
    protected function getCurrentVersion(): string
    {
        return $this->getConfig()->get('version');
    }

    /**
     * @return string
     */
    protected function getDomain(): string
    {
        return $this->readJsonData('composer.json')['extra']['treo-source'];
    }

    /**
     * @param string $path
     *
     * @return array
     */
    protected function readJsonData(string $path): array
    {
        return json_decode(file_get_contents($path), true);
    }

    /**
     * @return bool
     */
    protected function isDevelopMod(): bool
    {
        return !empty($this->getConfig()->get('developMode'));
    }

    /**
     * Notify about new version
     *
     * @param string $version
     */
    protected function notifyAboutNewVersion(string $version): void
    {
        if (!empty($users = $this->getAdminUsers())) {
            // is notification disabled ?
            $isDisabled = $this->getConfig()->get('notificationNewSystemVersionDisabled');

            foreach ($users as $user) {
                // prepare user data
                $data = json_decode($user['data'], true);

                // prepare config key
                $key = 'receiveNewSystemVersionNotifications';

                if ((isset($data[$key]) && $data[$key]) || (!isset($data[$key]) && !$isDisabled)) {
                    // create notification
                    $notification = $this->getEntityManager()->getEntity('Notification');
                    $notification->set(
                        [
                            'type'    => 'Message',
                            'userId'  => $user['id'],
                            'message' => sprintf($this->notification('newCoreVersion'), $version)
                        ]
                    );
                    $this->getEntityManager()->saveEntity($notification);
                }
            }
        }
    }

    /**
     * @return array
     */
    protected function getAdminUsers(): array
    {
        return $this
            ->getEntityManager()
            ->getRepository('User')
            ->getAdminUsers();
    }

    /**
     * @param string $key
     *
     * @return string
     */
    protected function notification(string $key): string
    {
        return $this
            ->getContainer()
            ->get('language')
            ->translate($key, 'treoNotifications', 'TreoNotification');
    }
}
