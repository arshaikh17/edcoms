<?php

namespace EdcomsCMS\ContentBundle\Helpers;

use EdcomsCMS\ContentBundle\Entity\SearchPhrases;

/**
 * Description of NaturalLanguageHelper
 *
 * @author richard
 */
class NaturalLanguageHelper {
    private $doctrine;
    private $term;
    /**
     *
     * @var pspell_dictionary
     */
    private $pspell;
    private $suggestions;
    public function __construct($doctrine)
    {
        $this->doctrine = $doctrine;
        $this->searchPhrases = $doctrine->getManager('edcoms_cms')->getRepository('EdcomsCMSContentBundle:SearchPhrases');
//        $this->pspell = \pspell_new('en');
    }
    public function getSuggestions($term)
    {
        $suggestions = $this->searchPhrases->findByTerm($term);
        if ($suggestions) {
            $this->term = $term;
            usort($suggestions, array(&$this, 'sortByRelevance'));
            array_walk($suggestions, array(&$this, 'getPhrase'));
            return $suggestions;
        }
        return [];
    }
    public function sortByRelevance(SearchPhrases $a, SearchPhrases $b)
    {
        return (levenshtein($this->term, $a->getPhrase()) < levenshtein($this->term, $b->getPhrase())) ? -1 : ($a->getTotalUsed() < $b->getTotalUsed()) ? -1 : 1;
    }
    public function getPhrase(SearchPhrases &$a)
    {
        $a = $a->getPhrase();
    }
    public function saveSuggestion($term)
    {
        $phrase = $this->searchPhrases->findOneBy(['phrase'=>$term]);
        if ($phrase) {
            $phrase->setTotalUsed($phrase->getTotalUsed()+1);
            $phrase->setLastUsed(new \DateTime());
        } else {
            $phrase = new SearchPhrases();
            $phrase->setFirstUsed(new \DateTime());
            $phrase->setLastUsed(new \DateTime());
            $phrase->setTotalUsed(1);
            $phrase->setPhrase($term);
        }
        $em = $this->doctrine->getManager('edcoms_cms');
        $em->persist($phrase);
        $em->flush();
    }
    /**
     * Take a phrase and tokenize to an array - default method is to use whitespace as split
     * @param string $phrase
     * @return array
     */
    private function tokenize($phrase)
    {
        return explode(' ', $phrase);
    }
    public function spellSuggest($phrase)
    {
        $tokens = $this->tokenize($phrase);
        array_walk($tokens, array(&$this, 'spellSuggestWord'));
        return $this->suggestions;
    }
    /**
     * Suggest alternative words if the word isn't in our dictionary
     * @param string $word
     * @return array Suggestions
     */
    public function spellSuggestWord($word)
    {
//        if (!pspell_check($this->pspell, $word)) {
//            $suggestions = \pspell_suggest($this->pspell, $word);
//            $this->suggestions[$word] = $suggestions;
//        }
        return true;
    }
}
