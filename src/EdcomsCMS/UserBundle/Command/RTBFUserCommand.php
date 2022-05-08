<?php

namespace EdcomsCMS\UserBundle\Command;


use EdcomsCMS\UserBundle\Service\RTBFUserServiceInterface;
use Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Helper\Table;

class RTBFUserCommand extends ContainerAwareCommand {

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
            ->setName('edcoms:cms_user:rtbf')
            ->setDescription('Hash personal data of a user. Apply RTBF when needed.')
            ->addOption('fileSource', null, InputOption::VALUE_OPTIONAL,'CSV file to read users from and apply RTBF')
            ->addOption('userID', null, InputOption::VALUE_OPTIONAL,'Single user to apply RTBF.')
            ->addOption('userIdentifierType', null, InputOption::VALUE_OPTIONAL,'Defines the passed user identifier type. It can be "id" or "email". Default value: "id"', "id")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try{
            // Validate command options
            $identifierTypeParam = $input->getOption('userIdentifierType');
            if(!in_array($identifierTypeParam, $this->RTBFService->getUserIdentifierTypes())){
                throw new \Exception(sprintf("The 'userIdentifierType' option is not valid. Allowed types are %s", implode(', ',$this->RTBFService->getUserIdentifierTypes())));
            }

            $userIDs = [];
            $usersToRTBF = [];

            // Gather User IDs either from a file or by the userID option
            $fileSourceParam = $input->getOption('fileSource');
            if($fileSourceParam){
                $userIDs = $this->getUserIDsFromCSV($fileSourceParam);
            }else{
                $userParam = $input->getOption('userID');
                $userIDs[] = $userParam;
            }

            // Retrieve users and add them in an array
            foreach ($userIDs as $userID){
                $user = $this->RTBFService->findUser($userID, $identifierTypeParam);
                if($user){
                    $usersToRTBF[] = $user;
                }
            }

            if(count($usersToRTBF)==0){
                $output->writeln(sprintf('<comment>No users found</comment>'));
                return ;
            }

            // Display confirmation dialog
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion(sprintf("Users found: %s.  Continue with the RTBF process? (Y/n): ", count($usersToRTBF)), false);

            if (!$helper->ask($input, $output, $question)) {
                return;
            }

            $rtbfAppliedUsersCount = 0;
            $rtbfNotAppliedUsersCount = 0;

            // Loop through users to apply RTBF
            foreach ($usersToRTBF as $userToRTBF){
                /** @var \EdcomsCMS\UserBundle\Entity\User $userToRTBF */
                $username = $userToRTBF->getUsername();
                if($this->RTBFService->isRTBFAllowed($userToRTBF)){

                    // Display User overview
                    $overviewData = $this->RTBFService->getUserOverview($userToRTBF);
                    $this->displayTable($output, ['User properties', ''], $overviewData);

                    // Display RTBF actions overview
                    $actionsOverviewData = $this->RTBFService->getRTBFActionsOverview($userToRTBF);
                    $this->displayTable($output, ['Actions to perform', ''], $actionsOverviewData);

                    // Display confirmation dialog for the underlying user
                    $confirmRTBFQuestion = new ConfirmationQuestion(sprintf("RTBF user with email '%s'? (Y/n): ", $username), false);
                    if (!$helper->ask($input, $output, $confirmRTBFQuestion)) {
                        $rtbfNotAppliedUsersCount++;
                        continue;
                    }

                    if($this->RTBFService->applyRTBF($userToRTBF)){
                        $output->writeln(sprintf('<info>User with email "%s" has been forgotten.</info>', $username));
                        $rtbfAppliedUsersCount++;
                    }else{
                        $output->writeln(sprintf('<comment>RTBF failed for user with email "%s"</comment>', $username));
                        $rtbfNotAppliedUsersCount++;
                    }
                }else{
                    $output->writeln(sprintf('<comment>RTBF is not allowed to user with email "%s"</comment>', $username));
                    $rtbfNotAppliedUsersCount++;
                }
            }
            if($rtbfAppliedUsersCount+$rtbfNotAppliedUsersCount>1){
                $formatter = $this->getHelper('formatter');
                $formattedLine = $formatter->formatSection(
                    'Summary',
                    sprintf('Successful RTBF applied to %s users. RTBF not applied to %s users', $rtbfAppliedUsersCount, $rtbfNotAppliedUsersCount)
                );
                $output->writeln($formattedLine);
            }

        }catch (\Exception $exception){
            $output->writeln(sprintf('<error>There was an error while RTBF users with error "%s"</error>', $exception->getMessage()));
//            $this->logger->addError(sprintf('There was an error while RTBF users with error "%s"', $exception->getMessage()));
        }
    }

    /**
     * @param string $csvPath Placed in var directory
     *
     * @return array
     *
     * @throws \Exception
     */
    private function getUserIDsFromCSV($csvPath){
        $userIds = [];
        $varDirectory = sprintf('%s/../var',$this->getContainer()->get('kernel')->getRootDir());
        $csvAbsolutePath = sprintf('%s/%s',$varDirectory, $csvPath);
        $fileSystem = new Filesystem();
        if(!$fileSystem->exists($csvAbsolutePath)){
            throw new RuntimeException(sprintf('CSV path "%s"is not valid',$csvPath));
        }
        $row = 0;
        ini_set("auto_detect_line_endings", true);
        if (($handle = fopen($csvAbsolutePath, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 20000000, ",")) !== FALSE) {
                $row++;
                if(count($data)>0){
                    $userIds[] = (int) $data[0];
                }

            }
            fclose($handle);
        }

        return $userIds;
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param $headers
     * @param $data
     */
    private function displayTable(OutputInterface $output, $headers, $data){
        $formattedRows = [];
        foreach ($data as $key => $od){
            $formattedRows[] = [$key, $od];
        }

        $overviewTable = new Table($output);

        $overviewTable
            ->setHeaders($headers)
            ->setRows($formattedRows)
        ;
        $overviewTable->render();
    }

}
