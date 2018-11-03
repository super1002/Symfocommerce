<?php

namespace Eshop\FixturesBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Eshop\FixturesBundle\DataFixtures\FixturesProviderTrait;
use Eshop\ShopBundle\Entity\Slide;

class SlideFixtures extends AbstractFixture
{
    use FixturesProviderTrait;

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 2; $i++) {
            $slide = new Slide();
            $slide->setName('slide' . $i);
            $slide->setImage($slide->getName() . '.jpg');
            $slide->setSlideOrder($i);
            $slide->setEnabled(true);
            $manager->persist($slide);
        }

        $manager->flush();
    }
}
