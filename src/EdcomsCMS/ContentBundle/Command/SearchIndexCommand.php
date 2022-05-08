<?php

namespace EdcomsCMS\ContentBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use EdcomsCMS\ContentBundle\Entity\Content;
use EdcomsCMS\ContentBundle\Entity\Structure;
use EdcomsCMS\ContentBundle\Helpers\SearchHelper;

class SearchIndexCommand extends ContainerAwareCommand
{
    /**
   
     * @var SearchHelper
     */
    protected $search;
    /**
     *
     * @var OutputInterface
     */
    protected $output;
    protected function configure()
    {
        $this
            ->setName('search:index')
            ->setDescription('Add content to the search index')
//            ->addOption('option', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->search = $this->getContainer()->get('SearchHelper');
        $this->indexContent();
        $this->removeIndex();
        $this->search->commit();
    }
    private function indexContent()
    {
        $doctrine = $this->getContainer()->get('doctrine');
        $em = $doctrine->getManager('edcoms_cms');
        $content = $em->getRepository('EdcomsCMSContentBundle:Content');
        $items = $content->findByStatusAndVisibility('published',true);
        if ($items) {
            array_walk($items, function($item) {
                $this->indexItem('content', $item);
            });
        }
    }
    private function removeIndex()
    {
        $doctrine = $this->getContainer()->get('doctrine');
        $em = $doctrine->getManager('edcoms_cms');
        $structures = $em->getRepository('EdcomsCMSContentBundle:Structure');
        $structuresEl = $structures->findAllAndKeys(true);
        $structureKeys = $structuresEl['keys'];
        $content = $em->getRepository('EdcomsCMSContentBundle:Content');
        $items = $content->findBy(['structure'=>$structureKeys]);
        if ($items) {
            array_walk($items, function($item) {
                $this->removeIndexItem($item->getId());
            });
        }
    }
    public function indexItem($type, $item)
    {
        $this->output->writeln($this->search->indexItem($type, $item));
    }
    public function removeIndexItem($ID)
    {
        $this->output->writeln($this->search->removeIndexItem($ID));
    }
}
