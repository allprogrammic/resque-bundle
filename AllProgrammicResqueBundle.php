<?php

/*
 * This file is part of the AllProgrammic ResqueBunde package.
 *
 * (c) AllProgrammic SAS <contact@allprogrammic.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AllProgrammic\Bundle\ResqueBundle;

use AllProgrammic\Bundle\ResqueBundle\DependencyInjection\ResqueExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class AllProgrammicResqueBundle extends Bundle
{
    public function getContainerExtension()
    {
        if (null === $this->extension) {
            $this->extension = new ResqueExtension();
        }

        return $this->extension;
    }
}
