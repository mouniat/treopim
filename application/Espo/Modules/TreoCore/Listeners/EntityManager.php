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

namespace Espo\Modules\TreoCore\Listeners;

/**
 * EntityManager listener
 *
 * @author r.ratsun <r.ratsun@zinitsolutions.com>
 */
class EntityManager extends AbstractListener
{
    /**
     * @var array
     */
    protected $scopesConfig = null;

    /**
     * Before update action
     *
     * @param array $data
     *
     * @return void
     */
    public function beforeUpdate(array $data)
    {
        // update scopes
        $this
            ->getContainer()
            ->get('metadata')
            ->set('scopes', $data['name'], $this->getPreparedScopesData($data['data']));
    }

    /**
     * Get prepared scopes data
     *
     * @param array $data
     *
     * @return array
     */
    protected function getPreparedScopesData(array $data): array
    {
        $scopeData = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $this->getScopesConfig()['edited'])) {
                $scopeData[$key] = $value;
            }
        }

        return $scopeData;
    }

    /**
     * Get scopes config
     *
     * @return array
     */
    protected function getScopesConfig(): array
    {
        if (is_null($this->scopesConfig)) {
            // prepare result
            $this->scopesConfig = [];

            foreach ($this->getContainer()->get('metadata')->getModuleList() as $module) {
                // prepare file
                $file = sprintf('application/Espo/Modules/%s/Configs/Scopes.php', $module);

                if (file_exists($file)) {
                    $this->scopesConfig = array_merge_recursive($this->scopesConfig, include $file);
                }
            }
        }

        return $this->scopesConfig;
    }
}