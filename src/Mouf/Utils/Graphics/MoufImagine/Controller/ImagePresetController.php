<?php
namespace Mouf\Utils\Graphics\MoufImagine\Controller;

use Imagine\Filter\FilterInterface;
use Imagine\Image\AbstractImagine;
use Imagine\Imagick\Imagine;
use Mouf\Mvc\Splash\Controllers\Controller;
use Mouf\Mvc\Splash\UrlEntryPoint;

class ImagePresetController extends Controller{

    /**
     * @var string $url the base URL for generated images
     */
    private $url;

    /**
     * @var AbstractImagine
     */
    private $imagine;

    /**
     * @var string the folder where the original images are stored
     */
    private $originalPath;

    /**
     * @var FilterInterface[] a set of FilterInterface that will be applyied to the original image
     */
    private $filters;

    /**
     * @param string $url
     * @param string $originalPath
     * @param AbstractImagine $imagine
     * @param FilterInterface[] $filters
     */
    public function __construct($url, $originalPath, AbstractImagine $imagine, $filters){
        $this->url = $url;
        $this->originalPath = $originalPath;
        $this->imagine = $imagine;
        $this->filters = $filters;
    }

    private function image($imagePath){
        $basePath = empty($this->originalPath) ? "" : ($this->originalPath . DIRECTORY_SEPARATOR);
        $originalImagePath = ROOT_PATH . $basePath . $imagePath;
        $pathInfo = pathinfo($originalImagePath);
        $folder = $pathInfo['dirname'];
        $newFolder = str_replace(ROOT_PATH . $basePath, "", $folder);

        $image = $this->imagine->open($originalImagePath);
        foreach ($this->filters as $filter){
            $image = $filter->apply($image);
        }



        $subPath = ROOT_PATH . $this->url . DIRECTORY_SEPARATOR . $newFolder;

        if (!file_exists($subPath)){
            $oldUmask = umask();
            umask(0);
            $dirCreate = mkdir($subPath, 0775, true);
            umask($oldUmask);
            if (!$dirCreate) {
                throw new \Exception("Could't create subfolders '$subPath' in " . ROOT_PATH . $this->getSavePath());
            }
        }

        $image->save(ROOT_PATH . $this->url . DIRECTORY_SEPARATOR . $imagePath);
        $image->show("jpg");
    }

    /**
     * @URL {$this->url}/{image}
     * @param string $image
     */
    public function baseImage($image){
        $this->image($image);
    }

    /**
     * @URL {$this->url}/{path1}/{image}
     * @param string $image
     * @param string path1
     */
    public function imageLevel1($image, $path1){
        $this->image("$path1/$image");
    }

    /**
     * @URL {$this->url}/{path1}/{path2}/{image}
     * @param string $image
     * @param string path1
     * @param string path2
     */
    public function imageLevel2($image, $path1, $path2){
        $this->image("$path1/$path2/$image");
    }

    /**
     * @URL {$this->url}/{path1}/{path2}/{path3}/{image}
     * @param string $image
     * @param string path1
     * @param string path2
     * @param string path3
     */
    public function imageLevel3($image, $path1, $path2, $path3){
        $this->image("$path1/$path2/$path3/$image");
    }

    /**
     * @URL {$this->url}/{path1}/{path2}/{path3}/{path4}/{image}
     * @param string $image
     * @param string path1
     * @param string path2
     * @param string path3
     * @param string path4
     */
    public function imageLevel4($image, $path1, $path2, $path3, $path4){
        $this->image("$path1/$path2/$path3/$path4/$image");
    }

    /**
     * @URL {$this->url}/{path1}/{path2}/{path3}/{path4}/{path5}/{image}
     * @param string $image
     * @param string path1
     * @param string path2
     * @param string path3
     * @param string path4
     * @param string path5
     */
    public function imageLevel5($image, $path1, $path2, $path3, $path4, $path5){
        $this->image("$path1/$path2/$path3/$path4/$path5/$image");
    }

}