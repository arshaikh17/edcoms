<?php

namespace EdcomsCMS\ContentBundle\Command;

use EdcomsCMS\BadgeBundle\Entity\BadgeSimple;
use EdcomsCMS\ContentBundle\Entity\ContentCache;
use EdcomsCMS\ContentBundle\Entity\ContentMigrationConfig;
use EdcomsCMS\ContentBundle\Entity\ContentType;
use EdcomsCMS\ContentBundle\Entity\CustomFieldData;
use EdcomsCMS\ContentBundle\Entity\CustomFields;
use EdcomsCMS\ContentBundle\Entity\LinkBuilder;
use EdcomsCMS\ContentBundle\Entity\Media;
use EdcomsCMS\ContentBundle\Entity\MediaFiles;
use EdcomsCMS\ContentBundle\Entity\MediaLinks;
use EdcomsCMS\ContentBundle\Entity\MediaTypes;
use EdcomsCMS\ContentBundle\Entity\TemplateFiles;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use EdcomsCMS\ContentBundle\Entity\Content;
use EdcomsCMS\ContentBundle\Entity\Structure;

class ContentMigrationCommand extends ContainerAwareCommand
{
    protected $originEm;
    protected $destinationEm;

    const BATCH_LIMIT = 500;

    protected function configure()
    {
        $this
            ->setName('migrate:content')
            ->setDescription(
                'Migrate content from current database to destination database.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Migrating content');
        $this->outputCurrentTime($output);
        $output->writeln('=================');

        $this->originEm = $this->getContainer()->get('doctrine')->getManager('edcoms_cms');
        $this->destinationEm = $this->getContainer()->get('doctrine')->getManager('destination_database');

        //not dependent on other db tables
        $output->writeln('');
        $output->writeln('Updating Structures');
        $this->outputCurrentTime($output);
        $output->writeln('===================');
        $this->updateStructures($output);

        //dependent on Structures
        $output->writeln('');
        $output->writeln('Updating LinkBuilders');
        $this->outputCurrentTime($output);
        $output->writeln('===================');
        $this->updateLinkBuilders($output);

        //not dependent on other db tables
        $output->writeln('');
        $output->writeln('Updating ContentCache');
        $this->outputCurrentTime($output);
        $output->writeln('=====================');
        $this->updateContentCache($output);

        //not dependent on other db tables
        $output->writeln('');
        $output->writeln('Updating MediaTypes');
        $this->outputCurrentTime($output);
        $output->writeln('===================');
        $this->updateMediaTypes($output);

        //dependent on MediaTypes indirectly through MediaFiles
        $output->writeln('');
        $output->writeln('Updating Media');
        $this->outputCurrentTime($output);
        $output->writeln('==============');
        $this->updateMedia($output);

        //media files are now handled during the Media update function
        //dependent on media & mediatypes
//        $output->writeln('');
//        $output->writeln('Updating MediaFiles');
//        $this->outputCurrentTime($output);
//        $output->writeln('===================');
//        $this->updateMediaFiles($output);

        //Media links are currently not used but this does need looking at as it references media by id
        //not dependent on other db tables
//        $output->writeln('');
//        $output->writeln('Updating MediaLinks');
//        $this->outputCurrentTime($output);
//        $output->writeln('===================');
//        $this->updateMediaLinks($output);

        //dependent on media
        $output->writeln('');
        $output->writeln('Updating Badges');
        $this->outputCurrentTime($output);
        $output->writeln('===============');
        $this->updateBadges($output);

        //not dependent on other db tables
        $output->writeln('');
        $output->writeln('Updating ContentType');
        $this->outputCurrentTime($output);
        $output->writeln('====================');
        $this->updateContentType($output);

        //not dependent on other db tables
        $output->writeln('');
        $output->writeln('Updating TemplateFiles');
        $this->outputCurrentTime($output);
        $output->writeln('======================');
        $this->updateTemplateFiles($output);

        //dependent on contenttype
        $output->writeln('');
        $output->writeln('Updating Content');
        $this->outputCurrentTime($output);
        $output->writeln('================');
        $this->updateContent($output);

        //dependent on contenttype
        $output->writeln('');
        $output->writeln('Updating CustomFields');
        $this->outputCurrentTime($output);
        $output->writeln('=====================');
        $this->updateCustomFields($output);

        //dependent on content & customfields
        $output->writeln('');
        $output->writeln('Updating CustomFieldData');
        $this->outputCurrentTime($output);
        $output->writeln('========================');
        $this->updateCustomFieldData($output);
        $output->writeln('Migration finished');
        $output->writeln('==================');
        $this->outputCurrentTime($output);
    }

