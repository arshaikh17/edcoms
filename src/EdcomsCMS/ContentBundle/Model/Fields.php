<?php
namespace AppBundle\Model;
// these are static lists for the CMS \\
class Fields {
    private $fields=[];
    public function __construct() {
        $this->fields['gender'] = [
            'Male',
            'Female',
            'Prefer not to say'
        ];
        $this->fields['regions'] = [
            'Wales',
            'Scotland',
            'N Ireland',
            'London',
            'South East of England (excluding London)',
            'South West of England',
            'East of England',
            'East Midlands',
            'West Midlands',
            'Yorkshire and Humberside',
            'North East of England',
            'North West of England',
            'Another area of the UK',
            'Don\'t know'
        ];
        $this->fields['disabled'] = [
            'Yes, I have a disability',
            'Yes, I have a long term health condition',
            'No, neither',
            'Prefer not to say'
        ];
        $this->fields['ethnic'] = [
            'White',
            'Mixed ethnic',
            'Asian/Asian UK',
            'Black / African / Caribbean / Black UK',
            'Other',
            'Prefer not to say'
        ];
        $this->fields['religion'] = [
            'Christian',
            'Buddhist',
            'Hindu',
            'Jewish',
            'Muslim',
            'Sikh',
            'Other',
            'No religion',
            'Prefer not to say'
        ];
        $this->fields['welsh'] = [
            'Yes',
            'No',
            'Prefer not to say'
        ];
        $this->fields['northern_ireland'] = [
            'Protestant',
            'Catholic',
            'Other religion',
            'No religion',
            'Prefer not to say',
            'Not applicable'
        ];
    }
    public function get($field)
    {
        if (isset($this->fields[$field])) {
            return $this->fields[$field];
        }
        return 'not_found';
    }
}