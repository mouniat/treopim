<?php
/**
 * This file is part of EspoCRM and/or TreoPIM.
 *
 * EspoCRM - Open Source CRM application.
 * Copyright (C) 2014-2018 Yuri Kuznetsov, Taras Machyshyn, Oleksiy Avramenko
 * Website: http://www.espocrm.com
 *
 * TreoPIM is EspoCRM-based Open Source Product Information Management application.
 * Copyright (C) 2017-2018 Zinit Solutions GmbH
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

use Espo\ORM\Entity;

/**
 * Class QueueItem
 *
 * @author r.ratsun@zinitsolutions.com
 */
class QueueItem extends \Espo\Core\Templates\Services\Base
{
    /**
     * @var array|null
     */
    private $jobs = null;

    /**
     * @var array
     */
    private $services = [];

    /**
     * @inheritdoc
     */
    public function prepareEntityForOutput(Entity $entity)
    {
        parent::prepareEntityForOutput($entity);

        // load jobs
        $this->loadJobs();

        // prepare entity
        $entity->set('status', $this->getItemStatus($entity));
        $entity->set('actions', $this->getItemActions($entity));
    }

    /**
     * @inheritdoc
     */
    protected function init()
    {
        parent::init();

        $this->addDependency('serviceFactory');
    }

    /**
     * Load jobs
     */
    protected function loadJobs(): void
    {
        if (is_null($this->jobs)) {
            $this->jobs = [];

            $jobs = $this
                ->getEntityManager()
                ->getRepository('Job')
                ->where(['queueItemId!=' => null])
                ->find();

            if (!empty($jobs)) {
                foreach ($jobs as $job) {
                    $this->jobs[$job->get('queueItemId')] = $job;
                }
            }
        }
    }

    /**
     * @param Entity $entity
     *
     * @return string
     */
    protected function getItemStatus(Entity $entity): string
    {
        $status = 'Pending';
        if (!empty($this->jobs[$entity->get('id')])) {
            $status = $this->jobs[$entity->get('id')]->get('status');
            if ($status == 'Pending') {
                $status = 'Running';
            }
        }

        return $status;
    }

    /**
     * @param Entity $entity
     *
     * @return array
     */
    protected function getItemActions(Entity $entity): array
    {
        if (empty($serviceName = $entity->get('serviceName'))) {
            return [];
        }

        // create service
        if (!isset($this->services[$serviceName])) {
            $this->services[$serviceName] = $this->getInjection('serviceFactory')->create($serviceName);
        }

        // prepare methodName
        $methodName = "get" . $entity->get('status') . "StatusActions";

        return $this->services[$serviceName]->$methodName($entity);
    }
}