    /**
     * Update CustomFieldData
     * Dependant on content & custom fields being updated first
     *
     * This function only adds new fields and does not update existing entries. Also CustomFieldData should not normally
     * be deleted from origin so destination is not checked for surplus (this is safer given that old entries are not
     * updated). This also a very large table so updating all entries can be very time consuming.
     *
     * The migration process happens in two steps:
     * 1. Any new rows are inserted at the destination. At this point we can't safely add the parent property because
     *    the parent entity may not yet exist.
     * 2. We have to return to either the first inserted row or the row that was last updated (this could be different
     *    if the script failed to finish on the previous run) to add the parent property.
     *
     *
     * @param $output
     */
    private function updateCustomFieldData($output)
    {
        //Get respective repos
        $originCustomFieldDataRepo = $this->originEm->getRepository('EdcomsCMSContentBundle:CustomFieldData');
        $destinationCustomFieldDataRepo = $this->destinationEm->getRepository('EdcomsCMSContentBundle:CustomFieldData');

        //Get the highest ID from origin and destination
        $originHighestId = $this->getHighestEntityId($originCustomFieldDataRepo);
        $output->writeln('originHighestId: '.$originHighestId);
        $destinationHighestId = $this->getHighestEntityId($destinationCustomFieldDataRepo);
        $output->writeln('destinationHighestId: '.$destinationHighestId);

        //this indicates an error as the destination ID should never get higher then the origin
        if ($destinationHighestId > $originHighestId) {
            throw new \UnexpectedValueException('Custom Field Data - Destination ID is: '.
                $destinationHighestId.' and origin ID is: '.$originHighestId.
                '. Destination ID should never be higher than origin.');
        }

        //Set destination meta to allow IDs to be set
        $this->updateDestinationMeta('EdcomsCMSContentBundle:CustomFieldData');

        /////////////////
        // Insert step //
        /////////////////
        //loop while there are entities to insert or the counter has reached the batch limit
        while ($originHighestId > $destinationHighestId) {//Origin ID is higher than destination, do inserts

            //Load in batches
            //Calculate which is smaller, the batch limit or difference between DB tables amd set as limit
            $limit = (($originHighestId - $destinationHighestId) < self::BATCH_LIMIT)?
                ($originHighestId - $destinationHighestId): self::BATCH_LIMIT;
            $output->writeln('Inserts calc limit expr: '.($originHighestId - $destinationHighestId));
            $output->writeln('Insert limit set to: '.$limit);

            //Load entities to insert from origin in batches
            $originCustomFieldDatas = $this->getRangeKeyedById($originCustomFieldDataRepo,
                $destinationHighestId+1, $limit);

            //Store destination entities ready for flushing
            $tempEntityArray = array();

            foreach ($originCustomFieldDatas as $originCustomFieldData) {

                //add a new object to destination array for each insert
                $output->writeln('Creating CustomFieldData id: ' . $originCustomFieldData->getId());
                $destinationCustomFieldData = new CustomFieldData();
                $destinationCustomFieldData->setId($originCustomFieldData->getId());
                $this->destinationEm->persist($destinationCustomFieldData);

                $destinationCustomFieldData->setValue($originCustomFieldData->getValue());
                $destinationCustomFieldData->setAddedOn($originCustomFieldData->getAddedOn());
                $destinationAddedUser = $this->checkUserAtDestination($originCustomFieldData->getAddedUser());
                $destinationCustomFieldData->setAddedUser($destinationAddedUser);

                //check if custom fields set in origin
                if (is_object($originCustomFieldData->getCustomFields())) {
                    $customFieldsRepo = $this->destinationEm->getRepository('EdcomsCMSContentBundle:CustomFields');
                    $customFields = $customFieldsRepo->find($originCustomFieldData->getCustomFields()->getId());

                    if ($customFields) {
                        $destinationCustomFieldData->setCustomFields($customFields);
                    }
                }

                //check if content set in origin
                if (is_object($originCustomFieldData->getContent())) {
                    $contentRepo = $this->destinationEm->getRepository('EdcomsCMSContentBundle:Content');
                    $content = $contentRepo->find($originCustomFieldData->getContent()->getId());

                    if ($content) {
                        $destinationCustomFieldData->setContent($content);
                    }
                }

                //Add new entity to temp array to be flushed after loop finishes
                $tempEntityArray[] = $destinationCustomFieldData;
                //Update the reference to the highest ID at destination
                $destinationHighestId = $destinationCustomFieldData->getId();
            }

            $this->destinationEm->flush();
            //Memory management
            $tempEntityArray = null;

        }

        //memory management
        $originCustomFieldDatas = null;

        /////////////////
        // Update step //
        /////////////////
        //Get the ID of the last updated row (updated with parent ID) from new DB used to track migration progress
        //Only used to track the last updated row (not last inserted row explanation in the function comment above)
        $contentMigrationConfigRepo = $this->originEm->getRepository('EdcomsCMSContentBundle:ContentMigrationConfig');
        $contentMigrationConfig = $contentMigrationConfigRepo->find(1);
        if (!$contentMigrationConfig) {
            $contentMigrationConfig = new ContentMigrationConfig();
            $this->originEm->persist($contentMigrationConfig);
        }

        $lastRunId = $contentMigrationConfig->getLastCFDUpdateParentId();
        //If the last updated ID is null then this is the first run
        if (is_null($lastRunId)) {
            //Set the last run ID to 1
            $lastRunId = 1;
        }

        //If the last run ID is less than the highest ID at destination we have fields to update
        while ($lastRunId < $destinationHighestId) {

            //Load in batches
            //Calculate which is smaller, minus the last run ID from the destination ID
            $limit = (($destinationHighestId - $lastRunId) < self::BATCH_LIMIT)?
                ($destinationHighestId - $lastRunId): self::BATCH_LIMIT;

            $output->writeln('Update calc limit expr: '.($destinationHighestId - $lastRunId));
            $output->writeln('Update limit set to: '.$limit);

            //Load entities from origin to check if parent is set
            $originCustomFieldDatas = $this->getRangeKeyedById($originCustomFieldDataRepo,
                $lastRunId+1, $limit);

            foreach ($originCustomFieldDatas as $originCustomFieldData) {
                //check if parent set in origin
                if (is_object($originCustomFieldData->getParent())) {
                    $destinationParent = $destinationCustomFieldDataRepo->find($originCustomFieldData->getParent()->getId());
                    $destinationCustomFieldData = $destinationCustomFieldDataRepo->find($originCustomFieldData->getId());
                    $destinationCustomFieldData->setParent($destinationParent);

                    $output->writeln('Adding Parent for CustomFieldData id: ' . $destinationCustomFieldData->getId());
                }

                //Update the last run ID
                $lastRunId = $originCustomFieldData->getId();
            }

            //Flush to DB
            $this->destinationEm->flush();
            //Update the config object
            $contentMigrationConfig->setLastCFDUpdateParentId($lastRunId);
            $this->originEm->flush();
        }

        $originCustomFieldDatas = null;
    }

    /**
     * Update CustomFields
     * Dependant on contentType being updated first
     *
     * @param $output
     */
    private function updateCustomFields($output)
    {
        $originCustomFieldsRepo = $this->originEm->getRepository('EdcomsCMSContentBundle:CustomFields');
        $destinationCustomFieldsRepo = $this->destinationEm->getRepository('EdcomsCMSContentBundle:CustomFields');

        //get content types
        $originCustomFields = $this->getAllKeyedById($originCustomFieldsRepo);
        $destinationCustomFields = $this->getAllKeyedById($destinationCustomFieldsRepo);

        if ($originCustomFields) {

            //check for inserts
            $inserts = array_diff_key($originCustomFields, $destinationCustomFields);

            //if more custom fields in origin than destination we have inserts
            if (count($inserts) > 0) {
                $this->updateDestinationMeta('EdcomsCMSContentBundle:CustomFields');

                //loop through inserts
                foreach ($inserts as $k => $insert) {
                    //add a new object to destination array for each insert
                    $output->writeln('Creating CustomField id: ' . $insert->getId());
                    $destinationCustomField = new CustomFields();
                    $destinationCustomField->setId($insert->getId());
                    $this->destinationEm->persist($destinationCustomField);
                    $destinationCustomFields[$insert->getId()] = $destinationCustomField;

                    $this->populateCustomFieldsProperties($insert, $destinationCustomFields[$k]);
                }

                //second loop required to set parent, id may not exist in loop above
                foreach ($inserts as $k => $insert) {
                    //check if parent set in origin
                    if (is_object($insert->getParent())) {
                        $destinationParent = $destinationCustomFields[$insert->getParent()->getId()];
                        $destinationCustomFields[$k]->setParent($destinationParent);
                    }
                }

                //and flush
                $this->destinationEm->flush();

                //remove inserts to avoid processing twice
                $originCustomFields = array_diff_key($originCustomFields, $inserts);
            }

            $counter=0;
            foreach ($originCustomFields as $k => $originCustomField) {
                $counter++;

                $output->writeln('Updating CustomFields id: ' . $originCustomField->getId());
                $this->populateCustomFieldsProperties($originCustomField, $destinationCustomFields[$k]);

                //check if parent set in origin
                if (is_object($originCustomField->getParent())) {
                    $destinationParent = $destinationCustomFields[$originCustomField->getParent()->getId()];
                    $destinationCustomFields[$k]->setParent($destinationParent);
                }

                //batch update database
                if ($counter % self::BATCH_LIMIT == 0) {
                    $this->destinationEm->flush();
                }
            }

            //and flush
            $this->destinationEm->flush();
        }

        $originCustomFields = null;
        $destinationCustomFields = null;
    }

