<?php

namespace app\models;

use Yii;
use yii\base\Model;

//define('IMAGE_COMPARE_SHAPE_RATIO_THRESHOLD', 0.01);

// Image will be same if difference less than 0.1 
define('IMAGE_COMPARE_SIMILARITY_THRESHOLD', 0.1);

// Reference: https://kosinix.github.io/grafika/compare-images.html
define('IMAGE_COMPARE_HAMMING_DISTANCE_THRESHOLD', 10);

/*
* Compare 2 Images for Similarity Check
*/
class ImageCompare extends Model
{
    private   $folder;
    
    /**
     * Constructor
     * Initialize variable
     */
    public function __construct() {
        $this->folder = \Yii::$app->basePath.'/web/uploads';
    }

    /**
     * Compare two image to chec similarity
     * @param string $inputImg
     * @param string $scrapeImg
     * @return boolean
     */
    public function compare($inputImg, $scrapeImg) {

        
        if (!$this->validImage($this->folder."/local/".$inputImg)) {
            return false;
        }

        // Compared to cryptographic hash functions like MD5 and SHA1 between two image
        if ($this->compareHash($inputImg, $scrapeImg)) {
            return true;
        }
        
        /**
         * Compare 2 images and get their hamming distance
         * The hamming distance is used to compare hashes. Low values will indicate that the images are similar or the same, high values indicate that the images are different. 
         */
        if ($this->hammingDistance($inputImg, $scrapeImg)) {
            return true;
        }
        
        /*
        # Compute signatures for two images
        $cvec1 = puzzle_fill_cvec_from_file($this->folder."/local/logo_small_health_1.png");
        $cvec2 = puzzle_fill_cvec_from_file($this->folder."/scrape/logo_small_health_1.png");

        # Compute the distance between both signatures
        $d = puzzle_vector_normalized_distance($cvec1, $cvec2);

        # Are pictures similar?
        if ($d < PUZZLE_CVEC_SIMILARITY_LOWER_THRESHOLD) {
          echo "Pictures are looking similar\n";
        } else {
          echo "Pictures are different, distance=$d\n";
        }

        # Compress the signatures for database storage
        //$compress_cvec1 = puzzle_compress_cvec($cvec1);
        //$compress_cvec2 = puzzle_compress_cvec($cvec2);
        */

        // Get GIS for each
        //$image1Gis = getimagesize($image1);
        //$image2Gis = getimagesize($image2);

        // Both the same kind of file?  -- No need 
        /*if ($image1Gis['mime'] !== $image2Gis['mime']) {
            //'Not the same kind of file'
            return false;
        }*/

        // Same shape?
        /*$image1Ratio = $image1Gis[0]/$image1Gis[1];
        $image2Ratio = $image2Gis[0]/$image2Gis[1];
        $ratioDiff = abs($image1Ratio - $image2Ratio);
        if ($ratioDiff >= IMAGE_COMPARE_SHAPE_RATIO_THRESHOLD) {
            //'Not the same shape. Ratios: '.$image1Ratio.' '.$image2Ratio.'; Difference: '.$ratioDiff);
            return false;
        }*/

        // init the image objects
        $image1 = new \Imagick();
        $image2 = new \Imagick();

        // set the fuzz factor (must be done BEFORE reading in the images)
        //$image1->SetOption('fuzz', '2%');
        
        // read in the images
        $image1->readImage($this->folder."/local/".$inputImg);
        $handle = fopen($scrapeImg, 'rb');
        $image2->readImageFile($handle);
        //$image2->readImage($this->folder."/scrape/".$scrapeImg);

        // compare the images using METRIC=1 (Absolute Error)
        $result = $image1->compareImages($image2, \Imagick::METRIC_MEANSQUAREERROR);
        //->compareImages($image2, 1);

        // compare the difference result with threshold value
        if (isset($result[1]) && $result[1] > IMAGE_COMPARE_SIMILARITY_THRESHOLD) {
            return false;
        }

        return true;
    }


    /**
     * Compared to cryptographic hash functions like MD5 and SHA1 between two image
     * With cryptographic hashes, the hash values are random. The data used to generate the hash acts like a random seed, so the same data will generate the same result, but different data will create different results. Comparing two SHA1 hash values really only tells you two things. If the hashes are different, then the data is different. And if the hashes are the same, then the data is likely the same. 
     * @param string $inputImg
     * @param string $scrapeImg
     * @return boolean
     */
    public function compareHash($inputImg, $scrapeImg) {

        $inputImgSha1Hash = sha1_file($this->folder."/local/".$inputImg);
        $inputImgMd5Hash = md5_file($this->folder."/local/".$inputImg);

        //$scrapeImgContent = file_get_contents($scrapeImg); 
        $scrapeImgSha1Hash = sha1_file($scrapeImg);
        $scrapeImgMd5Hash = md5_file($scrapeImg);

        if (($inputImgSha1Hash == $scrapeImgSha1Hash) || ($inputImgMd5Hash == $scrapeImgMd5Hash)) {
           return true;
        }

        return false;
    
    }


    /**
     * Calculate Hamming Distance between two image
     * Compare two image similarity
     *  perceptual hashes can be compared --giving you a sense of similarity between the two data sets
     * Reference: https://github.com/mcuelenaere/php-imagehash
     * Reference: https://kosinix.github.io/grafika/compare-images.html
     * Though the library is under development, it's work fine
     * @param string $inputImg
     * @param string $scrapeImg
     * @return boolean
     */
    public function hammingDistance($inputImg, $scrapeImg) {

        $hasher = new \Jenssegers\ImageHash\ImageHash();
        /*$distance = $hasher->compare($inputImg, $scrapeImg);*/

        $hash1 = $hasher->hash($this->folder."/local/".$inputImg);
        $hash2 = $hasher->hash($scrapeImg);
        $distance = $hasher->distance($hash1, $hash2);
        
        //echo $distance.' '.$inputImg.' '.$scrapeImg; exit;

        if ($distance <= IMAGE_COMPARE_HAMMING_DISTANCE_THRESHOLD) {
            return true;
        }

        return false;

    }

    /**
     * Validate image
     * @param string $image
     * @return boolean
     */
    public function validImage($image) {
        
        if (!is_file($image)) {
            return false;
        }
        if (!is_readable($image)) {
            return false;
        }
        /*if (getimagesize($image) === false) {
            return false;
        }*/
        return true;
    }
    
}
