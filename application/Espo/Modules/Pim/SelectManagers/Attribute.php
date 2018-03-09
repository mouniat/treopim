<?php
/**
 * This file is part of EspoCRM and/or TreoPIM.
 *
 * EspoCRM - Open Source CRM application.
 * Copyright (C) 2014-2018 Yuri Kuznetsov, Taras Machyshyn, Oleksiy Avramenko
 * Website: http://www.espocrm.com
 *
 * TreoPIM ist Open Source Product Information Managegement (PIM) application,
 * based on EspoCRM.
 * Copyright (C) 2017-2018 Zinit Solutions GmbH
 * Website: http://www.treopim.com
 *
 * TreoPIM as well es EspoCRM is free software: you can redistribute it and/or modify
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

namespace Espo\Modules\Pim\SelectManagers;

use Espo\Modules\Pim\Core\SelectManagers\AbstractSelectManager;

/**
 * Class of Attribute
 *
 * @author r.ratsun <r.ratsun@zinitsolutions.com>
 */
class Attribute extends AbstractSelectManager
{

    /**
     * NotLinkedWithProduct filter
     *
     * @param array $result
     */
    protected function boolFilterNotLinkedWithProduct(&$result)
    {
        // prepare data
        $productId = (string)$this->getSelectCondition('notLinkedWithProduct');

        foreach ($this->createService('Product')->getAttributes($productId) as $row) {
            $result['whereClause'][] = [
                'id!=' => $row['attributeId']
            ];
        }
    }

    /**
     * NotLinkedWithProductFamily filter
     *
     * @param array $result
     */
    protected function boolFilterNotLinkedWithProductFamily(&$result)
    {
        // prepare data
        $productFamilyId = (string)$this->getSelectCondition('notLinkedWithProductFamily');

        foreach ($this->getProductFamilyAttributes($productFamilyId) as $row) {
            $result['whereClause'][] = [
                'id!=' => $row['attribute_id']
            ];
        }
    }

    /**
     * Get product family attributes
     *
     * @param string $productFamilyId
     *
     * @return array
     */
    protected function getProductFamilyAttributes($productFamilyId)
    {
        $pdo = $this->getEntityManager()->getPDO();

        $sql = 'SELECT
          attribute_id
        FROM
          product_family_attribute
        WHERE
          product_family_id =' . $pdo->quote($productFamilyId) . '
          AND deleted = 0';
        $sth = $pdo->prepare($sql);
        $sth->execute();

        return $sth->fetchAll(\PDO::FETCH_ASSOC);
    }
}