    /**
     * Update TemplateFiles
     * Not dependent on any other entities
     *
     * @param $output
     */
    private function updateTemplateFiles($output)
    {
        $originTemplateFilesRepo = $this->originEm->getRepository('EdcomsCMSContentBundle:TemplateFiles');
        $destinationTemplateFilesRepo = $this->destinationEm->getRepository('EdcomsCMSContentBundle:TemplateFiles');

        //get TemplateFiles
        $originTemplateFiles = $this->getAllKeyedById($originTemplateFilesRepo);
        $destinationTemplateFiles = $this->getAllKeyedById($destinationTemplateFilesRepo);

        if ($originTemplateFiles) {
            $this->updateDestinationMeta('EdcomsCMSContentBundle:TemplateFiles');

            foreach ($originTemplateFiles as $k => $originTemplateFile) {

                if (isset($destinationTemplateFiles[$k])) {
                    $output->writeln('Updating TemplateFiles id: ' . $originTemplateFile->getId());
                } else {
                    $output->writeln('Creating TemplateFiles id: ' . $originTemplateFile->getId());

                    $destinationTemplateFile = new TemplateFiles();
                    $destinationTemplateFile->setId($originTemplateFile->getId());
                    $this->destinationEm->persist($destinationTemplateFile);
                    $destinationTemplateFiles[$k] = $destinationTemplateFile;
                }

                //update fields
                $destinationTemplateFiles[$k]->setTemplateFile($originTemplateFile->getTemplateFile());
            }

            //and flush
            $this->destinationEm->flush();
        }

        $originTemplateFiles = null;
        $destinationTemplateFiles = null;
    }

    /**
     * Update Content
     * Dependant on contentType being updated first
     * Whole table is checked and updated and surplus content entries are deleted from origin database
     *
     * @param $output
     */
    private function updateContent($output)
    {
        $originContentRepo = $this->originEm->getRepository('EdcomsCMSContentBundle:Content');
        $destinationContentRepo = $this->destinationEm->getRepository('EdcomsCMSContentBundle:Content');

        //get content types
        $originContents = $this->getAllKeyedById($originContentRepo);
        $destinationContents = $this->getAllKeyedById($destinationContentRepo);

        if ($originContents) {
            $this->updateDestinationMeta('EdcomsCMSContentBundle:Content');

            $counter = 0;
            foreach ($originContents as $k => $originContent) {
                $counter++;

                if (isset($destinationContents[$k])) {
                    $output->writeln('Updating Content id: ' . $originContent->getId());
                } else {
                    $output->writeln('Creating Content id: ' . $originContent->getId());

                    $destinationContent = new Content();
                    $destinationContent->setId($originContent->getId());
                    $this->destinationEm->persist($destinationContent);
                    $destinationContents[$k] = $destinationContent;

                }

                //update fields
                $destinationContents[$k]->setTitle($originContent->getTitle());
                $destinationContents[$k]->setStatus($originContent->getStatus());
                $destinationContents[$k]->setAddedOn($originContent->getAddedOn());
                $destinationContents[$k]->setApprovedOn($originContent->getApprovedOn());

                if (is_object($originContent->getTemplateFile())) {
                    $templateFileRepo = $this->destinationEm->getRepository('EdcomsCMSContentBundle:TemplateFiles');
                    $templateFile = $templateFileRepo->find($originContent->getTemplateFile()->getId());

                    if ($templateFile) {
                        $destinationContents[$k]->setTemplateFile($templateFile);
                    }
                }

                if (is_object($originContent->getContentType())) {
                    $contentTypeRepo = $this->destinationEm->getRepository('EdcomsCMSContentBundle:ContentType');
                    $contentType = $contentTypeRepo->find($originContent->getContentType()->getId());

                    if ($contentType) {
                        $destinationContents[$k]->setContentType($contentType);
                    }
                }

                if (is_object($originContent->getStructure())) {
                    $structureRepo = $this->destinationEm->getRepository('EdcomsCMSContentBundle:Structure');
                    $structure = $structureRepo->find($originContent->getStructure()->getId());

                    if ($structure) {
                        $destinationContents[$k]->setStructure($structure);
                    }
                }

                //add users
                $destinationAddedUser = $this->checkUserAtDestination($originContent->getAddedUser());
                $destinationContents[$k]->setAddedUser($destinationAddedUser);

                $destinationApprovedUser = $this->checkUserAtDestination($originContent->getApprovedUser());
                $destinationContents[$k]->setApprovedUser($destinationApprovedUser);

                //batch update database
                if ($counter % self::BATCH_LIMIT == 0) {
                    $this->destinationEm->flush();
                }
            }

            //and flush
            $this->destinationEm->flush();
        }

        $originContents = null;
        $destinationContents = null;
    }

