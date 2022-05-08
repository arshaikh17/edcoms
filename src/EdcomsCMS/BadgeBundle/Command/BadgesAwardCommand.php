<?php

namespace EdcomsCMS\BadgeBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BadgesAwardCommand extends ContainerAwareCommand
{
    /**
     * @var BadgeHelper
     */
    protected $badgeHelper;

    /**
     *
     * @var OutputInterface
     */
    protected $output;

    protected function configure()
    {
        $this
            ->setName('badges:award')
            ->setDescription('Assess and award all site badges')
            ->addOption('recalculate',
                null,
                InputOption::VALUE_NONE,
                'Set to true to delete previous badge awards before commencing assessment of badge criteria');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $recalculate = $input->getOption('recalculate');
        $this->badgeHelper = $this->getContainer()->get('badge_helper');
        $awards = $this->badgeHelper->awardAllBadges($recalculate);

        if (count($awards) > 0) {

            foreach ($awards as $k => $award) {
                $output->writeln('Processing badge id '.$k.' - Response: '. $award);
            }
        } else {
            $output->writeln('no active badges found');
        }
    }
}
