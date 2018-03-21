<?php

namespace App\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProduceCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('app:produce')->addArgument('name')->addArgument('code');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $msg = array('name' => $input->getArgument('name'), 'code' => $input->getArgument('code'));
        $this->getContainer()->get('old_sound_rabbit_mq.sulu_sylius_producer')->publish(serialize($msg));
    }
}