    /**
     * Update ContentType
     * Not dependant on any tables being updated first
     *
     * @param $output
     */
    private function updateContentType($output)
    {
        $originContentTypesRepo = $this->originEm->getRepository('EdcomsCMSContentBundle:ContentType');
        $destinationContentTypesRepo = $this->destinationEm->getRepository('EdcomsCMSContentBundle:ContentType');

        //get content types
        $originContentTypes = $this->getAllKeyedById($originContentTypesRepo);
        $destinationContentTypes = $this->getAllKeyedById($destinationContentTypesRepo);

        if ($originContentTypes) {
            $this->updateDestinationMeta('EdcomsCMSContentBundle:ContentType');

            foreach ($originContentTypes as $k => $originContentType) {

                if (isset($destinationContentTypes[$k])) {
                    $output->writeln('Updating ContentType id: ' . $originContentType->getId());
                } else {
                    $output->writeln('Creating ContentType id: ' . $originContentType->getId());
                    $destinationContentType = new ContentType();
                    $destinationContentType->setId($originContentType->getId());
                    $this->destinationEm->persist($destinationContentType);
                    $destinationContentTypes[$k] = $destinationContentType;

                }

                //update fields
                $destinationContentTypes[$k]->setName($originContentType->getName());
                $destinationContentTypes[$k]->setDescription($originContentType->getDescription());
                $destinationContentTypes[$k]->setThumbnail($originContentType->getThumbnail());
                $destinationContentTypes[$k]->setShowChildren($originContentType->getShowChildren());
                $destinationContentTypes[$k]->setIsChild($originContentType->getIsChild());

                $originTemplateFiles = $originContentType->getTemplateFiles();
                if (count($originTemplateFiles)>0) {
                    $this->updateDestinationMeta('EdcomsCMSContentBundle:TemplateFiles');
                    //1 or more files at origin
                    $destinationTemplateFilesRepo = $this->destinationEm->getRepository('EdcomsCMSContentBundle:TemplateFiles');
                    $fileIdArray = [];//collect origin file IDs and delete if not in destination
                    foreach ($originTemplateFiles as $originTemplateFile) {
                        //check if media file exists at destination by id
                        $destinationTemplateFile = $destinationTemplateFilesRepo->find($originTemplateFile->getId());

                        //add file id to a temp array to use in delete check later on
                        $fileIdArray[] = $originTemplateFile->getId();

                        //if templateFile update
                        if ($destinationTemplateFile) {
                            $output->writeln('Updating TemplateFile id: ' . $destinationTemplateFile->getId());
                        } else {
                            $destinationTemplateFile = new TemplateFiles();
                            $destinationTemplateFile->setId($originTemplateFile->getId());
                            $this->destinationEm->persist($destinationTemplateFile);
                            $destinationContentTypes[$k]->addTemplateFile($destinationTemplateFile);
                            $output->writeln('Creating TemplateFile id: ' . $destinationTemplateFile->getId());
                        }

                        $destinationTemplateFile->setTemplateFile($originTemplateFile->getTemplateFile());
                    }

                    //loop through destination files and check if id exists in temp array
                    //if not there delete from destination
                    $destinationTemplateFiles = $destinationContentTypes[$k]->getTemplateFiles();
                    foreach ($destinationTemplateFiles as $destinationTemplateFile) {
                        if (!in_array($destinationTemplateFile->getId(), $fileIdArray)) {
                            $output->writeln('Removing TemplateFile id: ' . $destinationTemplateFile->getId());
                            $destinationContentTypes[$k]->removeTemplateFile($destinationTemplateFile);
                        }
                    }

                } else {

                    $destinationTemplateFiles = $destinationContentTypes[$k]->getTemplateFiles();
                    if (count($destinationTemplateFiles)>0) {
                        //no template files at origin but files at destination
                        //remove all files at destination
                        foreach ($destinationTemplateFiles as $destinationTemplateFile) {
                            $destinationContentTypes[$k]->removeTemplateFile($destinationTemplateFile);
                        }
                    }
                }
            }

            //and flush
            $this->destinationEm->flush();

        }

        $originContentTypes = null;
        $destinationContentTypes = null;
    }

    /**
     * Update MediaLinks
     * Not dependant on any tables being updated first
     *
     * @param $output
     */
//    private function updateMediaLinks($output)
//    {
//        $originMediaLinksRepo = $this->originEm->getRepository('EdcomsCMSContentBundle:MediaLinks');
//        $destinationMediaLinksRepo = $this->destinationEm->getRepository('EdcomsCMSContentBundle:MediaLinks');
//
//        //get media links
//        $originMediaLinks = $this->getAllKeyedById($originMediaLinksRepo);
//        $destinationMediaLinks = $this->getAllKeyedById($destinationMediaLinksRepo);
//
//        if ($originMediaLinks) {
//            $this->updateDestinationMeta('EdcomsCMSContentBundle:MediaLinks');
//
//            $counter=0;
//            foreach ($originMediaLinks as $k => $originMediaLink) {
//                $counter++;
//
//                if (isset($destinationMediaLinks[$k])) {
//                    $output->writeln('Updating MediaLink id: ' . $originMediaLink->getId());
//                } else {
//                    $output->writeln('Creating MediaLink id: ' . $originMediaLink->getId());

//                    $destinationMediaLink = new MediaLinks();
//                    $destinationMediaLink->setId($originMediaLink->getId());
//                    $this->destinationEm->persist($destinationMediaLink);
//                    $destinationMediaLinks[$k] = $destinationMediaLink;
//
//                }
//
//                //update fields
//                $destinationMediaLinks[$k]->setMediaID($originMediaLink->getMediaID());
//                $destinationMediaLinks[$k]->setContentID($originMediaLink->getContentID());
//
//                //batch update database
//                if ($counter % self::BATCH_LIMIT == 0) {
//                    $this->destinationEm->flush();
//                }
//            }
//
//            //and flush
//            $this->destinationEm->flush();
//        }
//
//        $originMediaLinks = null;
//        $destinationMediaLinks = null;
//    }

