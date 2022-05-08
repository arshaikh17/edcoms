<?php
namespace EdcomsCMS\ContentBundle\Model;
/**
 * This class contains generic lists for job titles, accepted mimetypes, subjects and salutations.
 * Use them if required, but only modify if you want to change them across all projects here.
 * If you don't you must use the Fields method in the model of the projects relevant bundle.
 * A copy is included in the directory here for reference
 * It MUST be included as a service in your project
 * Fields:
 *     class: AppBundle\Model\Fields
 */

class CMSFields {
    private $fields=[];
    public function __construct() {
        $this->fields['mimetype'] = [
            'application/zip' => 'zip',
            'application/msword' => 'Word (.doc)',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'Word (.docx)',
            'application/vnd.ms-excel' => 'Excel (.xls)',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'Excel (.xlsx)',
            'application/vnd.ms-powerpoint' => 'Powerpoint (.ppt)',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'Powerpoint (.pptx)',
            'application/pdf' => 'PDF',
            'text/csv' => 'CSV',
            'text/plain' => 'Text',
            'image/jpeg' => 'Image jpeg',
            'image/png' => 'Image png',
            'image/gif' => 'Image gif',
            'image/bmp' => 'Image bmp',
            'image/svg+xml' => 'Image svg',
            'video/mp4' => 'Video mp4',
            'video/x-m4v' => 'Video m4v',
            'video/quicktime' => 'Video mov',
            'audio/mp4' => 'Audio mp4',
            'text/vtt' => 'Video Text Track (captions)',
            '*/*' => 'Generic'

        ];
        $this->fields['job_titles'] = [
            'Art Teacher',
            'Biology Teacher',
            'Business Studies Teacher',
            'Chemistry Teacher',
            'Citizenship Co-ordinator',
            'Class Teacher',
            'Clerical Staff',
            'Community Group leader',
            'Consultant',
            'Deputy Head Teacher',
            'Design and Technology Teacher',
            'Drama Teacher',
            'Early Years Co-ordinator',
            'Eco-Schools Co-ordinator',
            'English Teacher',
            'Food Technology Teacher',
            'Geography Teacher',
            'Governor',
            'Head of Geography',
            'Head of Science',
            'Head Teacher',
            'Healthy Schools Co-ordinator',
            'History Teacher',
            'Home Educator',
            'Humanities Teacher',
            'information/Technology Teacher',
            'Local Authority/County Council Officer',
            'Mathematics Teacher',
            'Modern Languages Teacher',
            'Music Teacher',
            'P.E./Games Teach',
            'Physics Teacher',
            'PTA Member',
            'Religious Education Teacher',
            'Science Teacher',
            'Special Needs Co-ordinator',
            'Teaching Assistant',
            'Other'
        ];
        $this->fields['subjects'] = [
            'Art',
            'Biology',
            'Business Studies',
            'Citizenship',
            'Computing',
            'Drama',
            'English',
            'Geography',
            'History',
            'Information and Communication Technology',
            'Languages',
            'Literacy',
            'Mathematics',
            'Modern Studies',
            'Music',
            'Numeracy',
            'Physical Education',
            'Personal, Social and Health Education',
            'Religious Education',
            'Science',
            'Mathematics',
            'Numeracy'
        ];
        $this->fields['titles'] = [
            'Mr',
            'Mrs',
            'Ms',
            'Miss',
            'Dr',
            'Rev',
            'Prof',
            'Br',
            'Sr'
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