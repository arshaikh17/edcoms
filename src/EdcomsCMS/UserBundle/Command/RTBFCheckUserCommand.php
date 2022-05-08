<?php

namespace EdcomsCMS\UserBundle\Command;


use Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use EdcomsCMS\UserBundle\Service\RTBFUserServiceInterface;

class RTBFCheckUserCommand extends ContainerAwareCommand {

    /**
     * @var \EdcomsCMS\UserBundle\Service\RTBFUserService
     */
    private $RTBFService;

    /**
     * @var \Monolog\Logger
     */
    private $logger;

    public function __construct(RTBFUserServiceInterface $RTBFService, Logger $logger) {
        $this->RTBFService = $RTBFService;
        $this->logger = $logger;
        parent::__construct();
    }

    protected function configure() {
        $this
            ->setName('edcoms:cms_user:rtbf_check')
            ->setDescription('Check if RTBF has been applied to a user')
            ->addArgument('userEmail', InputArgument::REQUIRED, 'User\'s email to check.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try{
            $userEmail = $input->getArgument('userEmail');
            if($rtbfRecord = $this->RTBFService->isRTBFApplied($userEmail)){
                $output->writeln(sprintf(sprintf('<info>RTBF has been applied to user with email "%s" on the %s</info>',$userEmail, $rtbfRecord->getCreatedOn()->format('d/m/y, H:i'))));
            }else{
                $output->writeln(sprintf(sprintf('<comment>RTBF hasn\'t been applied to user with email "%s"</comment>',$userEmail)));
            }
        }catch (\Exception $exception){
            $output->writeln(sprintf('<error>There was an error while checking whether RTBF has been applied to user with error "%s"</error>', $exception->getMessage()));
        }
    }
}