    /**
     * Update MediaFiles
     * Dependant on Media & MediaTypes tables being updated
     *
     * @param $output
     */
//    private function updateMediaFiles($output)
//    {
//        //get repos
//        $originMediaFilesRepo = $this->originEm->getRepository('EdcomsCMSContentBundle:MediaFiles');
//        $destinationMediaFilesRepo = $this->destinationEm->getRepository('EdcomsCMSContentBundle:MediaFiles');
//
//        //get media files
//        $originMediaFiles = $this->getAllKeyedById($originMediaFilesRepo);
//        $destinationMediaFiles = $this->getAllKeyedById($destinationMediaFilesRepo);
//
//        if ($originMediaFiles) {
//            $this->updateDestinationMeta('EdcomsCMSContentBundle:MediaFiles');
//
//            $counter=0;
//            foreach ($originMediaFiles as $k => $originMediaFile) {
//                $counter++;
//
//                if (isset($destinationMediaFiles[$k])) {
//                    $output->writeln('Updating MediaFile id: ' . $originMediaFile->getId());
//                } else {
//                    $output->writeln('Creating MediaFile id: ' . $originMediaFile->getId());
//                    $destinationMediaFile = new MediaFiles();
//                    $destinationMediaFile->setId($originMediaFile->getId());
//                    $this->destinationEm->persist($destinationMediaFile);
//                    $destinationMediaFiles[$k] = $destinationMediaFile;
//                }
//
//                //update fields
//                $destinationMediaFiles[$k]->setFilename($originMediaFile->getFilename());
//                $destinationMediaFiles[$k]->setAddedOn($originMediaFile->getAddedOn());
//                $destinationMediaFiles[$k]->setFilesize($originMediaFile->getFilesize());
//
//                //check for media
//                if (is_object($originMediaFile->getMedia())) {
//                    $mediaRepo = $this->destinationEm->getRepository('EdcomsCMSContentBundle:Media');
//                    $media = $mediaRepo->find($originMediaFile->getMedia()->getId());
//
//                    if ($media) {
//                        $destinationMediaFiles[$k]->setMedia($media);
//                    }
//                }
//
//                //check for media type
//                if (is_object($originMediaFile->getType())) {
//                    $mediaTypesRepo = $this->destinationEm->getRepository('EdcomsCMSContentBundle:MediaTypes');
//                    $mediaType = $mediaTypesRepo->find($originMediaFile->getType()->getId());
//
//                    if ($mediaType) {
//                        $destinationMediaFiles[$k]->setType($mediaType);
//                    }
//                }
//
//                //add user
//                $destinationAddedUser = $this->checkUserAtDestination($originMediaFile->getAddedUser());
//                $destinationMediaFiles[$k]->setAddedUser($destinationAddedUser);
//
//                //batch update database
//                if ($counter % self::BATCH_LIMIT == 0) {
//                    $this->destinationEm->flush();
//                }
//            }
//
//            //and flush
//            $this->destinationEm->flush();
//        }
//
//        $originMediaFiles = null;
//        $destinationMediaFiles = null;
//    }

    /**
     * Update MediaTypes
     * Not dependant on any tables being updated first
     *
     * @param $output
     */
    private function updateMediaTypes($output)
    {
        //get repos
        $originMediaTypesRepo = $this->originEm->getRepository('EdcomsCMSContentBundle:MediaTypes');
        $destinationMediaTypesRepo = $this->destinationEm->getRepository('EdcomsCMSContentBundle:MediaTypes');

        //get media types
        $originMediaTypes = $this->getAllKeyedById($originMediaTypesRepo);
        $destinationMediaTypes = $this->getAllKeyedById($destinationMediaTypesRepo);

        if ($originMediaTypes) {
            $this->updateDestinationMeta('EdcomsCMSContentBundle:MediaTypes');

            foreach ($originMediaTypes as $k => $originMediaType) {

                if (isset($destinationMediaTypes[$k])) {
                    $output->writeln('Updating MediaType id: ' . $originMediaType->getId());
                } else {
                    $output->writeln('Creating MediaType id: ' . $originMediaType->getId());
                    $destinationMediaType = new MediaTypes();
                    $destinationMediaType->setId($originMediaType->getId());
                    $this->destinationEm->persist($destinationMediaType);
                    $destinationMediaTypes[$k] = $destinationMediaType;
                }

                //update fields
                $destinationMediaTypes[$k]->setFiletype($originMediaType->getFiletype());
                $destinationMediaTypes[$k]->setCompression($originMediaType->getCompression());
                $destinationMediaTypes[$k]->setWidth($originMediaType->getWidth());
                $destinationMediaTypes[$k]->setHeight($originMediaType->getHeight());
                $destinationMediaTypes[$k]->setTarget($originMediaType->getTarget());
            }

            //and flush
            $this->destinationEm->flush();
        }

        $originMediaTypes = null;
        $destinationMediaTypes = null;
    }

