<?php

namespace LearnToWin\GeneralBundle;

use LearnToWin\GeneralBundle\DependencyInjection\GeneralExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class GeneralBundle extends AbstractBundle
{
    /**
     * We have to override this to get the correct extension class, symfony gets the wrong one.
     */
    public function getContainerExtension(): ?ExtensionInterface
    {
        if (null === $this->extension) {
            $this->extension = new GeneralExtension();
        }

        return $this->extension;
    }
}
