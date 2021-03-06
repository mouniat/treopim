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

namespace Treo\Listeners;

/**
 * Class Job
 *
 * @author r.ratsun <r.ratsun@zinitsolutions.com>
 */
class Job extends AbstractListener
{
    /**
     * @param array $data
     *
     * @return array
     */
    public function beforeSave(array $data): array
    {
        // prepare data
        $entity = $data['entity'];
        $options = $data['options'];

        // set scheduledJobId to data
        if (!empty($scheduledJobId = $entity->get('scheduledJobId'))) {
            $entity->set('targetType', 'ScheduledJob');
            $entity->set('targetId', $scheduledJobId);
        }

        // skip saving for Stream action
        if ($entity->get('serviceName') == 'Stream' && $entity->get('methodName') == 'controlFollowersJob') {
            // for skip saving
            $entity->setIsSaved(true);

            // call service method
            $this->controlFollowersJob($entity->get('data'));
        }

        return ['entity' => $entity, 'options' => $options];
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function afterRemove(array $data): array
    {
        // prepare data
        $entity = $data['entity'];
        $options = $data['options'];

        // for queueItem
        if (!empty($item = $entity->get('queueItem'))) {
            $this->getEntityManager()->removeEntity($item, ['force' => true]);
        }

        return ['entity' => $entity, 'options' => $options];
    }

    /**
     * @param array $data
     */
    protected function controlFollowersJob(array $data): void
    {
        // prepare input
        $input = new \stdClass();
        $input->entityId = $data['entityId'];
        $input->entityType = $data['entityType'];

        $this->getContainer()->get('serviceFactory')->create('Stream')->controlFollowersJob($input);
    }
}