    /**
     * Update Media
     * Not dependant on any tables being updated first
     *
     * @param $output
     */
    private function updateMedia($output)
    {
        //get repos
        $originMediaRepo = $this->originEm->getRepository('EdcomsCMSContentBundle:Media');
        $destinationMediaRepo = $this->destinationEm->getRepository('EdcomsCMSContentBundle:Media');

        //get media
        $originMedias = $this->getAllKeyedById($originMediaRepo);

        if ($originMedias) {

            $counter=0;
            foreach ($originMedias as $originMedia) {
                $counter++;

                //find at destination by path and title and addedon
                $destinationMedia = $destinationMediaRepo->findOneBy([
                    'path'=>$originMedia->getPath(),
                    'title'=>$originMedia->getTitle(),
                    'addedOn'=>$originMedia->getAddedOn()
                    ]);

                if ($destinationMedia) {
                    $output->writeln('Updating Media: ' . $originMedia->getPath().'/'.$originMedia->getTitle());
                } else {
                    $output->writeln('Creating Media: ' . $originMedia->getPath().'/'.$originMedia->getTitle());
                    $destinationMedia = new Media();
                    $this->destinationEm->persist($destinationMedia);
                }

                //get a suitable user at destination
                $destinationAddedUser = $this->checkUserAtDestination($originMedia->getAddedUser());
                $destinationModifiedUser = $this->checkUserAtDestination($originMedia->getModifiedUser());

                //update fields
                $destinationMedia->setTitle($originMedia->getTitle());
                $destinationMedia->setPath($originMedia->getPath());
                $destinationMedia->setAddedUser($destinationAddedUser);
                $destinationMedia->setAddedOn($originMedia->getAddedOn());
                $destinationMedia->setModifiedUser($destinationModifiedUser);
                $destinationMedia->setModifiedOn($originMedia->getModifiedOn());
                $destinationMedia->setDeleted($originMedia->getDeleted());

                //update mediaFiles
                $originMediaFiles = $originMedia->getMediaFiles();
                if (count($originMediaFiles)>0) {
                    //1 or more files at origin
                    $destinationMediaFilesRepo = $this->destinationEm->getRepository('EdcomsCMSContentBundle:MediaFiles');
                    $mediaTypesRepo = $this->destinationEm->getRepository('EdcomsCMSContentBundle:MediaTypes');
                    $fileTitleArray = [];//collect origin filenames and delete if not in destination
                    foreach ($originMediaFiles as $originMediaFile) {
                        //check if media file exists at destination by filename
                        $destinationMediaFile = $destinationMediaFilesRepo->findOneBy([ 'filename'=>$originMediaFile->getFilename() ]);

                        //add filename to a temp array to use in delete check later on
                        $fileTitleArray[] = $originMediaFile->getFilename();

                        //if mediaFile update
                        if ($destinationMediaFile) {
                            $output->writeln('Updating MediaFile id: ' . $destinationMediaFile->getId());
                        } else {
                            $destinationMediaFile = new MediaFiles();
                            $this->destinationEm->persist($destinationMediaFile);
                            $destinationMedia->addMediaFile($destinationMediaFile);
                            $output->writeln('Creating MediaFile id: ' . $destinationMediaFile->getId());
                        }

                        $destinationMediaFile->setFilename($originMediaFile->getFilename());
                        $destinationMediaFile->setAddedOn($originMediaFile->getAddedOn());
                        $destinationMediaFile->setFilesize($originMediaFile->getFilesize());

                        //check for media type
                        if (is_object($originMediaFile->getType())) {
                            //the media type table is update whole so searching by id is safe
                            $mediaType = $mediaTypesRepo->find($originMediaFile->getType()->getId());
                            if ($mediaType) {
                                $destinationMediaFile->setType($mediaType);
                            }
                        }

                        //add user
                        $destinationAddedUser = $this->checkUserAtDestination($originMediaFile->getAddedUser());
                        $destinationMediaFile->setAddedUser($destinationAddedUser);
                    }

                    //loop through destination files and check if name exists in temp array
                    //if not there delete from destination
                    $destinationMediaFiles = $destinationMedia->getMediaFiles();
                    foreach ($destinationMediaFiles as $destinationMediaFile) {
                        if (!in_array($destinationMediaFile->getFilename(), $fileTitleArray)) {
                            $output->writeln('Deleting MediaFile id: ' . $destinationMediaFile->getId());
                            $destinationMedia->removeMediaFile($destinationMediaFile);
                            $this->destinationEm->remove($destinationMediaFile);
                        }
                    }

                } else {

                    $destinationMediaFiles = $destinationMedia->getMediaFiles();
                    if (count($destinationMediaFiles)>0) {
                        //no media files at origin but files at destination
                        //delete all files at destination
                        foreach ($destinationMediaFiles as $destinationMediaFile) {
                            $destinationMedia->removeMediaFile($destinationMediaFile);
                            $this->destinationEm->remove($destinationMediaFile);
                        }
                    }
                }

                //batch update database
                if ($counter % self::BATCH_LIMIT == 0) {
                    $this->destinationEm->flush();
                }
            }

            //and flush
            $this->destinationEm->flush();
        }

        $originMedias = null;
        $destinationMedias = null;
    }

    /**
     * Update Badges
     * Dependant on media being updated first
     *
     * @param $output
     */
    private function updateBadges($output)
    {
        //get repos
        $originBadgeRepo = $this->originEm->getRepository('EdcomsCMSBadgeBundle:BadgeSimple');
        $destinationBadgeRepo = $this->destinationEm->getRepository('EdcomsCMSBadgeBundle:BadgeSimple');

        //get badges
        $originBadges = $this->getAllKeyedById($originBadgeRepo);
        $destinationBadges = $this->getAllKeyedById($destinationBadgeRepo);

        if ($originBadges) {
            $this->updateDestinationMeta('EdcomsCMSBadgeBundle:BadgeSimple');

            //now cycle through all origin badges and update in destination db
            foreach ($originBadges as $k => $originBadge) {

                if (isset($destinationBadges[$k])) {
                    $output->writeln('Updating Badge id: '. $k);
                } else {
                    $output->writeln('Creating Badge id: '. $k);
                    $destinationBadge = new BadgeSimple();
                    $destinationBadge->setId($originBadge->getId());
                    $this->destinationEm->persist($destinationBadge);
                    $destinationBadges[$k] = $destinationBadge;
                }

                //get the corresponding destination badge & update fields
                $destinationBadges[$k]->setName($originBadge->getName());
                $destinationBadges[$k]->setSlug($originBadge->getSlug());
                $destinationBadges[$k]->setDescription($originBadge->getDescription());
                $destinationBadges[$k]->setGroup($originBadge->getGroup());
                $destinationBadges[$k]->setOrder($originBadge->getOrder());
                $destinationBadges[$k]->setIsActive($originBadge->getIsActive());
                $destinationBadges[$k]->setIsOpenBadge($originBadge->getIsOpenBadge());
                $destinationBadges[$k]->setAction($originBadge->getAction());
                $destinationBadges[$k]->setTarget($originBadge->getTarget());
                $destinationBadges[$k]->setMultiplier($originBadge->getMultiplier());
                $destinationBadges[$k]->setIsDistinct($originBadge->getIsDistinct());

                //check for image
                if (is_object($originBadge->getImage())) {
                    $mediaRepo = $this->destinationEm->getRepository('EdcomsCMSContentBundle:Media');
                    $image = $mediaRepo->find($originBadge->getImage()->getId());

                    if ($image) {
                        $destinationBadges[$k]->setImage($image);
                    } else {
                        $destinationBadges[$k]->setImage();//defaults to null
                    }
                }

                //check for user group
                if (is_object($originBadge->getCmsUserGroup())) {
                    $cmsUserGroupRepo = $this->destinationEm->getRepository('EdcomsCMSAuthBundle:cmsUserGroups');
                    $userGroup = $cmsUserGroupRepo->find($originBadge->getCmsUserGroup()->getId());

                    if ($userGroup) {
                        $destinationBadges[$k]->setCmsUserGroup($userGroup);
                    } else {
                        $destinationBadges[$k]->setCmsUserGroup();//defaults to null
                    }
                }
            }

            //now flush
            $this->destinationEm->flush();
        }

        $originBadges = null;
        $destinationBadges = null;
    }

