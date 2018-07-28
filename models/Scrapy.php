<?php

namespace app\models;

use Yii;
use yii\base\Model;

use Goutte\Client;
use GuzzleHttp\Client as GuzzleClient;
use app\models\ImageCompare;

/*
* Scrapy is the model to scrape data from a specified URL
*/
class Scrapy extends Model
{
    /**
     * Used to initialize filters html tag
     * Format: array('img' => array('src', 'alt'))
     */
    protected $_filters = array();
    protected $_matchedImages = array();
    protected $_matchedTags = array();
    protected $_scrapeData = array();
    protected $_saveScrapedImage = false;
    private   $client = null;
    private   $url = null;
    private   $folder;

    /**
     * Constructor
     * Initialize variable
     */
    public function __construct() {
        $this->client = new Client();
        $guzzleClient = new GuzzleClient(array(
            'timeout' => 60,
        ));
        $this->client->setClient($guzzleClient);
        $this->folder = \Yii::$app->basePath.'/web/uploads';
    }

    /**
     * Set Save Scraped Image in local
     * @param boolean $saveImage
     * @return \app\models\Scrapy
     */
    public function saveScrapedImage($saveImage = false) {
        $this->_saveScrapedImage = $saveImage;
        return $this;
    }

    /**
     * Match Submited Image against Scraped Image
     * @param array $filters
     * @return \app\models\Scrapy
     */
    public function setFilters($filters = array()) {
        if ($filters) {
            $this->_filters = $filters; 
        }
        return $this;
    }

    /**
     * Match Submited Image against Scraped Image
     * @param string $url
     * @return \app\models\Scrapy
     */
    public function setUrl($url = '') {
        $this->url = $url;
        return $this;
    }


    /**
     * Match Submited Image against Scraped Image
     * @return \app\models\Scrapy
     */
    public function scrapeData() {

        if ($this->url != '' && $this->_filters) {
            
            $crawler = $this->client->request('GET', $this->url);
            $_scrapeData = array();
            $saveScrapedImage = $this->_saveScrapedImage;
            foreach($this->_filters as $key => $attributes) {
                $crawler->filter($key )
                    ->each(function ($node) use($attributes, &$_scrapeData, $saveScrapedImage) {
                    $scrapeData = array();
                    foreach($attributes as $attribute) {
                        $scrapeData[$attribute] =  trim($node->attr($attribute));
                        if ($attribute == 'src') {
                            if ($saveScrapedImage) {
                                $scrapeData['local'] = $this->saveHttpImage($scrapeData[$attribute]);
                            } else {
                                $scrapeData['local'] = basename($scrapeData[$attribute]);
                            }
                        }
                    }
                    $_scrapeData[] = $scrapeData;
                });
            }

            $this->_scrapeData = $_scrapeData;
        }

        //echo '<pre />'; print_r($this->_scrapeData);

        return $this;

    }

    /**
     * Match Submited Image against Scraped Image
     * @return array
     */
    public function getScrapeDate() {
        return $this->_scrapeData;
    }

    /**
     * Match Submited Image against Scraped Image
     * @param string $image
     * @return array
     */
    public function getDataByMatchImage($image = '') {

        $imageCompare  = new ImageCompare();
        if ($image != '') {
            foreach($this->_scrapeData as $data) {
                $data['src'] = str_replace($this->url, '', $data['src']);
                $data['src'] = $this->url.$data['src'];
                if ($imageCompare->compare($image, $data['src'])) {
                    $this->_matchedImages[] = $data;
                }
            }
        }

        return $this->_matchedImages;

    }

    /**
     * Match Search Tags against Image filename and alt
     * @param array $searchTags
     * @return array
     */
    public function getDataByMatchTags($searchTags = array()) {

        if ($searchTags) {
            // Create a pattern to match data
            $pattern = '';
            foreach($searchTags as $tag) {
                if ($pattern != '') {
                    $pattern .= '|';
                }
                $pattern .= strtolower($tag);
            }
            $pattern = '/('.$pattern.')/u'; // u used for unicode matching
            foreach($this->_scrapeData as $data) {
                preg_match($pattern, strtolower(basename($data['src'])), $matches, PREG_OFFSET_CAPTURE);
                if ($matches) {
                    $this->_matchedTags[] = $data;
                } else {
                    preg_match($pattern, strtolower($data['alt']), $matches, PREG_OFFSET_CAPTURE);
                    if ($matches) {
                        $this->_matchedTags[] = $data;
                    }
                }
            }
        }

        return $this->_matchedTags;

    }

    /**
     * Save Image in local pc from Image URL
     * @param string $image
     * @param string $url
     * @return string
     */
    public function saveHttpImage($image = '', $url = '') {

        if ($url == '') {
            $url = $this->url;
        }

        $image = str_replace($url, '', $image);
        $image = $url.$image;
        $imageName = \Yii::$app->security->generateRandomString().basename($image);
        $path = $this->folder."/scrape/".$imageName;
        if (!file_exists($path)) {
            $file = @file_get_contents($image);
            $insert = @file_put_contents($path, $file);
            if (!$insert) {
                throw new \Exception('Failed to write image');
            }
        }
        
        return $imageName;

    }
    
}
