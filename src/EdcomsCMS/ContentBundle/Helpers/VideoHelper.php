<?php

namespace EdcomsCMS\ContentBundle\Helpers;

/**
 * This helper class is used to handle the Video API methods and actions
 *
 * @author richard
 */
class VideoHelper extends APIHelper {

    public function __construct($doctrine,$container,$tokenStorage) {
        parent::__construct($doctrine,$container,$tokenStorage);
        parent::generateToken('video');
    }

    
    public function uploadVideo($inputformats, $title, $description, $file) {
        $token = $this->getToken();
        $response = $this->getClient()->request('POST', '/API/videos/upload', ['headers' => ['Authorization' => "Bearer $token"],
            'multipart' => [
                ["name" => "title",
                    "contents" => "$title"
                ],
                ["name" => "description",
                    "contents" => "$description"
                ],
                ["name" => "formats",
                    "contents" => json_encode($inputformats),
                ],
                ["name" => "video",
                    "contents" => fopen("$file", "r"),
                    "Content-Type" => "multipart/form-data"
                ],
            ]
        ]);
        if($response->getStatusCode() == 200) {
            $contents = $response->getBody()->getContents();
            $data = json_decode($contents);
        } else {
            $data->data = new \stdClass();
            $data->data->error = $response->getStatusCode();
            $data->data->success = false;
        }
        return $data;
    }

    public function renameVideo($videoID, $title) {
        $token = $this->getToken();
        $response = $this->getClient()->request('POST', '/API/videos/rename', ['headers' => ['Authorization' => "Bearer $token"],
            'multipart' => [
                ["name" => "title",
                 "contents" => "$title"
                ],
                ["name"=>"id",
                 "contents" =>$videoID
                ]
            ]
        ]);
        if($response->getStatusCode() == 200){
            $contents = $response->getBody()->getContents();
            $data = json_decode($contents);
        } else {
            $data->data = new \stdClass();
            $data->data->error = $response->getStatusCode();
            $data->data->success = false;
        }
        return $data;
    }

    public function getVideoDetails($videoID) {
        $response = $this->getClient()->get("/API/videos/$videoID");
        if($response->getStatusCode() === 200) {
            $contents = $response->getBody()->getContents();
            $data = json_decode($contents);
        } else {
            $data->data = new \stdClass();
            $data->data->error = $response->getStatusCode();
            $data->data->success = false;
        }
        return $data;
    }

    public function getAvailableVideos() {
        $response = $this->getClient()->get('/API/videos');
        if($response->getStatusCode() == 200) {
            $contents = $response->getBody()->getContents();
            $data = json_decode($contents);
        } else {
            $data->data = new \stdClass();
            $data->data->error = $response->getStatusCode();
            $data->data->success = false;
        }
        return $data;
    }

    public function getFormats() {
        $response = $this->getClient()->get('/API/sites/formats');
        if($response->getStatusCode() === 200) {
            $contents = $response->getBody()->getContents();
            $data = json_decode($contents);
        } else {
            $data->data = new \stdClass();
            $data->data->error = $response->getStatusCode();
            $data->data->success = false;
        }
        return $data;
    }

    public function deleteVideo($videoID) {
        $response = $this->getClient()->delete("/API/videos/$videoID");
        if($response->getStatusCode() == 200) {
            $contents = $response->getBody()->getContents();
            $data = json_decode($contents);
        } else {
            $data->data = new \stdClass();
            $data->data->error = $response->getStatusCode();
            $data->data->success = false;
        }
        return $data;
    }

    public function getQualityandSize() {
        $response = $this->getClient()->get('/API/sites/getDefaultQualityandSize');
        if($response->getStatusCode() == 200) {
            $contents = $response->getBody()->getContents();
            $data = json_decode($contents);
        } else {
            $data->data = new \stdClass();
            $data->data->error = $response->getStatusCode();
            $data->data->success = false;
        }
        return $data;
    }

    public function getInputFormats() {
        $data = array();
        $formats = $this->getFormats();
        $qualitAndSize = $this->getQualityandSize();
        foreach ($formats->data as $format) {
            foreach ($qualitAndSize->data as $qtSize) {
                $data[] = array('format' => $format, 'quality' => $qtSize->quality, 'size' => $qtSize->size);
            }
        }
        return $data;
    }

}