    /**
     * Update ContentCache
     * Not dependant on other entities
     * Whole table is replaced as duplicates in the UUID table were causing the script to fail
     *
     * @param $output
     */
    private function updateContentCache($output)
    {
        //get repos
        $originContentCache = $this->originEm->getRepository('EdcomsCMSContentBundle:ContentCache');
        $destinationContentCache = $this->destinationEm->getRepository('EdcomsCMSContentBundle:ContentCache');

        //get ContentCaches
        $originContentCaches = $this->getAllKeyedById($originContentCache);
        $destinationContentCaches = $this->getAllKeyedById($destinationContentCache);

        //remove all content cache from destination and re-insert
        if ($destinationContentCaches) {

            $counter=0;
            foreach ($destinationContentCaches as $destinationContentCache) {
                $counter++;
                $this->destinationEm->remove($destinationContentCache);

                //batch update database
                if ($counter % self::BATCH_LIMIT == 0) {
                    $this->destinationEm->flush();
                }
            }

            $destinationContentCaches = null;
        }

        if ($originContentCaches) {
            $this->updateDestinationMeta('EdcomsCMSContentBundle:ContentCache');

            //now cycle through all origin ContentCaches and update in destination db
            $counter=0;
            foreach ($originContentCaches as $k => $originContentCache) {
                $counter++;

                $output->writeln('Creating ContentCache id: '. $k);
                $destinationContentCache = new ContentCache();
                $destinationContentCache->setId($originContentCache->getId());
                $destinationContentCache->setUuid($originContentCache->getUuid());
                $destinationContentCache->setType($originContentCache->getType());
                $destinationContentCache->setValue($originContentCache->getValue());

                $this->destinationEm->persist($destinationContentCache);

                //batch update database
                if ($counter % self::BATCH_LIMIT == 0) {
                    $this->destinationEm->flush();
                }
            }

            //now flush
            $this->destinationEm->flush();
        }

        $originBadges = null;
        $destinationBadges = null;
    }

    /**
     * Update LinkBuilder Entities
     * Dependant on Structures being updated first
     *
     * @param $output
     */
    private function updateLinkBuilders($output)
    {
        //Init repositories
        $originLinkBuilderRepo = $this->originEm->getRepository('EdcomsCMSContentBundle:LinkBuilder');
        $destinationLinkBuilderRepo = $this->destinationEm->getRepository('EdcomsCMSContentBundle:LinkBuilder');

        //Get all link builder entities from the origin
        $originLinkBuilders = $originLinkBuilderRepo->findAll();

        //Loop through and determine if entities need migrating
        foreach ($originLinkBuilders as $originLinkBuilder) {

            $destinationLinkBuilder = $destinationLinkBuilderRepo->findOneBy(
                array( 'friendlyLink' => $originLinkBuilder->getFriendlyLink() ));

            //if no entity with this friendly link the insert
            if (!$destinationLinkBuilder) {
                $output->writeln('Creating LinkBuilder id: ' . $originLinkBuilder->getId());
                $output->writeln('Friendly Link: ' . $originLinkBuilder->getFriendlyLink());
                $destinationLinkBuilder = new LinkBuilder();
                $this->destinationEm->persist($destinationLinkBuilder);
                $destinationLinkBuilder->setLink($originLinkBuilder->getLink());
                $destinationLinkBuilder->setTarget($originLinkBuilder->getTarget());
                $destinationLinkBuilder->setFriendlyLink($originLinkBuilder->getFriendlyLink());

                //Check Structure exists in destination before trying to add
                if (is_object($originLinkBuilder->getStructure())) {
                    $structureRepo = $this->destinationEm->getRepository('EdcomsCMSContentBundle:Structure');
                    $structure = $structureRepo->find($originLinkBuilder->getStructure()->getId());

                    if ($structure) {
                        $destinationLinkBuilder->setStructure($structure);
                    }
                }

                //flush after each insert as link builder numbers are low and in frequently updated
                $this->destinationEm->flush();
            }
        }
    }

    /**
     * Update Structure Entities
     * Not dependant on any tables being updated first
     *
     * @param $output
     */
    private function updateStructures($output)
    {
        //get repos
        $originStructureRepo = $this->originEm->getRepository('EdcomsCMSContentBundle:Structure');
        $destinationStructureRepo = $this->destinationEm->getRepository('EdcomsCMSContentBundle:Structure');

        //get structures
        $originStructures = $this->getAllKeyedById($originStructureRepo);
        $destinationStructures = $this->getAllKeyedById($destinationStructureRepo);

        if ($originStructures) {

            //if structures (by id) are in origin that are not in destination
            $inserts = array_diff_key($originStructures, $destinationStructures);
            if (count($inserts) > 0) {

                //update destination meta
                $this->updateDestinationMeta('EdcomsCMSContentBundle:Structure');

                //loop through inserts
                foreach ($inserts as $k => $insert) {
                    $output->writeln('Creating Structure id: ' . $insert->getId());
                    $destinationStructure = new Structure();
                    $destinationStructure->setId($insert->getId());//set id to be same as origin
                    $this->destinationEm->persist($destinationStructure);

                    //add to array of destination structures for possible reference later (as parent or master)
                    $destinationStructures[$destinationStructure->getId()] = $destinationStructure;
                    $this->populateStructureProperties($insert, $destinationStructures[$k]);
                }

                //this can't be done in the last loop as some of the parents and master entries may not exist
                foreach ($inserts as $k => $insert) {
                    //check if parent set in origin
                    if (is_object($insert->getParent())) {
                        $destinationParent = $destinationStructures[$insert->getParent()->getId()];
                        $destinationStructures[$k]->setParent($destinationParent);
                    }

                    //check if master set in origin
                    if (is_object($insert->getMaster())) {
                        $destinationMaster = $destinationStructures[$insert->getMaster()->getId()];
                        $destinationStructures[$k]->setMaster($destinationMaster);
                    }
                }

                $this->destinationEm->flush();

                //remove inserts to avoid processing again in the update step
                $originStructures = array_diff_key($originStructures, $inserts);
            }

            //now cycle through all origin structures and update in destination db
            $counter=0;
            foreach ($originStructures as $k => $originStructure) {
                $counter++;

                $output->writeln('Updating Structure id: '. $k);
                $this->populateStructureProperties($originStructure, $destinationStructures[$k]);

                //check if parent set in origin
                if (is_object($originStructure->getParent())) {
                    $destinationParent = $destinationStructures[$originStructure->getParent()->getId()];
                    $destinationStructures[$k]->setParent($destinationParent);
                }

                //check if master set in origin
                if (is_object($originStructure->getMaster())) {
                    $destinationMaster = $destinationStructures[$originStructure->getMaster()->getId()];
                    $destinationStructures[$k]->setMaster($destinationMaster);
                }

                //batch update database
                if ($counter % self::BATCH_LIMIT == 0) {
                    $this->destinationEm->flush();
                }
            }

            //now flush
            $this->destinationEm->flush();

            //Structure entities do have a deleted flag so no need to delete at destination
            //if deletes are required then this should be handled manually at destination
            //(this would have been a manual task at origin)
        }

        $originStructures = null;
        $destinationStructures = null;
    }

