<?php

namespace EdcomsCMS\ContentBundle\Helpers;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use EdcomsCMS\ContentBundle\Entity\Content;
use EdcomsCMS\ContentBundle\Entity\CustomFieldData;
use EdcomsCMS\ContentBundle\Entity\CustomFields;
use EdcomsCMS\ContentBundle\Entity\Structure;
use EdcomsCMS\ContentBundle\Model\AbstractFilterOptions;
use EdcomsCMS\ContentBundle\Entity\ContentCache;

use EdcomsCMS\ContentBundle\Service\EdcomsContentConfigurationService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ContentHelper
{
    // created a trait to handle hydration methods \\
    use \EdcomsCMS\ContentBundle\Traits\ContentCustomFieldHydration;

    const CUSTOMFIELD_LIMIT_NAME = 'paginate_limit';
    const CUSTOMFIELD_PAGE_NAME = 'paginate_start';
    const CUSTOMFIELD_START_LIMIT_NAME = 'paginate_start_limit';
    const PAGINATIONINFO_LOADMORE = 'load_more';
    const PAGINATIONINFO_CURRENT_PAGE = 'current_page';
    const PAGINATIONINFO_NOT_SET = -1;
    const PAGINATIONINFO_NEXT_PAGE = 'next_page';
    const PAGINATIONINFO_PAGES = 'pages';
    const PAGINATIONINFO_PREVIOUS_PAGE = 'previous_page';
    const PAGINATIONINFO_TOTAL_COUNT = 'total_count';
    
    /**
     * @var \EdcomsCMS\ContentBundle\Entity\ContentRepository
     */
    private $contentRepository;
    /**
     *
     * @var \EdcomsCMS\ContentBundle\Entity\StructureRepository
     */
    private $structureRepository;

    private $targetStatus = 'published';
    private $filterOptions;
    private $paginationInfo;
    private $user;
    private $doctrine;
    private $subChildrenArr = [];
    /**
     *
     * @var FilterOptionsHelper 
     */
    private $filterOptionsHelper;
    
    private $contentCache = [];
    
    /**
     * The Full structure of the element we want
     * @var Structure 
     */
    private $structure;
    /**
     * The Structure ID being created via the createContent method
     * @var integer 
     */
    private $structureID = 0;
    
    /**
     * An ArrayCollection of Children associated with the Structure
     * @var ArrayCollection 
     */
    private $children;

    /**
     * @var EdcomsContentConfigurationService
     */
    private $configService;
    
    /**
     * @param   EntityRepository    $contentRepository      Repository object of the Content entity.
     * @param   EntityRepository    $structureRepository     Repository object of the Structure entity.
     */
    public function __construct(EntityRepository $contentRepository, EntityRepository $structureRepository, $doctrine, $user, EdcomsContentConfigurationService $configurationService)
    {
        $this->contentRepository = $contentRepository;
        $this->structureRepository = $structureRepository;
        $this->doctrine = $doctrine;
        $this->user = $user;
        $this->configService = $configurationService;
    }
    
    public function setFilterOptionsHelper(FilterOptionsHelper $filterOptionsHelper)
    {
        $this->filterOptionsHelper = $filterOptionsHelper;
        $this->filterOptionsHelper->setTargetStatus($this->targetStatus);
    }
    
    /**
     * Returns the pagination values calculated from '$request', '$structure' or using both.
     * Values are calculated by fetching the values from the 'customfields' if contained in the Content object in '$structure'.
     * These are overriden by the GET parameters found in '$request'.
     * Array is returned with the following keys and values:
     *   'paginate_limit' - amount of content items to be fetched in each page. Default value of -1 indicates a single page should hold infinate items.
     *   'paginate_start' - number of the page to fetch. Default value of 0 indicates to start at the beginning of the contents.
     * @param   Request     $request        The calling request object. Used to process the GET parameters.
     * @param   Structure   $structure      Parent object of requested contents.
     * @return  array                       The calcuated pagination values.
     */
    public function getPaginationInfo(Request $request, Structure $structure)
    {
        $requestQuery = $request->query;
        $limit = $requestQuery->get('limit');
        
        $limitSet = false;
        
        if ($limit === null || $limit === '') {
            $limit = self::PAGINATIONINFO_NOT_SET;
        } else {
            $limit = intval($limit);
            $limitSet = true;
        }
        
        $page = intval($requestQuery->get('page') ?: 0);
        $pageSet = $page !== 0;
        $startLimit = self::PAGINATIONINFO_NOT_SET;
        // check that content exists for the item first \\
        return ($structure->getContent()->first() !== false) ? $this->internalPaginationInfo($structure, $limit, $page, $startLimit, $limitSet, $pageSet) : [];
    }
    
    public function getPaginationInfoParams($limit, $page, Structure $structure)
    {
        $limitSet = false;
        
        if ($limit === null || $limit === '') {
            $limit = self::PAGINATIONINFO_NOT_SET;
        } else {
            $limit = intval($limit);
            $limitSet = true;
        }
        
        $page = intval($page ?: 0);
        $pageSet = $page !== 0;
        $startLimit = self::PAGINATIONINFO_NOT_SET;
        // check that content exists for the item first \\
        return ($structure->getContent()->first() !== false) ? $this->internalPaginationInfo($structure, $limit, $page, $startLimit, $limitSet, $pageSet) : [];
    }
    
    /**
     * This is the internal method to handle tracking Structure or predetermined filter and pagination options
     * @param Structure $structure
     * @param int $limit
     * @param int $page
     * @param int $startLimit
     * @param bool $limitSet
     * @param bool $pageSet
     * @return array
     */
    private function internalPaginationInfo(Structure $structure, $limit, $page, $startLimit, $limitSet, $pageSet)
    {

        //iterate through custom fields and search for pagination options that have been set in the CMS
        $structure
            ->getContent()
            ->first()
            ->getCustomFieldData()
            ->forAll(function ($i, $customField) use (&$limit, &$page, &$startLimit, $limitSet, $pageSet) {
                $name = $customField->getCustomFields()->getName();
                $value = $customField->getValue();
                
                // check to see if value is not empty.
                if (strlen($value) !== 0) {
                    if (!$limitSet) {
                        if ($name === self::CUSTOMFIELD_START_LIMIT_NAME) {
                            $startLimit = intval($value);
                        } else if ($name === self::CUSTOMFIELD_LIMIT_NAME) {
                            $limit = intval($value);
                        }
                    } else if (!$pageSet && $name === self::CUSTOMFIELD_PAGE_NAME) {
                        $page = intval($value);
                    }
                }
                
                return true;
            });
        
        // if '$startLimit' value has been set as -1,
        // inherit value from '$limit'.
        if ($startLimit === self::PAGINATIONINFO_NOT_SET) {
            $startLimit = $limit;
        }
        
        if ($page < 1) {
            $page = 1;
        }

        return [
            self::CUSTOMFIELD_LIMIT_NAME => $limit,
            self::CUSTOMFIELD_PAGE_NAME => $page,
            self::CUSTOMFIELD_START_LIMIT_NAME => $startLimit,
        ];
    }
    
    /**
     * Fetches the content with the path of '$path'.
     * Child contents are paginated according to the informaion stored in the '$paginationInfo' array.
     * Both '$content' and '$contentArr' are set with the configured results of the fetched content.
     * 
     * @param   Structure   $structure          Parent structure object used to fetch it's child structures and content.
     * @param   array       $paginationInfo     Values to paginate child content with.
     * @param   array       $filterOptions      Values to filter the content with.
     * @param   Content     $content            Pointer to set the value as the returned Content object.
     * @param   array       $contentArr         Pointer to set the value as the returned contents.
     * @param   string      $status             The target status to select content
     * 
     * @throws NotFoundHttpException
     */
    public function createContent($structure, &$paginationInfo, $filterOptions, &$content, &$contentArr, $status='published')
    {
        $this->targetStatus = $status;
        $this->filterOptions = $filterOptions;
        // fetch contents along with associated structure and children objects.
        // we always fetch one more than the limit so that the 'displayChildren' method can truncate the results,
        // and sent a boolean value to the view indicating that there are more children to be loaded.
        $allFilterValues = null;
        if (isset($filterOptions['options']['engine_search']['searched']) && count($this->subChildrenArr) === 0) {
            $allFilterValues = [];
            $selected = [];
            //loop through all available filters
            foreach ($filterOptions['filters'] as $filterVals) {
                //collect together filter values and the fake selected array
                if (isset($filterVals['values']) && $filterVals['values'] > 0) {
//                    $allFilterValues = array_merge($allFilterValues, $filterVals['values']);

                    //collect options
                    foreach ($filterVals['values'] as $filterValItem) {
                        if (isset($filterValItem['value'])) {
                            $selected[] = $filterValItem['value'];
                        }

                        if (isset($filterValItem['matches'])) {
                            $allFilterValues = array_merge($allFilterValues, $filterValItem['matches']);
                        }
                    }
                }
            }
            $allFilterValues = array_unique($allFilterValues);
        }

            // do the search to get a list of IDs that match so we can use this when filtering and paginating \\
        // filter the children by searched terms if provided \\
        //results being returned as an array of arrays is not ideal and the root cause needs to be looked at
        $childrenIDs = $this->filterOptionsHelper->filterBySearch([$structure], 'parent', $allFilterValues);
        if (isset($childrenIDs[0]) && count($childrenIDs[0])>1) {
            $childrenIDs = $childrenIDs[0];
        }

        //if a search has occurred and no results came back we should not continue with filtering
        if (isset($filterOptions['options']['engine_search']['searched']) && count($childrenIDs) === 0) {
            //search has occurred with nothing returned
            //find by structure and remove the children
            $contents = $this->contentRepository->findByStructureStatusAndVisibility($structure->getId());
            if (count($contents) === 1) {
                $contents[0]->getStructure()->setChildren(new ArrayCollection());
            }
            //alter pagination info to reflect no pages found
            $paginationInfo[self::PAGINATIONINFO_TOTAL_COUNT] = 0;
            $paginationInfo[self::PAGINATIONINFO_NEXT_PAGE] = false;
            $paginationInfo[self::PAGINATIONINFO_PREVIOUS_PAGE] = false;
            $paginationInfo[self::PAGINATIONINFO_LOADMORE] = false;
            $paginationInfo[self::PAGINATIONINFO_PAGES] = [ 1 => 0 ];
            $paginationInfo[self::PAGINATIONINFO_CURRENT_PAGE] = 1;


        } else {//no search
            //get contents
            $contents = $this->contentRepository->findByStructureWithFilterAndPagination($structure, $filterOptions['filters'], $paginationInfo, $status, false, $childrenIDs);
        }
        
        // set the paginationInfo now that it's been injected by contentRepository \\
        $this->paginationInfo = $paginationInfo;
        
        $this->structureID = $structure->getId();
        
        // throw an exception if no content is found.
        if (!$contents) {
            throw new NotFoundHttpException('Not Found');
        }
        
        // handle and prepare the found contents results.
        $content = $this->handleContent($contents[0]);
        $contentArr = $this->prepContentArr($content);
    }
    
    /**
     * RW - moving this method to a Trait as it handles manipulation of data that can be reused
     */
    // public function handleContent($content);
    
    /**
     * Prepares $content and returns a custom array with all the necessary properties along with their keys.
     * 'null' is returned if $filterOptions has been set and the content fails to match.
     * 
     * @param   Content   $content              Object to construct the returning array from.
     * @param   array     $filterOptions        Collection of filtering options to filter through the child content with.
     * @param   boolean   $matchAllCriteria     'true' if we wish to match all filter criteria, or 'false' to match any.
     * 
     * @return  array                           Constructed array populated from the data returned from $content.
     */
    public function prepContentArr(Content $content, $filterOptions = null, $matchAllCriteria = true)
    {
        // get customfielddata in the form of a formatted array.
        $customFieldData = $content->getCustomFieldDataArr();
        
        $data = [
            'id' => $content->getId(),
            'link' => $content->getStructure()->getFullLink(true),
            'title' => $content->getTitle(),
            'status' => $content->getStatus(),
            'addedOn' => $content->getAddedOn(),
            'addedUser' => $content->getAddedUser() ? $content->getAddedUser()->toJSON() : null,
            'custom_field_data' => $customFieldData,
            'context' => $this->configService->isContextEnabled() &&  $content->getStructure()->getContext() ? $content->getStructure()->getContext()->getContext() : null
        ];
        
        return $data;
    }
    
    public function displayChildren($children, $childrenOptions = null, &$paginationInfo = null, array $filterOptions = null, $status=null, $subChildren=false)
    {
        $status = (is_null($status)) ? $this->targetStatus : $status;
        $childrenArr = [];
        $childrenIDs = [];
        // passing by reference so we can store any modifications in the object \\
        $this->paginationInfo = &$paginationInfo;

        $filterParams = null;
        if ($childrenOptions !== null) {
            if (array_key_exists('filter', $childrenOptions)) {
                $filterParams = $childrenOptions['filter'];
                if (!is_array($filterParams)) {
                    $filterParams = null;
                } else if (self::isAssociative($filterParams)) {
                    $filterParamsTmp = null;
                    
                    foreach ($filterParams as $filterParamKey => $filterParam) {
                        if ($filterParam !== '') {
                            if ($filterParamsTmp === null) {
                                $filterParamsTmp = [];
                            }
                            
                            array_push($filterParamsTmp, [$filterParamKey, $filterParam]);
                        }
                    }
                    
                    $filterParams = $filterParamsTmp;
                }
            }
        }
        
        // set the count.
        $childrenCount = $children !== null ? $children->count() : 0;
        // boolean determining if pagination and start limit options have been set.
        $paginationInfoSet = $paginationInfo !== null;
        $startLimitSet = $paginationInfoSet && isset($paginationInfo[self::CUSTOMFIELD_START_LIMIT_NAME]);

        // set the preliminary limit.
        $limit = -1;

        // check children count compared to pagination limit.
        if ($paginationInfoSet) {
            if (isset($paginationInfo[self::CUSTOMFIELD_LIMIT_NAME])) {
                // limit has been set in pagination info.
                $limit = $paginationInfo[self::CUSTOMFIELD_LIMIT_NAME];

                if (
                    isset($paginationInfo[self::CUSTOMFIELD_PAGE_NAME]) &&
                    $startLimitSet &&
                    $paginationInfo[self::CUSTOMFIELD_PAGE_NAME] === 1
                ) {
                    // we're on the first page and,
                    // the start limit has been set in the pagination info.
                    $limit = $paginationInfo[self::CUSTOMFIELD_START_LIMIT_NAME];
                }
            }
        }
        $childrenAdded = 0;
        if ($childrenCount > 0) {
            //Get iterator and rewind to start
            $childrenItr = $children->getIterator();
            $childrenItr->rewind();

            while ($childrenItr->valid()) {//while the iterator has a current element
                $child = $childrenItr->current();
                $subContent = $child->getContent();
                if ($subContent) {
                    // loop through each one and see if it is the same as the request\\
                    $subContent->filter(function ($item) use ($status) {
                        return ($item->getStatus() === $status);
                    });
                    
                    $firstSubContent = $subContent->first();
                    $firstSubContent->link = $child->getLink();
                    
                    $childObj = $this->prepContentArr(
                        $this->handleContent($firstSubContent),
                        $filterOptions['filters']
                    );
                    
                    if ($childObj !== null) {
                        $childrenAdded++;
                        // truncate results only if '$limit' has been set with a positive value.
                        if ($limit !== -1 && $childrenAdded > $limit) {
                            break;
                        }
                        
                        // BREAKING CHANGE - Added RW 25/09/1016 - get the structure and return this to the FE along with content in a .content object \\
                        
                        $content = $childObj;
                        $tempStructure = $child->toJSON(['id', 'rateable']);
                        $tempStructure['content'] = $content;
                        if ($child->getRateable()) {
                            $rating = new RatingHelper($this->doctrine, $child);

                            //Check if the number of ratings is greater then the limit set in the site config
                            $numberOfRatings = $rating->getNumberOfRatings();
                            $average = $numberOfRatings >= RatingHelper::RATINGS_MINIMUM_LIMIT ?
                                $rating->GetAverage(): 0;

                            //If logged in add the users own rating
                            $myRating = ($this->user !== null) ? $rating->GetMyRatings($this->user) : null;

                            $structureRating = array(
                                'average' => $average,
                                'my-rating' => $myRating
                            );

                            $tempStructure['rating'] = $structureRating;
                        }
                        
                        if ($filterOptions['options'][AbstractFilterOptions::FILTEROPTION_RETURNCHILDREN] && $subChildren) {
                            $subchildren = $child->getChildren();
                            $tempStructure['children'] = $this->displayChildren($subchildren, $childrenOptions, $paginationInfo, $filterOptions, $status, false);
                        }
                        
                        $childrenArr[] = $tempStructure;
                        $childrenIDs[] = $child->getId();
                    }
                }

                //move to the next element
                $childrenItr->next();
            }
            
            if ($paginationInfoSet) {
                if ($limit !== -1 || $startLimitSet) {
                    // boolean to send back to the view.
                    // 'true' if there are more children left to load.
                    $loadMore = $limit !== -1 && $limit < $childrenAdded;
                    $paginationInfo[self::PAGINATIONINFO_LOADMORE] = $loadMore;
                }
            }
        }
        
        if (!is_null($childrenOptions) && array_key_exists('sort', $childrenOptions)) {
            $sortParams = $childrenOptions['sort'];
            if (is_array($sortParams) && !self::isAssociative($sortParams)) {
                $i = count($sortParams);
                
                if ($i > 0) {
                    while ($i--) {
                        $sortParam = $sortParams[$i];
                        $sortParamCount = count($sortParam);
                        
                        // validate sort parameter, ignore if fails.
                        if (self::isAssociative($sortParam) || $sortParamCount === 0) {
                            continue;
                        }
                        
                        $sortKey = $sortParam[0];
                        $sortOrderAsc = true;
                        if ($sortParamCount === 2 && strtolower($sortParam[1]) === 'desc') {
                            $sortOrderAsc = false;
                        }
                        
                        usort($childrenArr, function($a, $b) use($sortKey, $sortOrderAsc) {
                            $a = $a[$sortKey];
                            $b = $b[$sortKey];
                            
                            $first = $sortOrderAsc ? $a : $b;
                            $second = $sortOrderAsc ? $b : $a;
                            return strcmp($first, $second);
                        });
                    }   
                }
            }
        }
        // separate work here now for subChildren if applicable \\
        if ($subChildren) {
            $this->subChildrenArr = [];
            $filters = 0;
            foreach ($filterOptions['filters'] as $filterVals) {
                // get a list of IDs for this filter option \\
                $selected = $filterVals['selected'];
                $this->tempSubArr = [];
                if (isset($filterVals['values']) && !empty($selected)) {
                    array_walk($filterVals['values'], array(&$this, 'findMatches'), $selected);
                    $this->tempSubArr = array_diff($this->tempSubArr, $childrenIDs);
                    $this->subChildrenArr = (!empty($this->subChildrenArr)) ? array_intersect($this->subChildrenArr, $this->tempSubArr) : $this->tempSubArr;
                }
                if (!empty($selected)) {
                    $filters++;
                }
            }

            //Check if search has occurred with no filters being set if so we need to set all filters as if they were
            //i.e. fake the filters to be all set here instead of setting on the FE
            if (isset($filterOptions['options']['engine_search']['searched']) && count($this->subChildrenArr) === 0) {

                $allFilterValues = [];
                $selected = [];
                //loop through all available filters
                foreach ($filterOptions['filters'] as $filterVals) {
                    //collect together filter values and the fake selected array
                    if (isset($filterVals['values']) && $filterVals['values'] > 0) {
                        $allFilterValues = array_merge($allFilterValues, $filterVals['values']);

                        //collect options
                        foreach ($filterVals['values'] as $filterValItem) {
                            if (isset($filterValItem['value'])) {
                                $selected[] = $filterValItem['value'];
                            }
                        }
                    }
                }

                $this->tempSubArr = [];
                if (count($allFilterValues) > 0 && count($selected) > 0) {
                    array_walk($allFilterValues, array(&$this, 'findMatches'), $selected);
                    $this->tempSubArr = array_diff($this->tempSubArr, $childrenIDs);
                    $this->subChildrenArr = (!empty($this->subChildrenArr)) ? array_intersect($this->subChildrenArr, $this->tempSubArr) : $this->tempSubArr;
                }
            }

            // remove any direct children of the parent \\
            $structureID = $this->structureID;
            $subContent = $this->getContent($this->subChildrenArr, $childrenAdded);
            if ($subContent === false || is_null($subContent)) {
                $subContent = [];
            }
            $childrenArr['sub_children'] = $subContent;
            // RW 19/01/17 removing - this should be handled in the initial query of findByNotParentNotDeleted \\
//            $childrenArr['sub_children'] = array_filter($subContent, function($child) use ($structureID) {
//                return ($child['parent']['id'] !== $structureID);
//            });
        }
        return $childrenArr;
    }
    /**
    * Determines whether the passing array is associative.
    * @param   array   $array  The testing array.
    * @return  boolean         'true' if $array is associative.
    */
    static function isAssociative($array)
    {
        return array_keys($array) !== range(0, count($array) - 1);
    }

    public function findMatches($value, $ind, $selected)
    {
        if (in_array($value['value'], $selected)) {
            $this->tempSubArr = array_merge($this->tempSubArr, $value['matches']);
        }
    }
   
   
    public function getContent($ids, $childrenAdded=0)
    {
        $paginationInfo = $this->paginationInfo;
        $page = $paginationInfo[ContentHelper::CUSTOMFIELD_PAGE_NAME];
        $limit = $paginationInfo[self::CUSTOMFIELD_LIMIT_NAME];
        $startLimit = (!is_null($paginationInfo)) ? $paginationInfo[ContentHelper::CUSTOMFIELD_START_LIMIT_NAME] : $limit;
        
        $limitSet = $limit !== ContentHelper::PAGINATIONINFO_NOT_SET;
        $startLimitSet = $startLimit !== ContentHelper::PAGINATIONINFO_NOT_SET;
        $count = (isset($paginationInfo[ContentHelper::PAGINATIONINFO_TOTAL_COUNT])) ? $paginationInfo[ContentHelper::PAGINATIONINFO_TOTAL_COUNT] : 0;
        
        $offset = ContentHelper::PAGINATIONINFO_NOT_SET;
        if ($limitSet || ($startLimitSet && $page === 1)) {
            $limitValue = $limit;
            $limit = $page === 1 ? $startLimit : $limit;
            $offset = $page === 1 ? 0 : $startLimit + ($limitValue * ($page - 2));
        }
        if (empty($ids)) {
            return false;
        } else {
            $idArr = (!is_array($ids)) ? explode(',',$ids) : $ids;
            
            // get the structure and content of all items by ID but only within the range requested \\
            $structures = $this->structureRepository;
            $structureNonParent = $structures->findByNotParentNotDeleted($idArr, $this->structureID, $this->targetStatus);
            $structureCount = $this->filterOptionsHelper->filterBySearch($structureNonParent, 'structure');
            
            if (count($structureCount) > 0) {
                // update the count with the new result \\
                $paginationInfo[ContentHelper::PAGINATIONINFO_TOTAL_COUNT] = $count+count($structureCount);
                // calculate the new pagination with the new total \\
                // get the last page, if it doesn't meet the limit then add some from our new result \\
                $pages = $paginationInfo[ContentHelper::PAGINATIONINFO_PAGES];
                if (end($pages) <= $limit) {
                    $lastPage = end($pages);
                    $pages[count($pages)] = end($pages)+((count($structureCount) < $limit-end($pages)) ? count($structureCount) : $limit-end($pages));
                }

                // take our total of new structures and take away those which have been allocated to the previous page \\
                $divideCount = count($structureCount) - ($pages[count($pages)] - $lastPage);
                // now if we have any remaining items lets split them into pages too \\
                // this will populate the pages array.
                $i = count($pages)+1;
                while ($divideCount > 0) {
                    $pageCount = $limit;

                    if ($divideCount < $limit) {
                        $pageCount = $divideCount;
                    }
                    $pages[$i] = $pageCount;
                    $divideCount -= $limit;
                    $i++;
                }
                $paginationInfo[ContentHelper::PAGINATIONINFO_PAGES] = $pages;
                $paginationInfo[ContentHelper::PAGINATIONINFO_NEXT_PAGE] = $paginationInfo[ContentHelper::PAGINATIONINFO_CURRENT_PAGE] < count($pages) ? $paginationInfo[ContentHelper::PAGINATIONINFO_CURRENT_PAGE] + 1 : false;
                $paginationInfo[ContentHelper::PAGINATIONINFO_PREVIOUS_PAGE] = $paginationInfo[ContentHelper::PAGINATIONINFO_CURRENT_PAGE] !== 1 ? $paginationInfo[ContentHelper::PAGINATIONINFO_CURRENT_PAGE] - 1 : false;
                $this->paginationInfo = $paginationInfo;
            }
            $structureArr = [];
            if ($limit > $childrenAdded) {
                // slice the structure based on pagination info \\
                $structure = array_slice($structureCount, ($offset > $count) ? $offset-$count : 0, $limit-$childrenAdded);

                foreach ($structure as $struct) {
                    // handle and prepare the found contents results.
                    $content = $this->handleContent($struct->getContent($this->targetStatus)->first());
                    $contentArr = $this->prepContentArr($content);
                    $tempStructure = $struct->toJSON(['id', 'rateable', 'parent']);
                    if ($struct->getRateable()) {
                        if($struct->getMaster() !== null){
                            $struct = $struct->getMaster();
                        }
                        $rating = new RatingHelper($this->doctrine, $struct);

                        //Check if the number of ratings is greater then the limit set in the site config
                        $numberOfRatings = $rating->getNumberOfRatings();
                        $average = $numberOfRatings >= RatingHelper::RATINGS_MINIMUM_LIMIT ?
                            $rating->GetAverage(): 0;

                        //If logged in add the users own rating
                        $myRating = ($this->user !== null) ? $rating->GetMyRatings($this->user) : null;

                        $structureRating = array(
                            'average' => $average,
                            'my-rating' => $myRating
                        );

                        $tempStructure['rating'] = $structureRating;            
                    }
                    $tempStructure['content'] = $contentArr;
                    $structureArr[] = $tempStructure;
                }
            }
            return $structureArr;
        }
    }

    /**
     * 
     * @param Content $content
     * @return Content
     */
    public function saveContent(Content $content)
    {
        $em = $this->doctrine->getManager('edcoms_cms');
        $em->persist($content);
        return $content;
    }
    public function saveStructure()
    {
        
    }
    public function findInCache($type, $UUID)
    {
        $contentCacheRepository = $this->doctrine->getManager('edcoms_cms')->getRepository('EdcomsCMSContentBundle:ContentCache');
        if (!isset($this->contentCache[$type])) {
            $this->contentCache = $contentCacheRepository->findByType($type);
        }
        $found = (isset($this->contentCache[$UUID])) ? $this->contentCache[$UUID] : false;
        return $found;
    }
    /**
     * 
     * @param integer $id
     * @return ContentCache
     */
    private function findInCacheById($id)
    {
        $contentCacheRepository = $this->doctrine->getManager('edcoms_cms')->getRepository('EdcomsCMSContentBundle:ContentCache');
        $contentCache = $contentCacheRepository->find($id);
        return $contentCache;
    }
    public function putInCache($type, $UUID, $value, $flush=false)
    {
        $cacheArray = $this->findInCache($type, $UUID);
        $contentCache = ($cacheArray) ? $this->findInCacheById($cacheArray['id']) : new ContentCache();
        $contentCache->setType($type);
        $contentCache->setUuid($UUID);
        $contentCache->setValue($value);
        $em = $this->doctrine->getManager('edcoms_cms');
        $em->persist($contentCache);
        // only flush the database if requested - we might be in the middle of something! \\
        if ($flush) {
            $em->flush();
        }
    }

    /**
     * Prepare an array of custom fields for display on the front end
     * This function looks for fields with a parent and tranforms these to be subfields of their parent
     *
     * @param $customFields
     * @return array
     */
    public function prepareCustomFieldsForDisplay($customFields)
    {
        //find fields that have a parent set and move them to be a sub-field of their parent
        //collect the subfields keyed by parent id
        $subFields = [];
        foreach ($customFields as $k => &$customField) {
            if (!is_null($customField['parent'])) {
                //remove the parent object and replace it with the parent id for use when POSTing back to the backend
                $customField['parent'] = $customField['parent']['id'];
                $subFields[$customField['parent']][] = $customField;
                unset($customFields[$k]);
            }

            $customField['subfields'] = $this->serializeCustomFieldChildren($customField);

        }

        //add them to the parent
        if (count($subFields)>0) {
            foreach ($customFields as &$customField) {
                if (in_array($customField['id'], array_keys($subFields))) {
                    $customField['subfields'] = $subFields[$customField['id']];
                }
            }
        }

        //return updated custom fields, also reset array keys
        return array_values($customFields);
    }

    /**
     * @param array|CustomFieldData $customFieldsData
     *
     * @return array
     */
    public function prepareCustomFieldsDataForDisplay(array $customFieldsData){
        $serializedCustomFieldData = [];
        foreach ($customFieldsData as $cfd){
            /** @var CustomFieldData $cfd */
            if($cfd->getCustomFields()){
                $serializedCustomFieldData[$cfd->getCustomFields()->getId()] = $cfd->getValue();
            }
        }
        return $serializedCustomFieldData;
    }

    /**
     * Serialize CustomField children. By default, it doesn't serialize any nested CustomField children
     * as the default value of depth parameter is 1
     * @param array $customField
     * @param int $depth
     * @return array
     */
    private function serializeCustomFieldChildren($customField, $depth=1){
        $serializedChildren= [];
        /** @var CustomFields $child */
        foreach ($customField['children'] as $child){
            $jsonObj = $child->toJSON();
            $jsonObj['parent'] = $child->getParent() ? $child->getParent()->getId() : null;
            if($depth>1){
                // TODO For some reason, nested CustomField children ArrayCollection is empty even they exist in the database. Debugging is needed.
                $jsonObj['subfields'] = $this->serializeCustomFieldChildren($jsonObj, $depth-1);
            }
            $serializedChildren[] = $jsonObj;
        }
        return $serializedChildren;
    }

}