    /**
     * Helper function to retrieve the highest ID from an entity table
     *
     * @param $repo - Entity Repository
     * @return mixed
     */
    private function getHighestEntityId($repo)
    {
        $query = $repo->createQueryBuilder('e', 'e.id')
            ->select('MAX(e.id)')
            ->getQuery();

        return $query->getSingleScalarResult();
    }

    /**
     * Helper function to retrieve a range of entities from a repository keyed by DB id.
     * Entities can be returned as objects or arrays using the asArray parameter.
     *
     * @param $repo - Entity Repository
     * @param $offset - ID of entity to start at
     * @param $limit - Number of entities to retrieve
     * @param bool $asArray|false $asArray - set to true to get returned entities as arrays
     * @return mixed
     */
    private function getRangeKeyedById($repo, $offset, $limit, $asArray = false)
    {
        $query = $repo->createQueryBuilder('e', 'e.id')
            ->orderBy('e.id', 'ASC')
            ->where('e.id >= '.$offset)
            ->setMaxResults($limit)
            ->getQuery();

        if ($asArray) {
            return $query->getArrayResult();
        } else {
            return $query->getResult();
        }
    }

    /**
     * Helper function to retrieve all entities from a repository keyed by DB id.
     * Entities can be returned as objects or arrays using the asArray parameter.
     *
     * @param $repo - Entity Repository
     * @param bool|false $asArray - set to true to get returned entities as arrays
     * @return mixed
     */
    private function getAllKeyedById($repo, $asArray = false)
    {
        $query = $repo->createQueryBuilder('e', 'e.id')
            ->orderBy('e.id', 'ASC')
            ->getQuery();

        if ($asArray) {
            return $query->getArrayResult();
        } else {
            return $query->getResult();
        }
    }

    /**
     * Helper function to get a valid user in the destination database based on a user at origin.
     * If a user with the same username can't be found then the fall back is the username 'admin'.
     * If there is no admin user then the first member of the cms_admin group is returned.
     *
     * @param $originUser - cmsUser
     * @return mixed
     */
    private function checkUserAtDestination($originUser)
    {
        $user = false;
        if (!is_null($originUser)) {
            $usersRepo = $this->destinationEm->getRepository('EdcomsCMSAuthBundle:cmsUsers');
            $user = $usersRepo->findOneBy(['username' => $originUser->getUsername()]);
            if (!$user) {
                //if user doesn't exist look for 'admin'
                $user = $usersRepo->findOneBy(['username' => 'admin']);
            }
        }
        if (!$user) {
            //if no 'admin' user get first user with cms rights
            $cmsUserGroupRepo = $this->destinationEm->getRepository('EdcomsCMSAuthBundle:cmsUserGroups');
            $adminGroup = $cmsUserGroupRepo->findOneBy(['name'=>'cms_admin']);
            $adminUsers = $adminGroup->getUser();
            $user = $adminUsers->first();
        }

        return $user;
    }

    /**
     * Helper function to update the destination meta for an entity to allow inserting entities with specific IDs
     *
     * @param $className - string
     */
    private function updateDestinationMeta($className)
    {
        //update db identity policy to allow inserting at destination with a specific id
        $metadata = $this->destinationEm->getClassMetaData($className);
        $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
        $metadata->setIdGenerator(new \Doctrine\ORM\Id\AssignedGenerator());
    }

    /**
     * Helper function to populate the properties of the destination structure using the properties
     * of an origin structure object
     *
     * @param $originStructure
     * @param $destinationStructure
     */
    private function populateStructureProperties($originStructure, &$destinationStructure)
    {
        //get the corresponding destination structure & update fields
        $destinationStructure->setTitle($originStructure->getTitle());
        $destinationStructure->setLink($originStructure->getLink());
        $destinationStructure->setPriority($originStructure->getPriority());
        $destinationStructure->setAddedOn($originStructure->getAddedOn());
        $destinationStructure->setDeleted($originStructure->getDeleted());
        $destinationStructure->setRateable($originStructure->getRateable());
        $destinationStructure->setVisible($originStructure->getVisible());
    }
    /**
     * Helper function to populate the properties of the destination custom fields using the properties
     * of an origin custom field object
     *
     * @param $originCustomField
     * @param $destinationCustomField
     */
    private function populateCustomFieldsProperties($originCustomField, &$destinationCustomField)
    {
        //update fields
        $destinationCustomField->setName($originCustomField->getName());
        $destinationCustomField->setDescription($originCustomField->getDescription());
        $destinationCustomField->setFieldType($originCustomField->getFieldType());
        $destinationCustomField->setLabel($originCustomField->getLabel());
        $destinationCustomField->setDefaultValue($originCustomField->getDefaultValue());
        $destinationCustomField->setRequired($originCustomField->getRequired());
        $destinationCustomField->setOptions($originCustomField->getOptions());
        $destinationCustomField->setOrder($originCustomField->getOrder());
        $destinationCustomField->setAdminOnly($originCustomField->getAdminOnly());
        $destinationCustomField->setRepeatable($originCustomField->getRepeatable());

        if (is_object($originCustomField->getContentType())) {
            $contentTypeRepo = $this->destinationEm->getRepository('EdcomsCMSContentBundle:ContentType');
            $contentType = $contentTypeRepo->find($originCustomField->getContentType()->getId());

            if ($contentType) {
                $destinationCustomField->setContentType($contentType);
            }
        }
    }

    /**
     * Compare to arrays and remove any surplus from the database
     *
     * @param $destinationEntities - array of entities from destination
     * @param $originEntities - array of entities from origin
     * @param $output
     */
    private function removeEntitiesAtDestination(&$destinationEntities, $originEntities, $output)
    {
        //Check for surplus at destination
        $surplusEntities = array_diff_key($destinationEntities, $originEntities);
        if (count($surplusEntities)>0) {

            //remove any surplus
            $counter=0;
            foreach ($surplusEntities as &$surplusEntity) {
                $counter++;

                $className = (new \ReflectionClass($surplusEntity))->getShortName();
                $output->writeln('Deleting '.$className.' id: '.$surplusEntity->getId());
                $this->destinationEm->remove($surplusEntity);

                //batch update database
                if ($counter % self::BATCH_LIMIT == 0) {
                    $this->destinationEm->flush();
                }
            }

            //and flush
            $this->destinationEm->flush();
        }
    }

    /**
     * Output current time to cli
     *
     * @param $output
     */
    private function outputCurrentTime($output)
    {
        $output->writeln(date(\DateTime::RFC2822, time()));
    }
}
